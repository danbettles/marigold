<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\OutputHelper;

use BadMethodCallException;
use DanBettles\Marigold\OutputHelper\Html5OutputHelper;
use DanBettles\Marigold\OutputHelper\XmlOutputHelper;
use DanBettles\Marigold\Tests\AbstractTestCase;
use RangeException;
use ReflectionClass;

class Html5OutputHelperTest extends AbstractTestCase
{
    public function testIsAXmloutputhelper()
    {
        $class = new ReflectionClass(Html5OutputHelper::class);

        $this->assertTrue($class->isSubclassOf(XmlOutputHelper::class));
    }

    public function providesVoidElementArgs(): array
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
     * @dataProvider providesVoidElementArgs
     */
    public function testCreateelCanCreateVoidElements($expected, $tagName, $attributes)
    {
        $helper = new Html5OutputHelper();

        $this->assertSame($expected, $helper->createEl($tagName, $attributes));
    }

    public function testCreateelThrowsAnExceptionIfAnAttemptIsMadeToCreateAVoidElementWithContent()
    {
        $this->expectException(RangeException::class);
        $this->expectExceptionMessage('Content was passed: a void element may not have content.');

        (new Html5OutputHelper())->createEl('br', 'foo');
    }

    public function testCanCreateElementsMagically()
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
    }

    public function testCallThrowsAnExceptionIfTheMethodDoesNotExist()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The method, `create`, does not exist.');

        (new Html5OutputHelper())->create();
    }
}
