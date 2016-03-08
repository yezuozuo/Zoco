<?php

namespace Zoco;

/**
 * 模型加载器
 * Class ModelLoader
 *
 * @package Zoco
 */
class ModelLoader {
    /**
     * @var array
     */
    protected $_models = array();

    /**
     * @var array
     */
    protected $_tables = array();
    /**
     * @var /Zoco
     */
    private $zoco = null;

    /**
     * @param $zoco
     */
    public function __construct($zoco) {
        $this->zoco = $zoco;
    }

    /**
     * 获取一个类
     *
     * @param $modelName
     * @return mixed
     * @throws Error
     */
    public function __get($modelName) {
        return $this->loadModel($modelName, 'master');
    }

    /**
     * 加载一个类
     *
     * @param $modelName
     * @return mixed
     * @throws Error
     */
    public function loadModel($modelName, $dbKey = 'master') {
        if (isset($this->_models[$dbKey][$modelName])) {
            return $this->_models[$dbKey][$modelName];
        } else {
            $modelFile = \Zoco::$appPath . '/models/' . $modelName . '.php';
            if (!is_file($modelFile)) {
                echo Error::info('MVC ERROR', "The model [<b>$modelName</b>] does not exist.");
                exit;
            }

            /**
             * 模型所在的类的命名空间
             */
            $modelClass = '\\App\\Model\\' . $modelName;
            require_once $modelFile;

            /**
             * 新建一个类
             */
            $this->_models[$dbKey][$modelName] = new $modelClass($this->zoco, $dbKey);

            return $this->_models[$dbKey][$modelName];
        }
    }

    /**
     * 多DB实例
     *
     * @param $modelName
     * @param $params
     * @return mixed
     */
    public function __call($modelName, $params) {
        $dbKey = count($params) < 1 ? 'master' : $params[0];

        return $this->loadModel($modelName, $dbKey);
    }

    /**
     * 加载表
     *
*@param $table_name
     * @param $db_key
     * @return Model
     */
    public function loadTable($tableName, $dbKey = 'master') {
        if (isset($this->_tables[$dbKey][$tableName])) {
            return $this->_tables[$dbKey][$tableName];
        } else {
            $model        = new Model($this->zoco, $dbKey);
            $model->table = $tableName;
            $this->_tables[$dbKey][$tableName] = $model;

            return $model;
        }
    }
}