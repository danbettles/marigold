<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use InvalidArgumentException;

use function extract;
use function is_file;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function preg_match;
use function strtolower;

use const null;

class PhpTemplate
{
    /** @var string */
    private const DEFAULT_OUTPUT_FORMAT = 'html';

    private string $pathname;

    private string $outputFormat;

    public function __construct(string $pathname)
    {
        $this->setPathname($pathname);
    }

    public function render(array $_VARIABLES = []): string
    {
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

    private function setOutputFormat(string $format): self
    {
        $this->outputFormat = strtolower($format);
        return $this;
    }

    public function getOutputFormat(): string
    {
        return $this->outputFormat;
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

        $matches = null;
        $pathnameContainsOutputFormat = (bool) preg_match('~\.([a-zA-Z]+)\.[a-zA-Z]+$~', $this->pathname, $matches);

        $this->setOutputFormat(
            $pathnameContainsOutputFormat
                ? $matches[1]
                : self::DEFAULT_OUTPUT_FORMAT
        );

        return $this;
    }

    public function getPathname(): string
    {
        return $this->pathname;
    }
}
