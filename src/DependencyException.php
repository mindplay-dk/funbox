<?php

namespace mindplay\funbox;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class DependencyException
    extends RuntimeException
    implements ContainerExceptionInterface
{}
