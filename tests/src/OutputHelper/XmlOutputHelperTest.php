<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\OutputHelper;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\OutputHelper\XmlOutputHelper;
use InvalidArgumentException;
use stdClass;

use const false;
use const null;

class XmlOutputHelperTest extends AbstractTestCase
{
    public function testGetencodingReturnsTheEncodingSetUsingSetencoding(): void
    {
        $helper = new XmlOutputHelper();

        $this->assertNull($helper->getEncoding());

        $something = $helper->setEncoding('UTF-8');

        $this->assertSame('UTF-8', $helper->getEncoding());
        $this->assertSame($helper, $something);
    }

    /** @return array<mixed[]> */
    public function providesEscapedStrings(): array
    {
        return [
            [
                '',
                '',
            ],
            [
                '&amp;&quot;&apos;&lt;&gt;',
                '&"\'<>',
            ],
            [
                '&quot;The Life of Milarepa&quot; by Tsangnyon Heruka',
                '&quot;The Life of Milarepa&quot; by Tsangnyon Heruka',
            ],
        ];
    }

    /** @dataProvider providesEscapedStrings */
    public function testEscapeConvertsSpecialCharsInTheInput(
        string $expected,
        string $input
    ): void {
        $helper = new XmlOutputHelper();

        $this->assertSame($expected, $helper->escape($input));
    }

    public function testEscapeUsesTheEncoding(): void
    {
        // Something weird so we can easily see that the encoding is used.
        $japaneseEncoding = 'SJIS';

        $helperMock = $this
            ->getMockBuilder(XmlOutputHelper::class)
            ->onlyMethods(['getEncoding'])
            ->getMock()
        ;

        $helperMock
            ->method('getEncoding')
            ->willReturn($japaneseEncoding)
        ;

        $helperMock
            ->expects($this->once())
            ->method('getEncoding')
        ;

        $sourceUtf8Str = 'Ā';

        /** @var XmlOutputHelper $helperMock */
        $this->assertNotSame($sourceUtf8Str, $helperMock->escape($sourceUtf8Str));
    }

    /** @return array<mixed[]> */
    public function providesAttributesStrings(): array
    {
        return [
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
     * @param array<string,string|int|float> $input
     */
    public function testCreateattributesCreatesAnAttributesStringFromAnArrayOfKeyValuePairs(
        string $expected,
        array $input
    ): void {
        $helper = new XmlOutputHelper();

        $this->assertSame($expected, $helper->createAttributes($input));
    }

    public function testCreateattributesUsesEscape(): void
    {
        $helperMock = $this
            ->getMockBuilder(XmlOutputHelper::class)
            ->onlyMethods(['escape'])
            ->getMock()
        ;

        $helperMock
            ->expects($this->exactly(2))
            ->method('escape')
            ->withConsecutive(
                [$this->equalTo('bar')],
                [$this->equalTo('qux')]
            )
            ->willReturnOnConsecutiveCalls('bar', 'qux')
        ;

        /** @var XmlOutputHelper $helperMock */
        $attributesStr = $helperMock->createAttributes([
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertSame('foo="bar" baz="qux"', $attributesStr);
    }

    /** @return array<mixed[]> */
    public function providesAttributesWithInvalidNames(): array
    {
        return [
            [
                '',
                ['' => 'foo'],
            ],
            [
                'foo!',
                ['foo!' => 'bar'],
            ],
            [
                'one two',
                ['one two' => 'foo'],
            ],
            [
                'xml',
                ['xml' => 'foo'],
            ],
            [
                'XML',
                ['XML' => 'foo'],
            ],
            [
                '1-for-all',
                ['1-for-all' => 'and-all-for-one'],
            ],
            [
                '-foo',
                ['-foo' => 'bar'],
            ],
            [
                '.foo',
                ['.foo' => 'bar'],
            ],
        ];
    }

    /**
     * @dataProvider providesAttributesWithInvalidNames
     * @phpstan-param array<string,string|int|float> $attributesWithInvalidNames (Using the valid type to silence PHPStan.)
     */
    public function testCreateattributesThrowsAnExceptionIfAnAttributeNameIsInvalid(
        string $invalidName,
        array $attributesWithInvalidNames
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Attribute name `{$invalidName}` is invalid.");

        (new XmlOutputHelper())->createAttributes($attributesWithInvalidNames);
    }

    /** @return array<mixed[]> */
    public function providesAttributesWithInvalidValues(): array
    {
        return [
            [  // Boolean variables are not permitted because there are many ways to represent their value.
                'bar',
                ['bar' => false],
            ],
            [  // How would a `null` value -- as compared to a blank string -- be represented?
                'bar',
                ['bar' => null],
            ],
            [
                'bar',
                ['bar' => []],
            ],
            [
                'bar',
                ['bar' => new stdClass()],
            ],
        ];
    }

    /**
     * @dataProvider providesAttributesWithInvalidValues
     * @phpstan-param array<string,string|int|float> $attributesWithInvalidValues (Using the valid type to silence PHPStan.)
     */
    public function testCreateattributesThrowsAnExceptionIfTheTypeOfAnAttributeValueIsInvalid(
        string $invalidAttrName,
        array $attributesWithInvalidValues
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The type of attribute `{$invalidAttrName}` is invalid.");

        (new XmlOutputHelper())->createAttributes($attributesWithInvalidValues);
    }

    public function testCreateelCreatesAnElement(): void
    {
        $helper = new XmlOutputHelper();

        $this->assertSame(
            '<br/>',
            $helper->createEl('br')
        );

        $this->assertSame(
            '<p>Lorem ipsum dolor.</p>',
            $helper->createEl('p', 'Lorem ipsum dolor.')
        );

        $this->assertSame(
            '<p>Foo</p>',
            $helper->createEl('p', 'Foo', 'Bar')
        );

        $this->assertSame(
            '<p class="text-muted">Lorem ipsum dolor.</p>',
            $helper->createEl('p', ['class' => 'text-muted'], 'Lorem ipsum dolor.')
        );

        $this->assertSame(
            '<img src="pretty.jpg" alt="A pretty picture"/>',
            $helper->createEl('img', ['src' => 'pretty.jpg', 'alt' => 'A pretty picture'])
        );

        $this->assertSame(
            '<p>123</p>',
            $helper->createEl('p', 123)  // Integers are permitted.
        );

        $this->assertSame(
            '<p>12.3</p>',
            $helper->createEl('p', 12.3)  // Floats are permitted.
        );
    }

    public function testCreateelUsesCreateattributes(): void
    {
        $helperMock = $this
            ->getMockBuilder(XmlOutputHelper::class)
            ->onlyMethods(['createAttributes'])
            ->getMock()
        ;

        $helperMock
            ->expects($this->once())
            ->method('createAttributes')
            ->with($this->equalTo(['class' => 'text-muted']))
            ->willReturn('class="text-muted"')
        ;

        /** @var XmlOutputHelper $helperMock */
        $this->assertSame(
            '<p class="text-muted">Lorem ipsum dolor.</p>',
            $helperMock->createEl('p', ['class' => 'text-muted'], 'Lorem ipsum dolor.')
        );
    }

    /** @return array<mixed[]> */
    public function providesInvalidContent(): array
    {
        return [
            [
                false,
            ],
            [
                [],
            ],
            [
                new stdClass(),
            ],
        ];
    }

    /**
     * @dataProvider providesInvalidContent
     * @phpstan-param string $invalidContent (Using the valid type to silence PHPStan.)
     */
    public function testCreateelThrowsAnExceptionIfTheContentIsInvalid($invalidContent): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type of the content is invalid.');

        (new XmlOutputHelper())->createEl('foo', [], $invalidContent);
    }
}
