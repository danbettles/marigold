<?php

declare(strict_types=1);

namespace DanBettles\Marigold\TemplateFile;

use DanBettles\Marigold\Exception\FileTypeNotSupportedException;

use function extract;
use function in_array;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;

class PhpTemplateFile extends AbstractTemplateFile
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
     * @throws FileTypeNotSupportedException If the file does not appear to contain PHP.
     */
    public function __construct(string $filename)
    {
        parent::__construct($filename);

        if (!in_array($this->getExtension(), self::VALID_FILE_EXTENSIONS)) {
            throw new FileTypeNotSupportedException($this->getExtension(), self::VALID_FILE_EXTENSIONS);
        }
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
}
