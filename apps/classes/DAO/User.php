<?php

namespace App\DAO;

class User {
    protected $id;

    public function __construct($id) {
        $this->id = $id;
    }

    public function get() {
        return model('DbLog')->get($this->id);
    }
}
