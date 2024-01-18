<?php

namespace mindplay\funbox;

use Closure;
use Interop\Container\ExtensionDefinitionInterface;
use Psr\Container\ContainerInterface;
use Interop\Container\ServiceProviderInterface;

/**
 * @see ServiceProviderInterface::getExtensions()
 */
class Extension extends Definition implements ExtensionDefinitionInterface
{
    public function __construct(string $id, Closure $extend)
    {
        parent::__construct($id, $extend);

        $this->extension_id = $id;
    }

    public function __invoke(ContainerInterface $container, mixed $previous): mixed
    {
        // TODO handle errors
        return ($this->definition)(...$this->resolveDeps($container, $previous));
    }
}
