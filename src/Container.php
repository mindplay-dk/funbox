<?php

namespace mindplay\funbox;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var Component[] map where component name => Component instance
     */
    private array $components = [];

    /**
     * @var array map where component name => component instance
     */
    private array $instances = [];

    public function __construct(array $components)
    {
        $this->components = $components;
    }

    public function get(string $name): mixed
    {
        // TODO verify against cyclic dependencies

        if (! isset($this->instances[$name])) {
            $this->instances[$name] = $this->components[$name]->resolve($this);
        }

        return $this->instances[$name];
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->components);
    }
}
