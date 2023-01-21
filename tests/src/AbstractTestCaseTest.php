<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use DanBettles\Marigold\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function is_subclass_of;

use const DIRECTORY_SEPARATOR;

class AbstractTestCaseTest extends AbstractTestCase
{
    public function testIsAbstract(): void
    {
        $class = new ReflectionClass(AbstractTestCase::class);

        $this->assertTrue($class->isAbstract());
    }

    public function testIsAPhpunitTestcase(): void
    {
        $this->assertTrue(is_subclass_of(AbstractTestCase::class, TestCase::class));
    }

    public function testGetfixturesdir(): void
    {
        $self = new ReflectionClass(__CLASS__);

        $this->assertSame(
            __DIR__ . DIRECTORY_SEPARATOR . $self->getShortName(),
            $this->getFixturesDir()
        );
    }

    public function testCreatefixturepathname(): void
    {
        $self = new ReflectionClass(__CLASS__);

        $this->assertSame(
            __DIR__ . DIRECTORY_SEPARATOR . $self->getShortName() . DIRECTORY_SEPARATOR . 'foo.bar',
            $this->createFixturePathname('foo.bar')
        );
    }

    public function testGettestedclass(): void
    {
        $testedClass = $this->getTestedClass();

        $this->assertInstanceOf(ReflectionClass::class, $testedClass);
        $this->assertSame(AbstractTestCase::class, $testedClass->getName());
    }

    public function testGetfixturecontentsReturnsTheContentsOfTheFixture(): void
    {
        $this->assertSame('Lorem ipsum dolor.', $this->getFixtureContents('fixture.txt'));
    }

    public function testGetfixturecontentsThrowsAnExceptionIfTheFixtureDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('~^File `[^`]+` does not exist.$~');

        $this->getFixtureContents('non_existent');
    }
}
