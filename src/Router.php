<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use InvalidArgumentException;

use function array_combine;
use function array_filter;
use function array_intersect_key;
use function array_key_exists;
use function count;
use function explode;
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
 * The key of a route is its 'ID'.  `action` can be anything: the name of a method; a callable; whatever.
 *
 * A matched route, the return value of `match()`, will have an additional element, `parameters`, containing the values
 * of any parameters found in the path.
 */
class Router
{
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
     * @param array<string, string> $serverVars
     * @return array{path: string, action: mixed, parameters: string[]}|null
     * @throws InvalidArgumentException If the request URI is invalid.
     */
    public function match(array $serverVars): ?array
    {
        if (!array_key_exists('REQUEST_URI', $serverVars)) {
            throw new InvalidArgumentException('There is no request URI in the server vars.');
        }

        $requestUri = $serverVars['REQUEST_URI'];

        $requestUriParts = null;
        $requestUriIsValid = (bool) preg_match('~^(?P<path>/.*?)(\?.*)?$~', $requestUri, $requestUriParts);

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
     * @todo Validation.
     */
    public function generatePath($routeId, array $parameters = []): string
    {
        $route = $this->getRoutes()[$routeId];
        $path = $route['path'];

        if (!$parameters) {
            return $path;
        }

        $placeholders = $this->getRoutePlaceholders($routeId);

        $filteredParameters = array_intersect_key($parameters, $placeholders);

        foreach ($placeholders as $parameterName => $placeholder) {
            $path = str_replace($placeholder, (string) $filteredParameters[$parameterName], $path);
        }

        return $path;
    }

    /**
     * @param array<string|int, array{path: string, action: mixed}> $routes
     */
    private function setRoutes(array $routes): self
    {
        $this->routes = $routes;
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
                ? array_combine($matches[1], $matches[0])
                : []
            ;

            $this->placeholdersByRouteId[$routeId] = $placeholders;
        }

        return $this->placeholdersByRouteId[$routeId];
    }
}
