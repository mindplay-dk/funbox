<?php

namespace mindplay\funbox;

use Closure;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;

class ComponentFactory
{
    /**
     * @readonly
     */
    public Closure $create;

    /**
     * @var ReflectionParameter[]
     */
    public array $params = [];

    /**
     * @var string[] list of dependency component names
     * 
     * @readonly
     */
    public array $names = [];

    public function __construct(Closure $create)
    {
        $this->create = $create;

        $function = new ReflectionFunction($create);

        $this->params = $function->getParameters();

        foreach ($this->params as $param) {
            $type = $param->getType();

            $attrs = $param->getAttributes("name");

            if (count($attrs)) {
                $this->names[] = $attrs[0]->getArguments()[0];
            } else if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                $this->names[] = $type->getName();
            } else {
                throw new DependencyException(
                    "Factory function in {$function->getFileName()}"
                    . " at line {$function->getStartLine()}"
                    . " has an unspecified dependency \${$param->getName()}"
                    . " (use the #[name] attribute to specify the name or type)"
                );
            }
        }
    }
}
