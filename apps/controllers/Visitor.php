<?php

namespace App\Controller;

use App;
use Zoco;

class Visitor extends Zoco\Controller {

    /**
     * 登陆页面
     */
    public function login() {
        $this->notLogin();
    }

    /**
     *
     */
    private function notLogin($alert = false) {
        if ($alert) {
            Zoco\JS::alert('登录失败!');
        }
        $title     = 'zoco';
        $copyright = 'zoco';
        $this->assign('title', $title);
        $this->assign('copyright', $copyright);
        $this->display('head_not_login.php');
        $this->display('visitor/login.php');
        $this->display('foot_not_login.php');
    }

    /**
     * 登陆动作
     */
    public function doLogin() {
        Zoco\Auth::$passwordHash = Zoco\Auth::HASH_SHA1;
        $this->session->start();
        if ($this->user->isLogin()) {
            header('Location: index.php');

            return;
        }
        if (!empty($_POST['password'])) {
            $result = $this->user->login(trim($_POST['username']), $_POST['password']);
            if ($result) {
                header('Location: index.php');

                return;
            } else {
                $this->notLogin(true);
            }
        } else {
            $this->notLogin(true);
        }
    }

    /**
     * 登出
     */
    public function logout() {
        $this->session->start();
        $this->user->logout();

        $this->notLogin();
    }

    /**
     * 测试
     */
    public function register() {
        $this->user->register('wangzhihao', '123');
    }
}