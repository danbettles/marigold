<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\CssMinifier;

class CssMinifierTest extends AbstractTestCase
{
    /** @return array<mixed[]> */
    public function providesCssWithCommentsRemoved(): array
    {
        return [
            [
                '',
                '',
            ],
            [
                '',
                '/*Foo*/',
            ],
            [
                "\nbody {\n    font-family: sans-serif;\n}",
                "/*Foo*/\nbody {\n    font-family: sans-serif;\n}",
            ],
            [
                "body {\n    \n    font-family: sans-serif;\n}",
                "body {\n    /*Foo*/\n    font-family: sans-serif;\n}",
            ],
            [
                "body {\n    font-family: sans-serif;  \n}",
                "body {\n    font-family: sans-serif;  /*Foo*/\n}",
            ],
            [
                '',
                '/**#@+Something*//**#@-Something*/',
            ],
        ];
    }

    /** @dataProvider providesCssWithCommentsRemoved */
    public function testRemovecommentsfilterRemovesComments(
        string $expected,
        string $css
    ): void {
        $minified = (new CssMinifier())->removeCommentsFilter($css);

        $this->assertSame($expected, $minified);
    }

    /** @return array<mixed[]> */
    public function providesCssWithSuperfluousWhitespaceRemoved(): array
    {
        return [
            [
                '',
                '',
            ],
            [
                "body{\nfont-family:sans-serif;\nfont-size:1em;\ncolor:#000;\n}",
                "body {\r\nfont-family: sans-serif;\rfont-size: 1em;\ncolor: #000;\n}",
            ],
            [
                "body{\nfont-family:sans-serif;\nfont-size:1em;\ncolor:#000;\n}",
                "body {\n    font-family: sans-serif;\n    font-size: 1em;\n    color: #000;\n}",
            ],
            [
                "body{\nfont-family:sans-serif;\nfont-size:1em;\ncolor:#000;\n}",
                "body {\n\tfont-family: sans-serif;\n\tfont-size: 1em;\n\tcolor: #000;\n}",
            ],
            [
                'body{font-family:sans-serif;}',
                ' body {font-family: sans-serif;} ',
            ],
            [
                "body{\nfont-family:sans-serif;\n}",
                "body {\n    font-family: sans-serif;\n}",
            ],
            [
                "body{\nfont-family:sans-serif;\n}\np{\nline-height:1em;\n}",
                "body {\n    font-family: sans-serif;\n}\n\np {\n    line-height: 1em;\n}",
            ],
            [
                'body{font-family:sans-serif;}',
                "body {font-family: sans-serif;}\n",
            ],
            [
                "body{\nfont-family:sans-serif;\n}\np{\nline-height:1em;\n}",
                "body {\n    font-family: sans-serif;\n}\n    \np {\n    line-height: 1em;\n}",
            ],
            [
                "h1,h2{\nfont-weight:bold;\n}",
                "h1, h2 {\n    font-weight: bold;\n}",
            ],
            [
                "h1,h2{font-weight :bold;}",
                "h1 , h2 { font-weight : bold ; }",
            ],
        ];
    }

    /** @dataProvider providesCssWithSuperfluousWhitespaceRemoved */
    public function testRemovesuperfluouswhitespacefilterRemovesSuperfluousWhitespace(
        string $expected,
        string $css
    ): void {
        $minified = (new CssMinifier())->removeSuperfluousWhitespaceFilter($css);

        $this->assertSame($expected, $minified);
    }

    /** @return array<mixed[]> */
    public function providesCssContainingZeroesWithUnitsRemoved(): array
    {
        return [
            [
                '',
                '',
            ],
            [
                '0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0',
                '0em 0ex 0ch 0rem 0vw 0vh 0vmin 0vm 0vmax 0% 0cm 0mm 0in 0px 0pt 0pc',
            ],
            [
                '.smallest { font-size: 0; }',
                '.smallest { font-size: 0em; }',
            ],
        ];
    }

    /** @dataProvider providesCssContainingZeroesWithUnitsRemoved */
    public function testRemoveunitsfromzeroesfilterRemovesUnitsFromZeroes(
        string $expected,
        string $css
    ): void {
        $minified = (new CssMinifier())->removeUnitsFromZeroesFilter($css);

        $this->assertSame($expected, $minified);
    }

    /** @return array<mixed[]> */
    public function providesCssContainingHexColoursThatHaveBeenCondensed(): array
    {
        return [
            [
                '',
                '',
            ],
            [
                '#fff #abc #123456',
                '#ffffff #aabbcc #123456',
            ],
            [
                'body { color: #000; }',
                'body { color: #000000; }',
            ],
        ];
    }

    /** @dataProvider providesCssContainingHexColoursThatHaveBeenCondensed */
    public function testCondensehexcoloursfilterCondensesHexColours(
        string $expected,
        string $css
    ): void {
        $minified = (new CssMinifier())->condenseHexColoursFilter($css);

        $this->assertSame($expected, $minified);
    }

    /** @return array<mixed[]> */
    public function providesCssThatHasBeenMinified(): array
    {
        return [
            [
                '',
                '',
            ],
            [
                <<<END
                body{
                color :#000;
                }
                h1,h2{font-weight:bold;}
                .smallest{
                font-size:0;
                }
                END,
                <<<END
                body {
                    color : #000000 ;  /*Colour value will be condensed.*/
                }
                    /*This empty line will be removed.*/
                h1, h2 { font-weight: bold; }

                .smallest {
                    font-size: 0em;
                }
                END,
            ],
        ];
    }

    /** @dataProvider providesCssThatHasBeenMinified */
    public function testMinifyMinifiesTheSpecifiedCssUsingAllFilters(
        string $expected,
        string $css
    ): void {
        $minified = (new CssMinifier())->minify($css);

        $this->assertSame($expected, $minified);
    }

    public function testDoesNotBreakRuleSetsUsingTheWherePseudoClass(): void
    {
        $minified = (new CssMinifier())->minify(<<<END
        :where(nav) :where(ol, ul) {
            list-style-type: none;
            padding: 0;
            border : 0;
        }
        END);

        $this->assertSame(<<<END
        :where(nav) :where(ol,ul){
        list-style-type:none;
        padding:0;
        border :0;
        }
        END, $minified);
    }
}
