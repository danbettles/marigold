<?php

declare(strict_types=1);

namespace DanBettles\Marigold\File;

use SplFileInfo;

use function array_slice;
use function explode;

class FileInfo extends SplFileInfo
{
    /** @var string */
    private const BASENAME_SEPARATOR = '.';

    public function getExtensions(): array
    {
        $parts = explode(self::BASENAME_SEPARATOR, $this->getBasename());

        return array_slice($parts, 1);
    }
}
