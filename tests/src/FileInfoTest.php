<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\FileInfo;
use SplFileInfo;

class FileInfoTest extends AbstractTestCase
{
    public function testIsASplfileinfo(): void
    {
        $this->assertTrue($this->getTestedClass()->isSubclassOf(SplFileInfo::class));
    }

    /** @return array<mixed[]> */
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

    /**
     * @dataProvider providesExistentFileMetadata
     * @param string[] $expectedExtensions
     */
    public function testGetextensionsReturnsAllExtensionsInTheFilename(
        string $ignore,
        array $expectedExtensions,
        string $pathname
    ): void {
        $fileInfo = new FileInfo($pathname);

        $this->assertSame($expectedExtensions, $fileInfo->getExtensions());
    }

    /**
     * @dataProvider providesExistentFileMetadata
     * @param string[] $ignore
     */
    public function testGetbasenameminusextensionReturnsTheBasenameMinusExtension(
        string $expectedBasename,
        array $ignore,
        string $pathname
    ): void {
        $fileInfo = new FileInfo($pathname);

        $this->assertSame($expectedBasename, $fileInfo->getBasenameMinusExtension());
    }
}
