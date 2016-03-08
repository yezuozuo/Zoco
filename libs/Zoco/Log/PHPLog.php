<?php

namespace Zoco\Log;

/**
 * 使用PHP的error_log记录日志
 * Class PHPLog
 *
 * @package Zoco\Log
 */
class PHPLog extends \Zoco\Log implements \Zoco\IFace\Log {
    /**
     * @var
     */
    protected $logout;

    /**
     * @var
     */
    protected $type;

    /**
     * @var array
     */
    protected $putType = array(
        'file'  => 3,
        'sys'   => 0,
        'email' => 1,
    );

    /**
     * @param $params
     */
    public function __init($params) {
        if (isset($params['logout'])) {
            $this->logout = $params['logout'];
        }
        if (isset($params['type'])) {
            $this->type = $this->putType[$params['type']];
        }
    }

    /**
     * @param          $msg
     * @param int|null $level
     */
    public function put($msg, $level = self::INFO) {
        $msg = $this->format($msg, $level);
        if ($msg) {
            error_log($msg, $this->type, $this->logout);
        }
    }
}