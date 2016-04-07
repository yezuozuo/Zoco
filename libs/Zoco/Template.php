<?php

namespace Zoco;

require(LIBPATH . "/module/smarty/Smarty.class.php");

/**
 * Smarty模板系统封装类
 * 提供模板引擎类，可以访问到MVC结构，增加了pageCache静态页面缓存的功能
 * 由于需要继承Smarty类，所以一些变量的名字会和Smarty中的格式一致
 * Class Template
 *
 * @package Zoco
 * @method clearAllAssign
 */
class Template extends \Smarty {
    /**
     * @var bool
     */
    public $ifPageCache = false;

    /**
     * @var int
     */
    public $cacheLifeTime = 3600;

    public function __construct() {
        $this->__init();
        parent::Smarty();
        $this->compile_dir     = WEBPATH . '/data/cache/templatesCache';
        $this->config_dir      = WEBPATH . '/apps/configs';
        $this->cache_dir       = WEBPATH . '/data/cache/templatesCache';
        $this->left_delimiter  = '{{';
        $this->right_delimiter = '}}';
    }

    /**
     * 初始化
     */
    public function __init() {
        $this->clear_all_assign();
    }

    /**
     * 设置模板的目录
     *
     * @param $dir
     */
    public function setTemplateDir($dir) {
        $this->template_dir = WEBPATH . '/' . $dir;
    }

    /**
     * 设置缓存
     *
     * @param int $time
     */
    public function setCache($time = 3600) {
        $this->caching       = 1;
        $this->cacheLifeTime = $time;
    }

    /**
     * 缓存当前页面
     *
     * @return bool
     */
    public function pageCache() {
        $pageCache = new PageCache($this->cacheLifeTime);
        if ($pageCache->isCached()) {
            $pageCache->load();
        } else {
            return false;
        }

        return true;
    }

    /**
     * 传引用到模板中
     *
     * @param $key
     * @param $value
     */
    public function ref($key, &$value) {
        $this->_tpl_vars[$key] = &$value;
    }

    /**
     * 使用模板进行展示
     *
     * @param null $template
     * @param null $cacheId
     * @param null $compileId
     */
    public function display($template = null, $cacheId = null, $compileId = null) {
        if ($template == null) {
            global $php;
            $template = $php->env['mvc']['controller'] . '_' . $php->env['mvc']['view'] . '.html';
        }
        if ($this->ifPageCache) {
            $pageCache = new PageCache($this->cacheLifeTime);
            if (!$pageCache->isCached()) {
                $pageCache->create(parent::fetch($template, $cacheId, $compileId));
            }
            $pageCache->load();
        } else {
            parent::display($template, $cacheId, $compileId);
        }
    }

    /**
     * 生成静态页面
     *
     * @param        $template
     * @param        $filename
     * @param string $path
     * @return bool
     */
    public function outHtml($template, $filename, $path = '') {
        if ($path == '') {
            $path = dirname($filename);
            echo $path;
            $filename = basename($filename);
        }
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $content = $this->fetch($template);
        file_put_contents($path . '/' . $filename, $content);

        return true;
    }

    /**
     * 传递大规模数据
     *
     * @param $data
     */
    public function push($data) {
        foreach ($data as $key => $value) {
            $this->assign($key, $value);
        }
    }
}