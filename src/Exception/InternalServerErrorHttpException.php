<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Exception;

use DanBettles\Marigold\HttpResponse;
use Throwable;

use const null;

class InternalServerErrorHttpException extends HttpException
{
    public function __construct(
        string $specifier = '',
        ?Throwable $previous = null
    ) {
        parent::__construct(HttpResponse::HTTP_INTERNAL_SERVER_ERROR, $specifier, $previous);
    }
}
