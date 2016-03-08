<?php

namespace App\Controller;

use App;
use Zoco;

class Classes extends Zoco\Controller {
    public function __construct($zoco) {
        parent::__construct($zoco);
        Zoco::$php->session->start();
        Zoco\Auth::loginRequire();
    }

    public function index() {
        App\Test::hello();
    }

    public function dao() {
        $res = App\Test::dao()->get();
        var_dump($res);
    }
}