<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\TemplateEngine;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\FileInfo;
use DanBettles\Marigold\TemplateEngine\TemplateFile;

use function array_map;
use function array_merge;
use function file_exists;

use const false;
use const null;
use const true;

class TemplateFileTest extends AbstractTestCase
{
    public function testIsAFileinfo(): void
    {
        $this->assertTrue($this->getTestedClass()->isSubclassOf(FileInfo::class));
    }

    /** @return array<int, array<int, mixed>> */
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
    public function testIsConstructedUsingThePathnameOfAFile(
        ?string $ignore,
        string $pathname
    ): void {
        $templateFile = new TemplateFile($pathname);

        $this->assertSame($pathname, $templateFile->getPathname());
    }

    public function testCanBeConstructedUsingThePathnameOfAFileThatDoesNotExist(): void
    {
        $templateFilePathname = $this->createFixturePathname('non_existent.file');

        $this->assertFalse(file_exists($templateFilePathname));

        new TemplateFile($templateFilePathname);
    }

    /** @return array<int, array<int, mixed>> */
    public function providesValidTemplateFiles(): array
    {
        $validTemplateFiles = array_map(function (array $args): array {
            return [
                true,
                $args[1],
            ];
        }, $this->providesExistentFileMetadata());

        return array_merge($validTemplateFiles, [
            [
                false,
                $this->createFixturePathname('not_a_template_file/'),
            ],
        ]);
    }

    /** @dataProvider providesValidTemplateFiles */
    public function testIsvalidReturnsTrueIfTheReferencedObjectReallyIsATemplateFile(
        bool $valid,
        string $templateFilePathname
    ): void {
        $this->assertSame($valid, (new TemplateFile($templateFilePathname))->isValid());
    }

    /** @dataProvider providesExistentFileMetadata */
    public function testGetoutputformatReturnsTheOutputFormatOfTheTemplate(
        ?string $expectedOutputFormat,
        string $templateFilePathname
    ): void {
        $templateFile = new TemplateFile($templateFilePathname);

        $this->assertSame($expectedOutputFormat, $templateFile->getOutputFormat());
    }
}
