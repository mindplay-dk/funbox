<?php

namespace mindplay\funbox;

use Psr\Container\ContainerInterface;
use Interop\Container\ServiceProviderInterface;

/**
 * @see ServiceProviderInterface::getFactories()
 */
interface FactoryFunction
{
    public function __invoke(ContainerInterface $container): mixed;
}
