<?php

declare(strict_types=1);

namespace DanBettles\Marigold\TemplateEngine;

use DanBettles\Marigold\FileInfo;

use function array_slice;
use function count;
use function strtolower;

use const null;

class TemplateFile extends FileInfo
{
    private ?string $outputFormat;

    public function __construct(string $filename)
    {
        parent::__construct($filename);

        $maxExtensions = 2;
        $extensions = array_slice($this->getExtensions(), -$maxExtensions);

        $outputFormat = $maxExtensions === count($extensions)
            ? $extensions[0]
            : null
        ;

        $this->setOutputFormat($outputFormat);
    }

    /**
     * Returns `true` if the file really is a template file, or `false` otherwise.
     */
    public function isValid(): bool
    {
        return $this->isFile();
    }

    private function setOutputFormat(?string $format): self
    {
        // Normalize.
        $this->outputFormat = null === $format || '' === $format
            ? null
            : strtolower($format)
        ;

        return $this;
    }

    /**
     * Returns the name of the format of the output of the file, or `null` if the format is unknown.
     */
    public function getOutputFormat(): ?string
    {
        return $this->outputFormat;
    }
}
