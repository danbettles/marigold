<?php

declare(strict_types=1);

namespace DanBettles\Marigold\TemplateEngine;

use DanBettles\Marigold\Exception\FileNotFoundException;
use DanBettles\Marigold\Php;
use SplFileInfo;

use function array_replace;

use const null;

class Engine
{
    private Php $php;

    private TemplateFileLoader $templateFileLoader;

    public function __construct(
        Php $php,
        TemplateFileLoader $templateFileLoader
    ) {
        $this
            ->setPhp($php)
            ->setTemplateFileLoader($templateFileLoader)
        ;
    }

    /**
     * Inside the template being rendered, variables can be found in the `$input` collection.  The value of a variable
     * called "foo" could be accessed with `$input['foo']`, for example.
     *
     * The `$output` object provides:
     * - `include()`, which allows to include other files;
     * - and `insertInto()`/`wrapWith()`, which causes the output of the template to be inserted into another template.
     *
     * @param string|SplFileInfo $pathnameOrFileInfo
     * @param array<string, mixed> $variables
     * @throws FileNotFoundException If the template file could not be found.
     */
    public function render(
        $pathnameOrFileInfo,
        array $variables = []
    ): string {
        $templateFile = $this->getTemplateFileLoader()->findTemplate($pathnameOrFileInfo);

        if (null === $templateFile) {
            throw new FileNotFoundException((string) $pathnameOrFileInfo);
        }

        $output = new OutputFacade($this);

        $renderedOutput = null;

        $this->getPhp()->executeFile(
            $templateFile->getPathname(),
            [
                'input' => $variables,
                'output' => $output,
            ],
            $renderedOutput
        );

        if ($output->getWrapperArgs()) {
            // @phpstan-ignore-next-line
            list(
                $wrapperPathnameOrTemplateFile,
                $wrapperTargetVarName,
                $wrapperVariables
            ) = $output->getWrapperArgs();

            /** @var array<string, mixed> */
            $wrapperVariables = array_replace($wrapperVariables, [
                ($wrapperTargetVarName) => $renderedOutput,
            ]);

            return (new self($this->getPhp(), $this->getTemplateFileLoader()))
                ->render($wrapperPathnameOrTemplateFile, $wrapperVariables)
            ;
        }

        return $renderedOutput;
    }

    private function setPhp(Php $php): self
    {
        $this->php = $php;
        return $this;
    }

    public function getPhp(): Php
    {
        return $this->php;
    }

    private function setTemplateFileLoader(TemplateFileLoader $loader): self
    {
        $this->templateFileLoader = $loader;
        return $this;
    }

    public function getTemplateFileLoader(): TemplateFileLoader
    {
        return $this->templateFileLoader;
    }

    /**
     * Factory method, for convenience.
     */
    public static function create(TemplateFileLoader $loader): self
    {
        return new self(new Php(), $loader);
    }
}
