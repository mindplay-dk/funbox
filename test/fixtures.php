<?php

use mindplay\funbox\Context;
use mindplay\funbox\Provider;
use mindplay\funbox\id;

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
