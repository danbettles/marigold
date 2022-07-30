<?php

declare(strict_types=1);

namespace DanBettles\Marigold\TemplateFile;

interface TemplateFileInterface
{
    /**
     * Renders the template file and returns the output as a string.
     */
    public function render(): string;

    /**
     * Returns the name of the format of the output of the template file, or `null` if the format is unknown.
     */
    public function getOutputFormat(): ?string;
}
