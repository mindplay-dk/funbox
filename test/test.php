<?php

use mindplay\funbox\Context;
use mindplay\funbox\DependencyException;
use mindplay\funbox\id;

require dirname(__DIR__) . "/vendor/autoload.php";

use function mindplay\testies\{ test, ok, eq, expect, configure, run };

test(
    "can resolve dependency graph",
    function () {
        $context = new Context();

        $context->register(
            Cache::class,
            fn (#[id("cache.path")] string $path) => new FileCache($path)
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

        ok($container->get(Cache::class) instanceof FileCache);
        ok($container->get(UserRepository::class) instanceof UserRepository);
        eq($container->get(UserRepository::class)->db, $container->get(Database::class));
        eq($container->get(UserRepository::class)->cache, $container->get(Cache::class));
    }
);

test(
    "throws for unspecified dependencies",
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
                    fn (string $path) => new FileCache($path)
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

test(
    "can fall back to null for nullable parameters",
    function () {
        $context = new Context();

        $context->register("test", fn (?Database $db) => $db);

        $container = $context->createContainer();

        eq($container->get("test"), null, "nullable dependency should resolve to null");
    }
);

test(
    "can fall back to default for optional parameters",
    function () {
        $context = new Context();

        $context->register("test", fn (#[id("port")] ?int $number = 123) => $number);

        $container = $context->createContainer();

        eq($container->get("test"), 123, "optional dependency should resolve to null");
    }
);

test(
    "can extend components",
    function () {
        $context = new Context();

        $context->set("a", 1);
        $context->set("b", 100);

        $context->extend("a", fn (#[id("a")] int $a, #[id("b")] int $b) => $a + $b);

        $container = $context->createContainer();

        eq($container->get("a"), 101, "extension applied, with resolved dependency");
    }
);

exit(run());
