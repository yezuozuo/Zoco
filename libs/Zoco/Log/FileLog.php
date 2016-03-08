<?php

namespace Zoco\Log;

/**
 * Class FileLog
 *
 * @package Zoco\Log
 */
class FileLog extends \Zoco\Log implements \Zoco\IFace\Log {
    /**
     * 日志文件名
     *
     * @var
     */
    protected $logFile;

    /**
     * 日志目录
     *
     * @var string
     */
    protected $logDir;

    /**
     * 打开日志的文件描述符
     *
     * @var resource
     */
    protected $fp;

    /**
     * 是否按日期储存日志
     *
     * @var bool
     */
    protected $archive;

    /**
     * 待写入文件的日志队列（缓冲区）
     *
     * @var array
     */
    protected $queue = array();

    /**
     * 是否记录更详细的信息（目前记录了文件名和行号）
     *
     * @var bool
     */
    protected $verbose = true;

    /**
     * 是否启用缓存
     *
     * @var
     */
    protected $cacheEnable;

    /**
     * @var bool|string
     */
    protected $date;

    /**
     * @param $conf
     * @throws \Exception
     */
    public function __construct($conf) {
        if (is_string($conf)) {
            $file = $conf;
            $conf = array(
                'file' => $file,
            );
        }

        $this->archive = isset($conf['date']) && $conf['date'] == true;

        /**
         * 是否按日期储存日志
         */
        if ($this->archive) {
            if (isset($conf['dir'])) {
                $this->date    = date('Ymd');
                $this->logDir  = rtrim($conf['dir'], '/');
                $this->logFile = $this->logDir . '/' . $this->date . '.log';
            } else {
                throw new \Exception(__CLASS__ . ": require \$conf['dir']");
            }
        } else {
            if (isset($conf['file'])) {
                $this->logFile = $conf['file'];
            } else {
                throw new \Exception(__CLASS__ . ": require \$conf['file']");

            }
        }

        /**
         * 自动创建目录
         */
        $dir = dirname($this->logFile);
        if (file_exists($dir)) {
            if (!is_writable($dir) && !chmod($dir, 0755)) {
                throw new \Exception(__CLASS__ . ": {$dir} unWritable.");
            }
        } else {
            if (mkdir($dir, 0755, true) === false) {
                throw new \Exception(__CLASS__ . ": mkdir dir {$dir} fail.");
            }
        }

        $this->fp = fopen($this->logFile, 'a+');
        if (!$this->fp) {
            throw new \Exception(__CLASS__ . ": can not open log_file[{$this->logFile}].");
        }
    }

    /**
     * 写入日志队列
     *
     * @param          $msg
     * @param int|null $level
     */
    public function put($msg, $level = self::INFO) {
        $msg = $this->format($msg, $level, $date);

        if (!isset($this->queue[$date])) {
            $this->queue[$date] = array();
        }
        $this->queue[$date][] = $msg;

        /**
         * 如果没有开启缓存，直接将缓冲区的内容写入文件
         * 如果缓冲区内容日志条数达到一定程度，写入文件
         * 这里设定的缓冲区为11
         */
        if (count($this->queue, COUNT_RECURSIVE) >= 11 || $this->cacheEnable == false) {
            $this->flush();
        }
    }

    /**
     * 格式化日志
     *
     * @param      $msg
     * @param      $level
     * @param null $date
     * @return bool|string
     */
    public function format($msg, $level, &$date = null) {
        $level = self::convert($level);
        if ($level < $this->levelLine) {
            return false;
        }
        $levelStr = self::$levelStr[$level];
        $now      = new \DateTime('now');
        $date     = $now->format('Ymd');
        $log      = date(self::$dateFormat) . "\t{$levelStr}\t{$msg}";

        if ($this->verbose) {
            $debugInfo = debug_backtrace();
            $file      = isset($debugInfo[1]['file']) ? $debugInfo[1]['file'] : null;
            $line      = isset($debugInfo[1]['line']) ? $debugInfo[1]['line'] : null;

            if ($file && $line) {
                $log .= "\t{$file}\t{$line}";
            }
        }
        $log .= "\n";

        return $log;
    }

    /**
     * 将缓冲区刷出
     */
    public function flush() {
        if (empty($this->queue)) {
            return;
        }

        foreach ($this->queue as $date => $logs) {
            $date   = strval($date);
            $logStr = implode('', $logs);

            /**
             * 按日期存储日志的情况下
             * 如果日期变化（第二天）
             * 重新设置一下log文件和文件指针
             */
            if ($this->archive && $this->date != $date) {
                $this->date    = $date;
                $this->logFile = $this->logDir . '/' . $this->date . '.log';
                $this->fp      = fopen($this->logFile, 'a+');
            }

            fputs($this->fp, $logStr);

            /**
             * 如果日志文件大小超过200M
             * 重命名文件
             */
            if (filesize($this->logFile) > 209715200) {
                rename($this->logFile, $this->logFile . '.' . date('His'));
            }
        }
    }

    /**
     * web下开启这个，否则每次会写入两条日志
     */
    public function __destruct() {
        //$this->flush();
    }
}