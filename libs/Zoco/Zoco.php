<?php
/**
 * 库加载器
 */
require_once __DIR__ . '/Loader.php';

/**
 * 模型加载器
 */
require_once __DIR__ . '/ModelLoader.php';

/**
 * Zoco系统核心类，外部使用全局变量$php引用
 * Zoco框架系统的核心类，提供一个zoco对象引用树和基础的调用功能
 *
 * @property \Zoco\Database    $db
 * @property \Zoco\IFace\Cache $cache
 * @property \Zoco\Upload      $upload
 * @property \Zoco\Session     $session
 * @property \Zoco\Template    $tpl
 * @property \Redis            $redis
 * @property \MongoClient      $mongo
 * @property \Zoco\Config      $config
 * @property \Zoco\Log         $log
 * @property \Zoco\Auth        $user
 * @property \Zoco\URL         $url
 * @property \Zoco\Limit       $limit
 * @method \Zoco\Database      db($dbKey)
 * @method \Redis              redis($config)
 * @method \MongoClient        mongo
 * @method \Zoco\IFace\Cache   cache
 * @method \Zoco\URL           url
 * @method \Zoco\Log           log($config)
 */
class Zoco {
    /**
     * 所有全局对象都改为动态延迟加载
     * 如果希望启动加载,使用Zoco::load()函数
     */

    /**
     * 初始化
     */
    const HOOK_INIT = 1;
    /**
     * URL路由
     */
    const HOOK_ROUTE = 2;
    /**
     * 清理
     */
    const HOOK_CLEAN = 3;
    /**
     * app目录的地址
     *
     * @var string
     */
    static public $appPath;
    /**
     * 控制器的地址
     *
     * @var string
     */
    static public $controllerPath = '';
    /**
     * @var string
     */
    static $charset = 'utf-8';
    /**
     * @var bool
     */
    static $debug = false;
    /**
     * @var array
     */
    static $setting = array();
    /**
     * Zoco类的实例
     *
     * @var Zoco
     */
    static public $php;
    /**
     * 默认的控制器
     *
     * @var array
     */
    static $defaultController = array(
        'controller' => 'page',
        'view'       => 'index',
    );
    /**
     * 可使用的组件
     * 对应factory文件夹中的一个一个文件
     *
     * @var array
     */
    static $modules = array(
        'cache'   => true, //缓存
        'db'      => true, //数据库
        'http'    => true, //http
        'limit'   => true, //频率限制组件
        'log'     => true, //日志
        'mongo'   => true, //mongodb
        'redis'   => true, //redis
        'session' => true, //session
        'tpl'     => true, //模板系统
        'upload'  => true, //上传组件
        'url'     => true, //url
        'user'    => true, //用户验证组件
    );
    /**
     * 允许多实例的模块
     *
     * @var array
     */
    static $multiInstance = array(
        'cache' => true,
        'db'    => true,
        'mongo' => true,
        'redis' => true,
        'url'   => true,
        'log'   => true,
    );

    /**
     * 环境变量
     *
     * @var array
     */
    public $env;
    /**
     * 库加载器
     *
     * @var \Zoco\Loader
     */
    public $load;
    /**
     * 模型加载器
     *
     * @var \Zoco\ModelLoader
     */
    public $model;
    /**
     * @var array
     */
    public $genv;
    /**
     * 配置文件
     *
     * @var \Zoco\Config
     */
    public $config;
    /**
     * @var array
     */
    public $errorCall = array();
    /**
     * @var
     */
    public $pageCache;
    /**
     * 传给factory
     *
     * @var string
     */
    public $factoryKey = 'master';
    /**
     * 发生错误时的回调函数
     *
     * @var
     */
    public $errorCallback;
    /**
     * @var array HOOK
     */
    protected $hooks = array();
    /**
     * 陆游的函数
     *
     * @var string
     */
    protected $routerFunction;
    /**
     * 对象池
     *
     * @var array
     */
    protected $objects = array();

    private function __construct() {
        if (!defined('DEBUG')) {
            define('DEBUG', 'on');
        }

        /**
         * 判断PHP的运行环境
         */
        $this->env['sapi_name'] = php_sapi_name();

        /**
         * 如果不是命令刚运行的话可以输出错误页面
         */
        if ($this->env['sapi_name'] != 'cli') {
            Zoco\Error::$echoHtml = true;
        }

        if (empty(self::$appPath)) {
            if (defined('WEBPATH')) {
                self::$appPath = WEBPATH . '/apps';
            } else {
                echo Zoco\Error::info("core error", __CLASS__ . ":Zoco::\$appPath and WEBPATH empty.");
                exit();
            }
        }

        if (!defined('APPSPATH')) {
            define('APPSPATH', self::$appPath);
        }

        /**
         * 将此目录作为App命名空间的根目录
         */
        Zoco\Loader::addNameSpace('App', self::$appPath . '/classes');

        /**
         * 加载器
         */
        $this->load = new Zoco\Loader($this);

        /**
         * 模型加载器
         */
        $this->model = new Zoco\ModelLoader($this);

        /**
         * 加载配置文件
         */
        $this->config = new Zoco\Config;

        /**
         * 设置配置文件路径
         */
        $this->config->setPath(self::$appPath . '/configs');

        /**
         * 路由钩子，URLRewrite
         */
        $this->addHook(Zoco::HOOK_ROUTE, 'ZocoUrlRouterRewrite');

        /**
         * MVC
         */
        $this->addHook(Zoco::HOOK_ROUTE, 'ZocoUrlRouterMVC');

        /**
         * 设置路由函数
         */
        $this->router(array($this, 'urlRouter'));
    }

    /**
     * 增加钩子函数
     *
     * @param $type
     * @param $func
     */
    public function addHook($type, $func) {
        $this->hooks[$type][] = $func;
    }

    /**
     * 设置路由器
     *
     * @param $function
     */
    public function router($function) {
        $this->routerFunction = $function;
    }

    /**
     * 设置应用程序路径
     *
     * @param $dir
     */
    static public function setAppPath($dir) {
        if (is_dir($dir)) {
            self::$appPath = $dir;
        } else {
            echo Zoco\Error::info("fatal error", "appPath[$dir] is not exists.");
            exit();
        }
    }

    /**
     * 设置应用程序路径
     *
     * @param $dir
     */
    static public function setControllerPath($dir) {
        if (is_dir($dir)) {
            self::$controllerPath = $dir;
        } else {
            echo Zoco\Error::info("fatal error", "controllerPath[$dir] is not exists.");
            exit();
        }
    }

    /**
     * 单例
     *
     * @return Zoco
     */
    static public function getInstance() {
        if (!self::$php) {
            self::$php = new Zoco;
        }

        return self::$php;
    }

    /**
     * 获取运行时间
     *
     * @return mixed
     */
    public function runTime() {
        $return['time']   = number_format((microtime(true) - $this->env['runTime']['start']), 4) . 's';
        $startMem         = array_sum(explode(' ', $this->env['runTime']['mem']));
        $endMem           = array_sum(explode(' ', memory_get_usage()));
        $return['memory'] = number_format(($endMem - $startMem) / 1024) . 'kb';

        return $return;
    }

    /**
     * 压缩内容
     */
    public function gzip() {
        /**
         * 不要在文件中加入UTF-8的BOM头
         * ob_end_clean();
         * 这行代码使得PHP激活输出缓存，并压缩它发送出去的所有内容。
         */
        ob_start('ob_gzhandler');

        /**
         * 是否开启压缩
         */
        if (function_exists('ob_gzhandler')) {
            ob_start('ob_gzhandler');
        } else {
            ob_start();
        }
    }

    /**
     * 初始化环境
     */
    public function __init() {
        if (defined('DEBUG') && DEBUG == 'on') {
            /**
             * 记录运行时间和内存占用情况
             */
            $this->env['runTime']['start'] = microtime(true);
            $this->env['runTime']['mem']   = memory_get_usage();
        }
        $this->callHook(self::HOOK_INIT);
    }

    /**
     * 执行Hook函数列表
     *
     * @param string $type HOOK的类型
     */
    protected function callHook($type) {
        if (isset($this->hooks[$type])) {
            foreach ($this->hooks[$type] as $value) {
                if (!is_callable($value)) {
                    /**
                     * 创建自定义错误消息
                     */
                    trigger_error("hook function[$value] is not callable.");
                    continue;
                }
                $value();
            }
        }
    }

    /**
     * 清理
     */
    public function __clean() {
        $this->env['runTime'] = array();
        $this->callHook(self::HOOK_CLEAN);
    }

    /**
     * 如果不存在此对象，从工厂中创建一个
     *
     * @param $libName
     * @return mixed
     * @throws Exception
     */
    public function __get($libName) {
        if (empty($this->$libName)) {
            /**
             * 载入组件
             */
            $this->$libName = $this->loadModule($libName);
        }

        return $this->$libName;
    }

    /**
     * 加载内置的Zoco模块
     *
     * @param        $module
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    protected function loadModule($module, $key = 'master') {
        $objectId = $module . '_' . $key;
        if (empty($this->objects[$objectId])) {
            $this->factoryKey = $key;
            $userFactoryFile  = self::$appPath . '/factory/' . $module . '.php';

            /**
             * 尝试从用户工厂构建对象
             */
            if (is_file($userFactoryFile)) {
                $object = require $userFactoryFile;
            } /**
             * 系统默认
             */
            else {
                $systemFactoryFile = LIBPATH . 'factory/' . $module . '.php';

                /**
                 * 组件不存在的话s抛出异常
                 */
                if (!is_file($systemFactoryFile)) {
                    throw new \Exception("module [$module] not found.");
                }
                $object = require $systemFactoryFile;
            }
            $this->objects[$objectId] = $object;
        }

        return $this->objects[$objectId];
    }

    /**
     * @param $func
     * @param $param
     * @return mixed
     * @throws Exception
     */
    public function __call($func, $param) {
        if (isset(self::$multiInstance[$func])) {
            if (empty($param[0]) || !is_string($param[0])) {
                throw new Exception("module name cannot be null.");
            }

            return $this->loadModule($func, $param[0]);
        } else {
            throw new Exception("call an undefine method[$func].");
        }
    }

    /**
     * 进行路由
     *
     * @return array|bool
     */
    public function urlRouter() {
        if (empty($this->hooks[self::HOOK_ROUTE])) {
            echo Zoco\Error::info('MVC Error|', 'UrlRouter hook is empty');
            exit();
        }
        $uri = strstr($_SERVER['REQUEST_URI'], '?', true);
        if ($uri === false) {
            $uri = $_SERVER['REQUEST_URI'];
        }
        $uri = trim($uri, '/');
        $mvc = array();

        /**
         * URL Router
         */
        foreach ($this->hooks[self::HOOK_ROUTE] as $hook) {
            if (!is_callable($hook)) {
                trigger_error("hook function[$hook] is not callable.");
                continue;
            }
            $mvc = $hook($uri);

            /**
             * 命中
             */
            if ($mvc !== false) {
                break;
            }
        }

        return $mvc;
    }

    /**
     * 运行MVC处理模型
     *
     * @return bool|string
     */
    public function runMVC() {
        $mvc = call_user_func($this->routerFunction);
        if ($mvc === false) {
            echo Zoco\Error::info('MVC Error', 'url route fail!');
            exit;
        }

        /**
         * 检查controller name
         */
        if (!preg_match('/^[a-z0-9_]+$/i', $mvc['controller'])) {
            echo Zoco\Error::info('API Error', 'zoco is watching you');
            exit;
        }

        /**
         * 检查view name
         */
        if (!preg_match('/^[a-z0-9_]+$/i', $mvc['view'])) {
            return Zoco\Error::info('MVC Error!', "view[{$mvc['view']}] name incorrect.Regx: /^[a-z0-9_]+$/i");
        }

        /**
         * 检查app name
         */
        if (isset($mvc['app']) && !preg_match('/^[a-z0-9_]+$/i', $mvc['app'])) {
            echo Zoco\Error::info('API Error', 'zoco is watching you');
            exit;
        }
        $this->env['mvc'] = $mvc;

        /**
         * 使用命名空间
         * 文件名必须大写
         */
        $controllerClass = '\\App\\Controller\\' . ucwords($mvc['controller']);
        if (self::$controllerPath) {
            $controllerPath = self::$controllerPath . '/' . ucwords($mvc['controller']) . '.php';
        } else {
            $controllerPath = self::$appPath . '/controllers/' . ucwords($mvc['controller']) . '.php';
        }

        if (class_exists($controllerClass, false)) {
            goto doAction;
        } else {
            if (is_file($controllerPath)) {
                require_once $controllerPath;
                goto doAction;
            }
        }

        return Zoco\Error::info('MVC Error', "Controller <b>{$mvc['controller']}</b>[{$controllerPath}] not exist!");

        doAction:

        $controller = new $controllerClass($this);
        if (!method_exists($controller, $mvc['view'])) {
            echo Zoco\Error::info('API Error', 'zoco is watching you');
            exit;
        }

        $param  = empty($mvc['param']) ? null : $mvc['param'];
        $method = $mvc['view'];

        /**
         * doAction
         */
        $return = $controller->$method($param);

        /**
         * 相应请求
         */
        if (!empty($controller->isAjax)) {
            header('Cache-Control', 'no-cache, must-revalidate');
            header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
            header('Content-Type', 'application/json');
            $return = json_encode($return);
        }

        echo $return;

        return true;
    }
}

/**
 * @param $uri
 * @return bool
 */
function ZocoUrlRouterRewrite(&$uri) {
    $rewrite = Zoco::$php->config['rewrite'];
    if (empty($rewrite) || !is_array($rewrite)) {
        return false;
    }
    $match      = array();
    $uriForRegx = '/' . $uri;
    foreach ($rewrite as $rule) {
        if (preg_match('#' . $rule['regx'] . '#i', $uriForRegx, $match)) {
            if (isset($rule['get'])) {
                $p = explode(',', $rule['get']);
                foreach ($p as $k => $v) {
                    if (isset($match[$k + 1])) {
                        $_GET[$v] = $match[$k + 1];
                    }
                }

                return $rule['mvc'];
            }
        }
    }

    return false;
}

/**
 * @param $uri
 * @return array
 */
function ZocoUrlRouterMVC(&$uri) {
    $array = Zoco::$defaultController;

    if (!empty($_GET['c'])) {
        $array['controller'] = $_GET['c'];
    }
    if (!empty($_GET['v'])) {
        $array['view'] = $_GET['v'];
    }

    if (empty($uri) || substr($uri, -9) == 'index.php') {
        return $array;
    }

    $request = explode('/', $uri, 3);
    if (count($request) < 2) {
        return $array;
    }

    $array['controller'] = $request[0];
    $array['view']       = $request[1];

    if (isset($request[2])) {
        $request[2] = trim($request[2], '/');
        $_id        = str_replace('.html', '', $request[2]);
        if (is_numeric($_id)) {
            $_GET['id'] = $_id;
        } else {
            Zoco\Tool::$urlKeyJoin   = '-';
            Zoco\Tool::$urlParamJoin = '-';
            Zoco\Tool::$urlAddEnd    = '.html';
            Zoco\Tool::$urlPrefix    = WEBPATH . "/{$request[0]}/$request[1]/";
            Zoco\Tool::urlParseInto($request[2], $_GET);
        }
    }

    return $array;
}