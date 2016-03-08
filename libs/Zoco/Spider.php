<?php

namespace Zoco;

require_once LIBPATH . '/module/spider/curl.lib.php';
require_once LIBPATH . '/module/spider/process.lib.php';

/**
 * Class Spider
 *
 * @package Zoco
 */
class Spider {
    /**
     * @var int
     */
    private $processNum;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $uri;

    public function __construct($uri = '', $process = 1, $path = null) {
        $this->setUri($uri);
        $this->setProcessNum($process);
        $this->setPath($path);
    }

    /**
     * @param $uri
     */
    public function setUri($uri) {
        if (is_string($uri)) {
            $this->uri = $uri;
        } else {
            $this->uri = 'https://www.baidu.com';
        }
    }

    /**
     * @param $process
     */
    public function setProcessNum($process) {
        if (is_int($process)) {
            $this->processNum = $process;
        } else {
            $this->processNum = 1;
        }
    }

    /**
     * @param $path
     */
    public function setPath($path) {
        if (is_dir($path)) {
            $this->path = $path;
        } else {
            $this->path = WEBPATH . '/data/spider';
        }
    }

    /**
     * 运行
     */
    public function run() {
        multi_process($this->processNum);

        while (true) {
            $tid = mp_counter('tid');
            $tid = $tid + 400;
            $url = $this->uri . "/post/{$tid}.html";
            do {
                $html = curl_get($url);
            } while ($html === false);

            preg_match("/<title>(.*)</", $html, $preg);
            if (!empty($preg[1])) {
                $title = $preg[1];
                file_put_contents($this->path . '/result/' . date('Ymd') . 'txt', $tid . '  ' . $title . "\n", FILE_APPEND);
            } else {
                $title = '';
            }
            mp_msg(array('TID' => $tid, $title));
            rand_exit(100);
        }
    }
}