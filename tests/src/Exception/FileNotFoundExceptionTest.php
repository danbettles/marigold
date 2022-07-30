<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\Exception;

use DanBettles\Marigold\Exception\FileNotFoundException;
use DanBettles\Marigold\Tests\AbstractTestCase;
use RuntimeException;

class FileNotFoundExceptionTest extends AbstractTestCase
{
    public function testIsARuntimeexception()
    {
        $this->assertTrue($this->getTestedClass()->isSubclassOf(RuntimeException::class));
    }

    public function testThrowing()
    {
        $pathname = $this->createFixturePathname('file_that_does_not_exist.php');

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("The file `{$pathname}` does not exist.");

        throw new FileNotFoundException($pathname);
    }
}
