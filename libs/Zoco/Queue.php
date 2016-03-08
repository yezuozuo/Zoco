<?php

namespace Zoco;

/**
 * Class Queue
 *
 * @package Zoco
 */
class Queue {
    /**
     * @var \Zoco\Queue\File
     */
    public $queue;

    /**
     * @param $config
     * @param $serverType
     */
    public function __construct($config, $serverType) {
        $this->queue = new $serverType($config);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function push($data) {
        return $this->queue->push($data);
    }

    /**
     * @return mixed
     */
    public function pop() {
        return $this->queue->pop();
    }

    /**
     * @param       $method
     * @param array $param
     * @return mixed
     */
    public function __call($method, $param = array()) {
        return call_user_func_array(array($this->queue, $method), $param);
    }
}