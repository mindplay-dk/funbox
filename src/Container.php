<?php

namespace mindplay\funbox;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var ComponentFactory[] map where component name => Component instance
     */
    private array $factories = [];

    /**
     * @var array map where component name => component instance
     */
    private array $components = [];

    public function __construct(array $factories)
    {
        $this->factories = $factories;
    }

    public function get($name)
    {
        // TODO verify against cyclic dependencies

        if (! isset($this->components[$name])) {
            $factory = $this->factories[$name];

            $resolved = [];

            foreach ($factory->names as $dependency) {
                $resolved[] = $this->get($dependency);
            }

            // TODO handle errors
            $this->components[$name] = ($factory->create)(...$resolved);
        }

        return $this->components[$name];
    }

    public function has($name)
    {
        return array_key_exists($name, $this->factories);
    }
}
