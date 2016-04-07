<?php

namespace Zoco;

/**
 * Model类，ORM基础类，提供对某个数据库表的接口
 * Class Model
 *
 * @package Zoco
 */
class Model {
    /**
     * 数据库字段的具体值
     *
     * @var array
     */
    public $_data = array();

    /**
     * @var Database
     */
    public $db;

    /**
     * @var \Zoco
     */
    public $zoco;

    /**
     * 主键
     *
     * @var string
     */
    public $primary = 'id';

    /**
     * 外键
     *
     * @var string
     */
    public $foreignKey = 'foreignid';

    /**
     * @var
     */
    public $struct;

    /**
     * @var
     */
    public $form;

    /**
     * @var bool
     */
    public $formSecret = true;

    /**
     * @var
     */
    public $table;
    /**
     * 表切片参数
     *
     * @var int
     */
    public $tableSize = 1000000;
    /**
     * @var
     */
    public $fields;
    /**
     * @var string
     */
    public $select = '*';
    /**
     * @var string
     */
    public $createSql = '';
    /**
     * @var bool
     */
    public $ifCache = false;
    /**
     * 分表
     *
     * @var string
     */
    protected $tableBeforeShared;

    /**
     * @param \Zoco  $zoco
     * @param string $dbKey
     */
    public function __construct(\Zoco $zoco, $dbKey = 'master') {
        $this->db   = $zoco->db($dbKey);
        $this->dbs  = new SelectDB($zoco->db);
        $this->zoco = $zoco;
    }

    /**
     * 按ID切分表
     *
     * @param $id
     */
    public function shardTable($id) {
        if (empty($this->tableBeforeShared)) {
            $this->tableBeforeShared = $this->table;
        }
        $tableId     = intval($id / $this->tableSize);
        $this->table = $this->tableBeforeShared . '_' . $tableId;
    }

    /**
     * 获取主键$primaryKey为$objectId的一条记录对象
     * 如果参数为空的话，则返回一条空白的record，可以赋值，产生一条新的纪录
     *
     * @param int    $objectId
     * @param string $where
     * @return Record
     */
    public function get($objectId = 0, $where = '') {
        return new Record($objectId, $this->db, $this->table, $this->primary, $where, $this->select);
    }

    /**
     * 插入一条新的数据记录
     *
     * @param $data
     * @return bool
     */
    public final function put($data) {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        if ($this->db->insert($data, $this->table)) {
            $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    /**
     * 更新ID为$id的记录,值为$data关联数组
     *
     * @param        $id
     * @param        $data
     * @param string $where
     * @return bool
     */
    public final function set($id, $data, $where = '') {
        if (empty($where)) {
            $where = $this->primary;
        }

        return $this->db->update($id, $data, $this->table, $where);
    }

    /**
     * 更新一组数据
     *
     * @param $data
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public final function sets($data, $params) {
        if (empty($params)) {
            throw new \Exception("Model sets param is empty!");
        }
        $selectDb = new SelectDB($this->db);
        $selectDb->from($this->table);
        $selectDb->put($params);

        return $selectDb->update($data);
    }

    /**
     * 删除一条数据主键为$id的记录
     *
     * @param      $id
     * @param null $where
     * @return bool|mixed|\PDOStatement
     */
    public final function del($id, $where = null) {
        if ($where == null) {
            $where = $this->primary;
        }

        return $this->db->delete($id, $this->table, $where);
    }

    /**
     * 删除一条数据包含多个参数
     *
     * @param $params
     * @return bool
     * @throws \Exception
     */
    public final function dels($params) {
        if (empty($params)) {
            throw new \Exception("Model dels param is empty!");
        }
        $selectDb = new SelectDB($this->db);
        $selectDb->from($this->table);
        $selectDb->put($params);
        $selectDb->delete();

        return true;
    }

    /**
     * 获取到所有表记录的接口，通过这个接口可以访问到数据库的记录
     * RecordSet Object (这是一个接口，不包含实际的数据)
     *
     * @return RecordSet
     */
    public final function all() {
        return new RecordSet($this->db, $this->table, $this->primary, $this->select);
    }

    /**
     * 建立表，必须在Model类中，指定create_sql
     *
     * @return bool|mixed|\PDOStatement
     */
    public function createTable() {
        if ($this->createSql) {
            return $this->db->query($this->createSql);
        } else {
            return false;
        }
    }

    /**
     * 获取一个键值对应的结构，键为表记录主键的值，值为记录数据或者其中一个字段的值
     *
     * @param      $gets
     * @param null $field
     * @return array
     * @throws \Exception
     */
    public function getMap($gets, $field = null) {
        $list = $this->gets($gets);
        $new  = array();
        foreach ($list as $li) {
            if (empty($field)) {
                $new[$li[$this->primary]] = $li;
            } else {
                $new[$li[$this->primary]] = $li[$field];
            }
        }

        return $new;
    }

    /**
     * 获取表的一段数据，查询的参数由$params指定
     *
     * @param             $params
     * @param \Zoco\Pager $pager
     * @return mixed
     * @throws \Exception
     */
    public final function gets($params, &$pager = null) {
        if (empty($params)) {
            throw new \Exception("no params.");
        }

        $selectDb = new SelectDB($this->db);
        $selectDb->from($this->table);
        $selectDb->primary = $this->primary;
        $selectDb->select($this->select);

        if (!isset($params['order'])) {
            $params['order'] = "`{$this->table}`.{$this->primary} desc";
        }
        $selectDb->put($params);

        if (isset($params['page'])) {
            $selectDb->paging();
            $pager = $selectDb->pager;
        }

        return $selectDb->getAll();
    }

    /**
     * 获取一个2层的树状结构
     *
     * @param        $gets
     * @param string $categroy
     * @param string $order
     * @return array
     * @throws \Exception
     */
    public function getTree($gets, $categroy = 'fid', $order = 'id desc') {
        $gets['order'] = $categroy . ',' . $order;
        $list          = $this->gets($gets);
        $new           = array();
        foreach ($list as $li) {
            if ($li[$categroy] == 0) {
                $new[$li[$this->primary]] = $li;
            } else {
                $new[$li[$categroy]]['child'][$li[$this->primary]] = $li;
            }
        }

        return $new;
    }

    /**
     * 检测是否存在数据，实际可以用count代替，0为false，>0为true
     *
     * @param $gets
     * @return bool
     */
    public function exists($gets) {
        $c = $this->count($gets);
        if ($c > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 返回符合条件的纪录数
     *
     * @param $params
     * @return int
     */
    public final function count($params) {
        $selectDb = new SelectDB($this->db);
        $selectDb->from($this->table);
        $selectDb->put($params);

        return $selectDb->count();
    }

    /**
     * 获取表的字段描述
     *
     * @return array
     */
    public function desc() {
        return $this->db->query('describe ' . $this->table)->fetchAll();
    }
}

/**
 * Record类，表中的一条记录，通过对象的操作，映射到数据库表
 * 可以使用属性访问，也可以通过关联数组方式访问
 * Class Record
 *
 * @package Zoco
 */
class Record implements \ArrayAccess {
    const STATE_EMPTY      = 0;
    const STATE_INSERT     = 1;
    const STATE_UPDATE     = 2;
    const CACHE_KEY_PREFIX = 'zoco_record_';
    /**
     * @var Database
     */
    public $db;

    /**
     * @var string
     */
    public $primary = 'id';

    /**
     * @var string
     */
    public $table = '';

    /**
     * @var int
     */
    public $currentId = 0;

    /**
     * @var
     */
    public $currentKey;
    /**
     * @var array|string
     */
    protected $data = array();
    /**
     * @var array
     */
    protected $update = array();
    /**
     * @var int
     */
    protected $change = 0;
    /**
     * @var bool
     */
    protected $save = false;

    /**
     * @param        $id
     * @param        $db
     * @param        $table
     * @param        $primary
     * @param string $where
     * @param string $select
     */
    public function __construct($id, $db, $table, $primary, $where = '', $select = '*') {
        $this->db        = $db;
        $this->currentId = $id;
        $this->table     = $table;
        $this->primary   = $primary;

        if (empty($where)) {
            $where = $primary;
        }

        if (!empty($this->currentId)) {
            $res = $this->db->query("select {$select} from {$this->table} where {$where} ='{$id}' limit 1")->fetch();
            if (!empty($res)) {
                $this->data      = $res;
                $this->currentId = $this->data[$this->primary];
                $this->change    = self::STATE_INSERT;
            }
        }
    }

    /**
     * 是否存在
     *
     * @return bool
     */
    public function exist() {
        return !empty($this->data);
    }

    /**
     * 将关联数组压入object中，赋值给各个字段
     *
     * @param $data
     */
    public function put($data) {
        if ($this->change == self::STATE_INSERT) {
            $this->change = self::STATE_UPDATE;
            $this->update = $data;
        } else {
            if ($this->change == self::STATE_EMPTY) {
                $this->change = self::STATE_INSERT;
                $this->data   = $data;
            }
        }
    }

    /**
     * 获取数据数组
     *
     * @return array|string
     */
    public function get() {
        return $this->data;
    }

    /**
     * 获取属性
     *
     * @param $property
     * @return mixed
     * @throws \Exception
     */
    public function __get($property) {
        if (isset($this->data[$property])) {
            return $this->data[$property];
        } else {
            throw new \Exception("Record object no property: $property");
        }
    }

    /**
     * 设置属性
     *
     * @param $property
     * @param $value
     */
    public function __set($property, $value) {
        if ($this->change == self::STATE_INSERT || $this->change == self::STATE_UPDATE) {
            $this->change            = self::STATE_UPDATE;
            $this->update[$property] = $value;
            $this->data[$property]   = $value;
        } else {
            $this->data[$property] = $value;
        }
        $this->save = true;
    }

    /**
     * @return mixed
     */
    public function update() {
        $update = $this->data;
        unset($update[$this->primary]);

        return $this->db->update($this->currentId, $this->update, $this->table, $this->primary);
    }

    public function __destruct() {
        if ($this->save) {
            $this->save();
        }
    }

    /**
     * 保存对象到数据库
     * 如果是空白的记录，保存则会Insert到数据库
     * 如果是已存在的记录，保持则会update，修改过的值，如果没有任何值被修改，则不执行SQL
     *
     * @return bool
     */
    public function save() {
        if ($this->change == 0 || $this->change == 1) {
            $ret = $this->db->insert($this->data, $this->table);
            if ($ret === false) {
                return false;
            }

            /**
             * 改变状态
             */
            $this->change    = 1;
            $this->currentId = $this->db->lastInsertId();
        } else {
            if ($this->change == 2) {
                $update = $this->update;
                unset($update[$this->primary]);

                return $this->db->update($this->currentId, $update, $this->table, $this->primary);
            }
        }

        return true;
    }

    /**
     * 删除数据库中的此条记录
     */
    public function delete() {
        $this->db->delete($this->currentId, $this->table, $this->primary);
    }

    /**
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($key) {
        return isset($this->data[$key]);
    }

    /**
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key) {
        return $this->data[$key];
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     * @param mixed $key
     */
    public function offsetUnset($key) {
        unset($this->data[$key]);
    }
}

/**
 * 数据结果集，由Record组成
 * 通过foreach遍历，可以产生单条的Record对象，对每条数据进行操作
 * Class RecordSet
 *
 * @package Zoco
 * @method fetch()
 */
class RecordSet implements \Iterator {
    /**
     * @var string
     */
    public $primary = '';
    /**
     * @var int
     */
    public $currentId = 0;
    /**
     * @var array
     */
    protected $list = array();
    /**
     * @var string
     */
    protected $table = '';
    /**
     * @var
     */
    protected $db;
    /**
     * @var SelectDB
     */
    protected $selectDb;

    /**
     * @param $db
     * @param $table
     * @param $primary
     * @param $select
     */
    public function __construct($db, $table, $primary, $select) {
        $this->table    = $table;
        $this->primary  = $primary;
        $this->db       = $db;
        $this->selectDb = new SelectDB($db);
        $this->selectDb->from($table);
        $this->selectDb->primary = $primary;
        $this->selectDb->select($select);
        $this->selectDb->order($this->primary . ' desc');
    }

    /**
     * 获取得到的数据
     *
     * @return array
     */
    public function get() {
        return $this->list;
    }

    /**
     * 制定查询的参数，在调用数据之前进行
     *
     * @param $params
     */
    public function params($params) {
        $this->selectDb->put($params);
    }

    /**
     * 过滤器语法,SelectDB的where语句
     *
     * @param $where
     */
    public function filter($where) {
        $this->selectDb->where($where);
    }

    /**
     * 增加过滤条件,$field = $value,SelectDB的equal语句
     *
     * @param $field
     * @param $value
     */
    public function eq($field, $value) {
        $this->selectDb->equal($field, $value);
    }

    /**
     * 过滤器语法，SelectDB的orWhere语法
     *
     * @param $where
     */
    public function orFilter($where) {
        $this->selectDb->orWhere($where);
    }

    /**
     * 获取一条数据
     * 参数可以制定返回的字段
     *
     * @param string $field
     * @return mixed
     */
    public function fetchOne($field = '') {
        return $this->selectDb->getOne($field);
    }

    /**
     * 获取全部数据
     *
     * @return mixed
     */
    public function fetchAll() {
        return $this->selectDb->getAll();
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value) {
        $this->selectDb->$key = $value;
    }

    /**
     * @param $method
     * @param $argv
     * @return mixed
     */
    public function __call($method, $argv) {
        return call_user_func_array(array($this->selectDb, $method), $argv);
    }

    /**
     * 重新绑定
     */
    public function rewind() {
        if (empty($this->list)) {
            $this->list = $this->selectDb->getAll();
        }
        $this->currentId = 0;
    }

    /**
     * @return int
     */
    public function key() {
        return $this->currentId;
    }

    /**
     * @return Record
     */
    public function current() {
        $record = new Record(0, $this->db, $this->table, $this->primary);
        $record->put($this->list[$this->currentId]);
        $record->currentId = $this->list[$this->currentId][$this->primary];

        return $record;
    }

    public function next() {
        $this->currentId++;
    }

    /**
     * @return bool
     */
    public function valid() {
        if (isset($this->list[$this->currentId])) {
            return true;
        } else {
            return false;
        }
    }
}