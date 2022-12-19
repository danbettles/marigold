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

    /** @return array<mixed[]> */
    public function providesArguments(): array
    {
        return [
            [
                '500 Internal Server Error',
                [],
            ],
            [
                '500 Internal Server Error: Failed to do something.',
                ['Failed to do something.'],
            ],
        ];
    }

    /**
     * @dataProvider providesArguments
     * @param array{0?:string} $arguments
     */
    public function testGetmessageReturnsAHelpfulMessage(
        string $expectedMessage,
        array $arguments
    ): void {
        $ex = new InternalServerErrorHttpException(...$arguments);

        $this->assertSame($expectedMessage, $ex->getMessage());
    }
}
