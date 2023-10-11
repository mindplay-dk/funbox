<?php

namespace mindplay\funbox;

use RuntimeException;

throw new RuntimeException("this placeholder attribute provides type-checking, but is never loaded or used");

class id
{
    public function __construct(string $id)
    {
    }
}
