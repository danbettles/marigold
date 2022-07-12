<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use PHPUnit\Framework\TestCase;

use function get_called_class;
use function preg_replace;
use function strlen;
use function substr;

use const DIRECTORY_SEPARATOR;
use const null;

class AbstractTestCase extends TestCase
{
    private string $fixturesDir;

    public function __construct(
        ?string $name = null,
        array $data = [],
        $dataName = ''
    ) {
        parent::__construct($name, $data, $dataName);

        $relativeClassName = substr(get_called_class(), strlen(__NAMESPACE__) + 1);

        $fixturesDir = (
            __DIR__ .
            DIRECTORY_SEPARATOR .
            preg_replace('~[\x2F\x5C]~', DIRECTORY_SEPARATOR, $relativeClassName)
        );

        $this->setFixturesDir($fixturesDir);
    }

    private function setFixturesDir(string $dir)
    {
        $this->fixturesDir = $dir;
    }

    protected function getFixturesDir(): string
    {
        return $this->fixturesDir;
    }

    protected function createFixturePathname(string $basename): string
    {
        return $this->getFixturesDir() . DIRECTORY_SEPARATOR . $basename;
    }
}
