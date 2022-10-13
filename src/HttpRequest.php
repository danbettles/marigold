<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

/**
 * For now, just a container, for convenience.
 */
class HttpRequest
{
    /**
     * @var array<string, string>
     */
    public array $query;

    /**
     * @var array<string, string>
     */
    public array $request;

    /**
     * @var array<string, string>
     */
    public array $server;

    /**
     * @param array<string, string> $query
     * @param array<string, string> $request
     * @param array<string, string> $server
     */
    public function __construct(
        array $query,
        array $request,
        array $server
    ) {
        $this->query = $query;
        $this->request = $request;
        $this->server = $server;
    }

    public static function fromGlobals(): self
    {
        return new self($_GET, $_POST, $_SERVER);
    }
}