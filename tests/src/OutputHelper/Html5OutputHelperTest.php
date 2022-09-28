<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\OutputHelper;

use BadMethodCallException;
use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\OutputHelper\Html5OutputHelper;
use DanBettles\Marigold\OutputHelper\XmlOutputHelper;
use InvalidArgumentException;

use const false;
use const true;

// @todo Test that `createEl()`/`createElement()` calls `createAttributes()`.
class Html5OutputHelperTest extends AbstractTestCase
{
    public function testIsAXmloutputhelper(): void
    {
        $this->assertTrue($this->getTestedClass()->isSubclassOf(XmlOutputHelper::class));
    }

    /** @return array<int, array<int, mixed>> */
    public function providesArgsForVoidElements(): array
    {
        return [
            [
                '<area foo="bar">',
                'area',
                ['foo' => 'bar'],
            ],
            [
                '<base foo="bar">',
                'base',
                ['foo' => 'bar'],
            ],
            [
                '<br foo="bar">',
                'br',
                ['foo' => 'bar'],
            ],
            [
                '<col foo="bar">',
                'col',
                ['foo' => 'bar'],
            ],
            [
                '<embed foo="bar">',
                'embed',
                ['foo' => 'bar'],
            ],
            [
                '<hr foo="bar">',
                'hr',
                ['foo' => 'bar'],
            ],
            [
                '<img foo="bar">',
                'img',
                ['foo' => 'bar'],
            ],
            [
                '<input foo="bar">',
                'input',
                ['foo' => 'bar'],
            ],
            [
                '<link foo="bar">',
                'link',
                ['foo' => 'bar'],
            ],
            [
                '<meta foo="bar">',
                'meta',
                ['foo' => 'bar'],
            ],
            [
                '<source foo="bar">',
                'source',
                ['foo' => 'bar'],
            ],
            [
                '<track foo="bar">',
                'track',
                ['foo' => 'bar'],
            ],
            [
                '<wbr foo="bar">',
                'wbr',
                ['foo' => 'bar'],
            ],
        ];
    }

    /**
     * @dataProvider providesArgsForVoidElements
     * @param array<string, string|bool> $attributes
     */
    public function testCreateelCanCreateVoidElements(
        string $expected,
        string $tagName,
        array $attributes
    ): void {
        $helper = new Html5OutputHelper();

        $this->assertSame($expected, $helper->createEl($tagName, $attributes));
    }

    /** @return array<int, array<int, mixed>> */
    public function providesArgsForElementsWithBooleanAttributes(): array
    {
        return [
            [
                '<input type="checkbox" checked>',
                'input',
                ['type' => 'checkbox', 'checked' => true]
            ],
            [
                '<input type="checkbox">',
                'input',
                ['type' => 'checkbox', 'checked' => false]
            ],
        ];
    }

    /**
     * @dataProvider providesArgsForElementsWithBooleanAttributes
     * @param array<string, string|bool> $attributes
     */
    public function testCreateelCanCreateElementsWithBooleanAttributes(
        string $expected,
        string $tagName,
        array $attributes
    ): void {
        $helper = new Html5OutputHelper();

        $this->assertSame($expected, $helper->createEl($tagName, $attributes));
    }

    public function testCreateelThrowsAnExceptionIfAnAttemptIsMadeToCreateAVoidElementWithContent(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Content was passed: a void element may not have content.');

        (new Html5OutputHelper())->createEl('br', 'foo');
    }

    public function testCanCreateElementsMagically(): void
    {
        $helper = new Html5OutputHelper();

        $this->assertSame(
            '<area foo="bar">',
            $helper->createArea(['foo' => 'bar'])
        );

        $this->assertSame(
            '<base>',
            $helper->createBase()
        );

        $this->assertSame(
            '<br>',
            $helper->createBr()
        );

        $this->assertSame(
            '<col>',
            $helper->createCol()
        );

        $this->assertSame(
            '<embed>',
            $helper->createEmbed()
        );

        $this->assertSame(
            '<hr>',
            $helper->createHr()
        );

        $this->assertSame(
            '<img>',
            $helper->createImg()
        );

        $this->assertSame(
            '<input>',
            $helper->createInput()
        );

        $this->assertSame(
            '<link>',
            $helper->createLink()
        );

        $this->assertSame(
            '<meta>',
            $helper->createMeta()
        );

        $this->assertSame(
            '<source>',
            $helper->createSource()
        );

        $this->assertSame(
            '<track>',
            $helper->createTrack()
        );

        $this->assertSame(
            '<wbr>',
            $helper->createWbr()
        );

        $this->assertSame(
            '<p>Lorem ipsum dolor.</p>',
            $helper->createP('Lorem ipsum dolor.')
        );

        $this->assertSame(
            '<p class="text-muted">Lorem ipsum dolor.</p>',
            $helper->createP(['class' => 'text-muted'], 'Lorem ipsum dolor.')
        );

        $this->assertSame(
            '<img src="pretty.jpg" alt="A pretty picture">',
            $helper->createImg(['src' => 'pretty.jpg', 'alt' => 'A pretty picture'])
        );

        $this->assertSame(
            '<input type="checkbox" checked>',
            $helper->createInput(['type' => 'checkbox', 'checked' => true])
        );

        $this->assertSame(
            '<input type="checkbox">',
            $helper->createInput(['type' => 'checkbox', 'checked' => false])
        );
    }

    public function testCallThrowsAnExceptionIfTheMethodDoesNotExist(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The method, `create`, does not exist.');

        (new Html5OutputHelper())->create();
    }

    /** @return array<int, array<int, mixed>> */
    public function providesAttributesStrings(): array
    {
        return [
            [
                'value="1" checked',
                ['value' => '1', 'checked' => true],
            ],
            [
                '',
                ['checked' => false],
            ],
        ];
    }

    /**
     * @dataProvider providesAttributesStrings
     * @param array<string, string|bool> $input
     */
    public function testCreateattributesCanCreateBooleanAttributes(
        string $expected,
        array $input
    ): void {
        $helper = new Html5OutputHelper();

        $this->assertSame($expected, $helper->createAttributes($input));
    }
}
