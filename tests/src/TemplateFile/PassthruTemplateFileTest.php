<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\TemplateFile;

use DanBettles\Marigold\TemplateFile\AbstractTemplateFile;
use DanBettles\Marigold\TemplateFile\PassthruTemplateFile;
use DanBettles\Marigold\Tests\AbstractTestCase;

class PassthruTemplateFileTest extends AbstractTestCase
{
    public function testIsAnAbstracttemplatefile()
    {
        $this->assertTrue($this->getTestedClass()->isSubclassOf(AbstractTemplateFile::class));
    }

    public function providesInfoAboutSomeSupportedFiles(): array
    {
        return [
            [
                'Hello, World!',
                $this->createFixturePathname('hello_world'),
            ],
            [
                'Hello, World!',
                $this->createFixturePathname('hello_world.txt'),
            ],
            [
                '<message>Hello, World!</message>',
                $this->createFixturePathname('hello_world.xml'),
            ],
            [
                '{"message": "Hello, World!"}',
                $this->createFixturePathname('hello_world.json'),
            ],
            [
                "<?= 'Hello, World!' ?>",  // No interpretation, see.
                $this->createFixturePathname('hello_world.php'),
            ],
        ];
    }

    /** @dataProvider providesInfoAboutSomeSupportedFiles */
    public function testConstructorWillAcceptThePathnameOfAnyExistentFile($ignore, string $templateFilePathname)
    {
        $templateFile = new PassthruTemplateFile($templateFilePathname);

        $this->assertSame($templateFilePathname, $templateFile->getPathname());
    }

    /** @dataProvider providesInfoAboutSomeSupportedFiles */
    public function testRender($expectedOutput, $templateFilePathname)
    {
        $templateFile = new PassthruTemplateFile($templateFilePathname);

        $this->assertSame($expectedOutput, $templateFile->render());
    }
}
