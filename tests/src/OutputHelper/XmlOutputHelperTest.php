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
    public function testIsAnOutputhelper(): void
    {
        $this->assertTrue($this->getTestedClass()->implementsInterface(OutputHelperInterface::class));
    }

    public function testGetencodingReturnsTheEncodingSetUsingSetencoding(): void
    {
        $helper = new XmlOutputHelper();

        $this->assertNull($helper->getEncoding());

        $something = $helper->setEncoding('UTF-8');

        $this->assertSame('UTF-8', $helper->getEncoding());
        $this->assertSame($helper, $something);
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
    }

    /** @return array<int, array<int, mixed>> */
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
     * @param array<string, string> $attributesWithInvalidValues (Using valid type to silence PHPStan.)
     */
    public function testCreateelThrowsAnExceptionIfTheValueOfAnAttributeIsInvalid(array $attributesWithInvalidValues): void
    {
        $this->expectError();
        $this->expectErrorMessageMatches('@ must be of the type string, \w+ given, @');

        (new XmlOutputHelper())->createEl('foo', $attributesWithInvalidValues);
    }

    public function testAutomaticallyEscapesAttributeValuesIfNecessary(): void
    {
        $helper = new XmlOutputHelper();

        $this->assertSame(
            '<foo bar="&amp;&quot;&apos;&lt;&gt;"/>',
            $helper->createEl('foo', ['bar' => '&"\'<>'])
        );
    }

    public function testWillNotEncodeExistingEntitiesInAttributeValues(): void
    {
        $helper = new XmlOutputHelper();

        $this->assertSame(
            '<foo bar="&amp;&quot;&apos;&lt;&gt;"/>',
            $helper->createEl('foo', ['bar' => '&amp;&quot;&apos;&lt;&gt;'])
        );
    }

    /** @return array<int, array<int, mixed>> */
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
     * @param string $invalidContent (Using valid type to silence PHPStan.)
     */
    public function testCreateelThrowsAnExceptionIfTheContentIsInvalid($invalidContent): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The content is not a string/null.');

        (new XmlOutputHelper())->createEl('foo', [], $invalidContent);
    }

    /** @return array<int, array<int, mixed>> */
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
    public function testEscapeEscapesSpecialCharsInTheInput(
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

        /** @var MockObject */
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

    /** @return array<int, array<int, mixed>> */
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
     * @param array<string, string> $input
     */
    public function testCreateattributesCreatesAttributesHtmlFromAnArrayOfKeyValuePairs(
        string $expected,
        array $input
    ): void {
        $helper = new XmlOutputHelper();

        $this->assertSame($expected, $helper->createAttributes($input));
    }
}
