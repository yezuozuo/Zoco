<?php

namespace Zoco;

/**
 * 查询数据库的封装类，基于底层数据库封装类，实现SQL生成器
 * 注：仅支持MySQL，不兼容其他数据库的SQL语法
 * Class SelectDB
 *
 * @package Zoco
 */
class SelectDB {
    /**
     * 发生错误时的回调函数
     *
     * @var string
     */
    static $errorCall = '';

    /**
     * @var string
     */
    static $allowRegx = '#^([a-z0-9\(\)\._=\-\+\*\`\s\'\",]+)$#i';

    /**
     * @var string
     */
    public $table = '';

    /**
     * @var string
     */
    public $primary = 'id';

    /**
     * @var string
     */
    public $select = '*';

    /**
     * @var string
     */
    public $sql = '';

    /**
     * @var string
     */
    public $limit = '';

    /**
     * @var string
     */
    public $where = '';

    /**
     * @var string
     */
    public $order = '';

    /**
     * @var string
     */
    public $group = '';

    /**
     * @var string
     */
    public $useIndex = '';

    /**
     * @var string
     */
    public $having = '';

    /**
     * @var string
     */
    public $join = '';

    /**
     * @var string
     */
    public $union = '';

    /**
     * @var string
     */
    public $forUpdate = '';
    /**
     * 分页相关
     *
     * @var int
     */
    public $pageSize = 10;
    /**
     * @var int
     */
    public $num = 0;
    /**
     * @var int
     */
    public $pages = 0;
    /**
     * @var int
     */
    public $page = 0;
    /**
     * @var Pager
     */
    public $pager = null;
    /**
     * 缓存相关
     *
     * @var bool
     */
    public $autoCache = false;
    /**
     * @var
     */
    public $cacheLifetime;
    /**
     * @var string
     */
    public $cachePrefix = 'zoco_selectDB_';
    /**
     * @var string
     */
    public $cacheKey = 'key';
    /**
     * @var
     */
    public $recordSet;
    /**
     * @var int
     */
    public $isExecute = 0;
    /**
     * @var array
     */
    public $resultFilter = array();
    /**
     * @var string
     */
    public $callBy = 'func';
    /**
     * @var Database
     */
    public $db;
    /**
     * @var \Zoco\RecordSet
     */
    protected $result;
    /**
     * @var bool
     */
    private $ifUnion = false;
    /**
     * @var string
     */
    private $unionSelect = '';
    /**
     * Count计算
     *
     * @var string
     */
    private $countFields = '*';

    /**
     * @param $db
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * 初始化
     */
    public function __init() {
        $this->table    = '';
        $this->primary  = 'id';
        $this->select   = '*';
        $this->sql      = '';
        $this->limit    = '';
        $this->where    = '';
        $this->order    = '';
        $this->group    = '';
        $this->useIndex = '';
        $this->join     = '';
        $this->union    = '';
    }

    /**
     * 字段等于某个值
     * 支持子查询
     * $_where可以是对象
     *
     * @param $field
     * @param $_where
     */
    public function equal($_where, $field = 'id') {
        /**
         * 子查询
         */
        if ($_where instanceof SelectDB) {
            $where = $field . '=(' . $_where->getsql() . ')';
        } else {
            $where = "`$field`='$_where'";
        }
        $this->where($where);
    }

    /**
     * 获取组合成的SQL语句字符串
     *
     * @param bool|true $ifReturn
     * @return bool|mixed|string
     */
    public function getSql($ifReturn = true) {
        $this->sql = "select {$this->select} from {$this->table}";
        $this->sql .= implode(' ', array(
            $this->join,
            $this->useIndex,
            $this->where,
            $this->union,
            $this->group,
            $this->having,
            $this->order,
            $this->limit,
            $this->forUpdate,
        ));
        if ($this->ifUnion) {
            $this->sql = str_replace('{#unionSelect#}', $this->unionSelect, $this->sql);
        }
        if ($ifReturn) {
            return $this->sql;
        } else {
            return false;
        }
    }

    /**
     * where参数，查询的条件
     *
     * @param $where
     */
    public function where($where, $force = false) {
        if ($this->where == '' || $force) {
            $this->where = 'where ' . $where;
        } else {
            $this->where = $this->where . ' and ' . $where;
        }
    }

    /**
     * 指定表名
     *
     * @param $table
     */
    public function from($table) {
        if (strpos($table, "`") === false) {
            $this->table = "`" . $table . "`";
        } else {
            $this->table = $table;
        }
    }

    /**
     * 指定查询的字段，select * from table
     * 可多次使用，连接多个字段
     *
     * @param            $select
     * @param bool|false $force
     */
    public function select($select, $force = false) {
        if ($this->select == '*' || $force) {
            $this->select = $select;
        } else {
            $this->select = $this->select . ',' . $select;
        }
    }

    /**
     * 指定查询所使用的索引字段
     *
     * @param $field
     */
    public function useIndex($field) {
        self::sqlSafe($field);
        $this->useIndex = "use index($field)";
    }

    /**
     * 使SQL元素安全
     *
     * @param $sqlSub
     */
    static public function sqlSafe($sqlSub) {
        if (!preg_match(self::$allowRegx, $sqlSub)) {
            echo $sqlSub;
            if (self::$errorCall === '') {
                die('sql block is not safe!');
            } else {
                call_user_func(self::$errorCall);
            }
        }
    }

    /**
     * 模糊查询
     *
     * @param $field
     * @param $like
     */
    public function like($field, $like) {
        self::sqlSafe($field);
        $this->where("`{$field}` like '{$like}'");
    }

    /**
     * 排序方式
     *
     * @param $order
     */
    public function order($order) {
        if (!empty($order)) {
            self::sqlSafe($order);
            $this->order = "order by $order";
        } else {
            $this->order = '';
        }
    }

    /**
     * 组合方式
     *
     * @param $group
     */
    public function group($group) {
        if (!empty($group)) {
            self::sqlSafe($group);
            $this->group = "group by $group";
        } else {
            $this->group = '';
        }
    }

    /**
     * group后条件
     *
     * @param $having
     */
    public function having($having) {
        if (!empty($having)) {
            self::sqlSafe($having);
            $this->having = "having $having";
        } else {
            $this->having = '';
        }
    }

    /**
     * in条件
     *
     * @param $field
     * @param $ins
     */
    public function in($field, $ins) {
        $ins = trim($ins, ';');
        $this->where("`$field` in ({$ins})");
    }

    /**
     * not in条件
     *
     * @param $field
     * @param $ins
     */
    public function notIn($field, $ins) {
        $ins = trim($ins, ';');
        $this->where("`$field` not in ({$ins})");
    }

    /**
     * inner join
     *
     * @param $tableName
     * @param $on
     */
    public function join($tableName, $on) {
        $this->join .= "inner join `{$tableName}` on ({$on})";
    }

    /**
     * left join
     *
     * @param $tableName
     * @param $on
     */
    public function leftJoin($tableName, $on) {
        $this->join .= "left join `{$tableName}` on ({$on})";
    }

    /**
     * right join
     *
     * @param $tableName
     * @param $on
     */
    public function rightJoin($tableName, $on) {
        $this->join .= "right join `{$tableName}` on ({$on})";
    }

    /**
     * 分页参数，指定每页数量
     *
     * @param $pageSize
     */
    public function pageSize($pageSize) {
        $this->pageSize = (int)$pageSize;
    }

    /**
     * 分页参数,指定当前页数
     *
     * @param $page
     */
    public function page($page) {
        $this->page = (int)$page;
    }

    /**
     * 主键查询
     *
     * @param $id
     */
    public function id($id) {
        $this->where("`{$this->primary}` = '$id'");
    }

    /**
     * 启用缓存
     *
     * @param int $lifeTime
     */
    public function cache($lifeTime = 300) {
        $this->cacheLifetime = $lifeTime;
    }

    /**
     * 产生分页
     */
    public function paging() {
        $this->num = $this->count();
        $offset    = ($this->page - 1) * $this->pageSize;
        if ($offset < 0) {
            $offset = 0;
        }
        if ($this->num % $this->pageSize > 0) {
            $this->pages = intval($this->num / $this->pageSize) + 1;
        } else {
            $this->pages = $this->num / $this->pageSize;
        }
        $this->limit($offset . ',' . $this->pageSize);
        $this->pager = new Pager(array(
            'total'    => $this->num,
            'perPage'  => $this->pageSize,
            'nowIndex' => $this->page,
        ));
    }

    /**
     * 获取当前条件下的记录数
     *
     * @return int|mixed
     */
    public function count() {
        $sql = "select count({$this->countFields}) as c from {$this->table} {$this->join} {$this->where} {$this->union} {$this->group}";
        if ($this->cacheLifetime) {
            $this->getSql(false);
            $cacheKey = $this->cachePrefix . '_count_' . md5($this->sql);
            $data     = \Zoco::$php->cache->get($cacheKey);
            if ($data) {
                return $data;
            }
        }

        if ($this->ifUnion) {
            $sql     = str_replace('{#unionSelect#}', "count({$this->countFields}) as c", $sql);
            $records = $this->db->query($sql)->fetchAll();
            $num     = 0;
            foreach ($records as $record) {
                $num += $record['c'];
            }
            $count = intval($num);
        } else {
            $records = $this->db->query($sql)->fetch();
            $count   = intval($records['c']);
        }

        if ($this->cacheLifetime && $count !== false) {
            \Zoco::$php->cache->set($this->cacheKey, $count, $this->cacheLifetime);
        }

        return $count;
    }

    /**
     * 查询的条数
     *
     * @param $limit
     */
    public function limit($limit) {
        if (!empty($limit)) {
            $_limit = explode(',', $limit, 2);
            if (count($_limit) == 2) {
                $this->limit = 'limit ' . (int)$_limit[0] . ',' . (int)$_limit[1];
            } else {
                $this->limit = 'limit ' . (int)$_limit[0];
            }
        } else {
            $this->limit = '';
        }
    }

    /**
     * @param $params
     */
    public function rawPut($params) {
        foreach ($params as $value) {
            if (isset($value[0]) && isset($value[1]) && count($value) == 2) {
                $this->_call($value[0], $value[1]);
            } else {
                $this->rawPut($value);
            }
        }
    }

    /**
     * @param $method
     * @param $param
     * @return bool
     */
    private function _call($method, $param) {
        if ($method == 'update' || $method == 'delete' || $method == 'insert') {
            return false;
        }
        if (strpos($method, '_') !== 0) {
            if (method_exists($this, $method)) {
                if (is_array($param)) {
                    call_user_func_array(array($this, $method), $param);
                } else {
                    $this->$method($param);
                }
            } else {
                if ($this->callBy == 'func') {
                    $this->where($method . '="' . $param . '"');
                } else {
                    if ($this->callBy == 'smarty') {
                        if (strpos($param, '$') === false) {
                            $this->where($method . '="' . $param . '"');
                        } else {
                            $this->where($method . "='{" . $param . "}'");
                        }
                    } else {
                        echo Error::info('Error: SelectDB 错误的参数', "<pre>参数$method=$param</pre>");
                        exit();
                    }
                }
            }
        }

        return false;
    }

    /**
     * 锁定行或表
     */
    public function lock() {
        $this->forUpdate = 'for update';
    }

    /**
     * SQL联合
     *
     * @param $sql
     */
    public function union($sql) {
        $this->ifUnion = true;
        if ($sql instanceof SelectDB) {
            $this->unionSelect = $sql->select;
            $sql->select       = '{#unionSelect#}';
            $this->union       = 'union (' . $sql->getSql(true) . ')';
        } else {
            $this->union = 'union (' . $sql . ')';
        }
    }

    /**
     * 将数组作为指令调用
     *
     * @param $params
     */
    public function put($params) {
        if (isset($params['put'])) {
            Error::info('SelectDB Error!', 'Params put() cannot call put()!');
        }
        /**
         * 处理where条件
         */
        if (isset($params['where'])) {
            $wheres = $params['where'];
            if (is_array($wheres)) {
                foreach ($wheres as $where) {
                    $this->where($where);
                }
            } else {
                $this->where($wheres);
            }
            unset($params['where']);
        }
        /**
         * 处理orWhere条件
         */
        if (isset($params['orWhere'])) {
            $orWheres = $params['orWhere'];
            if (is_array($orWheres)) {
                foreach ($orWheres as $orWhere) {
                    $this->orWhere($orWhere);
                }
            } else {
                $this->orWhere($orWheres);
            }
            unset($params['orWhere']);
        }
        /**
         * 处理walk调用
         */
        if (isset($params['walk'])) {
            foreach ($params['walk'] as $call) {
                list($key, $value) = each($call);
                $this->_call($key, $value);
            }
            unset($params['walk']);
        }
        /**
         * 处理其他参数
         */
        foreach ($params as $key => $value) {
            $this->_call($key, $value);
        }
    }

    /**
     * 使用or连接的条件
     *
     * @param $where
     */
    public function orWhere($where) {
        if ($this->where == '') {
            $this->where = 'where ' . $where;
        } else {
            $this->where = $this->where . ' or ' . $where;
        }
    }

    /**
     * 获取一条记录
     *
     * @param string $field
     * @param string $cacheId
     * @return mixed
     */
    public function getOne($field = '', $cacheId = '') {
        $this->limit('1');
        if ($this->autoCache || !empty($cacheId)) {
            $cacheKey = empty($cacheId) ? $this->cachePrefix . '_one_' . md5($this->sql) : $this->cachePrefix . '_all_' . $cacheId;
            global $php;
            $record = $php->cache->get($cacheKey);
            if (empty($record)) {
                if ($this->isExecute == 0) {
                    $this->execute();
                }
                $record = $this->result->fetch();
                $php->cache->set($cacheKey, $record, $this->cacheLifetime);
            }
        } else {
            if ($this->isExecute == 0) {
                $this->execute();
            }
            $record = $this->result->fetch();
        }

        if ($field === '') {
            return $record;
        } else {
            return $record[$field];
        }
    }

    /**
     * 执行生成的SQL语句
     *
     * @param string $sql
     */
    public function execute($sql = '') {
        if ($sql == '') {
            $this->getSql(false);
        } else {
            $this->sql = $sql;
        }

        $this->result = $this->db->query($this->sql);
        $this->isExecute++;
    }

    /**
     * 获取所有记录
     *
     * @return mixed
     */
    public function getAll() {
        if ($this->cacheLifetime) {
            $this->getSql(false);
            $cacheKey = $this->cachePrefix . '_all_' . md5($this->sql);
            $data     = \Zoco::$php->cache->get($cacheKey);
            if (empty($data)) {
                if ($this->isExecute == 0) {
                    $this->execute();
                }
                $data = $this->result->fetchAll();
                \Zoco::$php->cache->set($cacheKey, $data, $this->cacheLifetime);

                return $data;
            } else {
                return $data;
            }
        } else {
            if ($this->isExecute == 0) {
                $this->execute();
            }

            return $this->result->fetchAll();
        }
    }

    /**
     * 执行插入操作
     *
     * @param $data
     * @return bool|mixed|\PDOStatement
     */
    public function insert($data) {
        $field  = '';
        $values = '';
        foreach ($data as $key => $value) {
            $field  = $field . "`$key`,";
            $values = $values . "'$value',";
        }
        $field  = substr($field, 0, -1);
        $values = substr($values, 0, -1);

        return $this->db->query("insert into {$this->table} ($field) values($values)");
    }

    /**
     * 对符合当前条件的记录执行更新操作
     *
     * @param $data
     * @return bool|mixed|\PDOStatement
     */
    public function update($data) {
        $update = '';
        foreach ($data as $key => $value) {
            if ($value != '' && $value[0] == '`') {
                $update = $update . "`$key`=$value,";
            } else {
                $update = $update . "`$key`='$value',";
            }
        }
        $update = substr($update, 0, -1);

        return $this->db->query("update {$this->table} set $update {$this->where} {$this->limit}");
    }

    /**
     * 删除当前条件下的记录
     *
     * @return bool|mixed|\PDOStatement
     */
    public function delete() {
        return $this->db->query("delete from {$this->table} {$this->where} {$this->limit}");
    }
}