<?php

namespace mindplay\funbox;

interface Provider
{
    public function bootstrap(Registry $registry): void;
}
