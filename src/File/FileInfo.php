<?php

declare(strict_types=1);

namespace DanBettles\Marigold\File;

use SplFileInfo;

use function array_slice;
use function explode;

class FileInfo extends SplFileInfo
{
    /**
     * @var string
     */
    private const BASENAME_SEPARATOR = '.';

    /**
     * @return string[]
     */
    public function getExtensions(): array
    {
        $parts = explode(self::BASENAME_SEPARATOR, $this->getBasename());

        return array_slice($parts, 1);
    }

    public function getBasenameMinusExtension(): string
    {
        if (self::BASENAME_SEPARATOR === $this->getBasename()[0]) {
            // `SplFileInfo::getExtension()` returns `"foo"` when pathname is `".foo"`.  Following that logic, we must
            // now return `""` when pathname is like `".foo"`.  That's not what happens, however, if you follow the
            // usual pattern -- as later on 🤦‍♂️
            return '';
        }

        return $this->getBasename(self::BASENAME_SEPARATOR . $this->getExtension());
    }
}
