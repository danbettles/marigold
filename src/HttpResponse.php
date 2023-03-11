<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use function array_key_exists;
use function header;

use const null;

/**
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Redirections#temporary_redirections
 */
class HttpResponse
{
    /** @var int */
    public const HTTP_OK = 200;
    /** @var int */
    public const HTTP_MULTIPLE_CHOICE = 300;
    /** @var int */
    public const HTTP_MOVED_PERMANENTLY = 301;
    /** @var int */
    public const HTTP_FOUND = 302;
    /** @var int */
    public const HTTP_SEE_OTHER = 303;
    /** @var int */
    public const HTTP_NOT_MODIFIED = 304;
    /** @var int */
    public const HTTP_TEMPORARY_REDIRECT = 307;
    /** @var int */
    public const HTTP_PERMANENT_REDIRECT = 308;
    /** @var int */
    public const HTTP_BAD_REQUEST = 400;
    /** @var int */
    public const HTTP_NOT_FOUND = 404;
    /** @var int */
    public const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * @var int[]
     */
    public const REDIRECT_STATUS_CODES = [
        self::HTTP_MULTIPLE_CHOICE,
        self::HTTP_MOVED_PERMANENTLY,
        self::HTTP_FOUND,
        self::HTTP_SEE_OTHER,
        self::HTTP_NOT_MODIFIED,
        self::HTTP_TEMPORARY_REDIRECT,
        self::HTTP_PERMANENT_REDIRECT,
    ];

    /**
     * @var array<int,string>
     */
    public const STATUS_TEXTS = [
        self::HTTP_OK => 'OK',
        self::HTTP_MULTIPLE_CHOICE => 'Multiple Choice',
        self::HTTP_MOVED_PERMANENTLY => 'Moved Permanently',
        self::HTTP_FOUND => 'Found',
        self::HTTP_SEE_OTHER => 'See Other',
        self::HTTP_NOT_MODIFIED => 'Not Modified',
        self::HTTP_TEMPORARY_REDIRECT => 'Temporary Redirect',
        self::HTTP_PERMANENT_REDIRECT => 'Permanent Redirect',
        self::HTTP_BAD_REQUEST => 'Bad Request',
        self::HTTP_NOT_FOUND => 'Not Found',
        self::HTTP_INTERNAL_SERVER_ERROR => 'Internal Server Error',
    ];

    private int $statusCode;

    private string $content;

    /**
     * @phpstan-var HeadersArray
     */
    private array $headers;

    /**
     * @phpstan-param HeadersArray $headers
     */
    public function __construct(
        string $content = '',
        int $statusCode = self::HTTP_OK,
        array $headers = []
    ) {
        $this
            ->setContent($content)
            ->setStatusCode($statusCode)
            ->setHeaders($headers)
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

        $statusText = array_key_exists($this->getStatusCode(), self::STATUS_TEXTS)
            ? ' ' . self::STATUS_TEXTS[$this->getStatusCode()]
            : ''
        ;

        $statusHeader = (
            ($serverVars['SERVER_PROTOCOL'] ?? 'HTTP/1.0')
            . ' '
            . (string) $this->getStatusCode()
            . $statusText
        );

        $headers = $this->getHeaders();
        $headers[$statusHeader] = null;

        foreach ($headers as $name => $value) {
            $this->sendHeader($name, $value);
        }

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

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @phpstan-param HeadersArray $headers
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @phpstan-return HeadersArray
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
