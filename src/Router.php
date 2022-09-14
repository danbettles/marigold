<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use InvalidArgumentException;

use function array_filter;
use function array_key_exists;
use function count;
use function explode;
use function preg_match;
use function preg_replace;
use function strpos;

use const ARRAY_FILTER_USE_KEY;
use const false;
use const null;

/**
 * Maps paths to actions.
 *
 * A route looks like:
 *
 *   [
 *     'path' => '/posts/{postId}',
 *     'action' => ['foo', 'bar'],
 *   ]
 *
 * `action` can be anything: the name of a method; a callable; whatever.
 *
 * A matched route, the return value of `match()`, will have an additional element, `parameters`, containing the values
 * of any parameters found in the path.
 *
 * @todo `generatePath()`.  Each route will need a name.
 */
class Router
{
    /**
     * @var array<int, array{path: string, action: mixed}>
     */
    private array $routes;

    /**
     * @param array<int, array{path: string, action: mixed}> $routes
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
     * @param array<int, array{path: string, action: mixed}> $routes
     * @return array<int, array{path: string, action: mixed}>
     */
    private function eliminateUnmatchableRoutes(string $path, array $routes): array
    {
        $numPathParts = $this->countPathParts($path);

        $filteredRoutes = array_filter($routes, function (array $route) use ($numPathParts): bool {
            return $numPathParts === $this->countPathParts($route['path']);
        });

        return $filteredRoutes;
    }

    private function createPathRegExpFromRoutePath(string $routePath): string
    {
        $placeholderNamePattern = '[a-zA-Z]+';
        $regExp = '~^' . preg_replace("~\{($placeholderNamePattern)\}~", '(?P<$1>.+?)', $routePath) . '$~';

        return $regExp;
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
        foreach ($this->getRoutes() as $route) {
            // We're looking for *exact* matches, so skip this route if its path contains placeholders.
            if (false !== strpos($route['path'], '{')) {
                $routesContainingPlaceholders[] = $route;
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

        foreach ($routesToInvestigate as $route) {
            $pathRegExp = $this->createPathRegExpFromRoutePath($route['path']);
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
     * @param array<int, array{path: string, action: mixed}> $routes
     */
    private function setRoutes(array $routes): self
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * @return array<int, array{path: string, action: mixed}>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
