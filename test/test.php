<?php

use mindplay\funbox\Context;
use mindplay\funbox\DependencyException;

require dirname(__DIR__) . "/vendor/autoload.php";

use function mindplay\testies\{ test, ok, eq, expect, configure, run };

test(
    "can resolve dependency graph",
    function () {
        $context = new Context();

        $context->register(
            Cache::class,
            fn (#[name("cache.path")] $path) => new FileCache($path)
        );

        $context->set("cache.path", "/tmp/cache");

        $context->register(
            Database::class,
            fn () => new Database()
        );
        
        $context->register(
            UserRepository::class,
            fn (Database $db, Cache $cache) => new UserRepository($db, $cache)
        );

        $container = $context->createContainer();

        $container->get(Cache::class);

        ok($container->get(UserRepository::class) instanceof UserRepository);
        eq($container->get(UserRepository::class)->db, $container->get(Database::class));
        eq($container->get(UserRepository::class)->cache, $container->get(Cache::class));
    }
);

test(
    "throws for unspecified dependency names",
    function () {
        $context = new Context();

        expect(
            DependencyException::class,
            "throws when no type is present and no name is specified",
            function () use ($context) {
                $context->register(
                    Cache::class,
                    fn ($path) => new FileCache($path)
                );
            },
            "/unspecified dependency \\\$path/"
        );

        expect(
            DependencyException::class,
            "throws when scalar type is present but no name is specified",
            function () use ($context) {
                $context->register(
                    Cache::class,
                    fn ($path) => new FileCache($path)
                );
            },
            "/unspecified dependency \\\$path/"
        );
    }
);

test(
    "detects and throws for unsatisfied dependency",
    function () {
        $context = new Context();

        $context->register(
            Database::class,
            fn () => new Database()
        );
        
        $context->register(
            UserRepository::class,
            fn (Database $db, Cache $cache) => new UserRepository($db, $cache)
        );

        expect(
            DependencyException::class,
            "should throw for unsatisfied dependency",
            function () use ($context) {
                $context->createContainer();
            },
            "/unsatisfied dependency: Cache for parameter \\\$cache/"
        );
    }
);

exit(run());
