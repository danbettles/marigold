<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\File\TemplateFile;
use DanBettles\Marigold\TemplateFileLoader;
use RangeException;

use function array_reverse;

class TemplateFileLoaderTest extends AbstractTestCase
{
    public function testIsConstructedFromOneOrMoreDirectoryPaths(): void
    {
        $fixturesDir = $this->createFixturePathname(__FUNCTION__);

        $paths = [
            "{$fixturesDir}/base",
            "{$fixturesDir}/overrides",
        ];

        $loader = new TemplateFileLoader($paths);

        $this->assertEquals($paths, $loader->getTemplateDirs());
    }

    public function testThrowsAnExceptionIfTheArrayOfDirectoryPathsIsEmpty(): void
    {
        $this->expectException(RangeException::class);
        $this->expectExceptionMessage('The array of directory paths is empty.');

        new TemplateFileLoader([]);
    }

    public function testThrowsAnExceptionIfADirectoryDoesNotExist(): void
    {
        $nonExistentDir = $this->createFixturePathname(__FUNCTION__ . "/non_existent");

        $this->expectException(RangeException::class);
        $this->expectExceptionMessage("The directory `{$nonExistentDir}` does not exist.");

        new TemplateFileLoader([
            $nonExistentDir,
        ]);
    }

    public function testFindtemplateReturnsTheFirstMatchingTemplateFile(): void
    {
        $fixturesDir = $this->createFixturePathname(__FUNCTION__);

        $paths = [
            "{$fixturesDir}/overrides",
            "{$fixturesDir}/base",
        ];

        $overridesBeforeBaseLoader = new TemplateFileLoader($paths);
        /** @var TemplateFile */
        $overrideTemplateFile = $overridesBeforeBaseLoader->findTemplate('hello_world.html.php');

        $this->assertInstanceOf(TemplateFile::class, $overrideTemplateFile);
        $this->assertSame("{$fixturesDir}/overrides/hello_world.html.php", $overrideTemplateFile->getPathname());

        $reversedPaths = array_reverse($paths);

        $baseBeforeOverridesLoader = new TemplateFileLoader($reversedPaths);
        $baseTemplateFile = $baseBeforeOverridesLoader->findTemplate('hello_world.html.php');

        $this->assertInstanceOf(TemplateFile::class, $baseTemplateFile);
        $this->assertSame("{$fixturesDir}/base/hello_world.html.php", $baseTemplateFile->getPathname());
    }

    public function testFindtemplateReturnsNullIfTheTemplateFileDoesNotExist(): void
    {
        $loader = new TemplateFileLoader([
            $this->createFixturePathname(__FUNCTION__),
        ]);

        $this->assertNull($loader->findTemplate('non_existent.php'));
    }
}
