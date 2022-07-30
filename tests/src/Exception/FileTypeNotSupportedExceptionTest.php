<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\Exception;

use DanBettles\Marigold\Exception\FileTypeNotSupportedException;
use DanBettles\Marigold\Tests\AbstractTestCase;
use ReflectionClass;
use RuntimeException;

class FileTypeNotSupportedExceptionTest extends AbstractTestCase
{
    public function testIsARuntimeexception()
    {
        $class = new ReflectionClass(FileTypeNotSupportedException::class);

        $this->assertTrue($class->isSubclassOf(RuntimeException::class));
    }

    public function testThrowingWithOnlyTheNameOfTheInvalidFileType()
    {
        $this->expectException(FileTypeNotSupportedException::class);
        $this->expectExceptionMessage("The file-type `foo` is not supported.");

        throw new FileTypeNotSupportedException('foo');
    }

    public function testThrowingWithAListOfSupportedTypes()
    {
        $this->expectException(FileTypeNotSupportedException::class);
        $this->expectExceptionMessage('The file-type `foo` is not supported.  Supported types: bar; baz');

        throw new FileTypeNotSupportedException('foo', ['bar', 'baz']);
    }
}
