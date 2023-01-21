<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function array_pop;
use function explode;
use function file_get_contents;
use function get_class;
use function implode;
use function is_file;
use function preg_replace;
use function strlen;
use function substr;

use const DIRECTORY_SEPARATOR;
use const null;

abstract class AbstractTestCase extends TestCase
{
    public static string $testsNamespace;

    public static string $testsDir;

    private string $fixturesDir;

    /**
     * @phpstan-var class-string
     */
    private string $testedClassName;

    private ReflectionClass $testedClass;

    // @phpstan-ignore-next-line
    public function __construct(
        ?string $name = null,
        array $data = [],
        $dataName = ''
    ) {
        parent::__construct($name, $data, $dataName);

        $relativeClassName = substr(get_class($this), strlen(self::$testsNamespace) + 1);

        $fixturesDir = (
            self::$testsDir .
            DIRECTORY_SEPARATOR .
            // (Forward slash or backslash.)
            preg_replace('~[\x2F\x5C]~', DIRECTORY_SEPARATOR, $relativeClassName)
        );

        $this->setFixturesDir($fixturesDir);

        $namespaceSeparator = '\\';
        $namespaceParts = explode($namespaceSeparator, self::$testsNamespace);
        array_pop($namespaceParts);
        $namespaceParts[] = preg_replace('~Test$~', '', $relativeClassName);
        /** @phpstan-var class-string */
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

    /**
     * @throws InvalidArgumentException If the file does not exist.
     */
    protected function getFixtureContents(string $basename): string
    {
        $fixturePathname = $this->createFixturePathname($basename);

        if (!is_file($fixturePathname)) {
            throw new InvalidArgumentException("File `{$fixturePathname}` does not exist.");
        }

        /** @var string */
        return file_get_contents($fixturePathname);
    }

    /**
     * @phpstan-param class-string $className
     */
    private function setTestedClassName(string $className): void
    {
        $this->testedClassName = $className;
    }

    /**
     * @phpstan-return class-string
     */
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
