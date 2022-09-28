<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\TemplateEngine;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\Exception\FileNotFoundException;
use DanBettles\Marigold\Php;
use DanBettles\Marigold\TemplateEngine\Engine;
use DanBettles\Marigold\TemplateEngine\OutputFacade;
use DanBettles\Marigold\TemplateEngine\TemplateFile;
use DanBettles\Marigold\TemplateEngine\TemplateFileLoader;
use ReflectionMethod;
use SplFileInfo;

use function array_keys;
use function is_array;

use const null;

class EngineTest extends AbstractTestCase
{
    public function testIsInstantiable(): void
    {
        $php = new Php();
        $loader = new TemplateFileLoader([$this->getFixturesDir()]);
        $engine = new Engine($php, $loader);

        $this->assertSame($php, $engine->getPhp());
        $this->assertSame($loader, $engine->getTemplateFileLoader());
    }

    /**
     * If we know the engine is using `TemplateFileLoader` in conjunction with `executeFile()` then we can be confident
     * in its behaviour.
     */
    public function testRenderReturnsTheRenderedOutputOfTheTemplate(): void
    {
        $fixturesDir = $this->createFixturePathname(__FUNCTION__);
        $templateFileBasename = 'hello_world.txt.php';
        $templateFilePathname = "{$fixturesDir}/{$templateFileBasename}";
        $templateFile = new TemplateFile($templateFilePathname);

        $this->assertFileDoesNotExist($templateFile->getPathname());

        $templateVars = [
            'name' => 'Dan',
        ];

        $expectedOutput = 'Hello, Dan!';

        $phpMock = $this
            ->getMockBuilder(Php::class)
            ->onlyMethods(['executeFile'])
            ->disableArgumentCloning()
            ->getMock()
        ;

        $phpMock
            ->expects($this->once())
            ->method('executeFile')
            ->with(
                $templateFilePathname,
                $this->callback(function ($variables) use ($templateVars) {
                    return is_array($variables)
                        && ['input', 'output'] == array_keys($variables)
                        && $templateVars === $variables['input']
                        && $variables['output'] instanceof OutputFacade
                    ;
                }),
                null
            )
            ->willReturnCallback(function ($pathname, $context, &$output) use ($expectedOutput) {
                $output = $expectedOutput;

                return 1;
            })
        ;

        $loaderMock = $this
            ->getMockBuilder(TemplateFileLoader::class)
            ->onlyMethods(['findTemplate'])
            ->setConstructorArgs([
                [$fixturesDir],
            ])
            ->getMock()
        ;

        $loaderMock
            ->expects($this->once())
            ->method('findTemplate')
            ->with($templateFileBasename)
            ->willReturn($templateFile)
        ;

        /** @var Php $phpMock */
        /** @var TemplateFileLoader $loaderMock */
        $actualOutput = (new Engine($phpMock, $loaderMock))
            ->render(
                $templateFileBasename,
                $templateVars
            )
        ;

        $this->assertSame($expectedOutput, $actualOutput);
    }

    /**
     * Here we care only that the `SplFileInfo` is passed unadulterated to the `TemplateFileLoader`.
     */
    public function testRenderAcceptsAFileInfoObject(): void
    {
        $fixturesDir = $this->createFixturePathname(__FUNCTION__);
        $templateFileBasename = 'hello_world.txt.php';
        $templateFilePathname = "{$fixturesDir}/{$templateFileBasename}";
        $templateFile = new TemplateFile($templateFilePathname);
        $splFileInfo = new SplFileInfo($templateFileBasename);

        $this->assertFileDoesNotExist($templateFile->getPathname());

        $phpMock = $this
            ->getMockBuilder(Php::class)
            ->onlyMethods(['executeFile'])
            ->disableArgumentCloning()
            ->getMock()
        ;

        $phpMock
            ->method('executeFile')
            ->willReturnCallback(function ($pathname, $context, &$output) {
                $output = '';

                return 1;
            })
        ;

        $loaderMock = $this
            ->getMockBuilder(TemplateFileLoader::class)
            ->onlyMethods(['findTemplate'])
            ->setConstructorArgs([
                [$fixturesDir],
            ])
            ->getMock()
        ;

        $loaderMock
            ->expects($this->once())
            ->method('findTemplate')
            ->with($splFileInfo)
            ->willReturn($templateFile)
        ;

        /** @var Php $phpMock */
        /** @var TemplateFileLoader $loaderMock */
        (new Engine($phpMock, $loaderMock))
            ->render(
                $splFileInfo
            )
        ;
    }

    public function testVarsDoNotHaveToBePassedToRender(): void
    {
        $renderMethod = new ReflectionMethod(Engine::class, 'render');
        $variablesParam = $renderMethod->getParameters()[1];

        $this->assertTrue($variablesParam->isOptional());
    }

    /** @return array<int, array<int, mixed>> */
    public function providesNonExistentTemplateFiles(): array
    {
        $fixturesDir = $this->createFixturePathname('testRenderThrowsAnExceptionIfTheFileDoesNotExist');
        $templateFileBasename = 'non_existent.file';
        $templateFilePathname = "{$fixturesDir}/{$templateFileBasename}";

        return [
            [
                $templateFilePathname,
                $fixturesDir,
                $templateFilePathname,
            ],
            [
                $templateFilePathname,
                $fixturesDir,
                new SplFileInfo($templateFilePathname),
            ],
        ];
    }

    /**
     * @dataProvider providesNonExistentTemplateFiles
     * @param string|SplFileInfo $templateFile
     */
    public function testRenderThrowsAnExceptionIfTheFileDoesNotExist(
        string $expectedPathname,
        string $fixturesDir,
        $templateFile
    ): void {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("`{$expectedPathname}`");

        $php = new Php();
        $loader = new TemplateFileLoader([$fixturesDir]);

        (new Engine($php, $loader))
            ->render(
                $templateFile
            )
        ;
    }

    public function testCreateReturnsANewInstance(): void
    {
        $createMethod = new ReflectionMethod(Engine::class, 'create');

        $this->assertTrue($createMethod->isPublic());
        $this->assertTrue($createMethod->isStatic());

        $loaderMock = $this
            ->getMockBuilder(TemplateFileLoader::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        /** @var TemplateFileLoader $loaderMock */
        $templateEngine = Engine::create($loaderMock);

        $this->assertInstanceOf(Engine::class, $templateEngine);

        $this->assertInstanceOf(Php::class, $templateEngine->getPhp());
        $this->assertSame($loaderMock, $templateEngine->getTemplateFileLoader());
    }

    public function testTemplatesCanIncludeOtherFiles(): void
    {
        $loader = new TemplateFileLoader([$this->createFixturePathname(__FUNCTION__)]);

        $actualOutput = (new Engine(new Php(), $loader))
            ->render('parent.php')
        ;

        $this->assertSame(<<<END
        Output from other template.
        Not-a-template content.
        END, $actualOutput);
    }

    public function testTemplatesCanInstructTheEngineToWrapTheirOutputWithAnotherTemplate(): void
    {
        $loader = new TemplateFileLoader([$this->createFixturePathname(__FUNCTION__)]);

        $actualOutput = (new Engine(new Php(), $loader))
            ->render('wrapped.php')
        ;

        $this->assertSame(<<<END
        # Test Wrapping
        Wrapped content.
        End of wrapper.
        END, $actualOutput);
    }
}
