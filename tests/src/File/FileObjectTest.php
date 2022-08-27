<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\File;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\File\FileInfo;
use DanBettles\Marigold\File\FileObject;

class FileObjectTest extends AbstractTestCase
{
    public function testIsAFileinfo()
    {
        $this->assertTrue($this->getTestedClass()->isSubclassOf(FileInfo::class));
    }

    public function providesFileContents(): array
    {
        return [
            [
                '',
                $this->createFixturePathname('empty_file'),
            ],
            [
                'Hello, World!',
                $this->createFixturePathname('hello_world'),
            ],
        ];
    }

    /** @dataProvider providesFileContents */
    public function testGetcontentsReturnsTheContentsOfTheFile($expected, string $pathname)
    {
        $file = new FileObject($pathname);

        $this->assertSame($expected, $file->getContents());
    }
}
