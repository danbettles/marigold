<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use DanBettles\Marigold\Exception\FileNotFoundException;
use DanBettles\Marigold\Exception\FileTypeNotSupportedException;
use DanBettles\Marigold\File\TemplateFile;

use function extract;
use function in_array;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;

use const null;

class TemplateEngine
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

    private ?TemplateFileLoader $templateFileLoader;

    public function __construct(TemplateFileLoader $templateFileLoader = null)
    {
        $this->setTemplateFileLoader($templateFileLoader);
    }

    /**
     * @param string|TemplateFile $pathnameOrTemplateFile
     * @param array<string, mixed> $variables
     * @throws FileNotFoundException If the template file does not exist.
     * @throws FileTypeNotSupportedException If the file does not appear to contain PHP.
     */
    public function render($pathnameOrTemplateFile, array $variables = []): string
    {
        $templateFile = null;
        $templateFilePathname = null;

        if ($pathnameOrTemplateFile instanceof TemplateFile) {
            $templateFile = $pathnameOrTemplateFile;
            $templateFilePathname = $templateFile->getPathname();
        } else {
            if ($this->getTemplateFileLoader()) {
                $templateFile = $this->getTemplateFileLoader()->findTemplate($pathnameOrTemplateFile);

                $templateFilePathname = null === $templateFile
                    ? $pathnameOrTemplateFile
                    : $templateFile->getPathname()
                ;
            } else {
                $templateFile = new TemplateFile($pathnameOrTemplateFile);
                $templateFilePathname = $templateFile->getPathname();
            }
        }

        if (
            null === $templateFile
            || !$templateFile->isFile()
        ) {
            throw new FileNotFoundException("The template file `{$templateFilePathname}` does not exist.");
        }

        if (!in_array($templateFile->getExtension(), self::VALID_FILE_EXTENSIONS)) {
            throw new FileTypeNotSupportedException($templateFile->getExtension(), self::VALID_FILE_EXTENSIONS);
        }

        $__FILE__ = $templateFile->getPathname();
        $__VARS__ = $variables;

        $__layout = null;

        $output = (static function () use ($__FILE__, $__VARS__, &$__layout): string {
            ob_start();

            try {
                extract($__VARS__);
                unset($__VARS__);  // (Aiming to expose as little as possible.)

                require $__FILE__;

                $output = ob_get_contents();

                /** @var string */
                return $output;
            } finally {
                ob_end_clean();
            }
        })();

        // We can't be specific enough in `phpstan.neon`, so ignore the troublesome line using the PHPStan annotation.
        // @phpstan-ignore-next-line
        if (null !== $__layout) {
            $variables['__contentForLayout'] = $output;

            return $this->render($__layout, $variables);
        }

        return $output;
    }

    private function setTemplateFileLoader(?TemplateFileLoader $loader): self
    {
        $this->templateFileLoader = $loader;
        return $this;
    }

    public function getTemplateFileLoader(): ?TemplateFileLoader
    {
        return $this->templateFileLoader;
    }
}
