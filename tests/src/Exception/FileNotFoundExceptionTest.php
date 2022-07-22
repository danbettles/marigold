<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\Exception;

use DanBettles\Marigold\Exception\FileNotFoundException;
use DanBettles\Marigold\Tests\AbstractTestCase;
use ReflectionClass;
use RuntimeException;

class FileNotFoundExceptionTest extends AbstractTestCase
{
    public function testIsARuntimeexception()
    {
        $class = new ReflectionClass(FileNotFoundException::class);

        $this->assertTrue($class->isSubclassOf(RuntimeException::class));
    }
}
