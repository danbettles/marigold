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
            "{$fixturesDir}/defaults",
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
            "{$fixturesDir}/defaults",
        ];

        $overridesBeforeDefaultsLoader = new TemplateFileLoader($paths);
        /** @var TemplateFile */
        $overrideTemplateFile = $overridesBeforeDefaultsLoader->findTemplate('hello_world.html.php');

        $this->assertInstanceOf(TemplateFile::class, $overrideTemplateFile);
        $this->assertSame("{$fixturesDir}/overrides/hello_world.html.php", $overrideTemplateFile->getPathname());

        $reversedPaths = array_reverse($paths);

        $defaultsBeforeOverridesLoader = new TemplateFileLoader($reversedPaths);
        /** @var TemplateFile */
        $defaultTemplateFile = $defaultsBeforeOverridesLoader->findTemplate('hello_world.html.php');

        $this->assertInstanceOf(TemplateFile::class, $defaultTemplateFile);
        $this->assertSame("{$fixturesDir}/defaults/hello_world.html.php", $defaultTemplateFile->getPathname());
    }

    public function testFindtemplateReturnsNullIfTheTemplateFileDoesNotExist(): void
    {
        $loader = new TemplateFileLoader([
            $this->createFixturePathname(__FUNCTION__),
        ]);

        $this->assertNull($loader->findTemplate('non_existent.php'));
    }
}
