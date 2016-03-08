<?php

namespace App\Controller;

use App;
use Zoco;

class Markdown extends Zoco\Controller {
    public function index() {
        $this->session->start();
        $_SESSION['zoco_markdown'] = $this->session->getId();
        $this->display('markdown/index.html');
    }

    public function save() {
        $this->session->start();
        $path = WEBPATH . '/data/markdown/' . date('Ymd') . $_SESSION['zoco_markdown'] . '.txt';
        file_put_contents($path, $_REQUEST, FILE_APPEND);
    }
}