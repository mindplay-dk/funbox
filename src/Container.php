<?php

namespace mindplay\funbox;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var FactoryFunction[] map where component name => Component instance
     */
    private array $factories = [];

    /**
     * @var (ExtensionFunction[])[] map where Entry ID => Extension list
     */
    private array $extensions = [];

    /**
     * @var array map where component name => component instance
     */
    private array $instances = [];

    public function __construct(array $components, array $extensions)
    {
        $this->factories = $components;
        $this->extensions = $extensions;
    }

    public function get(string $name): mixed
    {
        // TODO verify against cyclic dependencies

        if (! isset($this->instances[$name])) {
            $this->instances[$name] = $this->factories[$name]($this);

            if (array_key_exists($name, $this->extensions)) {
                foreach ($this->extensions[$name] as $extension) {
                    $this->instances[$name] = $extension($this, $this->instances[$name] ?? null);
                }
            }
        }

        return $this->instances[$name];
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->factories);
    }
}
