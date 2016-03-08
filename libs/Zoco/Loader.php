<?php

namespace Zoco;

/**
 * 库加载器
 * Class Loader
 *
 * @package Zoco
 */
class Loader {
    /**
     * zoco的实例
     *
     * @var \Zoco
     */
    static $zoco;
    /**
     * 储存model
     *
     * @var array
     */
    static $objects;
    /**
     * 命名空间的路径
     *
     * @var array
     */
    protected static $namespaces;

    /**
     * @param $zoco
     */
    public function __construct($zoco) {
        self::$zoco    = $zoco;
        self::$objects = array(
            'model'  => new \ArrayObject,
            'object' => new \ArrayObject,
        );
    }

    /**
     * 加载一个模型对象
     *
     * @param $modelName string 模型名称
     * @return $modelObject 模型对象
     */
    static public function loadModel($modelName) {
        /**
         * 如果请求的模型名称已经在列表中直接返回，否则加载模型
         */
        if (isset(self::$objects['model'][$modelName])) {
            return self::$objects['model'][$modelName];
        } else {
            /**
             * 在apps目录下寻找对应的模型文件
             */
            $modelFile = APPSPATH . '/models/' . $modelName . '.model.php';
            if (!file_exists($modelFile)) {
                echo Error::info('MVC ERROR', "不存在的模型,<b>$modelName</b>");
                exit();
            }

            /**
             * 模型存在的话
             */
            require $modelFile;

            /**
             * 将model加入到object的列表中
             */
            self::$objects['model'][$modelName] = new $modelName(self::$zoco);

            return self::$objects['model'][$modelName];
        }
    }

    /**
     * 自动载入类
     *
     * @param $class
     */
    static public function autoload($class) {
        /**
         * 对类进行拆分
         */
        $root = explode('\\', trim($class, '\\'), 2);

        /**
         * 载入类
         * $root[0]必须是根命名空间
         */
        if (count($root) > 1 && isset(self::$namespaces[$root[0]])) {
            include self::$namespaces[$root[0]] . '/' . str_replace('\\', '/', $root[1] . '.php');
        }
    }

    /**
     * 设置根命名空间
     *
     * @param $root
     * @param $path
     */
    static public function addNameSpace($root, $path) {
        self::$namespaces[$root] = $path;
    }
}