<?php

namespace App\Controller;

use App;
use Zoco;

class Cache extends Zoco\Controller {
    public function __construct($zoco) {
        parent::__construct($zoco);
        Zoco::$php->session->start();
        Zoco\Auth::loginRequire();
    }

    public function pageCacheSet() {
        $pageCache = new \Zoco\PageCache();
        $pageCache->create('131');
    }

    public function pageCacheGet() {
        $pageCache = new \Zoco\PageCache();
        $pageCache->load();
    }

    public function cacheGet() {
        $result = $this->cache->get("zoco_var_2");
        var_dump($result);
    }

    public function cacheSet() {
        $result = $this->cache->set("zoco_var_2", "zoco2");
        if ($result) {
            echo "cache set success.Key=zoco_var_1.";
        } else {
            echo "cache set failed.";
        }
    }
}