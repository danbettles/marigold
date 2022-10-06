<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Exception;

use DanBettles\Marigold\HttpResponse;
use Throwable;

use const null;

class NotFoundHttpException extends HttpException
{
    public function __construct(
        string $specifier = '',
        ?Throwable $previous = null
    ) {
        parent::__construct(HttpResponse::HTTP_NOT_FOUND, $specifier, $previous);
    }
}
