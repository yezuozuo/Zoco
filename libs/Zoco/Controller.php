<?php

namespace Zoco;

/**
 * Controller的基类
 * Class Controller
 *
 * @package Zoco
 */
class Controller extends Object {
    /**
     * 是否ajax
     *
     * @var bool
     */
    public $isAjax = false;

    /**
     * 是否对GET/POST/REQUEST/COOKIE参数进行转义
     *
     * @var bool
     */
    public $ifFilter = true;

    /**
     * 临时变量
     *
     * @var array
     */
    protected $tplVar = array();

    /**
     * 模版目录
     *
     * @var string
     */
    protected $templateDir;

    /**
     * 跟踪信息
     *
     * @var array
     */
    protected $trace = array();

    /**
     * 模型加载器
     *
     * @var ModelLoader
     */
    protected $model;

    /**
     * 配置文件
     *
     * @var Config
     */
    protected $config;

    protected $sdb;

    /**
     * @param \Zoco $zoco
     */
    public function __construct(\Zoco $zoco) {
        $this->zoco        = $zoco;
        $this->model       = $zoco->model;
        $this->config      = $zoco->config;
        $this->sdb         = new SelectDB($zoco->db);
        $this->templateDir = \Zoco::$appPath . '/templates/';
        if (!defined('TPL_PATH')) {
            define('TPL_PATH', $this->templateDir);
        }
        if ($this->ifFilter) {
            Filter::request();
        }
        $zoco->__init();
    }

    /**
     * 获取输出内容
     *
     * @param string $tplFile
     * @return string
     */
    public function fetch($tplFile = '') {
        ob_start();
        $this->display($tplFile);
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * 渲染临时文件并展示
     *
     * @param string $tplFile
     */
    public function display($tplFile = '') {
        if (empty($tplFile)) {
            $tplFile = strtolower($this->zoco->env['mvc']['controller']) . '/' . strtolower($this->zoco->env['mvc']['view']) . '.php';
        }

        if (!is_file($this->templateDir . $tplFile)) {
            echo Error::info('template error', "template file[" . $this->templateDir . $tplFile . "] not found");
            exit();
        }

        extract($this->tplVar);
        include $this->templateDir . $tplFile;
    }

    /**
     * @param            $array
     * @param            $key
     * @param string     $default
     * @param bool|false $intval
     * @return int|string
     */
    public function value($array, $key, $default = '', $intval = false) {
        if (isset($array[$key])) {
            return $intval ? intval($array[$key]) : intval($key);
        } else {
            return $default;
        }
    }

    /**
     * 输出JSON字符串
     *
     * @param string $data
     * @param int    $code
     * @param string $message
     * @return string
     */
    public function json($data = '', $code = 0, $message = '') {
        $json = array(
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        );
        if (!empty($_REQUEST['jsonp'])) {
            header('Content-type', 'application/x-javascript');

            return $_REQUEST['jsonp'] . "(" . json_encode($json) . ");";
        } else {
            header('Content-type', 'application/json');

            return json_encode($json);
        }
    }

    /**
     * @param string $code
     * @param string $msg
     * @return array|string
     */
    public function message($code = '', $msg = 'success') {
        $ret = array(
            'code'    => $code,
            'message' => $msg,
        );

        return $this->isAjax ? $ret : json_encode($ret);
    }

    /**
     * @param $key
     * @param $value
     */
    public function assign($key, $value) {
        $this->tplVar[$key] = $value;
    }

    /**
     * 显示跟踪信息
     *
     * @param bool|false $detail
     * @return string
     */
    public function showTrace($detail = false) {
        $_trace       = array();
        $includeFiles = get_included_files();

        $_trace['请求脚本']       = $_SERVER['SCRIPT_NAME'];
        $_trace['请求方法']       = $_SERVER['REQUEST_METHOD'];
        $_trace['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
        $_trace['HTTP版本']     = $_SERVER['SERVER_PROTOCOL'];
        $_trace['请求时间']       = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $_trace['读取数据库']      = $this->zoco->db->readTimes . '次';
        $_trace['写入数据库']      = $this->zoco->db->writeTimes . '次';
        $_trace['加载文件数目']     = count($includeFiles);
        $_trace['PHP执行占用']    = $this->showTime();

        if (isset($_SESSION)) {
            $_trace['SESSION_ID'] = session_id();
        }

        $_trace = array_merge($this->trace, $_trace);

        $html = <<<HTMLS
<style type="text/css">
#swoole_trace_content  {
font-family:		Consolas, Courier New, Courier, monospace;
font-size:			14px;
background-color:	#fff;
margin:				40px;
color:				#000;
border:				#999 1px solid;
padding:			20px 20px 12px 20px;
}
</style>
	<div id="content">
		<fieldset id="querybox" style="margin:5px;">
		<div style="overflow:auto;height:300px;text-align:left;">
HTMLS;

        foreach ($_trace as $key => $value) {
            $html .= $key . ' : ' . $value . BL;
        }

        if ($detail) {
            /**
             * 输出包含的文件
             */
            $html .= '加载的文件' . BL;
            foreach ($includeFiles as $key => $value) {
                $html .= 'require ' . $value . BL;
            }
        }
        $html .= "</div></fieldset></div>";

        return $html;
    }

    /**
     * 显示运行时间和内存占用
     *
     * @return string
     */
    protected function showTime() {
        $runTime  = $this->zoco->runTime();
        $showTime = '执行时间：' . $runTime['time'];
        $showTime .= ' | 内存时间：' . $runTime['memory'];

        return $showTime;
    }

    public function __destruct() {
        $this->zoco->__clean();
    }

    /**
     * 跟踪信息
     *
     * @param        $title
     * @param string $value
     */
    protected function trace($title, $value = '') {
        if (is_array($title)) {
            $this->trace = array_merge($this->trace, $title);
        } else {
            $this->trace[$title] = $value;
        }
    }
}