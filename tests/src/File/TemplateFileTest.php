<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\File;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\File\FileInfo;
use DanBettles\Marigold\File\TemplateFile;
use RangeException;

use function file_exists;

use const null;

class TemplateFileTest extends AbstractTestCase
{
    public function testIsAFileinfo(): void
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
    public function testIsConstructedWithThePathnameOfATemplateFile($ignore, $pathname): void
    {
        $templateFile = new TemplateFile($pathname);

        $this->assertSame($pathname, $templateFile->getPathname());
    }

    public function testCanBeConstructedFromThePathnameOfAFileThatDoesNotExist(): void
    {
        $templateFilePathname = $this->createFixturePathname('non_existent.php');

        $this->assertFalse(file_exists($templateFilePathname));

        new TemplateFile($templateFilePathname);
    }

    public function testThrowsAnExceptionIfThePathnameDoesNotPointAtAFile(): void
    {
        $dir = $this->getFixturesDir();

        $this->expectException(RangeException::class);
        $this->expectExceptionMessage("The filename `{$dir}` does not point at a file.");

        new TemplateFile($dir);
    }

    /** @dataProvider providesExistentFileMetadata */
    public function testGetoutputformatReturnsTheOutputFormatOfTheTemplate(
        $expectedOutputFormat,
        $templateFilePathname
    ): void {
        $templateFile = new TemplateFile($templateFilePathname);

        $this->assertSame($expectedOutputFormat, $templateFile->getOutputFormat());
    }
}
