<?php

namespace mindplay\funbox;

use Closure;
use Psr\Container\ContainerInterface;
use Interop\Container\ServiceProviderInterface;

class Factory implements FactoryFunction, Validation
{
    use Definition;

    public function __construct(string $id, Closure $create)
    {
        $this->init($id, $create);
    }

    public function __invoke(ContainerInterface $container): mixed
    {
        // TODO handle errors
        return ($this->definition)(...$this->resolveDeps($container));
    }
}
