<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\HttpRequest;
use DanBettles\Marigold\Router;
use InvalidArgumentException;
use OutOfBoundsException;

class RouterTest extends AbstractTestCase
{
    // ###> Factory methods ###
    /** @param array<string, string> $serverVars */
    private function createHttpRequest(array $serverVars): HttpRequest
    {
        return new HttpRequest([], [], $serverVars);
    }

    private function createRouterWithPostsRoute(): Router
    {
        return new Router([
            [
                'id' => 'blogPostsIndex',
                'path' => '/posts',
                'action' => 'anything',
            ],
        ]);
    }
    // ###< Factory methods ###

    public function testIsInstantiable(): void
    {
        $route = [
            'id' => 'blogPostsIndex',
            'path' => '/posts',
            'action' => 'anything',
        ];

        $actualRoutes = [
            $route,
        ];

        $expectedRoutes = [
            ($route['id']) => $route,
        ];

        $router = new Router($actualRoutes);

        $this->assertSame($expectedRoutes, $router->getRoutes());
    }

    public function testThrowsAnExceptionIfThereAreNoRoutes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('There are no routes.');

        new Router([]);
    }

    /** @return array<int, array<int, mixed>> */
    public function providesRoutesContainingInvalid(): array
    {
        return [
            [
                0,
                [
                    [
                        'id' => 'invalid',
                        // `path` missing.
                        'action' => 'anything',
                    ],
                ],
            ],
            [
                0,
                [
                    [
                        'id' => 'invalid',
                        'path' => '/something',
                        // `action` missing.
                    ],
                ],
            ],
            [
                1,
                [
                    [
                        'id' => 'valid',
                        'path' => '/something',
                        'action' => 'anything',
                    ],
                    [
                        'id' => 'invalid',
                        // `path` missing.
                        'action' => 'anything',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider providesRoutesContainingInvalid
     * @param array<array{id: string, path: string, action: mixed}> $routesContainingInvalid (Using the valid type to silence PHPStan.)
     */
    public function testThrowsAnExceptionIfARouteIsInvalid(
        int $routeIndex,
        array $routesContainingInvalid
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The route at index `{$routeIndex}` is missing elements.  Required: id, path, action.");

        new Router($routesContainingInvalid);
    }

    /** @return array<int, array<int, mixed>> */
    public function providesMatchableRoutes(): array
    {
        return [
            [
                [
                    'id' => 'homepage',
                    'path' => '/',
                    'action' => 'anything',
                    'parameters' => [],
                ],
                [
                    [
                        'id' => 'homepage',
                        'path' => '/',
                        'action' => 'anything',
                    ],
                    [
                        'id' => 'articlesIndex',
                        'path' => '/articles',
                        'action' => 'anything',
                    ],
                ],
                $this->createHttpRequest(['REQUEST_URI' => '/']),
            ],
            // #1: Trailing slashes are significant:
            [
                [
                    'id' => 'pageCalledArticles',
                    'path' => '/articles',
                    'action' => 'anything',
                    'parameters' => [],
                ],
                [
                    [
                        'id' => 'pageCalledArticles',
                        'path' => '/articles',
                        'action' => 'anything',
                    ],
                    [
                        'id' => 'articlesIndex',
                        'path' => '/articles/',
                        'action' => 'anything',
                    ],
                ],
                $this->createHttpRequest(['REQUEST_URI' => '/articles?name=value']),
            ],
            // #2: Trailing slashes are significant:
            [
                [
                    'id' => 'pageCalledArticles',
                    'path' => '/articles',
                    'action' => 'anything',
                    'parameters' => [],
                ],
                [
                    [  // Still isn't a match.
                        'id' => 'articlesIndex',
                        'path' => '/articles/',
                        'action' => 'anything',
                    ],
                    [
                        'id' => 'pageCalledArticles',
                        'path' => '/articles',
                        'action' => 'anything',
                    ],
                ],
                $this->createHttpRequest(['REQUEST_URI' => '/articles?name=value']),
            ],
            // Placeholders:
            [
                [
                    'id' => 'showArticle',
                    'path' => '/articles/{id}',
                    'action' => 'anything',
                    'parameters' => ['id' => 'the-quick-brown-fox'],
                ],
                [
                    [
                        'id' => 'articlesIndex',
                        'path' => '/articles',
                        'action' => 'anything',
                    ],
                    [
                        'id' => 'showArticle',
                        'path' => '/articles/{id}',
                        'action' => 'anything',
                    ],
                ],
                $this->createHttpRequest(['REQUEST_URI' => '/articles/the-quick-brown-fox?foo=bar']),
            ],
            // Trailing slashes are significant:
            [
                [
                    'id' => 'showArticleWithTrailingSlash',
                    'path' => '/articles/{id}/',
                    'action' => 'anything',
                    'parameters' => ['id' => 'the-quick-brown-fox'],
                ],
                [
                    [
                        'id' => 'showArticle',
                        'path' => '/articles/{id}',
                        'action' => 'anything',
                    ],
                    [
                        'id' => 'showArticleWithTrailingSlash',
                        'path' => '/articles/{id}/',
                        'action' => 'anything',
                    ],
                ],
                $this->createHttpRequest(['REQUEST_URI' => '/articles/the-quick-brown-fox/']),
            ],
            // The order of routes is important:
            [
                [
                    'id' => 'showArticleById',
                    'path' => '/articles/{id}',
                    'action' => 'anything',
                    'parameters' => ['id' => 'the-quick-brown-fox'],
                ],
                [
                    [
                        'id' => 'showArticleById',
                        'path' => '/articles/{id}',
                        'action' => 'anything',
                    ],
                    [
                        'id' => 'showArticleBySlug',
                        'path' => '/articles/{slug}',
                        'action' => 'anything',
                    ],
                ],
                $this->createHttpRequest(['REQUEST_URI' => '/articles/the-quick-brown-fox']),
            ],
            // *However*, exact matches are prioritised:
            [
                [
                    'id' => 'theQuickBrownFox',
                    'path' => '/articles/the-quick-brown-fox',
                    'action' => 'anything',
                    'parameters' => [],
                ],
                [
                    [
                        'id' => 'showArticle',
                        'path' => '/articles/{id}',
                        'action' => 'anything',
                    ],
                    [
                        'id' => 'theQuickBrownFox',
                        'path' => '/articles/the-quick-brown-fox',
                        'action' => 'anything',
                    ],
                ],
                $this->createHttpRequest(['REQUEST_URI' => '/articles/the-quick-brown-fox']),
            ],
        ];
    }

    /**
     * @dataProvider providesMatchableRoutes
     * @param array{path: string, action: mixed, parameters: string[]} $expectedRoute
     * @param array<array{id: string, path: string, action: mixed}> $routes
     */
    public function testMatchAttemptsToFindAMatchingRoute(
        array $expectedRoute,
        array $routes,
        HttpRequest $request
    ): void {
        $route = (new Router($routes))
            ->match($request)
        ;

        $this->assertSame($expectedRoute, $route);
    }

    /** @return array<int, array<int, mixed>> */
    public function providesUnmatchableRoutes(): array
    {
        return [
            [
                [
                    [
                        'id' => 'blogPostsIndex',
                        'path' => '/posts',
                        'action' => 'anything',
                    ],
                    [
                        'id' => 'showBlogPost',
                        'path' => '/posts/{postId}',
                        'action' => 'anything',
                    ],
                ],
                $this->createHttpRequest(['REQUEST_URI' => '/posts/']),  // (Trailing slash.)
            ],
            [
                [
                    [
                        'id' => 'blogPostsIndex',
                        'path' => '/posts',
                        'action' => 'anything',
                    ],
                    [
                        'id' => 'showBlogPost',
                        'path' => '/posts/{postId}',
                        'action' => 'anything',
                    ],
                ],
                $this->createHttpRequest(['REQUEST_URI' => '/posts/the-quick-brown-fox/']),  // (Trailing slash.)
            ],
        ];
    }

    /**
     * @dataProvider providesUnmatchableRoutes
     * @param array<array{id: string, path: string, action: mixed}> $unmatchableRoutes
     */
    public function testMatchReturnsNullIfThereIsNoMatchingRoute(
        array $unmatchableRoutes,
        HttpRequest $request
    ): void {
        $route = (new Router($unmatchableRoutes))
            ->match($request)
        ;

        $this->assertNull($route);
    }

    public function testMatchThrowsAnExceptionIfTheRequestDoesNotContainTheRequestUri(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('There is no request URI in the server vars.');

        $this
            ->createRouterWithPostsRoute()
            ->match($this->createHttpRequest([]))
        ;
    }

    /** @return array<int, array<int, mixed>> */
    public function providesInvalidRequestUris(): array
    {
        return [
            [
                $this->createHttpRequest(['REQUEST_URI' => '']),
            ],
            [
                $this->createHttpRequest(['REQUEST_URI' => '$&**^£(*&£*&']),
            ],
        ];
    }

    /** @dataProvider providesInvalidRequestUris */
    public function testMatchThrowsAnExceptionIfTheRequestUriIsInvalid(HttpRequest $request): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The request URI is invalid.');

        $this
            ->createRouterWithPostsRoute()
            ->match($request)
        ;
    }

    public function testGeneratepathGeneratesAUrlPath(): void
    {
        $routes = [
            [
                'id' => 'fooBar',
                'path' => '/foo/{fooId}/bar/{barId}',
                'action' => 'anything',
            ],
        ];

        $path = (new Router($routes))->generatePath('fooBar', [
            'fooId' => 123,
            'barId' => '456',
        ]);

        $this->assertSame('/foo/123/bar/456', $path);
    }

    public function testParameterValuesNeedNotBePassedToGeneratepath(): void
    {
        $routes = [
            [
                'id' => 'blogPostsIndex',
                'path' => '/posts',
                'action' => 'anything',
            ],
        ];

        $path = (new Router($routes))->generatePath('blogPostsIndex');

        $this->assertSame('/posts', $path);
    }

    public function testGeneratepathThrowsAnExceptionIfThePathDoesNotExist(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('The route, `nonExistent`, does not exist.');

        $this
            ->createRouterWithPostsRoute()
            ->generatePath('nonExistent')
        ;
    }

    /** @return array<int, array<int, mixed>> */
    public function providesIncompleteArgsForGeneratepath(): array
    {
        return [
            [
                [
                    [
                        'id' => 'fooBar',
                        'path' => '/foo/{fooId}/bar/{barId}',
                        'action' => 'anything',
                    ],
                ],
                'fooBar',
                [],
            ],
            [
                [
                    [
                        'id' => 'fooBar',
                        'path' => '/foo/{fooId}/bar/{barId}',
                        'action' => 'anything',
                    ],
                ],
                'fooBar',
                ['irrelevant' => 'foo'],
            ],
            [
                [
                    [
                        'id' => 'fooBar',
                        'path' => '/foo/{fooId}/bar/{barId}',
                        'action' => 'anything',
                    ],
                ],
                'fooBar',
                ['fooId' => 'foo'],
            ],
            [
                [
                    [
                        'id' => 'fooBar',
                        'path' => '/foo/{fooId}/bar/{barId}',
                        'action' => 'anything',
                    ],
                ],
                'fooBar',
                ['fooId' => 'foo', 'bar' => 'baz'],
            ],
        ];
    }

    /**
     * @dataProvider providesIncompleteArgsForGeneratepath
     * @param array<array{id: string, path: string, action: mixed}> $routes
     * @param array<string, string|int> $parameters
     */
    public function testGeneratepathThrowsAnExceptionIfInsufficientParametersWerePassed(
        array $routes,
        string $routeName,
        array $parameters
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter values were missing.  Required: ');

        (new Router($routes))->generatePath($routeName, $parameters);
    }
}
