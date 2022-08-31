<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\Exception\FileTypeNotSupportedException;
use DanBettles\Marigold\File\TemplateFile;
use DanBettles\Marigold\TemplateProcessor;

use function ob_get_length;
use function ob_end_clean;
use function ob_start;

class TemplateProcessorTest extends AbstractTestCase
{
    public function testRenderThrowsAnExceptionIfTheFileDoesNotAppearToContainPhp(): void
    {
        $this->expectException(FileTypeNotSupportedException::class);
        $this->expectExceptionMessage('The file-type `txt` is not supported.  Supported types: php; ');

        (new TemplateProcessor())->render(
            $this->createFixturePathname('hello_world.txt'),
            []
        );
    }

    /** @return array<int, array<int, mixed>> */
    public function providesRenderedTemplateOutput(): array
    {
        return [
            [
                '',
                $this->createFixturePathname('empty_file.php'),
                [],
            ],
            [
                'Hello, World!',
                $this->createFixturePathname('hello_world.php'),
                [],
            ],
            [
                <<<END
                <ul>
                    <li>Buddha</li>
                    <li>Dharma</li>
                    <li>Sangha</li>
                </ul>
                END,
                $this->createFixturePathname('the_three_jewels.php'),
                ['jewels' => ['Buddha', 'Dharma', 'Sangha']],
            ],
            [
                'Hello, World!',
                $this->createFixturePathname('hello_world.phtml'),
                [],
            ],
            [
                'Hello, World!',
                $this->createFixturePathname('hello_world.php5'),
                [],
            ],
            [
                'Hello, World!',
                $this->createFixturePathname('hello_world.php4'),
                [],
            ],
            [
                'Hello, World!',
                $this->createFixturePathname('hello_world.php3'),
                [],
            ],
            [
                'Hello, World!',
                $this->createFixturePathname('hello_world.phps'),
                [],
            ],
        ];
    }

    /**
     * @dataProvider providesRenderedTemplateOutput
     * @param array<string, mixed> $templateVars
     */
    public function testRenderReturnsTheRenderedOutputOfTheTemplate(
        string $expectedOutput,
        string $templateFilePathname,
        array $templateVars
    ): void {
        ob_start();

        try {
            $output = (new TemplateProcessor())->render(
                $templateFilePathname,
                $templateVars
            );

            $this->assertSame(0, ob_get_length());
            $this->assertSame($expectedOutput, $output);
        } finally {
            ob_end_clean();
        }
    }

    public function testRenderDoesNotRequireVars(): void
    {
        $output = (new TemplateProcessor())->render(
            $this->createFixturePathname('hello_world.php')
        );

        $this->assertSame('Hello, World!', $output);
    }

    public function testTemplateFilesDoNotHaveAccessToThis(): void
    {
        $this->expectError();
        $this->expectErrorMessage('Using $this when not in object context');

        (new TemplateProcessor())
            ->render($this->createFixturePathname('does_not_contain_var_this.php'))
        ;
    }

    public function testRenderCanAcceptATemplatefileInsteadOfAPathname(): void
    {
        $templateFile = new TemplateFile($this->createFixturePathname('hello_world.php'));

        $output = (new TemplateProcessor())->render(
            $templateFile
        );

        $this->assertSame('Hello, World!', $output);
    }

    public function testUsesTheTemplatesDirIfSet(): void
    {
        $templatesDir = $this->getFixturesDir();
        $templateProcessor = new TemplateProcessor($templatesDir);

        $this->assertSame($templatesDir, $templateProcessor->getTemplatesDir());

        $output = $templateProcessor->render('hello_world.php');

        $this->assertSame('Hello, World!', $output);
    }
}
