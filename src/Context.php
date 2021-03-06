<?php

namespace mindplay\funbox;

use Closure;
use ReflectionFunction;

class Context
{
    /**
     * @var ComponentFactory[] map where component name => ComponentFactory instance
     */
    private array $components = [];

    /**
     * @var ComponentFactory[] list of unvalidated ComponentFactory instances
     */
    private array $unvalidated = [];

    public function register(string $name, Closure $create): void
    {
        $this->components[$name] = $this->unvalidated[] = new ComponentFactory($create);
    }

    public function set(string $name, mixed $value): void
    {
        $this->register($name, fn () => $value);
    }

    public function createContainer()
    {
        foreach ($this->unvalidated as $factory) {
            foreach ($factory->names as $index => $name) {
                if (! array_key_exists($name, $this->components)) {
                    $function = new ReflectionFunction($factory->create);
                    
                    throw new DependencyException(
                        "Factory function in {$function->getFileName()}"
                        . " at line {$function->getStartLine()}"
                        . " has an unsatisfied dependency: {$name}"
                        . " for parameter \${$factory->params[$index]->getName()}"
                    );
                }
            }
        }

        $this->unvalidated = [];

        return new Container($this->components);
    }
}
