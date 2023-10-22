<?php

namespace mindplay\funbox;

interface Provider
{
    public function register(Context $context): void;
}
