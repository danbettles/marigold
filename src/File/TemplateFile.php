<?php

declare(strict_types=1);

namespace DanBettles\Marigold\File;

use DanBettles\Marigold\Exception\FileNotFoundException;

use function array_slice;
use function count;
use function strtolower;

use const null;

/**
 * The template file must exist.
 */
class TemplateFile extends FileInfo
{
    private ?string $outputFormat;

    /**
     * @throws FileNotFoundException If the template file does not exist.
     */
    public function __construct(string $filename)
    {
        parent::__construct($filename);

        if (!$this->isFile()) {
            throw new FileNotFoundException($this->getPathname());
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
