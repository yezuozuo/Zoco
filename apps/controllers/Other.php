<?php

namespace App\Controller;

use App;
use Zoco;

class Other extends Zoco\Controller {
    public function __construct($zoco) {
        parent::__construct($zoco);
        Zoco::$php->session->start();
        Zoco\Auth::loginRequire();
    }

    public function viewTest() {
        $this->assign('my_var', 'zoco view');
        $this->display('view_test.tpl.php');
    }

    public function tplTest() {
        $this->tpl->assign('my_var', 'zoco use smarty');
        $this->tpl->display('tpl_test.html');
    }
}