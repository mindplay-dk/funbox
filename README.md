# `mindplay/funbox`

**[Pimple](https://github.com/silexphp/Pimple) for the PHP 8 era:**

* IDE support, static type-checking, auto-completions.
* Full container bootstrapping validation at startup.
* Performance on par with that of Pimple.
* Verbosity similar to Pimple, but more declarative.

⚠ ***WARNING:** still under development.*

This container was designed specifically for PHP 8.x to leverage `fn` function expressions with [attributes](https://www.php.net/manual/en/language.attributes.overview.php) for configuration.

Compared with complex IOC containers, configuration is more verbose, but also more explicit - every component has a defined factory function, which makes it possible to validate all dependencies up front, without actually loading any classes. The use of function expressions enable an IDE or static analysis tool to verify and type-check all constructor calls.

## Usage

Create a `Context` and bootstrap it:

```php
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
```

Note the use of the attribute `#[id("cache.path")]` applied to the `string $path` argument for the `FileCache` function expression - this tells the container to resolve the dependency using the component named `cache.path`.

Now create a `Container` and look up a component instance:

```php
$container = $context->createContainer();

$cache = $container->get(UserRepository::class);
```

The dependencies of the `UserRepository` factory-function will get resolved and injected.

## Providers

You can achieve modularity by wrapping a section of bootstrapping in a `Provider` implementation:

```php
class CacheProvider implements Provider
{
    public function register(Context $context)
    {
        $context->register(
            Cache::class,
            fn (#[id("cache.path")] string $path) => new FileCache($path)
        );

        $context->set("cache.path", "/tmp/cache");
    }
}
```

Use the `add` method to apply the provider to a `Context`:

```php
$context->add(new CacheProvider());
```

### `Config` Provider

The built-in `Config` provider allows you to load configuration from standard JSON or INI files, and/or import your system environment variables.

You can use configuration providers to decouple yourself from configuration sources - for example, if you create a provider that expects some external configuration:

```php
class CacheProvider implements Provider
{
    public function register(Context $context)
    {
        $context->register(
            Cache::class,
            fn (#[id("cache.path")] string $path) => new FileCache($path)
        );
    }
}
```

Note that `cache.path` was not defined by this provider - we can now load it from a `config.json` file like this one:

```json
{
    "cache": {
        "path": "/tmp/my-app/cache"
    }
}
```

```php
$context->add(Config::fromJSON("config.json"));
```

Or, from a `config.ini` file like the following:

```ini
[cache]
path = /tmp/my-app/cache
```

```php
$context->add(Config::fromINI("config.ini"));
```

TODO import system environment
