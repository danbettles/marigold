<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use DanBettles\Marigold\PhpTemplate;
use InvalidArgumentException;

use function ob_get_length;
use function ob_end_clean;
use function ob_start;

class PhpTemplateTest extends AbstractTestCase
{
    public function testIsConstructedWithThePathnameOfATemplate()
    {
        $pathname = $this->createFixturePathname('empty_file');
        $template = new PhpTemplate($pathname);

        $this->assertSame($pathname, $template->getPathname());
    }

    public function testConstructorThrowsAnExceptionIfTheFileDoesNotExist()
    {
        $templatePathname = $this->createFixturePathname('file_that_does_not_exist.php');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The template file, `{$templatePathname}`, does not exist.");

        new PhpTemplate($templatePathname);
    }

    public function providesRenderedTemplateOutput(): array
    {
        return [
            [
                '',
                $this->createFixturePathname('empty_file'),
                [],
            ],
            [
                'Hello, world!',
                $this->createFixturePathname('hello_world.txt'),
                [],
            ],
            [
                'Goodbye, cruel world.',
                $this->createFixturePathname('goodbye_cruel_world.php'),
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
        $template = new PhpTemplate($this->createFixturePathname('hello_world.txt'));
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
                'html',
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
                'json',
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
