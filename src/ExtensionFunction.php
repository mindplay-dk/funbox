<?php

namespace mindplay\funbox;

use Psr\Container\ContainerInterface;
use Interop\Container\ServiceProviderInterface;

/**
 * @see ServiceProviderInterface::getExtensions()
 */
interface ExtensionFunction
{
    public function __invoke(ContainerInterface $container, mixed $previous): mixed;
}
