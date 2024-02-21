<?php

use mindplay\funbox\Config;
use mindplay\funbox\Context;
use mindplay\funbox\DependencyException;
use mindplay\funbox\id;

require dirname(__DIR__) . "/vendor/autoload.php";

use function mindplay\testies\{ test, ok, eq, expect, configure, run };

if (getenv("XDEBUG_MODE")) {
    configure()->enableCodeCoverage(
        output_path: __DIR__ . "/coverage.xml",
        source_paths: [dirname(__DIR__) . "/src"]
    );
}

test(
    "can resolve dependency graph",
    function () {
        $context = new Context();

        $context->add(new UserProvider);

        $container = $context->createContainer();

        ok($container->get(Cache::class) instanceof FileCache);
        ok($container->get(UserRepository::class) instanceof UserRepository);
        eq($container->get(UserRepository::class)->db, $container->get(Database::class));
        eq($container->get(UserRepository::class)->cache, $container->get(Cache::class));
    }
);

test(
    "can import PSR service providers",
    function () {
        $context = new Context();

        $context->addProvider(new SamplePSRProvider);

        $container = $context->createContainer();

        eq($container->get("A"), "A");
        eq($container->get("B"), "B");
        eq($container->get("AB"), "ABC");
    }
);

test(
    "can act as PSR service provider",
    function () {
        $context = new Context();

        $context->add(new UserProvider);

        $another_context = new Context();

        $another_context->addProvider($context);

        $container = $another_context->createContainer();

        ok($container->get(Cache::class) instanceof FileCache);
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
    }
);

test(
    "detects and throws for unsatisfied dependency in factory",
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
    "can fall back to parameter name for built-in types (configuration values)",
    function () {
        $context = new Context();

        $context->register(
            Cache::class,
            fn (string $CACHE_PATH) => new FileCache($CACHE_PATH)
        );

        $context->set("CACHE_PATH", "/tmp/cache");

        $container = $context->createContainer();

        eq($container->get(Cache::class)->path, $container->get("CACHE_PATH"));
    }
);

test(
    "can extend components",
    function () {
        $context = new Context();

        $context->set("a", 1);
        $context->set("b", 100);

        $context->extend("a", fn (#[id("a")] int $a, #[id("b")] int $b) => $a + $b);
        $context->extend("a", fn (#[id("a")] $a) => $a + 10);

        $container = $context->createContainer();

        eq($container->get("a"), 111, "extension applied, with resolved dependency");
    }
);

test(
    "detects and throws for undefined dependency in extension",
    function () {
        $context = new Context();

        $context->set("a", 1);

        $context->extend("a", fn (#[id("b")] int $does_not_exist) => $does_not_exist);

        expect(
            DependencyException::class,
            "should throw for missing dependency in extension",
            function () use ($context) {
                $context->createContainer();
            },
            "/unsatisfied dependency: b for parameter \\\$does_not_exist/"
        );
    }
);

test(
    "can apply configuration",
    function () {
        $context = new Context();

        $context->add(new Config(["A"=>1, "B"=>2]));

        $container = $context->createContainer();

        ok($container->get("A") === 1 && $container->get("B") === 2, "can apply configuration array");
    }
);

test(
    "can load JSON configuration",
    function () {
        $context = new Context();

        $context->add(Config::fromJSON(__DIR__ . "/fixture.json"));

        $container = $context->createContainer();

        eq($container->get("SECRETS"), [123, 456], "can load top-level values; can tell arrays from objects");
        eq($container->get("SERVER_PORT"), 123, "can load nested key/value");
        eq($container->get("A_B_C"), "TEST", "can load deeply nested key/value");

        expect(
            DependencyException::class,
            "should throw for missing file",
            function () {
                Config::fromJSON(__DIR__ . "/does_not_exist.json");
            },
            [
                "/File not found.*does_not_exist\\.json/"
            ]
        );
    }
);

test(
    "can load INI configuration",
    function () {
        $context = new Context();

        $context->add(Config::fromINI(__DIR__ . "/fixture.ini"));

        $container = $context->createContainer();

        eq($container->get("SECRETS"), [123, 456], "can load top-level values; can tell arrays from objects");
        eq($container->get("SERVER_PORT"), 123, "can load nested key/value");

        expect(
            DependencyException::class,
            "should throw for missing file",
            function () {
                Config::fromINI(__DIR__ . "/does_not_exist.ini");
            },
            [
                "/File not found.*does_not_exist\\.ini/"
            ]
        );
    }
);

test(
    "can load system environment as configuration",
    function () {
        $context = new Context();

        $context->add(Config::fromEnv());

        $container = $context->createContainer();

        eq($container->get("TEST"), "HELLO_WORLD", "it imports the system environment (did you run this test via 'composer test' ?)");
    }
);

exit(run());
