<?php

namespace App\Controller;

use App;
use Zoco;

class Redis extends Zoco\Controller {
    public function __construct($zoco) {
        parent::__construct($zoco);
        Zoco::$php->session->start();
        Zoco\Auth::loginRequire();
    }

    public function redisTest() {
        $result = $this->redis->keys('*');
        var_dump($result);
        \Zoco\Redis::getIncreaseId('a');
    }
}