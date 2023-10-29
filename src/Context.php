<?php

namespace mindplay\funbox;

use Closure;

/**
 * This class represents a collection of component/service definitions.
 */
class Context
{
    /**
     * @var Entry[] map where Entry ID => Component instance
     */
    private array $components = [];

    /**
     * @var (Component[])[] map where Entry ID => list of Component extensions
     */
    private array $extensions = [];

    /**
     * @var Definition[] list of unvalidated Definitions
     */
    private array $unvalidated = [];

    /**
     * @throws UnspecifiedDependencyException if the given Closure has any unspecified dependencies
     */
    public function register(string $id, Closure $create): void
    {
        $this->components[$id] = $this->unvalidated[] = new Component($id, $create);
    }

    public function set(string $id, mixed $value): void
    {
        $this->register($id, fn () => $value);
    }

    public function extend(string $id, Closure $extend): void
    {
        $this->extensions[$id][] = $this->unvalidated[] = new Component($id, $extend);
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->components);
    }

    public function add(Provider $provider): void
    {
        $provider->register($this);
    }

    /**
     * @throws UnsatisfiedDependencyException if any of the components in the Container have unsatisfied dependencies
     */
    public function createContainer(): Container
    {
        foreach ($this->unvalidated as $component) {
            $component->validate($this);
        }

        $this->unvalidated = [];

        return new Container($this->components, $this->extensions);
    }
}
