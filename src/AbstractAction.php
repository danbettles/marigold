<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use DanBettles\Marigold\Exception\NotFoundHttpException;
use DanBettles\Marigold\TemplateEngine\Engine;

/**
 * Takes an HTTP request and creates an appropriate response.
 */
abstract class AbstractAction
{
    private Engine $templateEngine;

    public function __construct(Engine $templateEngine)
    {
        $this->setTemplateEngine($templateEngine);
    }

    /**
     * @param array<string, mixed> $variables
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

    protected function createNotFoundException(string $specifier): NotFoundHttpException
    {
        return new NotFoundHttpException($specifier);
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
