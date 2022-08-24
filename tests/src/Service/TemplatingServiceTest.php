<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\Service;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\OutputHelper\OutputHelperInterface;
use DanBettles\Marigold\Service\TemplatingService;
use DanBettles\Marigold\ServiceFactory;
use DanBettles\Marigold\Tests\Service\TemplatingServiceTest\NotAnOutputHelper;
use DanBettles\Marigold\Tests\Service\TemplatingServiceTest\OutputHelper;
use RangeException;
use stdClass;

use function spl_object_id;

class TemplatingServiceTest extends AbstractTestCase
{
    public function testIsInstantiable()
    {
        $config = [];
        $serviceFactory = new ServiceFactory([]);
        $templatingService = new TemplatingService($config, $serviceFactory);

        $this->assertEquals($config, $templatingService->getConfig());
        $this->assertSame($serviceFactory, $templatingService->getServiceFactory());
    }

    public function testRenderPassesAllTemplateVarsToTheTemplateFileInAnArray()
    {
        $output = $this
            ->createEmptyTemplatingService()
            ->render($this->createFixturePathname('contains_var_variables.php'), [
                'foo' => 'bar',
                'baz' => 'qux',
            ])
        ;

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
        $output = $this
            ->createEmptyTemplatingService()
            ->render($this->createFixturePathname('hello_world.php'))
        ;

        $this->assertSame('Hello, World!', $output);
    }

    public function testRenderPassesTheTemplatingServiceToTheTemplateFile()
    {
        $templatingService = $this->createEmptyTemplatingService();
        $output = $templatingService->render($this->createFixturePathname('contains_var_service.php'));

        $this->assertSame((string) spl_object_id($templatingService), $output);
    }

    public function providesInjectedOutputHelperClassNames(): array
    {
        return [
            [
                OutputHelper::class,
                [
                    'output_helpers.html' => OutputHelper::class,
                ],
            ],
            [
                OutputHelper::class,
                [
                    'output_helpers.html' => function () {
                        return new OutputHelper();
                    },
                ],
            ],
        ];
    }

    /**
     * @dataProvider providesInjectedOutputHelperClassNames
     */
    public function testRenderInjectsAnAppropriateOutputHelperIfTheNameOfTheTemplateFileFollowsTheConvention(
        string $expectedClassName,
        array $serviceFactoryConfig
    ) {
        $serviceFactory = new ServiceFactory($serviceFactoryConfig);

        $output = (new TemplatingService([], $serviceFactory))
            ->render($this->createFixturePathname('contains_var_helper.html.php'))
        ;

        $this->assertSame($expectedClassName, $output);
    }

    public function testRenderThrowsAnExceptionIfTheOutputHelperClassIsNotAnOutputHelper()
    {
        $notAnOutputHelperClassName = NotAnOutputHelper::class;
        $outputHelperBaseName = OutputHelperInterface::class;

        $this->expectException(RangeException::class);
        $this->expectExceptionMessage("The helper for `html` output, `{$notAnOutputHelperClassName}`, does not implement `{$outputHelperBaseName}`.");

        $serviceFactoryConfig = [
            'output_helpers.html' => $notAnOutputHelperClassName,
        ];

        (new TemplatingService([], new ServiceFactory($serviceFactoryConfig)))
            ->render($this->createFixturePathname('empty_file.php'))
        ;
    }

    public function testRenderThrowsAnExceptionIfTheOutputHelperClosureDoesNotReturnAnOutputHelper()
    {
        $outputHelperBaseName = OutputHelperInterface::class;

        $this->expectException(RangeException::class);
        $this->expectExceptionMessage("The helper for `html` output, `stdClass`, does not implement `{$outputHelperBaseName}`.");

        $serviceFactoryConfig = [
            'output_helpers.html' => function () {
                return new stdClass();
            },
        ];

        (new TemplatingService([], new ServiceFactory($serviceFactoryConfig)))
            ->render($this->createFixturePathname('empty_file.php'))
        ;
    }

    public function testUsesTheTemplatesDirConfigIfSet()
    {
        $templatingServiceConfig = [
            'templates_dir' => $this->getFixturesDir(),
        ];

        $output = (new TemplatingService($templatingServiceConfig, new ServiceFactory([])))
            ->render('hello_world.php')
        ;

        $this->assertSame('Hello, World!', $output);
    }

    private function createEmptyTemplatingService(): TemplatingService
    {
        return new TemplatingService([], new ServiceFactory([]));
    }
}
