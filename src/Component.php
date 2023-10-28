<?php

namespace mindplay\funbox;

use Closure;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;

class Component implements Entry, Definition
{
    private string $id;

    private Closure $create;

    /**
     * @var ReflectionParameter[]
     */
    private array $params = [];

    /**
     * @var string[] list of dependencies (Entry IDs)
     */
    private array $dependencies = [];

    public function __construct(string $id, Closure $create)
    {
        $this->id = $id;
        $this->create = $create;

        $function = new ReflectionFunction($create);

        $this->params = $function->getParameters();

        foreach ($this->params as $param) {
            $type = $param->getType();

            $attrs = $param->getAttributes(id::class);

            if (count($attrs)) {
                $this->dependencies[] = $attrs[0]->getArguments()[0];
            } else if ($type instanceof ReflectionNamedType) {
                $this->dependencies[] = $type->isBuiltin() ? $param->getName() : $type->getName();
            } else {
                throw new UnspecifiedDependencyException($function, $param, $this->id);
            }
        }
    }

    public function validate(Context $context): void
    {
        foreach ($this->dependencies as $index => $id) {
            if (! $context->has($id) && ! $this->params[$index]->isOptional() && ! $this->params[$index]->allowsNull()) {
                $function = new ReflectionFunction($this->create);

                throw new UnsatisfiedDependencyException($function, $this->params[$index], $id);
            }
        }
    }

    public function resolve(Container $container): mixed
    {
        $resolved = [];

        foreach ($this->dependencies as $index => $id) {
            if ($container->has($id)) {
                $resolved[] = $container->get($id);
            } else if ($this->params[$index]->isOptional()) {
                $resolved[] = $this->params[$index]->getDefaultValue();
            } else if ($this->params[$index]->allowsNull()) {
                $resolved[] = null;
            }
        }

        // TODO handle errors
        return ($this->create)(...$resolved);
    }
}
