<?php

namespace mindplay\funbox;

use Closure;

class Context
{
    /**
     * @var Entry[] map where Entry ID => Component instance
     */
    private array $components = [];

    /**
     * @var (Extension[])[] map where Entry ID => Extension list
     */
    private array $extensions = [];

    /**
     * @var Definition[] list of unvalidated Definitions
     */
    private array $unvalidated = [];

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

    public function createContainer(): Container
    {
        foreach ($this->unvalidated as $component) {
            $component->validate($this);
        }

        $this->unvalidated = [];

        return new Container($this->components, $this->extensions);
    }
}
