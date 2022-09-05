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

use const DIRECTORY_SEPARATOR;
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

    private ?string $templatesDir;

    public function __construct(string $templatesDir = null)
    {
        $this->setTemplatesDir($templatesDir);
    }

    /**
     * @param string|TemplateFile $pathnameOrTemplateFile
     */
    private function createTemplateFile($pathnameOrTemplateFile): TemplateFile
    {
        if ($pathnameOrTemplateFile instanceof TemplateFile) {
            return $pathnameOrTemplateFile;
        }

        $pathname = null === $this->getTemplatesDir()
            ? $pathnameOrTemplateFile
            : $this->getTemplatesDir() . DIRECTORY_SEPARATOR . $pathnameOrTemplateFile
        ;

        return new TemplateFile($pathname);
    }

    /**
     * @param string|TemplateFile $pathnameOrTemplateFile
     * @param array<string, mixed> $variables
     * @throws FileTypeNotSupportedException If the file does not appear to contain PHP.
     */
    public function render($pathnameOrTemplateFile, array $variables = []): string
    {
        $templateFile = $this->createTemplateFile($pathnameOrTemplateFile);

        if (!in_array($templateFile->getExtension(), self::VALID_FILE_EXTENSIONS)) {
            throw new FileTypeNotSupportedException($templateFile->getExtension(), self::VALID_FILE_EXTENSIONS);
        }

        $__FILE__ = $templateFile->getPathname();
        $__VARS__ = $variables;

        return (static function () use ($__FILE__, $__VARS__): string {
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
    }

    private function setTemplatesDir(?string $dir): self
    {
        $this->templatesDir = $dir;
        return $this;
    }

    public function getTemplatesDir(): ?string
    {
        return $this->templatesDir;
    }
}
