<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\Exception;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\Exception\HttpException;
use RuntimeException;

class HttpExceptionTest extends AbstractTestCase
{
    public function testIsARuntimeexception(): void
    {
        $this->assertTrue($this->getTestedClass()->isSubclassOf(RuntimeException::class));
    }

    public function testCanBeThrown(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('404 Not Found');

        throw new HttpException(404);
    }

    public function testCanBeThrownWithASpecifier(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('404 Not Found: Article #123');

        throw new HttpException(404, 'Article #123');
    }

    public function testTheHttpStatusCodeIsAccessible(): void
    {
        $expectedStatusCode = 500;
        $httpException = new HttpException($expectedStatusCode);

        $this->assertSame($expectedStatusCode, $httpException->getCode());
        $this->assertSame($httpException->getCode(), $httpException->getStatusCode());
    }

    public function testGetstatustextReturnsTheStatusText(): void
    {
        $httpException = new HttpException(500);

        $this->assertSame('Internal Server Error', $httpException->getStatusText());
    }
}
