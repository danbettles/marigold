<?php

declare(strict_types=1);

namespace DanBettles\Marigold\TemplateEngine;

use SplFileInfo;

use function func_get_args;

use const null;

class OutputFacade
{
    private Engine $engine;

    /**
     * @var array{int:string|SplFileInfo,int:string,int:mixed[]}|null
     */
    private ?array $wrapperArgs;

    public function __construct(Engine $engine)
    {
        $this
            ->setEngine($engine)
            ->setWrapperArgs(null)
        ;
    }

    /**
     * @param string|SplFileInfo $pathnameOrFileInfo
     * @param array<string,mixed> $variables
     */
    public function include(
        $pathnameOrFileInfo,
        array $variables = []
    ): string {
        return $this->getEngine()->render($pathnameOrFileInfo, $variables);
    }

    private function setEngine(Engine $engine): self
    {
        $this->engine = $engine;
        return $this;
    }

    public function getEngine(): Engine
    {
        return $this->engine;
    }

    /**
     * @param array{int:string|SplFileInfo,int:string,int:mixed[]}|null $args
     */
    private function setWrapperArgs(?array $args): self
    {
        $this->wrapperArgs = $args;
        return $this;
    }

    /**
     * @param string|SplFileInfo $pathnameOrFileInfo
     * @param array<string,mixed> $variables
     */
    public function wrapWith(
        $pathnameOrFileInfo,
        string $targetVarName,
        array $variables = []
    ): self {
        /** @var array{int:string|SplFileInfo,int:string,int:mixed[]} */
        $wrapperArgs = func_get_args();
        return $this->setWrapperArgs($wrapperArgs);
    }

    /**
     * Alias for `wrapWith()`.
     *
     * @param string|SplFileInfo $pathnameOrFileInfo
     * @param array<string,mixed> $variables
     */
    public function insertInto(
        $pathnameOrFileInfo,
        string $targetVarName,
        array $variables = []
    ): self {
        return $this->wrapWith($pathnameOrFileInfo, $targetVarName, $variables);
    }

    /**
     * @return array{int:string|SplFileInfo,int:string,int:mixed[]}|null
     */
    public function getWrapperArgs(): ?array
    {
        return $this->wrapperArgs;
    }
}
