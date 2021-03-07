<?php

namespace mindplay\funbox;

use Closure;
use ReflectionFunction;

class Context
{
    /**
     * @var Component[] map where component name => Component instance
     */
    private array $components = [];

    /**
     * @var Component[] list of unvalidated Component instances
     */
    private array $unvalidated = [];

    public function register(string $name, Closure $create): void
    {
        $this->components[$name] = $this->unvalidated[] = new Component($name, $create);
    }

    public function set(string $name, mixed $value): void
    {
        $this->register($name, fn () => $value);
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->components);
    }

    public function createContainer()
    {
        foreach ($this->unvalidated as $component) {
            $component->validate($this);
        }

        $this->unvalidated = [];

        return new Container($this->components);
    }
}
