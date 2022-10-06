<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\Exception;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\Exception\HttpException;
use DanBettles\Marigold\Exception\InternalServerErrorHttpException;

class InternalServerErrorHttpExceptionTest extends AbstractTestCase
{
    public function testIsAnHttpexception(): void
    {
        $this->assertTrue($this->getTestedClass()->isSubclassOf(HttpException::class));
    }

    public function testCanBeThrown(): void
    {
        $this->expectException(InternalServerErrorHttpException::class);
        $this->expectExceptionMessage('500 Internal Server Error');

        throw new InternalServerErrorHttpException();
    }

    public function testCanBeThrownWithASpecifier(): void
    {
        $this->expectException(InternalServerErrorHttpException::class);
        $this->expectExceptionMessage('500 Internal Server Error: Failed to do something.');

        throw new InternalServerErrorHttpException('Failed to do something.');
    }
}
