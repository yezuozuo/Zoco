<?php

namespace Zoco;

/**
 * Class Factory
 *
 * @package Zoco
 * @method static getCache($key)
 */
class Factory {
    /**
     * @param $func
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    static public function __callStatic($func, $params) {
        $resourceId   = empty($params[0]) ? 'master' : $params[0];
        $resourceType = strtolower(substr($func, 3));
        if (empty(\Zoco::$php->config[$resourceType][$resourceId])) {
            throw new \Exception(__CLASS__ . ":resource[{$resourceType}/{$resourceId}] not found.");
        }
        $config = \Zoco::$php->config[$resourceType][$resourceId];
        $class  = '\\Zoco\\' . ucfirst($resourceType) . '\\' . $config['type'];

        return new $class($config);
    }
}