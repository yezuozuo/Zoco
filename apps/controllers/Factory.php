<?php

namespace App\Controller;

use App;
use Zoco;

class Factory extends Zoco\Controller {
    public function __construct($zoco) {
        parent::__construct($zoco);
        Zoco::$php->session->start();
        Zoco\Auth::loginRequire();
    }

    public function index() {
        $this->factory->hello();
        $this->factory->test();
    }
}