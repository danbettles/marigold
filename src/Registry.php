<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use Closure;
use InvalidArgumentException;
use OutOfBoundsException;
use RuntimeException;

use function array_key_exists;
use function class_exists;
use function is_object;
use function is_string;

class Registry
{
    /**
     * @var array<string, mixed>
     */
    private array $elements = [];

    /**
     * @var array<string, string|Closure>
     */
    private array $factories = [];

    /**
     * @param mixed $value
     */
    private function setElement(string $id, $value): self
    {
        $this->elements[$id] = $value;
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * @param mixed $factory
     * @throws InvalidArgumentException If the factory is invalid.
     */
    private function setFactory(string $id, $factory): self
    {
        $factoryIsValid = (is_string($factory) && class_exists($factory))
            || $factory instanceof Closure
        ;

        if (!$factoryIsValid) {
            throw new InvalidArgumentException('The factory is invalid.');
        }

        $this->factories[$id] = $factory;

        return $this;
    }

    /**
     * @return array<string, string|Closure>
     */
    public function getFactories(): array
    {
        return $this->factories;
    }

    private function containsElement(string $id): bool
    {
        return array_key_exists($id, $this->getElements());
    }

    private function containsFactory(string $id): bool
    {
        return array_key_exists($id, $this->getFactories());
    }

    private function contains(string $id): bool
    {
        return $this->containsElement($id) || $this->containsFactory($id);
    }

    /**
     * Adds an element.
     *
     * @param mixed $value
     * @throws RuntimeException If the ID already exists.
     */
    public function add(string $id, $value): self
    {
        if ($this->contains($id)) {
            throw new RuntimeException("The ID, `{$id}`, already exists.");
        }

        return $this->setElement($id, $value);
    }

    /**
     * @param string|Closure $factory
     * @throws RuntimeException If the ID already exists.
     */
    public function addFactory(string $id, $factory): self
    {
        if ($this->contains($id)) {
            throw new RuntimeException("The ID, `{$id}`, already exists.");
        }

        return $this->setFactory($id, $factory);
    }

    /**
     * @throws RuntimeException If the factory for the element does not return an object.
     */
    private function createService(string $id): object
    {
        $factory = $this->getFactories()[$id];

        if (is_string($factory)) {
            return new $factory();
        }

        // A closure:

        $service = $factory();

        if (!is_object($service)) {
            throw new RuntimeException("The factory for `{$id}` does not return an object.");
        }

        return $service;
    }

    /**
     * @return mixed
     * @throws OutOfBoundsException If the element does not exist.
     */
    public function get(string $id)
    {
        if ($this->containsElement($id)) {
            return $this->getElements()[$id];
        }

        // The element doesn't exist but we have a factory that will create it.
        if ($this->containsFactory($id)) {
            $this->setElement($id, $this->createService($id));

            return $this->getElements()[$id];
        }

        throw new OutOfBoundsException("The element, `{$id}`, does not exist.");
    }
}
