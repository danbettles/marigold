<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Service;

use Closure;
use DanBettles\Marigold\OutputHelper\OutputHelperInterface;
use DanBettles\Marigold\PhpTemplate;
use RangeException;

use function class_exists;
use function get_class;
use function is_object;
use function is_string;
use function sprintf;

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

    /**
     * @throws RangeException If the output-helper class does not exist.
     * @throws RangeException If the output-helper factory does not return an object.
     * @throws RangeException If the output-helper config is invalid.
     * @throws RangeException If the helper is not an output helper.
     */
    private function createOutputHelperFromTemplate(PhpTemplate $template): ?OutputHelperInterface
    {
        $outputFormat = $template->getOutputFormat()
            ?: 'html'
        ;

        $classNameOrClosure = $this->getConfig()['output_helpers'][$outputFormat]
            ?? null
        ;

        if (null === $classNameOrClosure) {
            return null;
        }

        $helper = $classNameOrClosure;

        if (is_string($classNameOrClosure)) {
            if (!class_exists($classNameOrClosure)) {
                throw new RangeException("The output-helper class `{$classNameOrClosure}` does not exist.");
            }

            $helper = new $classNameOrClosure();
        } elseif ($classNameOrClosure instanceof Closure) {
            $helper = $classNameOrClosure();

            if (!is_object($helper)) {
                throw new RangeException(
                    "The output-helper factory for `{$outputFormat}` output does not return an object."
                );
            }
        } elseif (!is_object($helper)) {
            throw new RangeException(
                "The output-helper config for `{$outputFormat}` format is invalid: it must be a class name, a closure, or an object."
            );
        }

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
