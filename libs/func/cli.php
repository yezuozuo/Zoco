<?php

/**
 * 导入所有的controller
 *
 * @param $appsPath
 * @return bool
 */
function importAllController($appsPath) {
    $dir = dir($appsPath . '/controllers');
    if (empty($dir)) {
        return false;
    }
    while ($file = $dir->read()) {
        $name = basename($file, '.php');
        /**
         * 不符合命名规则
         */
        if (!preg_match('/^[a-z0-9_]+$/i', $name)) {
            continue;
        }
        /**
         * 首字母大写的controller为基类控制器，不直接提供响应
         */
        if (ord($name{0}) > 64 && ord($name{0}) < 91) {
            continue;
        }

        $path = $appsPath . '/controllers' . '/' . $file;
        importController($name, $path);
    }
    $dir->close();

    return true;
}

/**
 * @param $name
 * @param $path
 */
function importController($name, $path) {
    global $php;
    require($path);
    $php->env['controllers'][$name] = array(
        'path' => $path,
        'time' => time(),
    );
}

/**
 * 检查是否加载了某个扩展
 *
 * @param $extName
 * @return bool
 */
function requireExt($extName) {
    if (extension_loaded($extName)) {
        return true;
    } else {
        echo \Zoco\Error::info('error', 'require php extension <b>' . $extName . '</b>');
        exit();
    }
}

/**
 * 导入所有model
 *
 * @param $appsPath
 */
function importAllModel($appsPath) {
    global $php;
    $dir = dir($appsPath . '/models');

    while ($file = $dir->read()) {
        $name = basename($file, '.model.php');

        /**
         * 不符合命名规则
         */
        if (!preg_match('/^[a-z0-9_]+$/i', $name)) {
            continue;
        }

        /**
         * 首字母大写的model为基类模型，不直接提供响应
         */
        if (ord($name{0}) > 64 && ord($name{0}) < 91) {
            continue;
        }

        $path = $appsPath . '/models' . '/' . $file;
        require($path);
        $php->env['models'][$name] = $path;
    }
    $dir->close();
}

/**
 * 创建控制器类的文件
 *
 * @param string     $name controller的名字
 * @param bool|false $hello
 */
function createControllerClass($name, $hello = false) {
    $content = "";
    $content .= "<?php\n";
    $content .= "namespace App\\Controller;\n";
    $content .= "use Zoco;\n";
    $content .= "use App;\n";
    $content .= "class {$name} extends \\Zoco\\Controller\n";
    $content .= "{\n";
    //$content .= "   function __construct(\$zoco)\n";
    //$content .= "   {\n";
    //$content .= "       parent:__construct(\$zoco)\n;";
    //$content .= "   }\n";

    /**
     * 增加一个hello function
     */
    if ($hello) {
        $content .= "   function index(\$zoco)\n";
        $content .= "   {\n";
        $content .= "        echo 'hello world.';\n";
        $content .= "   }\n";
    }
    $content .= "}";
    file_put_contents(WEBPATH . '/apps/controllers/' . $name . '.php', $content);
}

/**
 * 创建模型类的文件
 *
 * @param        $name
 * @param string $table
 */
function createModelClass($name, $table = '') {
    $content = "";
    $content .= "<?php\n";
    $content .= "namespace App\\Model;\n";
    $content .= "use Zoco;\n";
    $content .= "class {$name} extends Zoco\\Model\n";
    $content .= "{\n";
    $content .= "   var \$table = '{$table}';\n";
    $content .= "}";
    file_put_contents(WEBPATH . '/apps/models/' . $name . '.php', $content);
}

/**
 * 创建必须的目录
 */
function createRequireDir() {
    /**
     * /apps
     */
    if (!is_dir(WEBPATH . '/apps')) {
        mkdir(WEBPATH . '/apps', 0755);
    }
    if (!is_dir(WEBPATH . '/apps/classes')) {
        mkdir(WEBPATH . '/apps/classes', 0755);
    }
    if (!is_dir(WEBPATH . '/apps/configs')) {
        mkdir(WEBPATH . '/apps/configs', 0755);
    }
    if (!is_dir(WEBPATH . '/apps/controllers')) {
        mkdir(WEBPATH . '/apps/controllers', 0755);
    }
    if (!is_dir(WEBPATH . '/apps/factory')) {
        mkdir(WEBPATH . '/apps/factory', 0755);
    }
    if (!is_dir(WEBPATH . '/apps/models')) {
        mkdir(WEBPATH . '/apps/models', 0755);
    }
    if (is_dir(WEBPATH . '/apps/static')) {
        mkdir(WEBPATH . '/apps/static', 0755);
    }
    if (!is_dir(WEBPATH . '/apps/templates')) {
        mkdir(WEBPATH . '/apps/templates', 0755);
    }
    if (!is_dir(WEBPATH . '/apps/views')) {
        mkdir(WEBPATH . '/apps/views', 0755);
    }

    /**
     * /apps/static
     */
    if (is_dir(WEBPATH . '/apps/static/img')) {
        mkdir(WEBPATH . '/apps/static/img', 0755);
    }
    if (is_dir(WEBPATH . '/apps/static/css')) {
        mkdir(WEBPATH . '/apps/static/css', 0755);
    }
    if (is_dir(WEBPATH . '/apps/static/js')) {
        mkdir(WEBPATH . '/apps/static/js', 0755);
    }
    if (is_dir(WEBPATH . '/apps/static/font')) {
        mkdir(WEBPATH . '/apps/static/font', 0755);
    }

    /**
     * /data
     */
    if (is_dir(WEBPATH . '/data')) {
        mkdir(WEBPATH . '/data', 0755);
    }
    if (!is_dir(WEBPATH . '/data/cache')) {
        mkdir(WEBPATH . '/cache', 0755);
    }
    if (!is_dir(WEBPATH . '/data/excel')) {
        mkdir(WEBPATH . '/data/excel', 0755);
    }
    if (!is_dir(WEBPATH . '/data/logs')) {
        mkdir(WEBPATH . '/data/logs', 0755);
    }
    if (!is_dir(WEBPATH . '/data/markdown')) {
        mkdir(WEBPATH . '/data/markdown', 0755);
    }
    if (!is_dir(WEBPATH . '/data/spider')) {
        mkdir(WEBPATH . '/data/spider', 0755);
    }
    if (!is_dir(WEBPATH . '/data/upload')) {
        mkdir(WEBPATH . '/data/upload', 0755);
    }
    if (!is_dir(WEBPATH . '/data/zdb')) {
        mkdir(WEBPATH . '/data/zdb', 0755);
    }

    /**
     * /data/cache
     */
    if (!is_dir(WEBPATH . '/cache/pageCache')) {
        mkdir(WEBPATH . '/cache/pageCaches', 0755);
    }
    if (!is_dir(WEBPATH . '/cache/fileCache')) {
        mkdir(WEBPATH . '/cache/fileCaches', 0755);
    }
    if (!is_dir(WEBPATH . '/cache/templatesCache')) {
        mkdir(WEBPATH . '/cache/templatesCache', 0755);
    }

    /**
     * /data/spider
     */
    if (!is_dir(WEBPATH . '/spider/result')) {
        mkdir(WEBPATH . '/spider/result', 0755);
    }
}