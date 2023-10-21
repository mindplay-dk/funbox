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
     * @var (Component[])[] map where Entry ID => Extension list
     */
    private array $extensions = [];

    /**
     * @var array map where component name => component instance
     */
    private array $instances = [];

    public function __construct(array $components, array $extensions)
    {
        $this->components = $components;
        $this->extensions = $extensions;
    }

    public function get(string $name): mixed
    {
        // TODO verify against cyclic dependencies

        if (! isset($this->instances[$name])) {
            $this->instances[$name] = $this->components[$name]->resolve($this);

            if (array_key_exists($name, $this->extensions)) {
                foreach ($this->extensions[$name] as $extension) {
                    $this->instances[$name] = $extension->resolve($this);
                }
            }
        }

        return $this->instances[$name];
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->components);
    }
}
