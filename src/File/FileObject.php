<?php

declare(strict_types=1);

namespace DanBettles\Marigold\File;

use function file_get_contents;

class FileObject extends FileInfo
{
    public function getContents(): string
    {
        // "file_get_contents() is the preferred way to read the contents of a file into a string. It will use memory
        // mapping techniques if supported by your OS to enhance performance." --
        // https://www.php.net/manual/en/function.file-get-contents.php
        return file_get_contents($this->getPathname());
    }
}
