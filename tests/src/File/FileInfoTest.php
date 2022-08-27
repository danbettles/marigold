<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\File;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\File\FileInfo;
use SplFileInfo;

class FileInfoTest extends AbstractTestCase
{
    public function testIsASplfileinfo()
    {
        $this->assertTrue($this->getTestedClass()->isSubclassOf(SplFileInfo::class));
    }

    public function providesExistentFileMetadata(): array
    {
        return [
            [
                '',
                ['hello_world'],
                $this->createFixturePathname('.hello_world'),
            ],
            [
                'hello_world',
                [],  // No extensions.
                $this->createFixturePathname('hello_world'),
            ],
            [
                'hello_world',
                [''],  // A single, blank extension.
                $this->createFixturePathname('hello_world.'),
            ],
            [
                'hello_world',
                ['php'],
                $this->createFixturePathname('hello_world.php'),
            ],
            [
                'hello_world.html',
                ['html', 'php'],
                $this->createFixturePathname('hello_world.html.php'),
            ],
            [
                'hello_world.JSON',
                ['JSON', 'php'],  // Case as-is.
                $this->createFixturePathname('hello_world.JSON.php'),
            ],
        ];
    }

    /** @dataProvider providesExistentFileMetadata */
    public function testGetextensionsReturnsAllExtensionsInTheFilename($ignore, $expectedExtensions, $pathname)
    {
        $fileInfo = new FileInfo($pathname);

        $this->assertSame($expectedExtensions, $fileInfo->getExtensions());
    }

    /** @dataProvider providesExistentFileMetadata */
    public function testGetbasenameminusextensionReturnsTheBasenameMinusExtension(
        $expectedBasename,
        $ignore,
        $pathname
    ) {
        $fileInfo = new FileInfo($pathname);

        $this->assertSame($expectedBasename, $fileInfo->getBasenameMinusExtension());
    }
}
