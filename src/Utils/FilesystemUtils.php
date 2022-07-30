<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Utils;

use function array_replace;
use function pathinfo;

use const null;
use const PATHINFO_ALL;
use const PATHINFO_EXTENSION;

class FilesystemUtils
{
    /**
     * `PATHINFO_ALL`:
     *   The array will always contain the `extension` element: the value will be `null` if the pathname contains no
     *   extension, or an empty string if the pathname has a blank extension.
     *
     * `PATHINFO_EXTENSION`:
     *   `null` if the pathname contains no extension, or an empty string if the pathname has a blank extension.
     *
     * @return array|string|null
     */
    public static function pathinfo(
        string $path,
        int $flags = PATHINFO_ALL
    ) {
        if (PATHINFO_ALL === $flags || PATHINFO_EXTENSION === $flags) {
            $all = array_replace([
                'extension' => null,
            ], pathinfo($path));

            return PATHINFO_EXTENSION === $flags
                ? $all['extension']
                : $all
            ;
        }

        return pathinfo($path, $flags);
    }
}
