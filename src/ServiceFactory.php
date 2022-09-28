<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use Closure;
use InvalidArgumentException;
use OutOfBoundsException;

use function array_key_exists;
use function class_exists;
use function is_object;
use function is_string;

use const null;

class ServiceFactory
{
    /**
     * @phpstan-var array<string, class-string|Closure>
     */
    private array $config;

    /**
     * @var array<string, object>
     */
    private array $services = [];

    /**
     * @phpstan-param array<string, class-string|Closure> $config
     */
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
     * @throws InvalidArgumentException If the service class does not exist.
     * @throws InvalidArgumentException If the factory for the service does not return an object.
     * @throws InvalidArgumentException If the config for the service is invalid.
     */
    public function get(string $id): object
    {
        if (!$this->contains($id)) {
            throw new OutOfBoundsException("There is no service with the ID `{$id}`.");
        }

        if (!array_key_exists($id, $this->services)) {
            $resolvesToObject = $this->getConfig()[$id];

            $service = null;

            if (is_string($resolvesToObject)) {
                if (!class_exists($resolvesToObject)) {
                    throw new InvalidArgumentException("The class for service `{$id}`, `{$resolvesToObject}`, does not exist.");
                }

                $service = new $resolvesToObject();
            } elseif ($resolvesToObject instanceof Closure) {
                $service = $resolvesToObject();

                if (!is_object($service)) {
                    throw new InvalidArgumentException("The factory for service `{$id}` does not return an object.");
                }
            } elseif (is_object($resolvesToObject)) {
                $service = $resolvesToObject;
            } else {
                throw new InvalidArgumentException("The config for service `{$id}` is invalid: it must be a class-name, closure, or an object.");
            }

            $this->services[$id] = $service;
        }

        return $this->services[$id];
    }

    /**
     * @phpstan-param array<string, class-string|Closure> $config
     */
    private function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @phpstan-return array<string, class-string|Closure> $config
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
