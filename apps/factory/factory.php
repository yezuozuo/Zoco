<?php

class Factory {
    public function hello() {
        echo __METHOD__;
    }

    public function test() {
        echo 'test';
    }
}

return new Factory();