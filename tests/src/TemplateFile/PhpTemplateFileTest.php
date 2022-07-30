<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\TemplateFile;

use DanBettles\Marigold\Exception\FileNotFoundException;
use DanBettles\Marigold\Exception\FileTypeNotSupportedException;
use DanBettles\Marigold\TemplateFile\PhpTemplateFile;
use DanBettles\Marigold\TemplateFile\TemplateFileInterface;
use DanBettles\Marigold\Tests\AbstractTestCase;
use ReflectionClass;
use SplFileInfo;

use function ob_get_length;
use function ob_end_clean;
use function ob_start;

use const null;

class PhpTemplateFileTest extends AbstractTestCase
{
    public function testIsAFile()
    {
        $class = new ReflectionClass(PhpTemplateFile::class);

        $this->assertTrue($class->isSubclassOf(SplFileInfo::class));
    }

    public function testIsATemplateFile()
    {
        $class = new ReflectionClass(PhpTemplateFile::class);

        $this->assertTrue($class->implementsInterface(TemplateFileInterface::class));
    }

    public function providesValidPhpTemplateFilePathnames(): array
    {
        return [
            [
                $this->createFixturePathname('empty_file.php'),
            ],
            [
                $this->createFixturePathname('empty_file.phtml'),
            ],
            [
                $this->createFixturePathname('empty_file.php3'),
            ],
            [
                $this->createFixturePathname('empty_file.php4'),
            ],
            [
                $this->createFixturePathname('empty_file.php5'),
            ],
            [
                $this->createFixturePathname('empty_file.phps'),
            ],
        ];
    }

    /**
     * @dataProvider providesValidPhpTemplateFilePathnames
     */
    public function testIsConstructedWithThePathnameOfATemplate($pathname)
    {
        $templateFile = new PhpTemplateFile($pathname);

        $this->assertSame($pathname, $templateFile->getPathname());
    }

    public function testConstructorThrowsAnExceptionIfTheFileDoesNotExist()
    {
        $templateFilePathname = $this->createFixturePathname('file_that_does_not_exist.php');

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("The file `{$templateFilePathname}` does not exist.");

        new PhpTemplateFile($templateFilePathname);
    }

    public function testConstructorThrowsAnExceptionIfTheFileDoesNotAppearToContainPhp()
    {
        $this->expectException(FileTypeNotSupportedException::class);
        $this->expectExceptionMessage('The file-type `` is not supported.  Supported types: php; ');

        new PhpTemplateFile($this->createFixturePathname('empty_file'));
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

    /**
     * @dataProvider providesRenderedTemplateOutput
     */
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

    public function providesPathnamesContainingOutputFormat(): array
    {
        return [
            [
                null,
                $this->createFixturePathname('test_getoutputformat.php'),
            ],
            [
                'html',
                $this->createFixturePathname('test_getoutputformat.html.php'),
            ],
            [
                'xml',
                $this->createFixturePathname('test_getoutputformat.xml.php'),
            ],
            [
                'json',  // Normalized, see.
                $this->createFixturePathname('test_getoutputformat.JSON.php'),
            ],
        ];
    }

    /**
     * @dataProvider providesPathnamesContainingOutputFormat
     */
    public function testGetoutputformatReturnsTheOutputFormatOfTheTemplateFile($expectedFormat, $templateFilePathname)
    {
        $templateFile = new PhpTemplateFile($templateFilePathname);

        $this->assertSame($expectedFormat, $templateFile->getOutputFormat());
    }
}
