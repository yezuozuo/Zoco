<?php

namespace Zoco\Client;

/**
 * TCP客户端
 * Class TCP
 *
 * @package Zoco\Client
 */
class TCP extends Socket {
    /**
     * 是否重新连接
     *
     * @var bool
     */
    public $tryReconnect = true;

    /**
     * 是否已连接
     *
     * @var bool
     */
    public $connected = false;

    /*
     * 发送数据
     * @param $data
     * @return bool|int
     */
    public function send($data) {
        $length    = strlen($data);
        $written   = 0;
        $timeBegin = microtime(true);

        /**
         * 总超时，for循环计时
         */
        while ($written < $length) {
            $n = socket_send($this->sock, substr($data, $written), $length - $written, null);
            /**
             * 超时总时间
             */
            if (microtime(true) > $this->timeoutSend + $timeBegin) {
                return false;
            }

            /**
             * 发送错误
             */
            if ($n === false) {
                $errno = socket_last_error($this->sock);

                /**
                 * 判断错误信息，EAGAIN EINTR，重写一次
                 */
                if ($errno == 11 || $errno == 4) {
                    continue;
                } else {
                    return false;
                }
            }
            $written += $n;
        }

        return $written;
    }

    /**
     * 接收数据
     *
     * @param int $length
     * @param int $waitAll
     * @return bool
     */
    public function recv($length = 65535, $waitAll = 0) {
        if ($waitAll) {
            $waitAll = MSG_WAITALL;
        }

        $ret = socket_recv($this->sock, $data, $length, $waitAll);
        if ($ret === false) {
            $this->setError();

            /**
             * 重试一次，为防止意外，不使用递归循环
             */
            if ($this->errCode == 4) {
                socket_recv($this->sock, $data, $length, $waitAll);
            } else {
                return false;
            }
        }

        return $data;
    }

    /**
     * 连接到服务器
     * 接受一个浮点型数字作为超时，整数部分作为sec，小数部分*100万作为usec
     *
     * @param            $host
     * @param            $port
     * @param float      $timeout
     * @param bool|false $nonBlock
     * @return bool
     */
    public function connect($host, $port, $timeout = 0.1, $nonBlock = false) {
        if (empty($host) || empty($port) || $timeout <= 0) {
            $this->errCode = -10001;
            $this->errMsg  = 'param error';

            return false;
        }
        $this->host = $host;
        $this->port = $port;
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->sock === false) {
            $this->setError();

            return false;
        }

        /**
         * 设置connection超时
         */
        $this->setTimeout($timeout, $timeout);
        $this->setopt(SO_REUSEADDR, 1);

        /**
         * 非阻塞模式下connect立即返回
         */
        if ($nonBlock) {
            socket_set_nonblock($this->sock);
            @socket_connect($this->sock, $this->host, $this->port);

            return true;
        } else {
            if (@socket_connect($this->sock, $this->host, $this->port)) {
                $this->connected = true;

                return true;
            } else {
                if ($this->tryReconnect) {
                    if (@socket_connect($this->sock, $this->host, $this->port)) {
                        $this->connected = true;

                        return true;
                    }
                }
            }
        }
        $this->setError();
        trigger_error("connect server[{$this->host}:{$this->port}] fail. errno={$this->errCode}|{$this->errMsg}");

        return false;
    }

    /**
     * 关闭socket连接
     */
    public function close() {
        if ($this->sock) {
            socket_close($this->sock);
        }
        $this->sock = null;
    }
}