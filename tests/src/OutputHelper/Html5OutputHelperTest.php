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

class Html5OutputHelperTest extends AbstractTestCase
{
    public function testIsAXmloutputhelper(): void
    {
        $this->assertTrue($this->getTestedClass()->isSubclassOf(XmlOutputHelper::class));
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
            [
                '',
                [],
            ],
            [
                'foo="bar"',
                ['foo' => 'bar'],
            ],
            [
                'foo="bar" baz="qux"',
                ['foo' => 'bar', 'baz' => 'qux'],
            ],
            [  // Values are automatically escaped.
                'foo="&amp;&quot;&apos;&lt;&gt;"',
                ['foo' => '&"\'<>'],
            ],
            [  // Escaped values are not double encoded.
                'foo="&amp;&quot;&apos;&lt;&gt;"',
                ['foo' => '&amp;&quot;&apos;&lt;&gt;'],
            ],
            [  // Integers are permitted.
                'foo="123"',
                ['foo' => 123],
            ],
            [  // Floats are permitted.
                'foo="12.3"',
                ['foo' => 12.3],
            ],
        ];
    }

    /**
     * @dataProvider providesAttributesStrings
     * @param array<string, string|int|float|bool> $input
     */
    public function testCreateattributesCanCreateBooleanAttributes(
        string $expected,
        array $input
    ): void {
        $helper = new Html5OutputHelper();

        $this->assertSame($expected, $helper->createAttributes($input));
    }

    /** @return array<int, array<int, mixed>> */
    public function providesBooleanAttributesWithInvalidNames(): array
    {
        return [
            [
                '',
                ['' => true],
            ],
            [
                'foo!',
                ['foo!' => true],
            ],
            [
                'one two',
                ['one two' => true],
            ],
            [
                'xml',
                ['xml' => true],
            ],
            [
                'XML',
                ['XML' => true],
            ],
            [
                '1-for-all',
                ['1-for-all' => true],
            ],
            [
                '-foo',
                ['-foo' => true],
            ],
            [
                '.foo',
                ['.foo' => true],
            ],
        ];
    }

    /**
     * @dataProvider providesBooleanAttributesWithInvalidNames
     * @phpstan-param array<string, string|int|float|bool> $attrsWithInvalidNames (Using the valid type to silence PHPStan.)
     */
    public function testCreateattributesThrowsAnExceptionIfABooleanAttributeNameIsInvalid(
        string $invalidName,
        array $attrsWithInvalidNames
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Attribute name `{$invalidName}` is invalid.");

        (new Html5OutputHelper())->createAttributes($attrsWithInvalidNames);
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
     * @param array<string, string|int|float|bool> $attributes
     */
    public function testCreateelCanCreateVoidElements(
        string $expected,
        string $tagName,
        array $attributes
    ): void {
        $helper = new Html5OutputHelper();

        $this->assertSame($expected, $helper->createEl($tagName, $attributes));
    }

    public function testCreateelUsesCreateattributes(): void
    {
        $helperMock = $this
            ->getMockBuilder(Html5OutputHelper::class)
            ->onlyMethods(['createAttributes'])
            ->getMock()
        ;

        $helperMock
            ->expects($this->once())
            ->method('createAttributes')
            ->with($this->equalTo(['checked' => true]))
            ->willReturn('checked')
        ;

        /** @var Html5OutputHelper $helperMock */
        $voidElWithBooleanAttr = $helperMock->createEl('input', ['checked' => true]);

        $this->assertSame('<input checked>', $voidElWithBooleanAttr);
    }

    public function testCreateelThrowsAnExceptionIfAnAttemptIsMadeToCreateAVoidElementWithContent(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Content was passed: a void element may not have content.');

        (new Html5OutputHelper())->createEl('br', 'foo');
    }

    public function testCanCreateElementsMagically(): void
    {
        $helperMock = $this
            ->getMockBuilder(Html5OutputHelper::class)
            ->onlyMethods(['createEl'])
            ->getMock()
        ;

        $helperMock
            ->expects($this->once())
            ->method('createEl')
            ->with(
                $this->equalTo('input'),
                $this->equalTo(['checked' => true])
            )
            ->willReturn('<input checked>')
        ;

        /** @var Html5OutputHelper $helperMock */
        $voidElWithBooleanAttr = $helperMock->createInput(['checked' => true]);

        $this->assertSame('<input checked>', $voidElWithBooleanAttr);
    }

    public function testCallThrowsAnExceptionIfTheMethodDoesNotExist(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The method, `create`, does not exist.');

        (new Html5OutputHelper())->create();
    }
}
