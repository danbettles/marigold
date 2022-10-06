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

    public function testCanBeThrown(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('404 Not Found');

        throw new NotFoundHttpException();
    }

    public function testCanBeThrownWithASpecifier(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('404 Not Found: Article #123');

        throw new NotFoundHttpException('Article #123');
    }
}
