### `mindplay/funbox`

This package implements a simple IOC container.

⚠ ***WARNING:** just a playful prototype at this stage!*

For the moment, look at `test/test.php` to see how this approach works in practice.

This container was designed specifically for PHP 8 to leverage `fn` function expressions with [attributes](https://www.php.net/manual/en/language.attributes.overview.php) for configuration.

Compared with a more traditional IOC container, this approach is more verbose: no auto-wiring of any sort is possible. But it's also more explicit - every component has a defined function expression, which makes it possible to verify all dependencies up front, without actually loading any classes. While the use of function expressions enable an IDE or static analysis tool to verify and type-check all constructor calls.

In terms of performance, a [preliminary benchmark](https://github.com/mindplay-dk/unbox/compare/php-8...funbox-benchmark) against [unbox](https://github.com/mindplay-dk/unbox) suggests a 20% overhead in time to bootstrap, but with component lookups at least twice as fast - overall performance is on par with Pimple.

## Usage

Create a `Context` and register your components:

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

Note the use of the attribute `#[id("cache.path")]` applied to the `string $path` argument for the `FileCache` function expression - this tells the container to look up the dependency in the component named `cache.path`.

Other dependencies in this example are singletons - they're registered under their type-names, so they can be automatically resolved against the type-hints of other function expressions.

Now create a `Container` and grab your component instance from it:

```php
$container = $context->createContainer();

$cache = $container->get(UserRepository::class);
```

The dependencies of `UserRepository` get resolved and injected.
