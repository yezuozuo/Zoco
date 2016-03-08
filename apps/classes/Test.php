<?php

namespace App;

class Test {
    static public function hello() {
        echo __CLASS__.": load.\n";
    }

    static public function test1() {
        return array('file' => __FILE__, 'method' => __METHOD__);
    }

    static public function dao() {
        $user = new \App\DAO\User(4);
        $res = $user->get();
        return $res;
    }
}