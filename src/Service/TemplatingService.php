<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Service;

use DanBettles\Marigold\OutputHelper\OutputHelperInterface;
use DanBettles\Marigold\PhpTemplate;

use const DIRECTORY_SEPARATOR;
use const null;

class TemplatingService
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    private function createTemplatePathname(string $pathnameOrBasename): string
    {
        $templatesDir = $this->getConfig()['templates_dir']
            ?? null
        ;

        return null === $templatesDir
            ? $pathnameOrBasename
            : $templatesDir . DIRECTORY_SEPARATOR . $pathnameOrBasename
        ;
    }

    private function createOutputHelperFromTemplate(PhpTemplate $template): ?OutputHelperInterface
    {
        return $this->getConfig()['output_helpers'][$template->getOutputFormat()]
            ?? null
        ;
    }

    public function render(
        string $pathnameOrBasename,
        array $variables = []
    ): string {
        $template = new PhpTemplate($this->createTemplatePathname($pathnameOrBasename));

        $augmentedVars = [
            'variables' => $variables,
            'service' => $this,
        ];

        $outputHelper = $this->createOutputHelperFromTemplate($template);

        if ($outputHelper) {
            $augmentedVars['helper'] = $outputHelper;
        }

        return $template->render($augmentedVars);
    }

    private function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
