<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\TemplateEngine;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\TemplateEngine\TemplateFile;
use DanBettles\Marigold\TemplateEngine\TemplateFileLoader;
use InvalidArgumentException;
use SplFileInfo;

use function array_reverse;
use function basename;

use const null;

class TemplateFileLoaderTest extends AbstractTestCase
{
    public function testIsConstructedUsingOneOrMoreDirectoryPaths(): void
    {
        $fixturesDir = $this->createFixturePathname(__FUNCTION__);

        $paths = [
            "{$fixturesDir}/defaults",
            "{$fixturesDir}/overrides",
        ];

        $loader = new TemplateFileLoader($paths);

        $this->assertSame($paths, $loader->getTemplateDirs());
    }

    public function testThrowsAnExceptionIfTheArrayOfDirectoryPathsIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The array of directory paths is empty.');

        new TemplateFileLoader([]);
    }

    public function testThrowsAnExceptionIfADirectoryDoesNotExist(): void
    {
        $nonExistentDir = $this->createFixturePathname(__FUNCTION__ . '/non_existent/');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Directory `{$nonExistentDir}` does not exist.");

        new TemplateFileLoader([
            $nonExistentDir,
        ]);
    }

    public function testFindtemplateReturnsTheFirstMatchingTemplateFile(): void
    {
        $fixturesDir = $this->createFixturePathname(__FUNCTION__);

        $paths = [
            "{$fixturesDir}/overrides",
            "{$fixturesDir}/defaults",
        ];

        $overridesBeforeDefaultsLoader = new TemplateFileLoader($paths);
        /** @var TemplateFile */
        $overrideTemplateFile = $overridesBeforeDefaultsLoader->findTemplate('empty.php');

        $this->assertInstanceOf(TemplateFile::class, $overrideTemplateFile);
        $this->assertSame("{$fixturesDir}/overrides/empty.php", $overrideTemplateFile->getPathname());

        $reversedPaths = array_reverse($paths);

        $defaultsBeforeOverridesLoader = new TemplateFileLoader($reversedPaths);
        /** @var TemplateFile */
        $defaultTemplateFile = $defaultsBeforeOverridesLoader->findTemplate('empty.php');

        $this->assertInstanceOf(TemplateFile::class, $defaultTemplateFile);
        $this->assertSame("{$fixturesDir}/defaults/empty.php", $defaultTemplateFile->getPathname());
    }

    public function testFindtemplateReturnsNullIfTheTemplateFileDoesNotExist(): void
    {
        $fixturesDir = $this->createFixturePathname(__FUNCTION__);
        $loader = new TemplateFileLoader([$fixturesDir]);

        $nonExistentFile = "{$fixturesDir}/non_existent.file";
        $this->assertFileDoesNotExist($nonExistentFile);

        $this->assertNull($loader->findTemplate(basename($nonExistentFile)));  // Relative path.
        $this->assertNull($loader->findTemplate($nonExistentFile));  // Absolute path.

        $notATemplateFile = "{$fixturesDir}/not_a_template_file/";
        $this->assertDirectoryExists($notATemplateFile);

        $this->assertNull($loader->findTemplate(basename($notATemplateFile)));  // Relative path.  Exists but not a file.
        $this->assertNull($loader->findTemplate($notATemplateFile));  // Absolute path.  Exists but not a file.
    }

    /** @return array<int,array<int,mixed>> */
    public function providesInvalidPathnames(): array
    {
        return [
            [
                '',
            ],
            [
                new SplFileInfo(''),
            ],
            [
                null,
            ],
        ];
    }

    /**
     * @dataProvider providesInvalidPathnames
     * @param string|SplFileInfo $pathnameOrFileInfo
     */
    public function testFindtemplateThrowsAnExceptionIfThePathnameIsInvalid($pathnameOrFileInfo): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The pathname is invalid.');

        (new TemplateFileLoader([$this->createFixturePathname(__FUNCTION__)]))
            ->findTemplate($pathnameOrFileInfo)
        ;
    }

    public function testFindtemplatePrioritisesAbsolutePathnames(): void
    {
        $fixturesDir = $this->createFixturePathname(__FUNCTION__);
        $expectedPathname = "{$fixturesDir}/empty.php";

        /** @var TemplateFile */
        $templateFileFromPathname = (new TemplateFileLoader([$fixturesDir]))
            ->findTemplate($expectedPathname)
        ;

        $this->assertInstanceOf(TemplateFile::class, $templateFileFromPathname);
        $this->assertSame($expectedPathname, $templateFileFromPathname->getPathname());

        /** @var TemplateFile */
        $templateFileFromFileInfo = (new TemplateFileLoader([$fixturesDir]))
            ->findTemplate(new SplFileInfo($expectedPathname))
        ;

        $this->assertInstanceOf(TemplateFile::class, $templateFileFromFileInfo);
        $this->assertSame($expectedPathname, $templateFileFromFileInfo->getPathname());
    }

    public function testFindtemplateAcceptsAFileInfoObject(): void
    {
        $fixturesDir = $this->createFixturePathname(__FUNCTION__);
        $splFileInfo = new SplFileInfo('empty.php');

        /** @var TemplateFile */
        $templateFile = (new TemplateFileLoader([$fixturesDir]))->findTemplate($splFileInfo);

        $this->assertInstanceOf(TemplateFile::class, $templateFile);
        $this->assertSame("{$fixturesDir}/empty.php", $templateFile->getPathname());
    }
}
