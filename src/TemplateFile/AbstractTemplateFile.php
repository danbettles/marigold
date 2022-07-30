<?php

declare(strict_types=1);

namespace DanBettles\Marigold\TemplateFile;

use DanBettles\Marigold\Exception\FileNotFoundException;
use SplFileInfo;

use function strtolower;

use const null;

abstract class AbstractTemplateFile extends SplFileInfo implements TemplateFileInterface
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

        $basenameMinusExtension = $this->getBasename(".{$this->getExtension()}");
        $outputFormat = (new SplFileInfo($basenameMinusExtension))->getExtension();

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
     * @inheritDoc
     */
    public function getOutputFormat(): ?string
    {
        return $this->outputFormat;
    }
}
