<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use DanBettles\Marigold\TemplateEngine\Engine;

/**
 * Creates an `HttpResponse` from an `HttpRequest`.
 */
abstract class AbstractAction
{
    private Engine $templateEngine;

    public function __construct(Engine $templateEngine)
    {
        $this->setTemplateEngine($templateEngine);
    }

    /**
     * @param array<string,mixed> $variables
     */
    protected function render(
        string $templateFileBasename,
        array $variables = [],
        int $httpStatusCode = HttpResponse::HTTP_OK
    ): HttpResponse {
        $output = $this
            ->getTemplateEngine()
            ->render($templateFileBasename, $variables)
        ;

        return new HttpResponse($output, $httpStatusCode);
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
