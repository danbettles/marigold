<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\Service;

use DanBettles\Marigold\OutputHelper\OutputHelperInterface;
use DanBettles\Marigold\Service\TemplatingService;
use DanBettles\Marigold\Tests\AbstractTestCase;
use DanBettles\Marigold\Tests\Service\TemplatingServiceTest\NotAnOutputHelper;
use DanBettles\Marigold\Tests\Service\TemplatingServiceTest\OutputHelper;
use RangeException;
use stdClass;

use function spl_object_id;

class TemplatingServiceTest extends AbstractTestCase
{
    public function testConstructor()
    {
        $config = [];
        $service = new TemplatingService($config);

        $this->assertEquals($config, $service->getConfig());
    }

    public function testDoesNotRequireConfig()
    {
        $service = new TemplatingService();

        $this->assertInstanceOf(TemplatingService::class, $service);
    }

    public function testRenderPassesAllTemplateVarsToTheTemplateInAnArray()
    {
        $service = new TemplatingService();

        $output = $service->render($this->createFixturePathname('contains_var_variables.php'), [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertSame(<<<'END'
        Array
        (
            [foo] => bar
            [baz] => qux
        )

        END, $output);
    }

    public function testRenderDoesNotRequireVars()
    {
        $service = new TemplatingService();
        $output = $service->render($this->createFixturePathname('hello_world.php'));

        $this->assertSame('Hello, world!', $output);
    }

    public function testRenderPassesTheTemplatingServiceToTheTemplate()
    {
        $service = new TemplatingService();
        $output = $service->render($this->createFixturePathname('contains_var_service.php'), []);

        $this->assertSame((string) spl_object_id($service), $output);
    }

    public function providesInjectedOutputHelperClassNames(): array
    {
        return [
            [
                OutputHelper::class,
                [
                    'output_helpers' => [
                        'html' => new OutputHelper(),
                    ],
                ],
            ],
            // Lazy loading:
            [
                OutputHelper::class,
                [
                    'output_helpers' => [
                        'html' => OutputHelper::class,
                    ],
                ],
            ],
            [
                OutputHelper::class,
                [
                    'output_helpers' => [
                        'html' => function () {
                            return new OutputHelper();
                        },
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider providesInjectedOutputHelperClassNames
     */
    public function testRenderInjectsAnAppropriateOutputHelperIfTheNameOfTheTemplateFollowsTheConvention(
        string $expectedClassName,
        array $serviceConfig
    ) {
        $output = (new TemplatingService($serviceConfig))
            ->render($this->createFixturePathname('contains_var_helper.html.php'), [])
        ;

        $this->assertSame($expectedClassName, $output);
    }

    public function testRenderThrowsAnExceptionIfTheOutputHelperClassDoesNotExist()
    {
        $nonExistentClassName = __CLASS__ . '\\NonExistent';

        $this->expectException(RangeException::class);
        $this->expectExceptionMessage("The output-helper class `{$nonExistentClassName}` does not exist.");

        (new TemplatingService([
            'output_helpers' => [
                'html' => $nonExistentClassName,
            ],
        ]))
            ->render($this->createFixturePathname('empty_file.php'), [])
        ;
    }

    public function testRenderThrowsAnExceptionIfTheOutputHelperClassIsNotAnOutputHelper()
    {
        $notAnOutputHelperClassName = NotAnOutputHelper::class;
        $outputHelperBaseName = OutputHelperInterface::class;

        $this->expectException(RangeException::class);
        $this->expectExceptionMessage("The helper for `html` output, `{$notAnOutputHelperClassName}`, does not implement `{$outputHelperBaseName}`.");

        (new TemplatingService([
            'output_helpers' => [
                'html' => $notAnOutputHelperClassName,
            ],
        ]))
            ->render($this->createFixturePathname('empty_file.php'), [])
        ;
    }

    public function testRenderThrowsAnExceptionIfTheOutputHelperClosureDoesNotReturnAnObject()
    {
        $this->expectException(RangeException::class);
        $this->expectExceptionMessage("The output-helper factory for `html` output does not return an object.");

        (new TemplatingService([
            'output_helpers' => [
                'html' => function () {
                },
            ],
        ]))
            ->render($this->createFixturePathname('empty_file.php'), [])
        ;
    }

    public function testRenderThrowsAnExceptionIfTheOutputHelperClosureDoesNotReturnAnOutputHelper()
    {
        $outputHelperBaseName = OutputHelperInterface::class;

        $this->expectException(RangeException::class);
        $this->expectExceptionMessage("The helper for `html` output, `stdClass`, does not implement `{$outputHelperBaseName}`.");

        (new TemplatingService([
            'output_helpers' => [
                'html' => function () {
                    return new stdClass();
                },
            ],
        ]))
            ->render($this->createFixturePathname('empty_file.php'), [])
        ;
    }

    public function providesInvalidOutputHelpers(): array
    {
        // Garbage.
        return [
            [
                123,
            ],
            [
                1.23,
            ],
            [
                [],
            ],
        ];
    }

    /**
     * @dataProvider providesInvalidOutputHelpers
     */
    public function testRenderThrowsAnExceptionIfTheOutputHelperDoesNotResolveToAnOutputHelper($invalid)
    {
        $this->expectException(RangeException::class);
        $this->expectExceptionMessage("The output-helper config for `html` format is invalid: it must be a class name, a closure, or an object.");

        (new TemplatingService([
            'output_helpers' => [
                'html' => $invalid,
            ],
        ]))
            ->render($this->createFixturePathname('empty_file.php'), [])
        ;
    }

    public function testUsesTheTemplatesDirConfigIfSet()
    {
        $service = new TemplatingService([
            'templates_dir' => $this->getFixturesDir(),
        ]);

        $output = $service->render('hello_world.php');

        $this->assertSame('Hello, world!', $output);
    }
}
