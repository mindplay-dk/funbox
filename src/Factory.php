<?php

namespace mindplay\funbox;

use Closure;
use Interop\Container\FactoryDefinitionInterface;
use Psr\Container\ContainerInterface;
use Interop\Container\ServiceProviderInterface;

class Factory extends Definition implements FactoryDefinitionInterface
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
