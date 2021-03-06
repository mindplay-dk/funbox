<?php

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
