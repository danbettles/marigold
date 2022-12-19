<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\Exception;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\Exception\FileNotFoundException;
use RuntimeException;

class FileNotFoundExceptionTest extends AbstractTestCase
{
    public function testIsARuntimeexception(): void
    {
        $this->assertTrue($this->getTestedClass()->isSubclassOf(RuntimeException::class));
    }

    public function testGetmessageReturnsAHelpfulMessage(): void
    {
        $pathname = $this->createFixturePathname('non_existent.file');

        $ex = new FileNotFoundException($pathname);

        $this->assertSame("File `{$pathname}` does not exist.", $ex->getMessage());
    }
}
