<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Template;

use InvalidArgumentException;

use function extract;
use function file_get_contents;
use function is_file;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function preg_match;
use function strtolower;

use const null;
use const PREG_UNMATCHED_AS_NULL;

class PhpTemplate implements TemplateInterface
{
    private string $pathname;

    /**
     * The pathname *may* contain the output format.
     */
    private ?string $outputFormat;

    /**
     * The pathname *may* contain the file extension.
     */
    private ?string $fileExtension;

    public function __construct(string $pathname)
    {
        $this->setPathname($pathname);
    }

    /**
     * @inheritDoc
     */
    public function render(array $_VARIABLES = []): string
    {
        if ('php' !== $this->getFileExtension()) {
            return file_get_contents($this->getPathname());
        }

        $__FILE__ = $this->getPathname();

        return (static function () use ($__FILE__, $_VARIABLES) {
            ob_start();

            try {
                extract($_VARIABLES);
                require $__FILE__;
                return ob_get_contents();
            } finally {
                ob_end_clean();
            }
        })();
    }

    private function setOutputFormat(?string $format): self
    {
        $this->outputFormat = null === $format
            ? $format
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

    private function setFileExtension(?string $extension): self
    {
        $this->fileExtension = $extension;
        return $this;
    }

    public function getFileExtension(): ?string
    {
        return $this->fileExtension;
    }

    /**
     * @throws InvalidArgumentException If the template file does not exist.
     */
    private function setPathname(string $pathname): self
    {
        if (!is_file($pathname)) {
            throw new InvalidArgumentException("The template file, `{$pathname}`, does not exist.");
        }

        $this->pathname = $pathname;

        $extensionPattern = '\.([a-zA-Z]+)';
        $extensionMatches = [];

        preg_match(
            "~(?:{$extensionPattern})?(?:{$extensionPattern})$~",
            $this->pathname,
            $extensionMatches,
            PREG_UNMATCHED_AS_NULL
        );

        $outputFormat = null;
        $fileExtension = null;

        if ($extensionMatches) {
            list(, $outputFormat, $fileExtension) = $extensionMatches;
        }

        $this
            ->setOutputFormat($outputFormat)
            ->setFileExtension($fileExtension)
        ;

        return $this;
    }

    public function getPathname(): string
    {
        return $this->pathname;
    }
}
