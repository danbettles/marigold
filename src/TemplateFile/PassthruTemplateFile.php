<?php

declare(strict_types=1);

namespace DanBettles\Marigold\TemplateFile;

use function file_get_contents;

class PassthruTemplateFile extends AbstractTemplateFile
{
    /**
     * @inheritDoc
     */
    public function render(): string
    {
        return file_get_contents($this->getPathname());
    }
}
