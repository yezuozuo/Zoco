<?php

namespace Zoco\Log;

/**
 * Class EchoLog
 *
 * @package Zoco\Log
 */
class EchoLog extends \Zoco\log implements \Zoco\IFace\Log {
    /**
     * @var bool
     */
    protected $display = true;

    /**
     * @param $conf
     */
    public function __construct($conf) {
        if (isset($conf['display']) && $conf['display'] == false) {
            $this->display = false;
        }
    }

    /**
     * @param          $msg
     * @param int|null $level
     */
    public function put($msg, $level = self::INFO) {
        if ($this->display) {
            $log = $this->format($msg, $level);
            if ($log) {
                echo $log;
            }
        }
    }
}