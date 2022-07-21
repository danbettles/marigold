<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Template;

interface TemplateInterface
{
    /**
     * Renders the template and returns the output as a string.
     */
    public function render(): string;

    /**
     * Returns the name of the format of the output of the template, or `null` if the format is unknown.
     */
    public function getOutputFormat(): ?string;
}
