<?php

namespace Zoco\Queue;

/**
 * Class File
 *
 * @package Zoco\Queue
 */
class File implements \Zoco\IFace\Queue {
    /**
     * @var string
     */
    public $file;
    /**
     * @var
     */
    public $name;
    /**
     * true 即入队列即存
     *
     * @var bool
     */
    public $putSave = true;
    /**
     * @var
     */
    private $data;

    /**
     * @param $config
     */
    public function __construct($config) {
        if (!empty($config['name'])) {
            $this->name = $config['name'];
            $this->file = WEBPATH . '/data/cache/fileCache/queue/' . $config['name'] . '.fc';
            $this->load();
        }
    }

    /**
     * 加载队列
     */
    public function load() {
        $content    = trim(file_get_contents($this->file));
        $this->data = explode("\n", $content);
    }

    /**
     * @param $data
     */
    public function push($data) {
        if (is_array($data) || is_object($data)) {
            $data = serialize($data);
        }
        $this->data[] = $data;
        if ($this->putSave) {
            $this->save();
        }
    }

    /**
     * 保存队列
     */
    public function save() {
        file_put_contents($this->file, implode("\n", $this->data));
    }

    /**
     * @return mixed
     */
    public function pop() {
        return array_shift($this->data);
    }

    public function __destruct() {
        $this->save();
    }
}