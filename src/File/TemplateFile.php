<?php

declare(strict_types=1);

namespace DanBettles\Marigold\File;

use RangeException;

use function array_slice;
use function count;
use function file_exists;
use function strtolower;

use const null;

class TemplateFile extends FileInfo
{
    private ?string $outputFormat;

    /**
     * @throws RangeException If the filename does not point at a file.
     */
    public function __construct(string $filename)
    {
        parent::__construct($filename);

        if (
            file_exists($this->getPathname())
            && !$this->isFile()
        ) {
            throw new RangeException("The filename `{$this->getPathname()}` does not point at a file.");
        }

        $maxExtensions = 2;
        $extensions = array_slice($this->getExtensions(), -$maxExtensions);

        $outputFormat = $maxExtensions === count($extensions)
            ? $extensions[0]
            : null
        ;

        $this->setOutputFormat($outputFormat);
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
