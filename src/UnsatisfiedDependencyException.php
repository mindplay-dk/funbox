<?php

namespace mindplay\funbox;

use ReflectionFunction;
use ReflectionParameter;

class UnsatisfiedDependencyException extends DependencyException
{
    public function __construct(ReflectionFunction $function, ReflectionParameter $param, string $id)
    {
        parent::__construct(
            "Entry definition in {$function->getFileName()}"
            . " at line {$function->getStartLine()}"
            . " has an unsatisfied dependency: {$id}"
            . " for parameter \${$param->getName()}"
        );
    }
}
