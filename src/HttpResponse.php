<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use function header;

use const null;

class HttpResponse
{
    /** @var int */
    public const HTTP_OK = 200;
    /** @var int */
    public const HTTP_NOT_FOUND = 404;
    /** @var int */
    public const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * @var array<int,string>
     */
    public const STATUS_TEXTS = [
        self::HTTP_OK => 'OK',
        self::HTTP_NOT_FOUND => 'Not Found',
        self::HTTP_INTERNAL_SERVER_ERROR => 'Internal Server Error',
    ];

    private int $statusCode;

    private string $content;

    public function __construct(
        string $content = '',
        int $statusCode = self::HTTP_OK
    ) {
        $this
            ->setContent($content)
            ->setStatusCode($statusCode)
        ;
    }

    protected function sendHeader(string $name, string $value = null): void
    {
        $header = null === $value
            ? $name
            : "{$name}: {$value}"
        ;

        header($header);
    }

    public function send(HttpRequest $request): void
    {
        /** @var array{SERVER_PROTOCOL?:string} */
        $serverVars = $request->server;

        $statusHeader = (
            ($serverVars['SERVER_PROTOCOL'] ?? 'HTTP/1.0')
            . ' '
            . (string) $this->getStatusCode()
            . ' '
            . self::STATUS_TEXTS[$this->getStatusCode()]
        );

        $this->sendHeader($statusHeader);

        // phpcs:ignore
        echo $this->getContent();
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
