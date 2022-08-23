<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\Utils;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\Utils\FilesystemUtils;

use const null;
use const PATHINFO_EXTENSION;

class FilesystemUtilsTest extends AbstractTestCase
{
    public function testPathinfoReturnsInformationAboutAFilePath()
    {
        $this->assertEquals(
            [
                'dirname' => $this->getFixturesDir(),
                'basename' => 'hello_world.txt',
                'filename' => 'hello_world',
                'extension' => 'txt',
            ],
            FilesystemUtils::pathinfo($this->createFixturePathname('hello_world.txt'))
        );

        $this->assertSame(
            'txt',
            FilesystemUtils::pathinfo($this->createFixturePathname('hello_world.txt'), PATHINFO_EXTENSION)
        );

        $this->assertEquals(
            [
                'dirname' => $this->getFixturesDir(),
                'basename' => 'no_extension',
                'filename' => 'no_extension',
                'extension' => null,
            ],
            FilesystemUtils::pathinfo($this->createFixturePathname('no_extension'))
        );

        $this->assertNull(
            FilesystemUtils::pathinfo($this->createFixturePathname('no_extension'), PATHINFO_EXTENSION)
        );

        $this->assertEquals(
            [
                'dirname' => $this->getFixturesDir(),
                'basename' => 'blank_extension.',
                'filename' => 'blank_extension',
                'extension' => '',
            ],
            FilesystemUtils::pathinfo($this->createFixturePathname('blank_extension.'))
        );

        $this->assertSame(
            '',
            FilesystemUtils::pathinfo($this->createFixturePathname('blank_extension.'), PATHINFO_EXTENSION)
        );
    }
}
