<?php

namespace mindplay\funbox;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var Entry[] map where component name => component Entry instance
     */
    private array $components = [];

    /**
     * @var (Entry[])[] map where Entry ID => extension Entry list
     */
    private array $extensions = [];

    /**
     * @var array map where component name => component instance
     */
    private array $instances = [];

    /**
     * @var array<string,int> map where component name => recursion depth
     */
    private array $resolving = [];

    public function __construct(array $components, array $extensions)
    {
        $this->components = $components;
        $this->extensions = $extensions;
    }

    public function get(string $name): mixed
    {
        if (! isset($this->instances[$name])) {
            try {
                if (array_key_exists($name, $this->resolving)) {
                    $resolved_names = array_flip($this->resolving);

                    ksort($resolved_names, SORT_NUMERIC);

                    $cycle_start_index = array_search($name, $resolved_names, true) ?: 0;

                    $names_in_cycle = [
                        ...array_slice($resolved_names, $cycle_start_index),
                        $name
                    ];

                    throw new DependencyException(
                        "Dependency cycle detected: " . implode(' -> ', $names_in_cycle)
                    );
                } else {
                    $this->resolving[$name] = count($this->resolving);
                }

                $instance = $this->components[$name]->resolve($this);

                if (array_key_exists($name, $this->extensions)) {
                    $unresolved = [$name => $instance];

                    foreach ($this->extensions[$name] as $extension) {
                        $instance = $extension->resolve($this, $unresolved);
                    }
                }

                $this->instances[$name] = $instance;
            } finally {
                unset($this->resolving[$name]);
            }
        }

        return $this->instances[$name];
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->components);
    }
}
