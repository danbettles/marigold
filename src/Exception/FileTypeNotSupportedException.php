<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Exception;

use RuntimeException;
use Throwable;

use function implode;

use const null;

class FileTypeNotSupportedException extends RuntimeException
{
    public function __construct(
        string $unsupportedType,
        array $supportedTypes = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = "The file-type `{$unsupportedType}` is not supported.";

        if ($supportedTypes) {
            $message .= '  Supported types: ' . implode('; ', $supportedTypes);
        }

        parent::__construct($message, $code, $previous);
    }
}
