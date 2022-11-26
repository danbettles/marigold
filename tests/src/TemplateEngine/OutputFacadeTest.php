<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\TemplateEngine;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\Php;
use DanBettles\Marigold\TemplateEngine\Engine;
use DanBettles\Marigold\TemplateEngine\OutputFacade;
use DanBettles\Marigold\TemplateEngine\TemplateFileLoader;
use SplFileInfo;

class OutputFacadeTest extends AbstractTestCase
{
    public function testIsInstantiable(): void
    {
        $loader = new TemplateFileLoader([$this->createFixturePathname(__FUNCTION__)]);
        $engine = new Engine(new Php(), $loader);

        $facade = new OutputFacade($engine);

        $this->assertSame($engine, $facade->getEngine());
        $this->assertNull($facade->getWrapperArgs());
    }

    /** @return array<int,array<int,mixed>> */
    public function providesTheRenderedOutputOfFiles(): array
    {
        return [
            [
                'Lorem ipsum dolor.',
                [
                    'single.file',
                    // (No variables.)
                ],
            ],
            [
                'Sit amet, consectetur.',
                [
                    'single.file',
                    ['foo' => 'bar', 'baz' => 'qux'],
                ],
            ],
            [
                'Adipiscing elit, sed.',
                [
                    new SplFileInfo('single.file'),
                    // (No variables.)
                ],
            ],
        ];
    }

    /**
     * Here we care only that:
     * - input is passed unadulterated to `Engine::render()`;
     * - and `OutputFacade::include()` returns the unadulterated output from `Engine::render()`.
     *
     * @dataProvider providesTheRenderedOutputOfFiles
     * @param array{0:string|SplFileInfo,1?:mixed[]} $args
     */
    public function testIncludeReturnsTheRenderedOutputOfTheFile(
        string $expectedOutput,
        array $args
    ): void {
        $engineMock = $this
            ->getMockBuilder(Engine::class)
            ->onlyMethods(['render'])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $engineMock
            ->expects($this->once())
            ->method('render')
            ->with(...$args)
            ->willReturn($expectedOutput)
        ;

        /** @var Engine $engineMock */
        $actualOutput = (new OutputFacade($engineMock))
            ->include(...$args)
        ;

        $this->assertSame($expectedOutput, $actualOutput);
    }

    public function testWrapwithSetsTheWrapperArgs(): void
    {
        $facade = new OutputFacade($this->createStub(Engine::class));

        $something = $facade->wrapWith('wrapper.php', 'varInWrapper', [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertSame([
            'wrapper.php', 'varInWrapper', [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ], $facade->getWrapperArgs());

        $this->assertSame($facade, $something);
    }

    public function testVarsNeedNotBePassedToWrapwith(): void
    {
        $wrapwithMethod = $this->getTestedClass()->getMethod('wrapWith');
        $variablesParam = $wrapwithMethod->getParameters()[2];

        $this->assertTrue($variablesParam->isOptional());
    }

    public function testInsertintoIsAnAliasForWrapwith(): void
    {
        $pathnameOrFileInfo = 'wrapper.php';
        $targetVarName = 'varInWrapper';

        $variables = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $facadeMock = $this
            ->getMockBuilder(OutputFacade::class)
            ->onlyMethods(['wrapWith'])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $facadeMock
            ->expects($this->once())
            ->method('wrapWith')
            ->with($pathnameOrFileInfo, $targetVarName, $variables)
            ->willReturn($facadeMock)
        ;

        /** @var OutputFacade $facadeMock */
        $facadeMock->insertInto($pathnameOrFileInfo, $targetVarName, $variables);
    }
}
