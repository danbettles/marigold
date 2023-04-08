<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\Exception\FileNotFoundException;
use DanBettles\Marigold\Php;
use Error;

use const null;

class PhpTest extends AbstractTestCase
{
    public function testExecutefileIsAnInstanceMethod(): void
    {
        $executeFileMethod = $this->getTestedClass()->getMethod('executeFile');

        $this->assertTrue($executeFileMethod->isPublic());
        $this->assertFalse($executeFileMethod->isStatic());
    }

    public function testExecutefileExecutesAPhpFileAndReturnsTheReturnValue(): void
    {
        $returnValue = (new Php())->executeFile($this->createFixturePathname('returns_a_value.php'));

        $this->assertSame([
            'foo' => 'bar',
        ], $returnValue);
    }

    public function testFilesExecutedByExecutefileDoNotHaveAccessToItsParameters(): void
    {
        $phpFilePathname = $this->createFixturePathname('returns_defined_vars.php');
        $returnValue = (new Php())->executeFile($phpFilePathname);

        $this->assertSame([
            '__FILE__' => $phpFilePathname,
            '__OUTPUT__' => '',
        ], $returnValue);
    }

    public function testFilesExecutedByExecutefileDoNotHaveAccessToTheObjectToWhichItBelongs(): void
    {
        try {
            (new Php())->executeFile($this->createFixturePathname('attempts_to_access_this.php'));
        } catch (Error $err) {
            $this->assertSame('Using $this when not in object context', $err->getMessage());
            return;
        }

        $this->fail();
    }

    public function testExecutefileExecutesAPhpFileAndPassesBackAnyOutput(): void
    {
        $output = null;
        $something = (new Php())->executeFile($this->createFixturePathname('hello_world.php'), [], $output);

        $this->assertSame('Hello, World!', $output);
        $this->assertSame(1, $something);
    }

    public function testExecutefileExecutesAPhpFileUsingTheSpecifiedContext(): void
    {
        $output = null;

        (new Php())->executeFile($this->createFixturePathname('hello_name.php'), [
            'name' => 'Dan',
        ], $output);

        $this->assertSame('Hello, Dan!', $output);
    }

    public function testExecutefileStripsTheUtf8BomIfThereIsOne(): void
    {
        $output = null;

        (new Php())->executeFile(
            $this->createFixturePathname('stylesheet_with_a_utf8_bom.css'),
            [],
            $output
        );

        // (No BOM)
        $this->assertSame('html { font-family: "Comic Sans MS", "Comic Sans", cursive }', $output);
    }

    public function testExecutefileThrowsAnExceptionIfThePhpFileDoesNotExist(): void
    {
        $nonExistentFile = $this->createFixturePathname('non_existent.file');

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("`{$nonExistentFile}`");

        (new Php())->executeFile($nonExistentFile);
    }
}
