<?php

declare(strict_types=1);

namespace DanBettles\Marigold\Tests\Utils;

use DanBettles\Marigold\AbstractTestCase;
use DanBettles\Marigold\ServiceFactory;
use OutOfBoundsException;
use RangeException;
use stdClass;

class ServiceFactoryTest extends AbstractTestCase
{
    public function testIsInstantiable()
    {
        $config = [
            'foo' => stdClass::class,
        ];

        $factory = new ServiceFactory($config);

        $this->assertEquals($config, $factory->getConfig());
    }

    public function providesConfig(): array
    {
        return [
            [
                stdClass::class,
                [
                    'foo' => stdClass::class,
                ],
                'foo',
            ],
            [
                stdClass::class,
                [
                    'foo' => function () {
                        return new stdClass();
                    },
                ],
                'foo',
            ],
        ];
    }

    /** @dataProvider providesConfig */
    public function testGetReturnsTheServiceWithTheSpecifiedId($expectedClassName, $config, $id)
    {
        $factory = new ServiceFactory($config);

        $foo = $factory->get($id);

        $this->assertInstanceOf($expectedClassName, $foo);

        // `get()` must always return the same instance.
        $this->assertSame($foo, $factory->get($id));
    }

    public function testGetThrowsAnExceptionIfThereIsNoServiceWithTheSpecifiedId()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("There is no service with the ID `non_existent_service`.");

        (new ServiceFactory([]))->get('non_existent_service');
    }

    public function testGetThrowsAnExceptionIfAServiceClassDoesNotExist()
    {
        $nonExistentClassName = __CLASS__ . '\\NonExistent';

        $this->expectException(RangeException::class);
        $this->expectExceptionMessage("The class for service `class_name`, `{$nonExistentClassName}`, does not exist.");

        (new ServiceFactory([
            'class_name' => $nonExistentClassName,
        ]))
            ->get('class_name')
        ;
    }

    public function testGetThrowsAnExceptionIfAServiceFactoryClosureDoesNotReturnAnObject()
    {
        $this->expectException(RangeException::class);
        $this->expectExceptionMessage("The factory for service `closure` does not return an object.");

        (new ServiceFactory([
            'closure' => function () {
                return 'foo';
            },
        ]))
            ->get('closure')
        ;
    }

    public function providesInvalidConfig(): array
    {
        return [
            [
                [
                    'foo' => 123,
                ],
                'foo',
            ],
            [
                [
                    'foo' => 1.23,
                ],
                'foo',
            ],
            [
                [
                    'foo' => [],
                ],
                'foo',
            ],
        ];
    }

    /** @dataProvider providesInvalidConfig */
    public function testGetThrowsAnExceptionIfAServiceConfigurationDoesNotResolveToAnObject($invalidConfig, $id)
    {
        $this->expectException(RangeException::class);
        $this->expectExceptionMessage("The config for service `{$id}` is invalid: it must be a class-name or a closure.");

        (new ServiceFactory($invalidConfig))->get($id);
    }

    public function testContainsReturnsTrueIfTheFactoryContainsTheServiceWithTheSpecifiedId()
    {
        $containsNonExistentService = (new ServiceFactory([]))->contains('nonExistentService');

        $this->assertFalse($containsNonExistentService);

        $containsExistentService = (new ServiceFactory([
            'existentService' => function () {
                return new stdClass();
            },
        ]))->contains('existentService');

        $this->assertTrue($containsExistentService);
    }
}
