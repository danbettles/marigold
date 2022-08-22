<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\File;

use DanBettles\Marigold\File\FileInfo;
use DanBettles\Marigold\Tests\AbstractTestCase;
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
                ['hello_world'],
                $this->createFixturePathname('.hello_world'),
            ],
            [
                [],  // No extensions.
                $this->createFixturePathname('hello_world'),
            ],
            [
                [''],  // A single, blank extension.
                $this->createFixturePathname('hello_world.'),
            ],
            [
                ['php'],
                $this->createFixturePathname('hello_world.php'),
            ],
            [
                ['html', 'php'],
                $this->createFixturePathname('hello_world.html.php'),
            ],
            [
                ['JSON', 'php'],  // Case as-is.
                $this->createFixturePathname('hello_world.JSON.php'),
            ],
        ];
    }

    /** @dataProvider providesExistentFileMetadata */
    public function testGetextensions($expectedExtensions, $pathname)
    {
        $fileInfo = new FileInfo($pathname);

        $this->assertSame($expectedExtensions, $fileInfo->getExtensions());
    }
}
