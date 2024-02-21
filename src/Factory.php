<?php

namespace mindplay\funbox;

use Closure;
use Psr\Container\ContainerInterface;

class Factory extends Definition
{
    public function __construct(string $id, Closure $create)
    {
        parent::__construct($id, $create);
    }

    public function __invoke(ContainerInterface $container): mixed
    {
        // TODO handle errors
        return ($this->definition)(...$this->resolveDeps($container));
    }
}
