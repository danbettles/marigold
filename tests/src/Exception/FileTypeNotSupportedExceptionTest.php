<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\Exception;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\Exception\FileTypeNotSupportedException;
use RuntimeException;

class FileTypeNotSupportedExceptionTest extends AbstractTestCase
{
    public function testIsARuntimeexception(): void
    {
        $this->assertTrue($this->getTestedClass()->isSubclassOf(RuntimeException::class));
    }

    public function testCanBeThrownWithOnlyTheNameOfTheInvalidFileType(): void
    {
        $this->expectException(FileTypeNotSupportedException::class);
        $this->expectExceptionMessage("The file-type `foo` is not supported.");

        throw new FileTypeNotSupportedException('foo');
    }

    public function testCanBeThrownWithAListOfSupportedTypes(): void
    {
        $this->expectException(FileTypeNotSupportedException::class);
        $this->expectExceptionMessage('The file-type `foo` is not supported.  Supported types: bar; baz');

        throw new FileTypeNotSupportedException('foo', ['bar', 'baz']);
    }
}
