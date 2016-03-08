<?php

namespace Zoco\Client;

/**
 * 网络客户端封装基类
 * Class Socket
 *
 * @package Zoco\Client
 * @method close()
 */
abstract class Socket {
    /**
     * 接收数据超时，server端在规定的时间内没回包
     */
    const ERR_RECV_TIMEOUT = 11;
    /**
     * 正在处理中
     */
    const ERR_IN_PROGRESS = 115;
    /**
     * @var int
     */
    public $sendBufferSize = 65535;
    /**
     * @var int
     */
    public $recvBufferSize = 65535;
    /**
     * @var int
     */
    public $errCode = 0;
    /**
     * @var string
     */
    public $errMsg = '';
    /**
     * @var
     */
    public $host;
    /**
     * @var
     */
    public $port;
    /**
     * @var
     */
    protected $sock;
    /**
     * @var
     */
    protected $timeoutSend;
    /**
     * @var
     */
    protected $timeoutRecv;

    /**
     * 设置超时
     *
     * @param $timeoutRecv
     * @param $timeoutSend
     */
    public function setTimeout($timeoutRecv, $timeoutSend) {
        $timeoutRecvSec = (int)$timeoutRecv;
        $timeoutSendSec = (int)$timeoutSend;

        $this->timeoutRecv = $timeoutRecv;
        $this->timeoutSend = $timeoutSend;

        $timeoutRecvArr = array(
            'sec'  => $timeoutRecvSec,
            'usec' => (int)(($timeoutRecv - $timeoutRecvSec) * 1000 * 1000),
        );

        $timeoutSendArr = array(
            'sec'  => $timeoutRecvSec,
            'usec' => (int)(($timeoutSend - $timeoutSendSec) * 1000 * 1000),
        );

        $this->setopt(SO_RCVTIMEO, $timeoutRecvArr);
        $this->setopt(SO_SNDTIMEO, $timeoutSendArr);
    }

    /**
     * 设置Socket参数
     *
     * @param $opt
     * @param $set
     */
    public function setopt($opt, $set) {
        socket_set_option($this->sock, SOL_SOCKET, $opt, $set);
    }

    /**
     * 获取Socket参数
     *
     * @param $opt
     * @return mixed
     */
    public function getopt($opt) {
        return socket_get_option($this->sock, SOL_SOCKET, $opt);
    }

    /**
     * 返回socket
     *
     * @return mixed
     */
    public function getSocket() {
        return $this->sock;
    }

    /**
     * 设置buffer区
     *
     * @param $sendBufferSize
     * @param $recvBufferSize
     */
    public function setBufferSize($sendBufferSize, $recvBufferSize) {
        $this->setopt(SO_SNDBUF, $sendBufferSize);
        $this->setopt(SO_RCVBUF, $recvBufferSize);
    }

    public function __destruct() {
        $this->close();
    }

    /**
     * 错误信息赋值
     */
    protected function setError() {
        $this->errCode = socket_last_error($this->sock);
        $this->errMsg  = socket_strerror($this->errCode);
        socket_clear_error($this->sock);
    }
}