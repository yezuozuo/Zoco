<?php

namespace Zoco;

/**
 * Class Auth
 *
 * @package Zoco
 */
class Auth {
    const ERR_NO_EXIST = 1;
    const ERR_PASSWORD = 2;
    const HASH_SHA1    = 'sha1';
    const HASH_CRYPT   = 'crypt';
    /**
     * @var string
     */
    static $loginUrl = '/login.php?';
    /**
     * @var string
     */
    static $username = 'username';
    /**
     * @var string
     */
    static $password = 'password';
    /**
     * @var string
     */
    static $userId = 'id';
    /**
     * @var string
     */
    static $sessionPrefix = '';
    /**
     * @var string
     */
    static $mkPassword = 'username,password';
    /**
     * @var string
     */
    static $passwordHash = 'sha1';
    /**
     * @var int
     */
    static $passwordCost = 10;
    /**
     * @var int
     */
    static $passwordSaltSize = 22;
    /**
     * @var int
     */
    static $cookieLife = 2592000;
    /**
     * @var bool
     */
    static $sessionDestroy = false;
    /**
     * @var string
     */
    static $lastLogin = 'last_login';
    /**
     * @var string
     */
    static $lastIp = 'last_ip';
    /**
     * @var string
     */
    public $select = '*';
    /**
     * @var string|Database
     */
    public $db = '';
    /**
     * @var
     */
    public $user;
    /**
     * @var
     */
    public $profile;
    /**
     * @var bool
     */
    public $isLogin = true;
    /**
     * @var
     */
    public $dict;
    /**
     * @var
     */
    public $errCode;
    /**
     * @var
     */
    public $errMessage;
    /**
     * @var
     */
    protected $config;
    /**
     * @var string
     */
    protected $loginTable = '';
    /**
     * @var string
     */
    protected $loginDb = '';
    /**
     * @var string
     */
    protected $profileTable = '';

    /**
     * @param $config
     * @throws \Exception
     */
    public function __construct($config) {
        $this->config = $config;
        if (empty($config['loginTable'])) {
            throw new \Exception(__CLASS__ . 'request loginTable config.');
        }
        if (!empty($config['loginDb'])) {
            $this->loginDb = $config['loginDb'];
        } else {
            $this->loginDb = 'master';
        }

        $this->loginTable                           = $config['loginTable'];
        $this->db                                   = \Zoco::$php->db($this->loginDb);
        $_SESSION[self::$sessionPrefix . 'saveKey'] = array();
    }

    /**
     * @return bool
     */
    static public function loginRequire() {
        $user = \Zoco::$php->user;

        if (!$user->isLogin()) {
            $url = $user->config['loginUrl'];
            header('Location: ' . $url);

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isLogin() {
        if (isset($_SESSION[self::$sessionPrefix . 'isLogin']) && $_SESSION[self::$sessionPrefix . 'isLogin'] == 1) {
            return true;
        } else {
            if (isset($_COOKIE[self::$sessionPrefix . 'autoLogin']) && isset($_COOKIE[self::$sessionPrefix . 'username']) && isset($_COOKIE[self::$sessionPrefix . 'password'])) {
                return $this->login($_COOKIE[self::$sessionPrefix . 'username'], $_COOKIE[self::$sessionPrefix . 'password'], $auto = 1);
            }
        }

        return false;
    }

    /**
     * @param      $username
     * @param      $password
     * @param bool $autoLogin 是否自动登录
     * @return bool
     */
    public function login($username, $password, $autoLogin = false) {
        Cookie::set(self::$sessionPrefix . 'username', $username, time() + self::$cookieLife);
        $this->user = $this->db->query('select ' . $this->select . ' from ' . $this->loginTable . ' where ' . self::$username . "='$username' limit 1")->fetch();
        if (empty($this->user)) {
            $this->errCode = self::ERR_NO_EXIST;

            return false;
        } else {
            if (self::verifyPassword($username, $password, $this->user[self::$password])) {
                $_SESSION[self::$sessionPrefix . 'isLogin'] = true;
                $_SESSION[self::$sessionPrefix . 'userID']  = $this->user['id'];
                if ($autoLogin) {
                    $this->autoLogin();
                }

                return true;
            } else {
                $this->errCode = self::ERR_PASSWORD;

                return false;
            }
        }
    }

    /**
     * @param $username
     * @param $inputPassword
     * @param $realPassword
     * @return bool
     * @throws \Exception
     */
    static public function verifyPassword($username, $inputPassword, $realPassword) {
        if (self::$passwordHash == 'crypt') {
            if (!function_exists('password_verify')) {
                throw new \Exception('require password_verify function.');
            }

            return password_verify($inputPassword, $realPassword);
        } else {
            $pwdHash = self::makePasswordHash($username, $inputPassword);

            return $realPassword == $pwdHash;
        }
    }

    /**
     * @param $username
     * @param $password
     * @return bool|string
     * @throws \Exception
     */
    static public function makePasswordHash($username, $password) {
        if (self::$passwordHash == 'sha1') {
            return sha1($username . $password);
        } else {
            if (self::$passwordHash == 'crypt') {
                if (!function_exists('password_hash')) {
                    throw new \Exception("require password_hash function.");
                }
                $options = [
                    'cost' => self::$passwordCost,
                    'salt' => mcrypt_create_iv(self::$passwordSaltSize, MCRYPT_DEV_URANDOM),
                ];

                return password_hash($password, PASSWORD_BCRYPT, $options);
            } else {
                if (self::$passwordHash == 'md5') {
                    return md5($username . $password);
                } else {
                    if (self::$passwordHash == 'sha1Single') {
                        return sha1($password);
                    } else {
                        if (self::$passwordHash == 'md5Single') {
                            return md5($password);
                        }
                    }
                }
            }
        }

        return false;
    }

    public function autoLogin() {
        Cookie::set(self::$sessionPrefix . 'autoLogin', 1, time() + self::$cookieLife);
        Cookie::set(self::$sessionPrefix . 'username', $this->user['username'], time() + self::$cookieLife);
        Cookie::set(self::$sessionPrefix . 'password', $this->user['password'], time() + self::$cookieLife);
    }

    /**
     * @param string $key
     */
    public function saveUserInfo($key = 'userInfo') {
        $_SESSION[self::$sessionPrefix . $key]        = $this->user;
        $_SESSION[self::$sessionPrefix . 'saveKey'][] = self::$sessionPrefix . $key;
    }

    /**
     * 更新用户信息
     *
     * @param null $set
     */
    public function updateStatus($set = null) {
        if (empty($set)) {
            $set = array(
                self::$lastLogin => date('Y-m-d H:i:s'),
                self::$lastIp    => Client::getIp(),
            );
        }
        $this->db->update($this->user['id'], $set, $this->loginTable);
    }

    /**
     * @param $key
     */
    public function setSession($key) {
        $_SESSION[$key]                               = $this->user[$key];
        $_SESSION[self::$sessionPrefix . 'saveKey'][] = self::$sessionPrefix . $key;
    }

    /**
     * @return mixed
     */
    public function getUid() {
        return $_SESSION[self::$sessionPrefix . 'userID'];
    }

    /**
     * @return mixed
     */
    public function getUserInfo() {
        return $this->user;
    }

    /**
     * @param $uid
     * @param $oldPwd
     * @param $newPwd
     * @return bool
     */
    public function changePassword($uid, $oldPwd, $newPwd) {
        $table          = table($this->loginTable, $this->loginDb);
        $table->primary = self::$userId;
        $_res           = $table->gets(array(
            'select'      => self::$username . ',' . self::$password,
            'limit'       => 1,
            self::$userId => $uid,
        ));
        if (count($_res) < 1) {
            $this->errMessage = '用户不存在';
            $this->errCode    = 1;

            return false;
        }

        $user = $_res[0];
        if ($user[self::$password] != self::makePasswordHash($user[self::$username], $oldPwd)) {
            $this->errMessage = '原密码不正确';
            $this->errCode    = 2;

            return false;
        } else {
            $table->set($uid, array(self::$password => self::makePasswordHash($user[self::$username], $newPwd)), self::$userId);

            return true;
        }
    }

    /**
     * @return bool
     */
    public function logout() {
        if (!\Zoco::$php->session->isStart) {
            \Zoco::$php->session->start();
        }

        if (self::$sessionDestroy) {
            $_SESSION = array();

            return true;
        }

        unset($_SESSION[self::$sessionPrefix . 'isLogin']);
        unset($_SESSION[self::$sessionPrefix . 'userId']);

        if (!empty($_SESSION[self::$sessionPrefix . 'saveKey'])) {
            foreach ($_SESSION[self::$sessionPrefix . 'saveKey'] as $sk) {
                unset($_SESSION[$sk]);
            }
        }

        unset($_SESSION[self::$sessionPrefix . 'saveKey']);
        if (isset($_COOKIE[self::$sessionPrefix . 'password'])) {
            Cookie::set(self::$sessionPrefix . 'password', '', 0);
        }

        return true;
    }

    /**
     * @param      $userName
     * @param      $password
     */
    public function register($userName, $password) {
        $set['username'] = $userName;
        $set['password'] = self::makePasswordHash($userName, $password);

        $this->db->insert($set, $this->loginTable);
    }
}