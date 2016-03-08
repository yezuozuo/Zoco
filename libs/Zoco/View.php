<?php

namespace Zoco;

/**
 * 视图类，提供一个简单视图封装
 * Class View
 *
 * @package Zoco
 */
class View extends \ArrayObject {
    /**
     * @var string
     */
    public $templateDir = '';
    /**
     * @var bool
     */
    public $ifPageCache = false;
    /**
     * @var int
     */
    public $cacheLife = 3600;
    /**
     * @var bool
     */
    public $showRunTime = false;
    /**
     * @var array
     */
    protected $var = array();
    /**
     * @var array
     */
    protected $trace = array();
    /**
     * @var \Zoco
     */
    protected $zoco;

    /**
     * @param \Zoco $zoco
     */
    function __construct(\Zoco $zoco) {
        $this->zoco        = $zoco;
        $this->templateDir = APPSPATH . '/views/';
    }

    /**
     * 模板变量赋值
     *
     * @param        $name
     * @param string $value
     */
    public function assign($name, $value = '') {
        if (is_array($name)) {
            $this->var = array_merge($this->var, $name);
        } else {
            if (is_object($name)) {
                foreach ($name as $key => $value) {
                    $this->var[$key] = $value;
                }
            } else {
                $this->var[$name] = $value;
            }
        }
    }

    /**
     * 加载模板和页面输出
     *
     * @param string     $template    模板文件名 留空为自动获取
     * @param string     $charset     模板输出字符集
     * @param string     $contentType 输出mime类型
     * @param bool|false $display     是否直接显示
     */
    public function fetch($templateFile = '', $display = false, $charset = '', $contentType = 'text/html') {
        $GLOBALS['viewStartTime'] = microtime(true);
        if ($templateFile === null) {
            /**
             * 使用null参数作为模版名直接返回不做任何输出
             */
            return null;
        }

        if (empty($charset)) {
            $charset = 'utf-8';
        }

        /**
         * 网页字符编码
         */
        header("Content-Type;" . $contentType . "; charset=" . $charset);
        /**
         * 支持页面回跳
         */
        header("Cache-control: private");

        /**
         * 页面缓存
         */
        ob_start();
        ob_implicit_flush(0);

        $this->render($templateFile);

        /**
         * 获取并清空缓存
         */
        $content = ob_get_clean();

        /**
         * 布局模板解析
         */
        $content = $this->layout($content, $charset, $contentType);

        /**
         * 输出模板文件
         */

        return $this->output($content, $display);
    }

    /**
     * 渲染页面
     *
     * @param $templateFile
     */
    private function render($templateFile) {
        extract($this->var);
        $templateFile = $this->parseTemplateFile($templateFile);
        require $templateFile;
    }

    /**
     * 解析模板文件
     *
     * @param $templateFile
     * @return string
     */
    private function parseTemplateFile($templateFile) {
        if ($templateFile === '') {
            $templateFile = $this->zoco->env['mvc']['controller'] . '_' . $this->zoco->env['mvc']['view'] . '.html';
        }
        $templateFile = $this->templateDir . $templateFile;
        if (!file_exists($templateFile)) {
            echo Error::info('View Error!', 'Template file not exists! <b>' . $templateFile . '</b>');
            exit();
        }

        return $templateFile;
    }

    /**
     * 输出布局模板
     *
     * @param        $content
     * @param string $charset
     * @param string $contentType
     * @return mixed
     */
    private function layout($content, $charset = '', $contentType = 'text/html') {
        $find = preg_match_all('/<!-- layout::(.+?)::(.+?) -->/is', $content, $matches);
        if ($find) {
            for ($i = 0; $i < $find; $i++) {
                /**
                 * 读取相关的页面模板替换布局单元
                 */
                if (strpos($matches[1][$i], '*') === 0) {
                    /**
                     * 动态布局
                     */
                    $matches[1][$i] = $this->get(substr($matches[1][$i], 1));
                }

                /**
                 * 设置了布局缓存
                 * 检查布局缓存是否有效
                 */
                if ($matches[2][$i] == 0) {
                    $guid  = md5($matches[1][$i]);
                    $cache = $guid;
                    if ($cache) {
                        $layoutContent = $cache;
                    } else {
                        $layoutContent = $this->fetch($matches[1][$i], $charset, $contentType);
                    }
                } else {
                    $layoutContent = $this->fetch($matches[1][$i], $charset, $contentType);
                }
                $content = str_replace($matches[0][$i], $layoutContent, $content);
            }
        }

        return $content;
    }

    /**
     * 取得模板变量的值
     *
     * @param $name
     * @return bool
     */
    public function get($name) {
        if (isset($this->var[$name])) {
            return $this->var[$name];
        } else {
            return false;
        }
    }

    /**
     * 输出模板
     *
     * @param $content
     * @param $display
     * @return null
     */
    private function output($content, $display) {
        if ($this->ifPageCache) {
            $pageCache = new PageCache($this->cacheLife);
            if ($pageCache->isCached()) {
                $pageCache->load();
            } else {
                $pageCache->create($content);
            }
        }

        if ($display) {
            $showTime = $this->showTime();
            echo $showTime . BL;
            echo $content;
            if ($this->showRunTime) {
                $this->showTrace();
            }

            return null;
        } else {
            return $content;
        }
    }

    /**
     * 显示运行时间、数据库操作、缓存次数、内存使用信息
     *
     * @return string
     */
    private function showTime() {
        /**
         * 显示运行的时间
         */
        $startTime = $this->zoco->env['runTime']['start'];
        $endTime   = microtime(true);

        $totalRunTime = number_format(($endTime - $startTime), 4);
        $showTime     = '执行时间：' . $totalRunTime . 's ';

        $startMem = array_sum(explode(' ', $this->zoco->env['runTime']['mem']));
        $endMem   = array_sum(explode(' ', memory_get_usage()));
        $showTime .= ' | 内存占用：' . number_format(($endMem - $startMem) / 1024) . ' kb';

        return $showTime;
    }

    /**
     * 显示页面trace信息
     *
     * @param bool|false $detail
     */
    public function showTrace($detail = false) {
        /**
         * 显示页面Trace信息 读取Trace定义文件
         * 定义格式 return array('当前页面'=>$_SERVER['PHP_SELF'],'通信协议'=>$_SERVER['SERVER_PROTOCOL'],...);
         */
        $trace = array();

        /**
         * 系统默认显示信息
         */
        $this->trace('当前页面', $_SERVER['REQUEST_URI']);
        $this->trace('请求方法', $_SERVER['REQUEST_METHOD']);
        $this->trace('通信协议', $_SERVER['SERVER_PROTOCOL']);
        $this->trace('请求时间', date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']));
        $this->trace('用户代理', $_SERVER['HTTP_USER_AGENT']);

        if (isset($_SESSION)) {
            $this->trace('会话ID', session_id());
        }

        $this->trace('读取数据库', $this->zoco->db->readTimes . '次');
        $this->trace('写入数据库', $this->zoco->db->writeTimes . '次');

        $includedFiles = get_included_files();

        $this->trace('加载文件', count($includedFiles));
        $this->trace('PHP执行', $this->showTime());

        $trace = array_merge($trace, $this->trace);

        /**
         * 调用Trace页面模板
         */
        echo <<<HTML
		<div id="think_page_trace" style="background:white;margin:6px;font-size:14px;border:1px dashed silver;padding:8px">
		<fieldset id="querybox" style="margin:5px;">
		<legend style="color:gray;font-weight:bold">页面Trace信息</legend>
		<div style="overflow:auto;height:300px;text-align:left;">
HTML;
        foreach ($trace as $key => $value) {
            echo $key . ' : ' . $value . '<br/>';
        }

        /**
         * 详细信息
         * 输出包含的文件
         */
        if ($detail) {
            echo '加载的文件<br/>';
            foreach ($includedFiles as $file) {
                echo 'require ' . $file . '<br/>';
            }
        }
        echo '</div></fieldset></div>';
    }

    /**
     * trace变量赋值
     *
     * @param        $title
     * @param string $value
     */
    public function trace($title, $value = '') {
        if (is_array($title)) {
            $this->trace = array_merge($this->trace, $title);
        } else {
            $this->trace[$title] = $value;
        }
    }
}