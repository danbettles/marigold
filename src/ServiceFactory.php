<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use Closure;
use OutOfBoundsException;
use RangeException;

use function array_key_exists;
use function class_exists;
use function is_object;
use function is_string;

use const null;

class ServiceFactory
{
    private array $config;

    private array $services = [];

    public function __construct(array $config)
    {
        $this->setConfig($config);
    }

    /**
     * Returns `true` if the factory knows about the service, or `false` otherwise.
     *
     * This method checks only for the existence of config for the service: the config may be invalid.
     */
    public function contains(string $id): bool
    {
        return array_key_exists($id, $this->config);
    }

    /**
     * Gets a service by its ID.
     *
     * @throws OutOfBoundsException If the service does not exist.
     * @throws RangeException If the service class does not exist.
     * @throws RangeException If the factory for the service does not return an object.
     * @throws RangeException If the config for the service is invalid.
     */
    public function get(string $id): object
    {
        if (!$this->contains($id)) {
            throw new OutOfBoundsException("There is no service with the ID `{$id}`.");
        }

        if (!array_key_exists($id, $this->services)) {
            $classNameOrClosure = $this->getConfig()[$id];

            $service = null;

            if (is_string($classNameOrClosure)) {
                if (!class_exists($classNameOrClosure)) {
                    throw new RangeException("The class for service `{$id}`, `{$classNameOrClosure}`, does not exist.");
                }

                $service = new $classNameOrClosure();
            } elseif ($classNameOrClosure instanceof Closure) {
                $service = $classNameOrClosure();

                if (!is_object($service)) {
                    throw new RangeException("The factory for service `{$id}` does not return an object.");
                }
            } else {
                throw new RangeException("The config for service `{$id}` is invalid: it must be a class-name or a closure.");
            }

            $this->services[$id] = $service;
        }

        return $this->services[$id];
    }

    private function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
