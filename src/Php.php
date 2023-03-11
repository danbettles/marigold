<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use DanBettles\Marigold\Exception\FileNotFoundException;

use function extract;
use function is_file;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;

use const null;

class Php
{
    /**
     * @param array<string,mixed> $context
     * @return mixed
     * @throws FileNotFoundException If the PHP file does not exist
     */
    public function executeFile(
        string $pathname,
        array $context = [],
        ?string &$output = null
    ) {
        if (!is_file($pathname)) {
            throw new FileNotFoundException($pathname);
        }

        return (static function (
            string $__FILE__,
            array $context,
            &$__OUTPUT__
        ) {
            ob_start();

            try {
                extract($context);
                unset($context);

                $__RESPONSE__ = require $__FILE__;

                $__OUTPUT__ = ob_get_contents();

                return $__RESPONSE__;
            } finally {
                ob_end_clean();
            }
        })($pathname, $context, $output);
    }
}
