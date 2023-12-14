<?php

namespace mindplay\funbox;

interface Validation
{
    public function validate(Context $context): void;
}
