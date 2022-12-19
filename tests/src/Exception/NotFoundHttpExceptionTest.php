<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\Exception;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\Exception\HttpException;
use DanBettles\Marigold\Exception\NotFoundHttpException;

class NotFoundHttpExceptionTest extends AbstractTestCase
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
                '404 Not Found',
                [],
            ],
            [
                '404 Not Found: Article #123',
                ['Article #123'],
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
        $ex = new NotFoundHttpException(...$arguments);

        $this->assertSame($expectedMessage, $ex->getMessage());
    }
}
