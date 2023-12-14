<?php

namespace mindplay\funbox;

use Closure;
use Psr\Container\ContainerInterface;
use Interop\Container\ServiceProviderInterface;

/**
 * @see ServiceProviderInterface::getExtensions()
 */
class Extension implements ExtensionFunction, Validation
{
    use Definition;

    public function __construct(string $id, Closure $extend)
    {
        $this->init($id, $extend);
    }

    public function __invoke(ContainerInterface $container, mixed $previous): mixed
    {
        // TODO handle errors
        return ($this->definition)(...$this->resolveDeps($container, $previous));
    }
}
