<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\Router;
use InvalidArgumentException;

class RouterTest extends AbstractTestCase
{
    public function testIsInstantiable(): void
    {
        $routes = [
            [
                'path' => '/posts',
                'action' => ['foo', 'bar'],
            ],
        ];

        $router = new Router($routes);

        $this->assertEquals($routes, $router->getRoutes());
    }

    /** @return array<int, array<int, mixed>> */
    public function providesMatchedRoutes(): array
    {
        return [
            [
                [
                    'path' => '/',
                    'action' => ['foo', 'bar'],
                    'parameters' => [],
                ],
                [
                    [
                        'path' => '/',
                        'action' => ['foo', 'bar'],
                    ],
                    [
                        'path' => '/path',
                        'action' => ['baz', 'qux'],
                    ],
                ],
                ['REQUEST_URI' => '/'],
            ],
            // #1: Trailing slashes are significant:
            [
                [
                    'path' => '/path',
                    'action' => ['foo', 'bar'],
                    'parameters' => [],
                ],
                [
                    [
                        'path' => '/path',
                        'action' => ['foo', 'bar'],
                    ],
                    [
                        'path' => '/path/',
                        'action' => ['baz', 'qux'],
                    ],
                ],
                ['REQUEST_URI' => '/path?arg=value'],
            ],
            // #2: Trailing slashes are significant:
            [
                [
                    'path' => '/path',
                    'action' => ['foo', 'bar'],
                    'parameters' => [],
                ],
                [
                    [
                        'path' => '/path/',  // Still isn't a match.
                        'action' => ['baz', 'qux'],
                    ],
                    [
                        'path' => '/path',
                        'action' => ['foo', 'bar'],
                    ],
                ],
                ['REQUEST_URI' => '/path?arg=value'],
            ],
            // Placeholders:
            [
                [
                    'path' => '/posts/{postId}',
                    'action' => ['baz', 'qux'],
                    'parameters' => ['postId' => 'the-quick-brown-fox'],
                ],
                [
                    [
                        'path' => '/posts',
                        'action' => ['foo', 'bar'],
                    ],
                    [
                        'path' => '/posts/{postId}',
                        'action' => ['baz', 'qux'],
                    ],
                ],
                ['REQUEST_URI' => '/posts/the-quick-brown-fox?foo=bar'],
            ],
            // Trailing slashes are significant:
            [
                [
                    'path' => '/posts/{postId}/',
                    'action' => ['baz', 'qux'],
                    'parameters' => ['postId' => 'the-quick-brown-fox'],
                ],
                [
                    [
                        'path' => '/posts/{postId}',
                        'action' => ['foo', 'bar'],
                    ],
                    [
                        'path' => '/posts/{postId}/',
                        'action' => ['baz', 'qux'],
                    ],
                ],
                ['REQUEST_URI' => '/posts/the-quick-brown-fox/'],
            ],
            // The order of routes is important:
            [
                [
                    'path' => '/posts/{id}',
                    'action' => ['foo', 'bar'],
                    'parameters' => ['id' => 'the-quick-brown-fox'],
                ],
                [
                    [
                        'path' => '/posts/{id}',
                        'action' => ['foo', 'bar'],
                    ],
                    [
                        'path' => '/posts/{slug}',
                        'action' => ['baz', 'qux'],
                    ],
                ],
                ['REQUEST_URI' => '/posts/the-quick-brown-fox'],
            ],
            // However, exact matches are prioritised:
            [
                [
                    'path' => '/posts/the-quick-brown-fox',
                    'action' => ['baz', 'qux'],
                    'parameters' => [],
                ],
                [
                    [
                        'path' => '/posts/{postId}',
                        'action' => ['foo', 'bar'],
                    ],
                    [
                        'path' => '/posts/the-quick-brown-fox',
                        'action' => ['baz', 'qux'],
                    ],
                ],
                ['REQUEST_URI' => '/posts/the-quick-brown-fox'],
            ],
        ];
    }

    /**
     * @dataProvider providesMatchedRoutes
     * @param array<string, mixed> $expectedRoute
     * @param array<int, array<string, mixed>> $routes
     * @param array<string, string> $serverVars
     */
    public function testMatchAttemptsToFindAMatchingRoute(
        array $expectedRoute,
        array $routes,
        array $serverVars
    ): void {
        $route = (new Router($routes))
            ->match($serverVars)
        ;

        $this->assertEquals($expectedRoute, $route);
    }

    /** @return array<int, array<int, mixed>> */
    public function providesUnmatchableRoutes(): array
    {
        return [
            [
                [
                    [
                        'path' => '/posts',
                        'action' => ['foo', 'bar'],
                    ],
                    [
                        'path' => '/posts/{postId}',
                        'action' => ['baz', 'qux'],
                    ],
                ],
                ['REQUEST_URI' => '/posts/'],  // (Trailing slash.)
            ],
            [
                [
                    [
                        'path' => '/posts',
                        'action' => ['foo', 'bar'],
                    ],
                    [
                        'path' => '/posts/{postId}',
                        'action' => ['baz', 'qux'],
                    ],
                ],
                ['REQUEST_URI' => '/posts/the-quick-brown-fox/'],  // (Trailing slash.)
            ],
        ];
    }

    /**
     * @dataProvider providesUnmatchableRoutes
     * @param array<int, array<string, mixed>> $routes
     * @param array<string, string> $serverVars
     */
    public function testMatchReturnsNullIfThereIsNoMatchingRoute(
        array $routes,
        array $serverVars
    ): void {
        $route = (new Router($routes))
            ->match($serverVars)
        ;

        $this->assertNull($route);
    }

    /** @return array<int, array<int, mixed>> */
    public function providesInvalidRequestUris(): array
    {
        return [
            [
                ['REQUEST_URI' => ''],
            ],
            [
                ['REQUEST_URI' => '$&**^£(*&£*&'],
            ],
        ];
    }

    public function testMatchThrowsAnExceptionIfTheServerVarsDoNotContainTheRequestUri(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('There is no request URI in the server vars.');

        (new Router([]))
            ->match([])
        ;
    }

    /**
     * @dataProvider providesInvalidRequestUris
     * @param array<string, string> $serverVars
     */
    public function testMatchThrowsAnExceptionIfTheRequestUriIsInvalid(array $serverVars): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The request URI is invalid.');

        (new Router([]))
            ->match($serverVars)
        ;
    }
}