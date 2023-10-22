<?php

namespace mindplay\funbox;

class Config implements Provider
{
    public function __construct(private array $data)
    {}

    public static function fromJSON(string $path): self
    {
        self::check($path);

        return new self(self::expandKeys(json_decode(file_get_contents($path), true)));
    }

    public static function fromINI(string $path, int $mode = INI_SCANNER_TYPED): self
    {
        self::check($path);

        return new self(self::expandKeys(parse_ini_file($path, true, $mode)));
    }

    public static function fromEnv(bool $local_only = true): self
    {
        return new self(getenv(null, $local_only));
    }

    public function register(Context $context): void
    {
        foreach ($this->data as $id => $value) {
            $context->set($id, $value);
        }
    }

    private static function check(string $path): void
    {
        if (! is_file($path)) {
            throw new DependencyException("File not found: {$path}");
        }

        if (! is_readable($path)) {
            throw new DependencyException("Access denied: {$path}");
        }
    }

    private static function expandKeys(mixed $data, array $path = []): array
    {
        $result = [];

        $prefix = count($path)
            ? implode(".", $path) . "."
            : "";

        foreach ($data as $name => $value) {
            if (is_array($value) && ! array_is_list($value)) {
                $result = array_merge($result, self::expandKeys($value, [...$path, $name]));
            } else {
                $result[$prefix . $name] = $value;
            }
        }

        return $result;
    }
}
