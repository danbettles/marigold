<?php

declare(strict_types=1);

namespace DanBettles\Marigold\TemplateFile;

use DanBettles\Marigold\Exception\FileNotFoundException;
use RangeException;
use SplFileInfo;

use function array_map;
use function extract;
use function implode;
use function in_array;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function sprintf;
use function strtolower;

use const null;

class PhpTemplateFile extends SplFileInfo implements TemplateFileInterface
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

    /**
     * The pathname *may* contain the output format.
     */
    private ?string $outputFormat;

    /**
     * @throws FileNotFoundException If the template file does not exist.
     * @throws RangeException If the file does not appear to contain PHP.
     */
    public function __construct(string $filename)
    {
        parent::__construct($filename);

        if (!$this->isFile()) {
            throw new FileNotFoundException($this->getPathname());
        }

        if (!in_array($this->getExtension(), self::VALID_FILE_EXTENSIONS)) {
            throw new RangeException(sprintf(
                'The file does not appear to contain PHP: its extension must be one of [%s].',
                implode(', ', array_map(function (string $validFileExtension) {
                    return "\"{$validFileExtension}\"";
                }, self::VALID_FILE_EXTENSIONS))
            ));
        }

        $basenameMinusExtension = $this->getBasename(".{$this->getExtension()}");
        $outputFormat = (new SplFileInfo($basenameMinusExtension))->getExtension();

        $this->setOutputFormat($outputFormat);
    }

    /**
     * @inheritDoc
     */
    public function render(array $variables = []): string
    {
        $__FILE__ = $this->getPathname();
        $__VARS__ = $variables;

        return (static function () use ($__FILE__, $__VARS__) {
            ob_start();

            try {
                extract($__VARS__);
                unset($__VARS__);  // (Aiming to expose as little as possible.)

                require $__FILE__;

                return ob_get_contents();
            } finally {
                ob_end_clean();
            }
        })();
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
