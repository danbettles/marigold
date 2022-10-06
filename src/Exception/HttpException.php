<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Exception;

use DanBettles\Marigold\HttpResponse;
use RuntimeException;
use Throwable;

use const null;

class HttpException extends RuntimeException
{
    private string $statusText;

    public function __construct(
        int $statusCode,
        string $specifier = '',
        ?Throwable $previous = null
    ) {
        $this->setStatusText(HttpResponse::STATUS_TEXTS[$statusCode] ?? '');

        $message = (
            "{$statusCode} {$this->getStatusText()}"
            . ('' === $specifier ? $specifier : ": {$specifier}")
        );

        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * An alias for `getCode()`.
     */
    public function getStatusCode(): int
    {
        return (int) $this->getCode();
    }

    private function setStatusText(string $text): self
    {
        $this->statusText = $text;
        return $this;
    }

    public function getStatusText(): string
    {
        return $this->statusText;
    }
}
