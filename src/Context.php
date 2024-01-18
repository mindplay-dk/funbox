<?php

namespace mindplay\funbox;

use Closure;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

/**
 * This class represents a collection of component/service definitions.
 */
class Context implements ServiceProviderInterface
{
    /**
     * @var array<string,(callable(ContainerInterface):mixed)> map where Entry ID => Component instance
     */
    private array $factories = [];

    /**
     * @var array<string,(callable(ContainerInterface,mixed):mixed)[]> map where Entry ID => list of Component extensions
     */
    private array $extensions = [];

    /**
     * @var Validation[] list of unvalidated Factories and Extensions
     */
    private array $unvalidated = [];

    /**
     * @throws UnspecifiedDependencyException if the given Closure has any unspecified dependencies
     */
    public function register(string $id, Closure $create): void
    {
        $this->factories[$id] = $this->unvalidated[] = new Factory($id, $create);
    }

    public function set(string $id, mixed $value): void
    {
        $this->register($id, fn () => $value);
    }

    public function extend(string $id, Closure $extend): void
    {
        $this->extensions[$id][] = $this->unvalidated[] = new Extension($id, $extend);
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->factories);
    }

    // TODO yeah, we have two different add-methods at the moment, one for our own providers
    //      and another for the Interop/Container/ServiceProviderInterface - we should figure
    //      out how to unify these, or get rid of one of them...
    //
    //      also, fun fact, it's now possible to add a provider to a provider, which is
    //      probably not a good idea, but it's possible, and it works, and it's fun :-)

    public function add(Provider $provider): void
    {
        $provider->register($this);
    }

    public function addProvider(ServiceProviderInterface $provider): void
    {
        $this->factories = array_merge($this->factories, $provider->getFactories());
        $this->extensions = array_merge_recursive($this->extensions, $provider->getExtensions());
    }

    /**
     * @throws UnsatisfiedDependencyException if any of the components in the Container have unsatisfied dependencies
     */
    public function createContainer(): Container
    {
        foreach ($this->unvalidated as $component) {
            $component->validate($this);
        }

        $this->unvalidated = [];

        return new Container($this->factories, $this->extensions);
    }

    public function getFactories(): array
    {
        return $this->factories;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }
}
