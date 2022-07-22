<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\Template;

use DanBettles\Marigold\Exception\FileNotFoundException;
use DanBettles\Marigold\Template\PhpTemplate;
use DanBettles\Marigold\Template\TemplateInterface;
use DanBettles\Marigold\Tests\AbstractTestCase;
use RangeException;
use ReflectionClass;

use function ob_get_length;
use function ob_end_clean;
use function ob_start;

use const null;

class PhpTemplateTest extends AbstractTestCase
{
    public function testIsATemplate()
    {
        $class = new ReflectionClass(PhpTemplate::class);

        $this->assertTrue($class->implementsInterface(TemplateInterface::class));
    }

    public function providesValidPhpTemplatePathnames(): array
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
     * @dataProvider providesValidPhpTemplatePathnames
     */
    public function testIsConstructedWithThePathnameOfATemplate($pathname)
    {
        $template = new PhpTemplate($pathname);

        $this->assertSame($pathname, $template->getPathname());
    }

    public function testConstructorThrowsAnExceptionIfTheFileDoesNotExist()
    {
        $templatePathname = $this->createFixturePathname('file_that_does_not_exist.php');

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("The file `{$templatePathname}` does not exist.");

        new PhpTemplate($templatePathname);
    }

    public function testConstructorThrowsAnExceptionIfTheFileDoesNotAppearToContainPhp()
    {
        $this->expectException(RangeException::class);
        $this->expectExceptionMessage('The file does not appear to contain PHP: its extension must be one of ');

        new PhpTemplate($this->createFixturePathname('empty_file'));
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
    public function testRender($expectedOutput, $templatePathname, $templateVars)
    {
        ob_start();

        try {
            $template = new PhpTemplate($templatePathname);

            $output = $template->render($templateVars);

            $this->assertSame(0, ob_get_length());
            $this->assertSame($expectedOutput, $output);
        } finally {
            ob_end_clean();
        }
    }

    public function testRenderDoesNotRequireVars()
    {
        $template = new PhpTemplate($this->createFixturePathname('hello_world.php'));
        $output = $template->render();

        $this->assertSame('Hello, world!', $output);
    }

    public function testTemplatesDoNotHaveAccessToThis()
    {
        $this->expectError();
        $this->expectErrorMessage('Using $this when not in object context');

        (new PhpTemplate($this->createFixturePathname('does_not_contain_var_this.php')))
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
    public function testGetoutputformatReturnsTheOutputFormatOfTheTemplate($expectedFormat, $templatePathname)
    {
        $template = new PhpTemplate($templatePathname);

        $this->assertSame($expectedFormat, $template->getOutputFormat());
    }
}
