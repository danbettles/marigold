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

    /** @return array<mixed[]> */
    public function providesArguments(): array
    {
        return [
            [
                '404 Not Found',
                [404],
            ],
            [
                '404 Not Found: Article #123',
                [404, 'Article #123'],
            ],
        ];
    }

    /**
     * @dataProvider providesArguments
     * @param array{0:int,1?:string} $arguments
     */
    public function testGetmessageReturnsAHelpfulMessage(
        string $expectedMessage,
        array $arguments
    ): void {
        $ex = new HttpException(...$arguments);

        $this->assertSame($expectedMessage, $ex->getMessage());
    }

    public function testTheHttpStatusCodeIsAccessible(): void
    {
        $expectedStatusCode = 500;

        $httpException = new HttpException($expectedStatusCode);

        $this->assertSame($expectedStatusCode, $httpException->getStatusCode());
        $this->assertSame($httpException->getStatusCode(), $httpException->getCode());
    }

    public function testGetstatustextReturnsTheStatusText(): void
    {
        $httpException = new HttpException(500);

        $this->assertSame('Internal Server Error', $httpException->getStatusText());
    }
}
