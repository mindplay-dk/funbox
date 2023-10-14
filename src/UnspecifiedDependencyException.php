<?php

namespace mindplay\funbox;

use ReflectionFunction;
use ReflectionParameter;

class UnspecifiedDependencyException extends DependencyException
{
    public function __construct(ReflectionFunction $function, ReflectionParameter $param, string $id)
    {
        parent::__construct(
            "Entry definition in {$function->getFileName()}"
            . "#{$function->getStartLine()}"
            . " has an unspecified dependency \${$param->getName()}"
            . " for entry: {$id}"
            . " of type " . ($param->hasType() ? $param->getType() : "mixed")
            . " (use an #[id] attribute to specify the name or type)"
        );
    }
}
