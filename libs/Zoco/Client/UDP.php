<?php

namespace Zoco\Client;

/**
 * UDP客户端
 * Class UDP
 *
 * @package Zoco\Client
 */
class UDP extends Socket {
    /**
     * @var
     */
    public $remoteHost;

    /**
     * @var
     */
    public $remotePort;

    /**
     * 连接到服务器
     * 接受一个浮点型数字作为超时，整数部分作为sec，小数部分*100万作为usec
     *
     * @param           $host
     * @param           $port
     * @param float     $timeout
     * @param bool|true $udpConnect
     * @return bool
     */
    public function connect($host, $port, $timeout = 0.1, $udpConnect = true) {
        if (empty($host) || empty($port) || $timeout <= 0) {
            $this->errCode = -10001;
            $this->errMsg  = 'param error';

            return false;
        }
        $this->host = $host;
        $this->port = $port;
        $this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $this->setTimeout($timeout, $timeout);

        /**
         * 是否用UDP connect
         */
        if ($udpConnect !== true) {
            return true;
        }

        if (socket_connect($this->sock, $host, $port)) {
            /**
             * 清理connect前的buffer数据遗留
             */
            while (@socket_recv($this->sock, $buf, 65535, MSG_DONTWAIT)) {
                ;
            }

            return true;
        } else {
            $this->setError();

            return false;
        }
    }

    /**
     * 发送数据
     *
     * @param $data
     * @return bool|int
     */
    public function send($data) {
        $len = strlen($data);
        $n   = socket_sendto($this->sock, $data, $len, 0, $this->host, $this->port);

        if ($n === false || $n < $len) {
            $this->setError();

            return false;
        } else {
            return $n;
        }
    }

    /**
     * 接收数据，UD包不能分2次读，recv后会清除数据包，所以必须要一次性读完
     *
     * @param int $length
     * @param int $waitAll
     * @return bool
     */
    public function recv($length = 65535, $waitAll = 1) {
        if ($waitAll) {
            $waitAll = MSG_WAITALL;
        }
        $ret = socket_recvfrom($this->sock, $data, $length, $waitAll, $this->remoteHost, $this->remotePort);
        if ($ret === false) {
            $this->setError();
            /**
             * 重试一次，这里为防止意外，不使用递归循环
             */
            if ($this->errCode == 4) {
                socket_recvfrom($this->sock, $data, $length, $waitAll, $this->remoteHost, $this->remotePort);
            } else {
                return false;
            }
        }

        return $data;
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