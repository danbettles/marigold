<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Exception;

use RuntimeException;
use Throwable;

use const null;

class FileNotFoundException extends RuntimeException
{
    public function __construct(
        string $pathname,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = "The file `{$pathname}` does not exist.";

        parent::__construct($message, $code, $previous);
    }
}
