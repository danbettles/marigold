<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\TemplateFile;

use DanBettles\Marigold\Exception\FileNotFoundException;
use DanBettles\Marigold\TemplateFile\AbstractTemplateFile;
use DanBettles\Marigold\TemplateFile\TemplateFileInterface;
use DanBettles\Marigold\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use SplFileInfo;

use const null;

class AbstractTemplateFileTest extends AbstractTestCase
{
    public function testIsAbstract()
    {
        $this->assertTrue($this->getTestedClass()->isAbstract());
    }

    public function testIsASplfileinfo()
    {
        $this->assertTrue($this->getTestedClass()->isSubclassOf(SplFileInfo::class));
    }

    public function testIsATemplateFile()
    {
        $this->assertTrue($this->getTestedClass()->implementsInterface(TemplateFileInterface::class));
    }

    public function providesExistentTemplateFileMetadata(): array
    {
        return [
            [
                null,
                $this->createFixturePathname('hello_world'),
            ],
            [
                null,
                $this->createFixturePathname('hello_world.php'),
            ],
            [
                'html',
                $this->createFixturePathname('hello_world.html.php'),
            ],
            [
                'json',  // Normalized, see.
                $this->createFixturePathname('hello_world.JSON.php'),
            ],
        ];
    }

    /** @dataProvider providesExistentTemplateFileMetadata */
    public function testIsConstructedWithThePathnameOfATemplate($ignore, $pathname)
    {
        /** @var MockObject|AbstractTemplateFile */
        $templateFile = $this->getMockForAbstractClass(AbstractTemplateFile::class, [
            'filename' => $pathname,
        ]);

        $this->assertSame($pathname, $templateFile->getPathname());
    }

    public function testConstructorThrowsAnExceptionIfTheFileDoesNotExist()
    {
        $templateFilePathname = $this->createFixturePathname('file_that_does_not_exist');

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("The file `{$templateFilePathname}` does not exist.");

        /** @var MockObject|AbstractTemplateFile */
        $this->getMockForAbstractClass(AbstractTemplateFile::class, [
            'filename' => $templateFilePathname,
        ]);
    }

    /** @dataProvider providesExistentTemplateFileMetadata */
    public function testGetoutputformat($expectedOutputFormat, $templateFilePathname)
    {
        /** @var MockObject|AbstractTemplateFile */
        $templateFile = $this->getMockForAbstractClass(AbstractTemplateFile::class, [
            'filename' => $templateFilePathname,
        ]);

        $this->assertSame($expectedOutputFormat, $templateFile->getOutputFormat());
    }
}
