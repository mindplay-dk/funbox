<?php

namespace mindplay\funbox;

use Closure;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;

class Component
{
    private string $name;

    private Closure $create;

    /**
     * @var ReflectionParameter[]
     */
    private array $params = [];

    /**
     * @var string[] list of dependencies (component names)
     */
    private array $dependencies = [];

    public function __construct(string $name, Closure $create)
    {
        $this->name = $name;
        $this->create = $create;

        $function = new ReflectionFunction($create);

        $this->params = $function->getParameters();

        foreach ($this->params as $param) {
            $type = $param->getType();

            $attrs = $param->getAttributes("name");

            if (count($attrs)) {
                $this->dependencies[] = $attrs[0]->getArguments()[0];
            } else if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                $this->dependencies[] = $type->getName();
            } else {
                throw new DependencyException(
                    "Factory function in {$function->getFileName()}"
                    . "#{$function->getStartLine()}"
                    . " has an unspecified dependency \${$param->getName()}"
                    . " for component: {$this->name}"
                    . " (use the #[name] attribute to specify the name or type)"
                );
            }
        }
    }

    public function validate(Context $context): void
    {
        foreach ($this->dependencies as $index => $name) {
            if (! $context->has($name)) {
                $function = new ReflectionFunction($this->create);
                
                throw new DependencyException(
                    "component function in {$function->getFileName()}"
                    . " at line {$function->getStartLine()}"
                    . " has an unsatisfied dependency: {$name}"
                    . " for parameter \${$this->params[$index]->getName()}"
                );
            }
        }
    }

    public function resolve(Container $container)
    {
        $resolved = [];

        foreach ($this->dependencies as $dependency) {
            $resolved[] = $container->get($dependency);
        }

        // TODO handle errors
        return ($this->create)(...$resolved);
    }
}
