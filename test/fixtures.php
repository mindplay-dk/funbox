<?php

use Interop\Container\ServiceProviderInterface;
use mindplay\funbox\Context;
use mindplay\funbox\Provider;
use mindplay\funbox\id;
use Psr\Container\ContainerInterface;

interface Cache
{}

class FileCache implements Cache
{
    public function __construct(
        public string $path
    ) {}
}

class Database
{}

class UserRepository
{
    public function __construct(
        public Database $db,
        public Cache $cache,
    ) {}
}

class UserProvider implements Provider
{
    public function register(Context $context): void
    {
        $context->register(
            Cache::class,
            fn (#[id("CACHE_PATH")] string $path) => new FileCache($path)
        );

        $context->set("CACHE_PATH", "/tmp/cache");

        $context->register(
            Database::class,
            fn () => new Database()
        );

        $context->register(
            UserRepository::class,
            fn (Database $db, Cache $cache) => new UserRepository($db, $cache)
        );
    }
}

class SamplePSRProvider implements ServiceProviderInterface
{
    public function getFactories(): array
    {
        return [
            "A" => fn () => "A",
            "B" => fn () => "B",
            "AB" => fn (ContainerInterface $container) => $container->get("A") . $container->get("B"),
        ];
    }

    public function getExtensions(): array
    {
        return [
            "AB" => [
                fn (ContainerInterface $container, string $value) => $value . "C",
            ],
        ];
    }
}
