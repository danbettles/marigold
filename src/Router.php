<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use InvalidArgumentException;
use OutOfBoundsException;

use function array_combine;
use function array_filter;
use function array_intersect_key;
use function array_keys;
use function array_key_exists;
use function count;
use function explode;
use function implode;
use function preg_match;
use function preg_match_all;
use function strpos;
use function str_replace;

use const ARRAY_FILTER_USE_KEY;
use const false;
use const null;

/**
 * Maps paths to actions.
 *
 * An array of routes looks like:
 *
 * [
 *     'posts' => [
 *       'path' => '/posts/{postId}',
 *       'action' => ['foo', 'bar'],
 *     ],
 * ]
 *
 * The key of a route is its 'ID'.  `action` can be anything: the name of a method; a callable; whatever's appropriate
 * for the app.
 *
 * A matched route, the return value of `match()`, will have an additional element, `parameters`, containing the values
 * of any parameters found in the path.
 */
class Router
{
    /**
     * @var array{path: null, action: null}>
     */
    private const EMPTY_ROUTE = [
        'path' => null,
        'action' => null,
    ];

    /**
     * @var array<string|int, array{path: string, action: mixed}>
     */
    private array $routes;

    /**
     * @var array<string|int, array<string, string>>
     */
    private array $placeholdersByRouteId = [];

    /**
     * @param array<string|int, array{path: string, action: mixed}> $routes
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
     * @param array<string|int, array{path: string, action: mixed}> $routes
     * @return array<string|int, array{path: string, action: mixed}>
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
     * @return array{path: string, action: mixed, parameters: string[]}|null
     * @throws OutOfBoundsException If there is no request URI in the server vars.
     * @throws InvalidArgumentException If the request URI is invalid.
     */
    public function match(HttpRequest $request): ?array
    {
        if (!array_key_exists('REQUEST_URI', $request->server)) {
            throw new OutOfBoundsException('There is no request URI in the server vars.');
        }

        $requestUriParts = null;

        $requestUriIsValid = (bool) preg_match(
            '~^(?P<path>/.*?)(\?.*)?$~',
            $request->server['REQUEST_URI'],
            $requestUriParts
        );

        if (!$requestUriIsValid) {
            throw new InvalidArgumentException('The request URI is invalid.');
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
                $route['parameters'] = [];

                return $route;
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

            $route['parameters'] = array_filter($pathParameterMatches, '\is_string', ARRAY_FILTER_USE_KEY);

            return $route;
        }

        return null;
    }

    /**
     * @param string|int $routeId
     * @param array<string, string|int> $parameters
     * @throws OutOfBoundsException If the route does not exist.
     * @throws InvalidArgumentException If parameter values were missing.
     */
    public function generatePath($routeId, array $parameters = []): string
    {
        if (!array_key_exists($routeId, $this->getRoutes())) {
            throw new OutOfBoundsException("The route, `{$routeId}`, does not exist.");
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
                'Parameter values were missing.  Required: ' . implode(', ', array_keys($placeholders)) . '.'
            );
        }

        foreach ($placeholders as $parameterName => $placeholder) {
            $path = str_replace($placeholder, (string) $filteredParameters[$parameterName], $path);
        }

        return $path;
    }

    /**
     * @param array<string|int, array{path: string, action: mixed}> $routes
     * @throws InvalidArgumentException If there are no routes.
     * @throws InvalidArgumentException If a route is missing elements.
     */
    private function setRoutes(array $routes): self
    {
        if (!$routes) {
            throw new InvalidArgumentException('There are no routes.');
        }

        $numExpectedRouteEls = count(self::EMPTY_ROUTE);

        foreach ($routes as $id => $route) {
            $filteredRoute = array_intersect_key($route, self::EMPTY_ROUTE);

            // In reality, some routes may not be what we were hoping for, hence why we need to adjust PHPStan's
            // expectations.
            /** @phpstan-var mixed[] $filteredRoute */
            if ($numExpectedRouteEls !== count($filteredRoute)) {
                throw new InvalidArgumentException(
                    "Route `{$id}` is missing elements.  Required: " . implode(', ', array_keys(self::EMPTY_ROUTE)) . '.'
                );
            }

            /** @var array{path: string, action: mixed} $filteredRoute */
            $this->routes[$id] = $filteredRoute;
        }

        return $this;
    }

    /**
     * @return array<string|int, array{path: string, action: mixed}>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @param string|int $routeId
     * @return array<string, string>
     */
    private function getRoutePlaceholders($routeId): array
    {
        if (!array_key_exists($routeId, $this->placeholdersByRouteId)) {
            $route = $this->getRoutes()[$routeId];

            $placeholderNamePattern = '[a-zA-Z]+';
            $matches = null;
            $matched = (bool) preg_match_all("~\{($placeholderNamePattern)\}~", $route['path'], $matches);

            /** @var array<string, string> */
            $placeholders = $matched
                // name => placeholder
                ? array_combine($matches[1], $matches[0])
                : []
            ;

            $this->placeholdersByRouteId[$routeId] = $placeholders;
        }

        return $this->placeholdersByRouteId[$routeId];
    }
}
