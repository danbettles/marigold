<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\Router;
use InvalidArgumentException;
use OutOfBoundsException;

class RouterTest extends AbstractTestCase
{
    public function testIsInstantiable(): void
    {
        $routes = [
            [
                'path' => '/posts',
                'action' => ['FooBar', 'baz'],
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
                    'action' => ['FooBar', 'baz'],
                    'parameters' => [],
                ],
                [
                    [
                        'path' => '/',
                        'action' => ['FooBar', 'baz'],
                    ],
                    [
                        'path' => '/path',
                        'action' => ['QuxQuux', 'corge'],
                    ],
                ],
                ['REQUEST_URI' => '/'],
            ],
            // #1: Trailing slashes are significant:
            [
                [
                    'path' => '/path',
                    'action' => ['FooBar', 'baz'],
                    'parameters' => [],
                ],
                [
                    [
                        'path' => '/path',
                        'action' => ['FooBar', 'baz'],
                    ],
                    [
                        'path' => '/path/',
                        'action' => ['QuxQuux', 'corge'],
                    ],
                ],
                ['REQUEST_URI' => '/path?arg=value'],
            ],
            // #2: Trailing slashes are significant:
            [
                [
                    'path' => '/path',
                    'action' => ['FooBar', 'baz'],
                    'parameters' => [],
                ],
                [
                    [
                        'path' => '/path/',  // Still isn't a match.
                        'action' => ['QuxQuux', 'corge'],
                    ],
                    [
                        'path' => '/path',
                        'action' => ['FooBar', 'baz'],
                    ],
                ],
                ['REQUEST_URI' => '/path?arg=value'],
            ],
            // Placeholders:
            [
                [
                    'path' => '/posts/{postId}',
                    'action' => ['QuxQuux', 'corge'],
                    'parameters' => ['postId' => 'the-quick-brown-fox'],
                ],
                [
                    [
                        'path' => '/posts',
                        'action' => ['FooBar', 'baz'],
                    ],
                    [
                        'path' => '/posts/{postId}',
                        'action' => ['QuxQuux', 'corge'],
                    ],
                ],
                ['REQUEST_URI' => '/posts/the-quick-brown-fox?foo=bar'],
            ],
            // Trailing slashes are significant:
            [
                [
                    'path' => '/posts/{postId}/',
                    'action' => ['QuxQuux', 'corge'],
                    'parameters' => ['postId' => 'the-quick-brown-fox'],
                ],
                [
                    [
                        'path' => '/posts/{postId}',
                        'action' => ['FooBar', 'baz'],
                    ],
                    [
                        'path' => '/posts/{postId}/',
                        'action' => ['QuxQuux', 'corge'],
                    ],
                ],
                ['REQUEST_URI' => '/posts/the-quick-brown-fox/'],
            ],
            // The order of routes is important:
            [
                [
                    'path' => '/posts/{id}',
                    'action' => ['FooBar', 'baz'],
                    'parameters' => ['id' => 'the-quick-brown-fox'],
                ],
                [
                    [
                        'path' => '/posts/{id}',
                        'action' => ['FooBar', 'baz'],
                    ],
                    [
                        'path' => '/posts/{slug}',
                        'action' => ['QuxQuux', 'corge'],
                    ],
                ],
                ['REQUEST_URI' => '/posts/the-quick-brown-fox'],
            ],
            // However, exact matches are prioritised:
            [
                [
                    'path' => '/posts/the-quick-brown-fox',
                    'action' => ['QuxQuux', 'corge'],
                    'parameters' => [],
                ],
                [
                    [
                        'path' => '/posts/{postId}',
                        'action' => ['FooBar', 'baz'],
                    ],
                    [
                        'path' => '/posts/the-quick-brown-fox',
                        'action' => ['QuxQuux', 'corge'],
                    ],
                ],
                ['REQUEST_URI' => '/posts/the-quick-brown-fox'],
            ],
        ];
    }

    /**
     * @dataProvider providesMatchedRoutes
     * @param array{path: string, action: mixed, parameters: string[]} $expectedRoute
     * @param array<int, array{path: string, action: mixed}> $routes
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
                        'action' => ['FooBar', 'baz'],
                    ],
                    [
                        'path' => '/posts/{postId}',
                        'action' => ['QuxQuux', 'corge'],
                    ],
                ],
                ['REQUEST_URI' => '/posts/'],  // (Trailing slash.)
            ],
            [
                [
                    [
                        'path' => '/posts',
                        'action' => ['FooBar', 'baz'],
                    ],
                    [
                        'path' => '/posts/{postId}',
                        'action' => ['QuxQuux', 'corge'],
                    ],
                ],
                ['REQUEST_URI' => '/posts/the-quick-brown-fox/'],  // (Trailing slash.)
            ],
        ];
    }

    /**
     * @dataProvider providesUnmatchableRoutes
     * @param array<int, array{path: string, action: mixed}> $routes
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
        $this->expectException(OutOfBoundsException::class);
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

    public function testGeneratepathGeneratesAUrlPath(): void
    {
        $routes = [
            'fooBar' => [
                'path' => '/foo/{fooId}/bar/{barId}',
                'action' => ['FooBar', 'baz'],
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
            'posts' => [
                'path' => '/posts',
                'action' => ['FooBar', 'baz'],
            ],
        ];

        $path = (new Router($routes))->generatePath('posts');

        $this->assertSame('/posts', $path);
    }
}
