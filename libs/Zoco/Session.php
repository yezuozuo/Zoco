<?php

namespace Zoco;

/**
 * 会话控制类
 * Class Session
 *
 * @package Zoco
 */
class Session {
    /**
     * 缓存的前缀
     *
     * @var string
     */
    static $cachePrefix = 'php_session_';

    /**
     * cookie的生存时间
     *
     * @var int
     */
    static $cookieLifetime = 8640000;

    /**
     * 缓存的生存时间
     *
     * @var int
     */
    static $cacheLifetime = 0;
    /**
     * @var int
     */
    static $sessSize = 32;
    /**
     * @var string
     */
    static $sessName = 'SESSID';
    /**
     * @var string
     */
    static $cookieKey = 'PHPSESSID';
    /**
     * @var
     */
    static $sessDomain;
    /**
     * 是否启动
     *
     * @var
     */
    public $isStart;
    /**
     * @var
     */
    public $sessid;
    /**
     * 是否为只读,只读不需要保存
     *
     * @var
     */
    public $readonly;
    /**
     * @var
     */
    public $open;
    /**
     * 使用PHP内建的session
     *
     * @var bool
     */
    public $usePHPSession = true;
    /**
     * @var \Zoco\Cache
     */
    protected $cache;

    /**
     * @param null $cache
     */
    public function __construct($cache = null) {
        $this->cache = $cache;
    }

    /**
     * @param bool|false $readonly
     */
    public function start($readonly = false) {
        $this->isStart = true;
        if ($this->usePHPSession) {
            session_start();
        } else {
            $this->readonly = $readonly;
            $this->open     = true;
            $sessid         = Cookie::get(self::$cookieKey);
            if (empty($sessid)) {
                $sessid = RandomKey::randmd5(40);
                Cookie::set(self::$cookieKey, $sessid);
            }
            $_SESSION = $this->load($sessid);
        }
    }

    /**
     * @param $sessid
     * @return array|mixed
     */
    public function load($sessid) {
        $this->sessid = $sessid;
        $data         = $this->get($sessid);
        if ($data) {
            return unserialize($data);
        } else {
            return array();
        }
    }

    /**
     * 读取session
     *
     * @param $sessid
     * @return array
     */
    public function get($sessid) {
        $session = $this->cache->get(self::$cachePrefix . $sessid);
        /**
         * 先读数据，如果没有，就初始化一个
         */
        if (!empty($session)) {
            return $session;
        } else {
            return array();
        }
    }

    /**
     * 设置session id
     *
     * @param $sessid
     */
    public function setId($sessid) {
        $this->sessid = $sessid;
        if ($this->usePHPSession) {
            session_id($sessid);
        }
    }

    /**
     * 获取session id
     *
     * @return string
     */
    public function getId() {
        if ($this->usePHPSession) {
            return session_id();
        } else {
            return $this->sessid;
        }
    }

    /**
     * @return mixed
     */
    public function save() {
        return $this->set($this->sessid, serialize($_SESSION));
    }

    /**
     * 设置session
     *
     * @param        $sessid
     * @param string $session
     * @return mixed
     */
    public function set($sessid, $session = '') {
        $key = self::$cachePrefix . $sessid;
        $ret = $this->cache->set($key, $session, self::$cacheLifetime);

        return $ret;
    }

    /**
     * 打开session
     *
     * @param string $savePath
     * @param string $sessName
     * @return bool
     */
    public function open($savePath = '', $sessName = '') {
        self::$cachePrefix = $savePath . '_' . $sessName;

        return true;
    }

    /**
     * 关闭session
     *
     * @return bool
     */
    public function close() {
        return true;
    }

    /**
     * 销毁session
     *
     * @param string $sessid
     * @return mixed
     */
    public function delete($sessid = '') {
        return $this->cache->delete(self::$cachePrefix . $sessid);
    }

    /**
     * 初始化session，配置session
     *
     * @return bool
     */
    public function initSess() {
        /**
         * 不使用GET/POST变量方式
         */
        ini_set('session.use_trans_sid', 0);
        /**
         * 设置垃圾回收最大生存时间
         */
        ini_set('session.gc_maxlifetime', self::$cacheLifetime);
        /**
         * 使用COOKIE保存SESSION ID的方式
         */
        ini_set('session.use_cookies', 1);
        ini_set('session.cookie_path', '/');
        /**
         * 多主机共享保存SESSION ID的COOKIE
         */
        ini_set('session.cookie_domain', self::$sessDomain);
        /**
         * 将session.save_handler设置为user，而不是默认的files
         */
        session_module_name('user');
        /**
         * 定义SESSION各项操作所对应的方法名
         */
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'get'),
            array($this, 'set'),
            array($this, 'delete'),
            array($this, 'gc')
        );
        session_start();

        return true;
    }
}