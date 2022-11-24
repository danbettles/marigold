<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests;

use Closure;
use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\Registry;
use InvalidArgumentException;
use OutOfBoundsException;
use RuntimeException;
use stdClass;

use const null;

class RegistryTest extends AbstractTestCase
{
    public function testIsInstantiable(): void
    {
        $registry = new Registry();

        $this->assertSame([], $registry->getFactories());
        $this->assertSame([], $registry->getElements());
    }

    /** @return array<int, array<int, mixed>> */
    public function providesElements(): array
    {
        return [
            [
                [
                    'foo' => 'bar',
                ],
                'foo',
                'bar',
            ],
            [
                [
                    'baz' => [
                        'qux' => 'quux',
                    ],
                ],
                'baz',
                [
                    'qux' => 'quux',
                ],
            ],
        ];
    }

    /**
     * @dataProvider providesElements
     * @param array<string, mixed> $expectedElements
     * @param mixed $elementValue
     */
    public function testAddAddsAnElement(
        array $expectedElements,
        string $elementId,
        $elementValue
    ): void {
        $registry = new Registry();
        $something = $registry->add($elementId, $elementValue);

        $this->assertSame($expectedElements, $registry->getElements());
        $this->assertSame($registry, $something);
    }

    public function testMultipleElementsCanBeAddedByCallingAddRepeatedly(): void
    {
        $registry = (new Registry())
            ->add('foo', 'bar')
            ->add('baz', 'qux')
        ;

        $this->assertSame([
            'foo' => 'bar',
            'baz' => 'qux',
        ], $registry->getElements());
    }

    /** @return array<int, array<int, mixed>> */
    public function providesFactories(): array
    {
        return [
            [
                stdClass::class,
                [
                    'foo',
                    stdClass::class,
                ],
                'foo',
            ],
            [
                stdClass::class,
                [
                    'bar',
                    function () {
                        return new stdClass();
                    },
                ],
                'bar',
            ],
        ];
    }

    /**
     * @dataProvider providesFactories
     * @param array{string, string|Closure} $addFactoryArgs
     */
    public function testAddfactoryAddsAFactory(
        string $ignore,
        array $addFactoryArgs
    ): void {
        $registry = new Registry();
        $something = $registry->addFactory(...$addFactoryArgs);

        $this->assertSame([
            $addFactoryArgs[0] => $addFactoryArgs[1],
        ], $registry->getFactories());

        $this->assertSame($registry, $something);
    }

    public function testMultipleFactoriesCanBeAddedByCallingAddfactoryRepeatedly(): void
    {
        $fooFactory = function () {
        };

        $barFactory = function () {
        };

        $registry = (new Registry())
            ->addFactory('foo', $fooFactory)
            ->addFactory('bar', $barFactory)
        ;

        $this->assertSame([
            'foo' => $fooFactory,
            'bar' => $barFactory,
        ], $registry->getFactories());
    }

    /** @return array<int, array<int, mixed>> */
    public function providesLoadedRegistries(): array
    {
        return [
            [
                (new Registry())
                    ->add('foo', 'bar'),
                'foo',
            ],
            [
                (new Registry())
                    ->addFactory('bar', function () {
                    }),
                'bar',
            ],
        ];
    }

    /** @dataProvider providesLoadedRegistries */
    public function testAddThrowsAnExceptionIfTheIdAlreadyExists(Registry $registry, string $duplicateId): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("The ID, `{$duplicateId}`, already exists.");

        $registry->add($duplicateId, 'anything');
    }

    /** @dataProvider providesLoadedRegistries */
    public function testAddfactoryThrowsAnExceptionIfTheIdAlreadyExists(Registry $registry, string $duplicateId): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("The ID, `{$duplicateId}`, already exists.");

        $registry->addFactory($duplicateId, function () {
        });
    }

    /** @return array<int, array<int, mixed>> */
    public function providesInvalidFactories(): array
    {
        return [
            [
                new stdClass(),
            ],
            [
                __NAMESPACE__ . '\NonExistent',
            ],
            [
                123,
            ],
            [
                null,
            ],
        ];
    }

    /**
     * @dataProvider providesInvalidFactories
     * @param mixed $invalidFactory
     */
    public function testAddfactoryThrowsAnExceptionIfTheFactoryIsInvalid($invalidFactory): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The factory is invalid.');

        $registry = new Registry();
        /** @phpstan-var string|Closure $invalidFactory */
        $registry->addFactory('validId', $invalidFactory);
    }

    public function testGetReturnsTheValueOfTheElementWithTheSpecifiedId(): void
    {
        $expected = new stdClass();

        $registry = new Registry();
        $something = $registry->add('foo', $expected);

        $this->assertSame($registry, $something);

        $actual = $registry->get('foo');

        $this->assertSame($expected, $actual);

        // `get()` must always return the same object.
        $this->assertSame($expected, $registry->get('foo'));
    }

    /** @return array<int, array<int, mixed>> */
    public function providesRegistriesContainingFactories(): array
    {
        return [
            [
                stdClass::class,
                (new Registry())
                    ->addFactory('foo', stdClass::class),
                'foo',
            ],
            [
                stdClass::class,
                (new Registry())
                    ->addFactory('bar', function () {
                        return new stdClass();
                    }),
                'bar',
            ],
        ];
    }

    /**
     * @dataProvider providesRegistriesContainingFactories
     * @phpstan-param class-string $expectedClassName
     */
    public function testGetReturnsTheObjectCreatedByAFactory(
        string $expectedClassName,
        Registry $registry,
        string $elementId
    ): void {
        $object = $registry->get($elementId);

        $this->assertInstanceOf($expectedClassName, $object);

        // `get()` must always return the same object.
        $this->assertSame($object, $registry->get($elementId));
    }

    public function testGetThrowsAnExceptionIfTheElementDoesNotExist(): void
    {
        $elementId = 'nonExistentElement';

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("The element, `{$elementId}`, does not exist.");

        (new Registry())->get($elementId);
    }

    public function testGetThrowsAnExceptionIfTheFactoryClosureDoesNotReturnAnObject(): void
    {
        $elementId = 'doesNotReturnAnObject';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("The factory for `{$elementId}` does not return an object.");

        (new Registry())
            ->addFactory($elementId, function () {
                return 'foo';
            })
            ->get($elementId)
        ;
    }

    public function testFactoriesArePassedTheRegistryInstance(): void
    {
        $registry = new Registry();

        $registry
            ->addFactory('foo', function ($shouldBeTheRegistry) use ($registry) {
                $this->assertSame($registry, $shouldBeTheRegistry);

                return new stdClass();
            })
            ->get('foo')
        ;
    }
}
