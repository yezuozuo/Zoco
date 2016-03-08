<?php

namespace Zoco;

/**
 * Class log
 *
 * @package Zoco
 * @method put($msg, $level = null)
 * @method __init($arr)
 * @method warn($info)
 */
abstract class log {
    const TRACE  = 0;
    const INFO   = 1;
    const NOTICE = 2;
    const WARN   = 3;
    const ERROR  = 4;
    /**
     * @var string
     */
    static $dateFormat = '[Y-m-d H:i:s]';
    /**
     * @var array
     */
    protected static $levelCode = array(
        'TRACE'  => 0,
        'INFO'   => 1,
        'NOTICE' => 2,
        'WARN'   => 3,
        'ERROR'  => 4,
    );

    /**
     * @var array
     */
    protected static $levelStr = array(
        'TRACE',
        'INFO',
        'NOTICE',
        'WARN',
        'ERROR',
    );
    /**
     * @var
     */
    protected $levelLine;

    /**
     * @param $config
     */
    public function __construct($config) {
        if (isset($config['level'])) {
            $this->setLevel(intval($config['level']));
        }
        $this->config = $config;
    }

    /**
     * @param int $level
     */
    public function setLevel($level = self::TRACE) {
        $this->levelLine = $level;
    }

    /**
     * @param $func
     * @param $param
     */
    public function __call($func, $param) {
        $this->put($param[0], $func);
    }

    /**
     * @param $msg
     * @param $level
     * @return bool|string
     */
    public function format($msg, $level) {
        $level = self::convert($level);
        if ($level < $this->levelLine) {
            return false;
        }
        $levelStr = self::$levelStr[$level];

        return date(self::$dateFormat) . "\t{$levelStr}\t{$msg}\n";
    }

    /**
     * @param $level
     * @return mixed
     */
    static public function convert($level) {
        if (!is_numeric($level)) {
            $level = self::$levelCode[strtoupper($level)];
        }

        return $level;
    }
}