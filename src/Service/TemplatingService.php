<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Service;

use DanBettles\Marigold\OutputHelper\OutputHelperInterface;
use DanBettles\Marigold\File\TemplateFile;
use DanBettles\Marigold\ServiceFactory;
use DanBettles\Marigold\TemplateEngine;
use RangeException;

use function get_class;
use function sprintf;

use const DIRECTORY_SEPARATOR;
use const null;

class TemplatingService
{
    private array $config;

    private ServiceFactory $serviceFactory;

    public function __construct(
        array $config,
        ServiceFactory $serviceFactory
    ) {
        $this
            ->setConfig($config)
            ->setServiceFactory($serviceFactory)
        ;
    }

    private function createTemplateFilePathname(string $pathnameOrBasename): string
    {
        $templatesDir = $this->getConfig()['templates_dir']
            ?? null
        ;

        return null === $templatesDir
            ? $pathnameOrBasename
            : $templatesDir . DIRECTORY_SEPARATOR . $pathnameOrBasename
        ;
    }

    /**
     * @throws RangeException If the helper is not an output helper.
     */
    private function createOutputHelperFromTemplateFile(TemplateFile $templateFile): ?OutputHelperInterface
    {
        $outputFormat = $templateFile->getOutputFormat()
            ?: 'html'
        ;

        $helperServiceId = "output_helpers.{$outputFormat}";

        if (!$this->getServiceFactory()->contains($helperServiceId)) {
            return null;
        }

        $helper = $this->getServiceFactory()->get($helperServiceId);

        if (!$helper instanceof OutputHelperInterface) {
            throw new RangeException(sprintf(
                'The helper for `%s` output, `%s`, does not implement `%s`.',
                $outputFormat,
                get_class($helper),
                OutputHelperInterface::class
            ));
        }

        return $helper;
    }

    public function render(
        string $pathnameOrBasename,
        array $variables = []
    ): string {
        $templatePathname = $this->createTemplateFilePathname($pathnameOrBasename);
        $templateFile = new TemplateFile($templatePathname);

        $augmentedVars = [
            'variables' => $variables,
            'service' => $this,
        ];

        $outputHelper = $this->createOutputHelperFromTemplateFile($templateFile);

        if ($outputHelper) {
            $augmentedVars['helper'] = $outputHelper;
        }

        return (new TemplateEngine())->render(
            $templateFile,
            $augmentedVars
        );
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

    private function setServiceFactory(ServiceFactory $serviceFactory): self
    {
        $this->serviceFactory = $serviceFactory;
        return $this;
    }

    public function getServiceFactory(): ServiceFactory
    {
        return $this->serviceFactory;
    }
}
