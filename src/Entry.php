<?php

namespace mindplay\funbox;

interface Entry {
    public function resolve(Container $container): mixed;
}
