<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\TemplateFile;

use DanBettles\Marigold\Exception\FileTypeNotSupportedException;
use DanBettles\Marigold\TemplateFile\AbstractTemplateFile;
use DanBettles\Marigold\TemplateFile\PhpTemplateFile;
use DanBettles\Marigold\Tests\AbstractTestCase;

use function ob_get_length;
use function ob_end_clean;
use function ob_start;

class PhpTemplateFileTest extends AbstractTestCase
{
    public function testIsAnAbstracttemplatefile()
    {
        $this->assertTrue($this->getTestedClass()->isSubclassOf(AbstractTemplateFile::class));
    }

    public function providesPathnamesOfPhpTemplateFiles(): array
    {
        return [
            [
                $this->createFixturePathname('empty_file.php'),
            ],
            [
                $this->createFixturePathname('empty_file.phtml'),
            ],
            [
                $this->createFixturePathname('empty_file.php5'),
            ],
            [
                $this->createFixturePathname('empty_file.php4'),
            ],
            [
                $this->createFixturePathname('empty_file.php3'),
            ],
            [
                $this->createFixturePathname('empty_file.phps'),
            ],
        ];
    }

    /** @dataProvider providesPathnamesOfPhpTemplateFiles */
    public function testConstructorAcceptsPathnamesWithVariousPhpFileExtensions(string $templateFilePathname)
    {
        $templateFile = new PhpTemplateFile($templateFilePathname);

        $this->assertSame($templateFilePathname, $templateFile->getPathname());
    }

    public function testConstructorThrowsAnExceptionIfTheFileDoesNotAppearToContainPhp()
    {
        $this->expectException(FileTypeNotSupportedException::class);
        $this->expectExceptionMessage('The file-type `txt` is not supported.  Supported types: php; ');

        new PhpTemplateFile($this->createFixturePathname('hello_world.txt'));
    }

    public function providesRenderedTemplateOutput(): array
    {
        return [
            [
                '',
                $this->createFixturePathname('empty_file.php'),
                [],
            ],
            [
                'Hello, world!',
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
        ];
    }

    /** @dataProvider providesRenderedTemplateOutput */
    public function testRender($expectedOutput, $templateFilePathname, $templateVars)
    {
        ob_start();

        try {
            $templateFile = new PhpTemplateFile($templateFilePathname);

            $output = $templateFile->render($templateVars);

            $this->assertSame(0, ob_get_length());
            $this->assertSame($expectedOutput, $output);
        } finally {
            ob_end_clean();
        }
    }

    public function testRenderDoesNotRequireVars()
    {
        $templateFile = new PhpTemplateFile($this->createFixturePathname('hello_world.php'));
        $output = $templateFile->render();

        $this->assertSame('Hello, world!', $output);
    }

    public function testTemplateFilesDoNotHaveAccessToThis()
    {
        $this->expectError();
        $this->expectErrorMessage('Using $this when not in object context');

        (new PhpTemplateFile($this->createFixturePathname('does_not_contain_var_this.php')))
            ->render()
        ;
    }
}
