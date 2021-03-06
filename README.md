### `mindplay/funbox`

This package implements a simple IOC container.

It was designed specifically for PHP 8 to leverage `fn` function expressions with PHP 8 attributes for configuration.

There are two interesting benefits to this approach. First, you get full IDE support for all factory expressions - your IDE will be able to highlight missing or wrong parameters/types, so you can correct these before even attempting to run it. Secondly, the container is able to perform a full, up-front validation of all the bootstrapping - when you create a container instance, any missing components/dependencies will generate an immediate error.

⚠ ***WARNING:** just a playful prototype at this stage!*

For the moment, look at `test/test.php` to see how this approach works in practice.
