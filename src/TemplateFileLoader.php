<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use DanBettles\Marigold\File\TemplateFile;
use RangeException;

use function is_dir;

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

    public function findTemplate(string $basenameOrPathname): ?TemplateFile
    {
        foreach ($this->getTemplateDirs() as $templatesDir) {
            $templateFilePathname = $templatesDir . DIRECTORY_SEPARATOR . $basenameOrPathname;
            $templateFile = new TemplateFile($templateFilePathname);

            if ($templateFile->isFile()) {
                return $templateFile;
            }
        }

        return null;
    }

    /**
     * @throws RangeException If the directory does not exist.
     */
    private function addTemplateDir(string $dir): self
    {
        if (!is_dir($dir)) {
            throw new RangeException("The directory `{$dir}` does not exist.");
        }

        $this->templateDirs[] = $dir;

        return $this;
    }

    /**
     * @param string[] $dirs
     * @throws RangeException If the array of directory paths is empty.
     */
    private function setTemplateDirs(array $dirs): self
    {
        if (!$dirs) {
            throw new RangeException('The array of directory paths is empty.');
        }

        $this->templateDirs = [];

        foreach ($dirs as $dir) {
            $this->addTemplateDir($dir);
        }

        return $this;
    }

    public function getTemplateDirs(): array
    {
        return $this->templateDirs;
    }
}
