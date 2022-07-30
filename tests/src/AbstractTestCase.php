<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function array_pop;
use function explode;
use function get_called_class;
use function implode;
use function preg_replace;
use function strlen;
use function substr;

use const DIRECTORY_SEPARATOR;
use const null;

/**
 * @todo Extract, and test, this.
 */
class AbstractTestCase extends TestCase
{
    private string $fixturesDir;

    private string $testedClassName;

    private ReflectionClass $testedClass;

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

        $namespaceSeparator = '\\';
        $namespaceParts = explode($namespaceSeparator, __NAMESPACE__);
        array_pop($namespaceParts);
        $namespaceParts[] = preg_replace('~Test$~', '', $relativeClassName);
        $testedClassName = implode($namespaceSeparator, $namespaceParts);

        $this->setTestedClassName($testedClassName);
    }

    private function setFixturesDir(string $dir): void
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

    private function setTestedClassName(string $testedClassName): void
    {
        $this->testedClassName = $testedClassName;
    }

    private function getTestedClassName(): string
    {
        return $this->testedClassName;
    }

    protected function getTestedClass(): ReflectionClass
    {
        if (!isset($this->testedClass)) {
            $this->testedClass = new ReflectionClass($this->getTestedClassName());
        }

        return $this->testedClass;
    }
}
