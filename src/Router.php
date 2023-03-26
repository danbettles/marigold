<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use InvalidArgumentException;
use OutOfBoundsException;

use function array_combine;
use function array_filter;
use function array_intersect_key;
use function array_key_exists;
use function array_keys;
use function array_replace;
use function count;
use function explode;
use function implode;
use function preg_match;
use function preg_match_all;
use function str_replace;
use function strpos;

use const ARRAY_FILTER_USE_KEY;
use const false;
use const null;
use const true;

/**
 * Maps paths to actions.
 *
 * An array of routes looks like:
 *
 * [
 *     [
 *       'id' => 'showBlogPost',
 *       'path' => '/posts/{postId}',
 *       'action' => ['FooBar', 'baz'],
 *     ],
 *     [
 *       'id' => 'showArticle',
 *       'path' => '/articles/{articleId}',
 *       'action' => ShowArticleAction::class,
 *     ],
 *     [
 *       'id' => 'showAboutPage',
 *       'path' => '/about',
 *       'action' => ShowArticleAction::class,
 *       'parameters' => [
 *          'articleId' => 123,
 *       ],
 *     ],
 *     // ...
 * ]
 *
 * `action` can be anything: the name of a method; a callable; whatever's appropriate for the app.
 *
 * A matched route, the return value of `match()`, will always have an additional array element, `parameters`,
 * containing the values of any parameters found in the path.
 *
 * Default values for parameters can be supplied in the `parameters` element.
 *
 * @phpstan-type MatchedRouteArray array{id:string,path:string,action:mixed,parameters:array<string,mixed>}
 * @phpstan-type IndexedRouteArrayArray array<string,RouteArray>
 * @phpstan-type PlaceholdersArray array<string,string>
 */
class Router
{
    /**
     * @var array<string,bool>
     */
    private const ROUTE_ELEMENTS = [
        // Name => mandatory?
        'id' => true,
        'path' => true,
        'action' => true,
        'parameters' => false,
    ];

    /**
     * @phpstan-var IndexedRouteArrayArray
     */
    private array $routes;

    /**
     * @phpstan-var array<string,PlaceholdersArray>
     */
    private array $placeholdersByRouteId = [];

    /**
     * @phpstan-param RouteArray[] $routes
     */
    public function __construct(array $routes)
    {
        $this->setRoutes($routes);
    }

    private function countPathParts(string $path): int
    {
        return count(explode('/', $path));
    }

    /**
     * @phpstan-param IndexedRouteArrayArray $routes
     * @phpstan-return IndexedRouteArrayArray
     */
    private function eliminateUnmatchableRoutes(string $path, array $routes): array
    {
        $numPathParts = $this->countPathParts($path);

        $filteredRoutes = array_filter($routes, function (array $route) use ($numPathParts): bool {
            return $numPathParts === $this->countPathParts($route['path']);
        });

        return $filteredRoutes;
    }

    /**
     * @phpstan-param RouteArray $baseRoute
     * @param array<string,string> $parameters
     * @phpstan-return MatchedRouteArray
     */
    private function createMatchedRoute(
        array $baseRoute,
        array $parameters = []
    ): array {
        $baseRoute['parameters'] = array_replace(
            ($baseRoute['parameters'] ?? []),
            $parameters
        );

        return $baseRoute;
    }

    /**
     * @phpstan-return MatchedRouteArray|null
     * @throws OutOfBoundsException If there is no request URI in the server vars
     * @throws InvalidArgumentException If the request URI is invalid
     */
    public function match(HttpRequest $request): ?array
    {
        if (!array_key_exists('REQUEST_URI', $request->server)) {
            throw new OutOfBoundsException('There is no request URI in the server vars');
        }

        /** @var array{REQUEST_URI:string} */
        $serverVars = $request->server;

        $requestUriParts = null;

        $requestUriIsValid = (bool) preg_match(
            '~^(?P<path>/.*?)(\?.*)?$~',
            $serverVars['REQUEST_URI'],
            $requestUriParts
        );

        if (!$requestUriIsValid) {
            throw new InvalidArgumentException('The request URI is invalid');
        }

        $path = $requestUriParts['path'];

        $routesContainingPlaceholders = [];

        // Look for exact matches.
        foreach ($this->getRoutes() as $routeId => $route) {
            // We're looking for *exact* matches, so skip this route if its path contains placeholders.
            if (false !== strpos($route['path'], '{')) {
                $routesContainingPlaceholders[$routeId] = $route;
                continue;
            }

            if ($path === $route['path']) {
                return $this->createMatchedRoute($route);
            }
        }

        $routesToInvestigate = $this->eliminateUnmatchableRoutes($path, $routesContainingPlaceholders);

        if (!$routesToInvestigate) {
            return null;
        }

        foreach ($routesToInvestigate as $routeId => $route) {
            $pathRegExp = $route['path'];

            foreach ($this->getRoutePlaceholders($routeId) as $parameterName => $placeholder) {
                $pathRegExp = str_replace($placeholder, "(?P<{$parameterName}>.+?)", $pathRegExp);
            }

            $pathRegExp = '~^' . $pathRegExp . '$~';

            $pathParameterMatches = null;
            $routeIsAMatch = (bool) preg_match($pathRegExp, $path, $pathParameterMatches);

            if (!$routeIsAMatch) {
                continue;
            }

            return $this->createMatchedRoute(
                $route,
                array_filter($pathParameterMatches, '\is_string', ARRAY_FILTER_USE_KEY)
            );
        }

        return null;
    }

    /**
     * @param array<string,string|int> $parameters
     * @throws OutOfBoundsException If the route does not exist
     * @throws InvalidArgumentException If parameter values were missing
     */
    public function generatePath(string $routeId, array $parameters = []): string
    {
        if (!array_key_exists($routeId, $this->getRoutes())) {
            throw new OutOfBoundsException("The route, `{$routeId}`, does not exist");
        }

        $route = $this->getRoutes()[$routeId];
        $path = $route['path'];

        $placeholders = $this->getRoutePlaceholders($routeId);

        if (!$placeholders) {
            return $path;
        }

        $filteredParameters = array_intersect_key($parameters, $placeholders);

        if (count($placeholders) !== count($filteredParameters)) {
            throw new InvalidArgumentException(
                'Parameter values were missing.  Required: ' . implode(', ', array_keys($placeholders))
            );
        }

        foreach ($placeholders as $parameterName => $placeholder) {
            $path = str_replace($placeholder, (string) $filteredParameters[$parameterName], $path);
        }

        return $path;
    }

    /**
     * @phpstan-param RouteArray[] $routes
     * @throws InvalidArgumentException If there are no routes
     * @throws InvalidArgumentException If a route is missing elements
     */
    private function setRoutes(array $routes): self
    {
        if (!$routes) {
            throw new InvalidArgumentException('There are no routes');
        }

        $mandatoryRouteElements = array_filter(self::ROUTE_ELEMENTS);
        $numMandatoryRouteElements = count($mandatoryRouteElements);

        foreach ($routes as $i => $route) {
            // In reality, some routes won't be valid, hence why we need to adjust PHPStan's expectations.
            /** @phpstan-var mixed[] */
            $filteredRoute = array_intersect_key($route, self::ROUTE_ELEMENTS);

            $numMandatoryInFiltered = count(array_intersect_key($filteredRoute, $mandatoryRouteElements));

            if ($numMandatoryRouteElements !== $numMandatoryInFiltered) {
                throw new InvalidArgumentException(
                    "The route at index `{$i}` is missing elements.  " .
                    'Required: ' . implode(', ', array_keys($mandatoryRouteElements)) . '.'
                );
            }

            /** @phpstan-var RouteArray $filteredRoute */

            $routeId = $route['id'];
            $this->routes[$routeId] = $filteredRoute;
        }

        return $this;
    }

    /**
     * @phpstan-return IndexedRouteArrayArray
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @phpstan-return PlaceholdersArray
     */
    private function getRoutePlaceholders(string $routeId): array
    {
        if (!array_key_exists($routeId, $this->placeholdersByRouteId)) {
            $route = $this->getRoutes()[$routeId];

            $placeholderNamePattern = '[a-zA-Z]+';
            $matches = null;
            $matched = (bool) preg_match_all("~\{($placeholderNamePattern)\}~", $route['path'], $matches);

            /** @phpstan-var PlaceholdersArray */
            $placeholders = $matched
                // Name => placeholder.  E.g. "articleId" => "{articleId}".
                ? array_combine($matches[1], $matches[0])
                : []
            ;

            $this->placeholdersByRouteId[$routeId] = $placeholders;
        }

        return $this->placeholdersByRouteId[$routeId];
    }
}
