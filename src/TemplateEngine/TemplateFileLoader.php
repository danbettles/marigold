<?php

declare(strict_types=1);

namespace DanBettles\Marigold\TemplateEngine;

use InvalidArgumentException;
use SplFileInfo;

use function is_dir;
use function is_string;
use function strlen;
use function substr;

use const DIRECTORY_SEPARATOR;
use const null;

class TemplateFileLoader
{
    /**
     * @var string[]
     */
    private array $templateDirs;

    /**
     * @param string[] $templateDirs
     */
    public function __construct(array $templateDirs)
    {
        $this->setTemplateDirs($templateDirs);
    }

    /**
     * @param string|SplFileInfo $pathnameOrFileInfo
     * @throws InvalidArgumentException If the pathname is invalid
     */
    public function findTemplate($pathnameOrFileInfo): ?TemplateFile
    {
        $pathnameOrBasename = $pathnameOrFileInfo instanceof SplFileInfo
            ? $pathnameOrFileInfo->getPathname()
            : $pathnameOrFileInfo
        ;

        if (!is_string($pathnameOrBasename) || !strlen($pathnameOrBasename)) {
            throw new InvalidArgumentException('The pathname is invalid');
        }

        if (DIRECTORY_SEPARATOR === substr($pathnameOrBasename, 0, 1)) {
            $templateFile = new TemplateFile($pathnameOrBasename);

            // We're assuming the pathname is absolute, so there's no need to check in the template directories.
            return $templateFile->isValid()
                ? $templateFile
                : null
            ;
        }

        foreach ($this->getTemplateDirs() as $templatesDir) {
            $templateFilePathname = $templatesDir . DIRECTORY_SEPARATOR . $pathnameOrBasename;
            $templateFile = new TemplateFile($templateFilePathname);

            if ($templateFile->isValid()) {
                return $templateFile;
            }
        }

        return null;
    }

    /**
     * @throws InvalidArgumentException If the directory does not exist
     */
    private function addTemplateDir(string $dir): self
    {
        if (!is_dir($dir)) {
            throw new InvalidArgumentException("Directory `{$dir}` does not exist");
        }

        $this->templateDirs[] = $dir;

        return $this;
    }

    /**
     * @param string[] $dirs
     * @throws InvalidArgumentException If the array of directory paths is empty
     */
    private function setTemplateDirs(array $dirs): self
    {
        if (!$dirs) {
            throw new InvalidArgumentException('The array of directory paths is empty');
        }

        $this->templateDirs = [];

        foreach ($dirs as $dir) {
            $this->addTemplateDir($dir);
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public function getTemplateDirs(): array
    {
        return $this->templateDirs;
    }
}
