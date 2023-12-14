<?php

namespace mindplay\funbox;

use Closure;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;
use Psr\Container\ContainerInterface;
use Interop\Container\ServiceProviderInterface;

abstract class Definition implements Validation
{
    protected string $id;
    protected ?string $extension_id = null;
    protected Closure $definition;

    /**
     * @var ReflectionParameter[]
     */
    private array $params;

    /**
     * @var string[] list of dependencies (Entry IDs)
     */
    private array $dependencies;

    public function __construct(string $id, Closure $definition)
    {
        $function = new ReflectionFunction($definition);

        $this->id = $id;
        $this->definition = $definition;
        $this->params = $function->getParameters();
        $this->dependencies = [];

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

    protected function resolveDeps(ContainerInterface $container, mixed $previous = null): mixed
    {
        $resolved = [];

        foreach ($this->dependencies as $index => $id) {
            if ($id === $this->id) {
                $resolved[] = $previous;
            } else if ($container->has($id)) {
                $resolved[] = $container->get($id);
            } else if ($this->params[$index]->isOptional()) {
                $resolved[] = $this->params[$index]->getDefaultValue();
            } else if ($this->params[$index]->allowsNull()) {
                $resolved[] = null;
            }
        }

        return $resolved;
    }

    public function validate(Context $context): void
    {
        foreach ($this->dependencies as $index => $id) {
            if (! $context->has($id) && ! $this->params[$index]->isOptional() && ! $this->params[$index]->allowsNull() && ! ($this->extension_id === $id)) {
                $function = new ReflectionFunction($this->definition);

                throw new UnsatisfiedDependencyException($function, $this->params[$index], $id);
            }
        }
    }
}
