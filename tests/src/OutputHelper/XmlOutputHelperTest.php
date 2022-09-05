<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\OutputHelper;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\OutputHelper\OutputHelperInterface;
use DanBettles\Marigold\OutputHelper\XmlOutputHelper;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

use const false;
use const null;

// @todo Test that `createEl()`/`createElement()` calls `createAttributes()`.
class XmlOutputHelperTest extends AbstractTestCase
{
    public function testIsAnOutputhelper()
    {
        $this->assertTrue($this->getTestedClass()->implementsInterface(OutputHelperInterface::class));
    }

    public function testGetencodingReturnsTheEncodingSetUsingSetencoding()
    {
        $helper = new XmlOutputHelper();

        $this->assertNull($helper->getEncoding());

        $something = $helper->setEncoding('UTF-8');

        $this->assertSame('UTF-8', $helper->getEncoding());
        $this->assertSame($helper, $something);
    }

    public function testCreateelCreatesAnElement()
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
    }

    public function providesAttributesWithInvalidValues(): array
    {
        return [
            [
                ['bar' => null],
            ],
            [
                ['bar' => 123],
            ],
            [
                ['bar' => 1.23],
            ],
            [
                ['bar' => false],
            ],
            [
                ['bar' => []],
            ],
            [
                ['bar' => new stdClass()],
            ],
        ];
    }

    /**
     * @dataProvider providesAttributesWithInvalidValues
     */
    public function testCreateelThrowsAnExceptionIfTheValueOfAnAttributeIsInvalid(array $attributesWithInvalidValues)
    {
        $this->expectError();
        $this->expectErrorMessageMatches('@ must be of the type string, \w+ given, @');

        (new XmlOutputHelper())->createEl('foo', $attributesWithInvalidValues);
    }

    public function testAutomaticallyEscapesAttributeValuesIfNecessary()
    {
        $helper = new XmlOutputHelper();

        $this->assertSame(
            '<foo bar="&amp;&quot;&apos;&lt;&gt;"/>',
            $helper->createEl('foo', ['bar' => '&"\'<>'])
        );
    }

    public function testWillNotEncodeExistingEntitiesInAttributeValues()
    {
        $helper = new XmlOutputHelper();

        $this->assertSame(
            '<foo bar="&amp;&quot;&apos;&lt;&gt;"/>',
            $helper->createEl('foo', ['bar' => '&amp;&quot;&apos;&lt;&gt;'])
        );
    }

    public function providesInvalidContent(): array
    {
        return [
            [
                123,
            ],
            [
                1.23,
            ],
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
     */
    public function testCreateelThrowsAnExceptionIfTheContentIsInvalid($invalidContent)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The content is not a string/null.');

        (new XmlOutputHelper())->createEl('foo', [], $invalidContent);
    }

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

    /**
     * @dataProvider providesEscapedStrings
     */
    public function testEscapeEscapesSpecialCharsInTheInput($expected, $input)
    {
        $helper = new XmlOutputHelper();

        $this->assertSame($expected, $helper->escape($input));
    }

    public function testEscapeUsesTheEncoding()
    {
        // Something weird so we can easily see that the encoding is used.
        $japaneseEncoding = 'SJIS';

        /** @var MockObject|XmlOutputHelper */
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

        $this->assertNotSame($sourceUtf8Str, $helperMock->escape($sourceUtf8Str));
    }

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
            [
                'foo="&amp;&quot;&apos;&lt;&gt;"',
                ['foo' => '&"\'<>'],
            ],
        ];
    }

    /**
     * @dataProvider providesAttributesStrings
     */
    public function testCreateattributesCreatesAttributesHtmlFromAnArrayOfKeyValuePairs($expected, $input)
    {
        $helper = new XmlOutputHelper();

        $this->assertSame($expected, $helper->createAttributes($input));
    }
}
