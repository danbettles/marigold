<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\File;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\Exception\FileNotFoundException;
use DanBettles\Marigold\File\FileInfo;
use DanBettles\Marigold\File\TemplateFile;
use PHPUnit\Framework\MockObject\MockObject;

use const null;

class TemplateFileTest extends AbstractTestCase
{
    public function testIsAFileinfo()
    {
        $this->assertTrue($this->getTestedClass()->isSubclassOf(FileInfo::class));
    }

    public function providesExistentFileMetadata(): array
    {
        return [
            [
                null,
                $this->createFixturePathname('.hello_world'),
            ],
            [
                null,
                $this->createFixturePathname('hello_world'),
            ],
            [
                null,
                $this->createFixturePathname('hello_world.'),
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

    /** @dataProvider providesExistentFileMetadata */
    public function testIsConstructedWithThePathnameOfATemplate($ignore, $pathname)
    {
        /** @var MockObject|TemplateFile */
        $templateFile = $this->getMockForAbstractClass(TemplateFile::class, [
            'filename' => $pathname,
        ]);

        $this->assertSame($pathname, $templateFile->getPathname());
    }

    public function testConstructorThrowsAnExceptionIfTheFileDoesNotExist()
    {
        $templateFilePathname = $this->createFixturePathname('file_that_does_not_exist');

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("The file `{$templateFilePathname}` does not exist.");

        /** @var MockObject|TemplateFile */
        $this->getMockForAbstractClass(TemplateFile::class, [
            'filename' => $templateFilePathname,
        ]);
    }

    /** @dataProvider providesExistentFileMetadata */
    public function testGetoutputformatReturnsTheOutputFormatOfTheTemplate($expectedOutputFormat, $templateFilePathname)
    {
        /** @var MockObject|TemplateFile */
        $templateFile = $this->getMockForAbstractClass(TemplateFile::class, [
            'filename' => $templateFilePathname,
        ]);

        $this->assertSame($expectedOutputFormat, $templateFile->getOutputFormat());
    }
}
