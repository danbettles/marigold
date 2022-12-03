<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use const null;

/**
 * For now, just a container, for convenience.
 */
class HttpRequest
{
    /**
     * @var array<string,string>
     */
    public array $query;

    /**
     * @var array<string,string>
     */
    public array $request;

    /**
     * @var array<string,string|string[]>
     */
    public array $server;

    /**
     * @var mixed
     */
    private $content;

    /**
     * Somewhere to store additional information about the request.
     *
     * @var array<string,mixed>
     */
    public array $attributes;

    /**
     * @param array<string,string> $query
     * @param array<string,string> $request
     * @param array<string,string|string[]> $server
     * @param mixed $content = null
     */
    public function __construct(
        array $query,
        array $request,
        array $server,
        $content = null
    ) {
        $this->query = $query;
        $this->request = $request;
        $this->server = $server;
        $this->attributes = [];

        $this->setContent($content);
    }

    /**
     * @param mixed $content
     */
    public function setContent($content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    public static function createFromGlobals(): self
    {
        return new self($_GET, $_POST, $_SERVER);
    }
}
