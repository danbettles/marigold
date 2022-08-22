<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use DanBettles\Marigold\Exception\FileTypeNotSupportedException;
use DanBettles\Marigold\File\TemplateFile;

use function extract;
use function in_array;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;

class TemplateProcessor
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
     * @param string|TemplateFile $pathnameOrTemplateFile
     * @param array $variables
     * @throws FileTypeNotSupportedException If the file does not appear to contain PHP.
     */
    public function render($pathnameOrTemplateFile, array $variables = []): string
    {
        $templateFile = $pathnameOrTemplateFile instanceof TemplateFile
            ? $pathnameOrTemplateFile
            : new TemplateFile($pathnameOrTemplateFile)
        ;

        if (!in_array($templateFile->getExtension(), self::VALID_FILE_EXTENSIONS)) {
            throw new FileTypeNotSupportedException($templateFile->getExtension(), self::VALID_FILE_EXTENSIONS);
        }

        $__FILE__ = $templateFile->getPathname();
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
