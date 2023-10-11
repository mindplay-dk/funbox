<?php

namespace mindplay\funbox;

interface Definition {
    public function validate(Context $context): void;
}
