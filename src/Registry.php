<?php

namespace mindplay\funbox;

use Closure;

interface Registry
{
    public function register(string $id, Closure $create): void;

    public function set(string $id, mixed $value): void;

    public function extend(string $id, Closure $extend): void;

    public function has(string $id): bool;
}
