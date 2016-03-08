<?php

namespace Zoco\Cache;

/**
 * 数据库缓存
 * Class DBCache
 *
 * @package Zoco\Cache
 */
class DBCache implements \Zoco\IFace\Cache {
    /**
     * @var \Zoco
     */
    public $zoco;

    /**
     * 碎片的Id
     *
     * @var int
     */
    public $shardId = 0;

    public function __construct() {
        global $php;
        $this->model = new \Zoco\Model($php);
    }

    /**
     * @param $table
     */
    public function setTable($table) {
        $this->model->table     = $table;
        $this->model->createSql = "CREATE TABLE `{$table}` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `ckey` VARCHAR( 128 ) NOT NULL ,
        `cvalue` TEXT NOT NULL ,
        `sid` INT NOT NULL ,
        `expire` INT NOT NULL ,
         INDEX ( `ckey` )
         ) ENGINE = MYISAM ;";
    }

    public function createTable() {
        $this->model->createTable();
    }

    /**
     * @param $id
     */
    public function shard($id) {
        $this->shardId = $id;
    }

    /**
     * @param $keyLike
     * @return array
     * @throws \Exception
     */
    public function gets($keyLike) {
        $gets['sid']    = $this->shardId;
        $gets['order']  = '';
        $gets['select'] = 'id,ckey,cvalue,expire';
        $gets['like']   = array('ckey', $keyLike . '%');
        $list           = $this->model->gets($gets);
        $return         = array();
        foreach ($list as $li) {
            $return[$li['ckey']] = $this->filterExpire($li);
        }

        return $return;
    }

    /**
     * 把结果中超时的都删除
     *
     * @param $rs
     * @return bool
     */
    private function filterExpire($rs) {
        if ($rs['expire'] != 0 && $rs['expire'] < time()) {
            $this->model->del($rs['id']);

            return false;
        } else {
            return $rs['cvalue'];
        }
    }

    /**
     * @param $key
     * @return bool
     * @throws \Exception
     */
    public function get($key) {
        $gets['sid']    = $this->shardId;
        $gets['limit']  = 1;
        $gets['order']  = '';
        $gets['select'] = 'id,cvalue,expire';
        $gets['ckey']   = $key;
        $rs             = $this->model->gets($gets);
        if (empty($rs)) {
            return false;
        }

        return $this->filterExpire($rs[0]);
    }

    /**
     * @param     $key
     * @param     $value
     * @param int $expire
     */
    public function set($key, $value, $expire = 0) {
        $in['ckey'] = $key;
        if (is_array($value)) {
            $value = serialize($value);
        }
        $in['cvalue'] = $value;
        if ($expire == 0) {
            $in['expire'] = $expire;
        } else {
            $in['expire'] = time() + $expire;
        }
        $in['sid'] = $this->shardId;

        $this->model->put($in);
    }

    /**
     * @param $key
     * @throws \Exception
     */
    public function delete($key) {
        $gets['sid']   = $this->shardId;
        $gets['limit'] = 1;
        $gets['ckey']  = $key;
        $this->model->dels($gets);
    }
}