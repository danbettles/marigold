<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use DanBettles\Marigold\TemplateEngine\Engine;

abstract class AbstractAction
{
    private Engine $templateEngine;

    public function __construct(Engine $templateEngine)
    {
        $this->setTemplateEngine($templateEngine);
    }

    abstract public function __invoke(HttpRequest $request): HttpResponse;

    private function setTemplateEngine(Engine $engine): self
    {
        $this->templateEngine = $engine;
        return $this;
    }

    public function getTemplateEngine(): Engine
    {
        return $this->templateEngine;
    }
}
