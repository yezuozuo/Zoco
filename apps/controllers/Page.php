<?php

namespace App\Controller;

use App;
use Zoco;

class Page extends Zoco\Controller {
    public function __construct($zoco) {
        parent::__construct($zoco);
        Zoco::$php->session->start();
        Zoco\Auth::loginRequire();
    }

    public function index() {
        $title = 'zoco';
        $copyright = 'zoco';
        $this->assign('title', $title);
        $this->assign('copyright', $copyright);
        $this->display('head.php');
        $this->display('foot.php');
    }
}