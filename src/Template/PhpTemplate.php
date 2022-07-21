<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Template;

use InvalidArgumentException;
use RangeException;

use function array_map;
use function array_slice;
use function extract;
use function implode;
use function in_array;
use function is_file;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function preg_match;
use function sprintf;
use function strtolower;

use const null;
use const PREG_UNMATCHED_AS_NULL;

class PhpTemplate implements TemplateInterface
{
    /** @var string[] */
    private const VALID_FILE_EXTENSIONS = [
        'php',
        'phtml',
        'php5',
        'php4',
        'php3',
        'phps',
    ];

    private string $pathname;

    /**
     * The pathname *may* contain the output format.
     */
    private ?string $outputFormat;

    public function __construct(string $pathname)
    {
        $this->setPathname($pathname);
    }

    /**
     * @inheritDoc
     */
    public function render(array $vars = []): string
    {
        $__FILE__ = $this->getPathname();

        return (static function () use ($__FILE__, $vars) {
            ob_start();

            try {
                extract($vars);
                unset($vars);  // (Aiming to expose as little as possible.)
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

    /**
     * @throws InvalidArgumentException If the template file does not exist.
     * @throws RangeException If the file does not appear to contain PHP.
     */
    private function setPathname(string $pathname): self
    {
        if (!is_file($pathname)) {
            throw new InvalidArgumentException("The template file, `{$pathname}`, does not exist.");
        }

        list($outputFormat, $fileExtension) = self::splitPathname($pathname);

        if (!in_array($fileExtension, self::VALID_FILE_EXTENSIONS)) {
            throw new RangeException(sprintf(
                'The file does not appear to contain PHP: its extension must be one of [%s].',
                implode(', ', array_map(function (string $validFileExtension) {
                    return "\"{$validFileExtension}\"";
                }, self::VALID_FILE_EXTENSIONS))
            ));
        }

        $this->pathname = $pathname;

        $this->setOutputFormat($outputFormat);

        return $this;
    }

    public function getPathname(): string
    {
        return $this->pathname;
    }

    /**
     * Returns an array with the following structure.
     *
     * Array
     * (
     *   0 => [output format]
     *   1 => [file extension]
     * )
     */
    private static function splitPathname(string $pathname): array
    {
        $extensionPattern = '\.([a-zA-Z0-9]+)';
        $extensionMatches = [];

        preg_match(
            "~(?:{$extensionPattern})?(?:{$extensionPattern})$~",
            $pathname,
            $extensionMatches,
            PREG_UNMATCHED_AS_NULL
        );

        if (!$extensionMatches) {
            return [
                null,
                null,
            ];
        }

        return array_slice($extensionMatches, 1);
    }
}
