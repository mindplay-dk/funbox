# `mindplay/funbox`

**[Pimple](https://github.com/silexphp/Pimple) for the PHP 8 era:**

* IDE support, static type-checking, auto-completions.
* Full container bootstrapping validation at startup.
* Performance on par with that of Pimple.
* Verbosity similar to Pimple, but more declarative.
* Mutable Context, immutable Containers.

This container was designed specifically for PHP 8.x to leverage `fn` function expressions with [attributes](https://www.php.net/manual/en/language.attributes.overview.php) for configuration.

Compared with some more complex container libraries, bootstrapping may be more verbose, but is also more explicit - every component has a defined factory function, which makes it possible to validate all dependencies up front, without actually loading any classes. The use of function expressions enable an IDE or static analysis tool to verify and type-check all constructor calls.

## Usage

This container has two primary APIs:

- `Context` represents a logical dependency injection context - this is where you register your component/service *definitions*.
- `Container` represents an actual container instance - this is where your component/service *instances* exist.

### Creating a `Context`

First off, create a `Context` and bootstrap it:

```php
$context = new Context();

$context->register(
    Cache::class,
    fn (string $CACHE_PATH) => new FileCache($path)
);

$context->set("CACHE_PATH", "/tmp/cache");

$context->register(
    "db.write-master",
    fn () => new Database()
);

$context->register(
    UserRepository::class,
    fn (#[id("db.write-master")] Database $db, Cache $cache) => new UserRepository($db, $cache)
);
```

Note how the `string $CACHE_PATH` argument is resolved using the parameter name `CACHE_PATH` - this fallback is available for built-in types such as `string`, `int` and `array`. You can load configuration values from JSON or INI files, or from the system environment, using `Config` providers - this will be covered below.

Next, note the use of the `#[id("db.write-master")]` attribute applied to the `Database $db` argument for the `UserRepository` factory function - this tells the container to resolve the dependency using the component named `db.write-master`. This pattern is useful when you have multiple instances of the same class.

**By convention:**

- **Singletons** should be registered using `ClassName::class` expressions.
- **Named instances** should be registered using `dotted.lower.case` names.
- **Configuration values** should be registered using `ALL_CAPS` names.

Following these conventions avoids component name collisions.

Once your `Context` is ready, create a `Container`, and you can look up a component instance:

```php
$container = $context->createContainer();

$cache = $container->get(UserRepository::class);
```

The dependencies of the `UserRepository` factory-function will get resolved and injected.

Note that validation of the `Context` takes place when you first call `createContainer` - any
unsatisfied dependencies will generate an `UnsatisfiedDependencyException`, which enables you to
catch and correct mistakes as early as possible.

## Providers

You can achieve reusable bootstrapping by wrapping registrations in a `Provider` implementation:

```php
class CacheProvider implements Provider
{
    public function register(Context $context)
    {
        $context->register(
            Cache::class,
            fn (string $CACHE_PATH) => new FileCache($path)
        );

        $context->set("CACHE_PATH", "/tmp/cache");
    }
}
```

Then use the `add` method to apply the provider to a `Context`:

```php
$context->add(new CacheProvider());
```

## PSR Service Providers

TODO add docs

### `Config` Providers

The built-in `Config` provider allows you to load configuration from standard JSON or INI files, and/or import your system environment variables. You can use configuration providers to decouple yourself from configuration sources, e.g. loading different configuration files in production or staging, or injecting configuration values directly in tests.

The advantage of keeping configuration values in the container (as opposed to using some sort of configuration facility) is you get consistent dependency validation up front - the values in your configuration are just dependencies, same as any other. You can connect these dependencies to the components where they're needed, the exact same way you connect every other component in your application.

As an example, here's a provider that requires a `CACHE_PATH` configuration value:

```php
class CacheProvider implements Provider
{
    public function register(Context $context)
    {
        $context->register(
            Cache::class,
            fn (string $CACHE_PATH) => new FileCache($CACHE_PATH)
        );
    }
}
```

Note that `CACHE_PATH` is not defined by the provider itself - we can load this value from a `config.json` file like this one:

```json
{
    "CACHE": {
        "PATH": "/tmp/my-app/cache"
    }
}
```

And then bootstrap it like this:

```php
$context->add(Config::fromJSON("config.json"));
```

Similarly, we could load this value from a `config.ini` file like this one:

```ini
[cache]
path = /tmp/my-app/cache
```

And then bootstrap it like this:

```php
$context->add(Config::fromINI("config.ini"));
```

If you prefer using system environment variables, that's possible too, e.g. from a bash script:

```bash
CACHE_PATH=/tmp
```

And then bootstrap the system environment:

```php
$context->add(Config::fromEnv());
```
