<?php

namespace Zoco;

/**
 * Class URL
 *
 * @package Zoco
 */
class URL {
    const CACHE_LIFE_TIME  = 300;
    const CACHE_KEY_PREFIX = 'zoco_url_cache_';

    /**
     * @var
     */
    public $config;

    /**
     * @var
     */
    public $info;

    /**
     * @var Client\CURL
     */
    protected $curl;

    /**
     * @param $config
     * @throws \Exception
     */
    public function __construct($config) {
        if (empty($config) || !isset($config['url'])) {
            throw new \Exception('require url.');
        }

        if (!empty($config['cache'])) {
            if (empty($config['lifetime'])) {
                $config['lifetime'] = self::CACHE_LIFE_TIME;
            }
        }
        $this->curl   = new Client\CURL(!empty($config['debug']));
        $this->config = $config;

        if (!empty($config['setting'])) {
            foreach ($config['setting'] as $key => $value) {
                call_user_func_array(array($this->curl, 'set' . ucfirst($key)), $value);
            }
        }
    }

    /**
     * @param null   $params
     * @param string $cacheId
     * @return bool|mixed
     */
    public function get($params = null, $cacheId = '') {
        $cacheKey = '';
        $url      = $this->config['url'];
        if ($params) {
            if (Tool::endChar($url) == '&') {
                $url .= http_build_query($params);
            } else {
                $url .= '?' / http_build_query($params);
            }
        }

        if (!empty($this->config['cache'])) {
            if (empty($cacheId)) {
                $cacheId = md5($url);
            }
            $cacheKey = self::CACHE_KEY_PREFIX . $cacheId;
            $result   = \Zoco::$php->cache->get($cacheKey);
            if ($result) {
                return $result;
            }
        }

        $result = $this->curl->get($url);
        if ($result && $this->curl->info['http_code'] == 200) {
            if (!empty($this->config['json'])) {
                $result = json_decode($result, true);
            } else {
                if (!empty($this->config['serialize'])) {
                    $result = unserialize($result);
                }
            }

            if (!empty($this->config['cache'])) {
                \Zoco::$php->cache->set($cacheKey, $result, $this->config['lifetime']);
            }
        }
        $this->info = $this->curl->info;

        return $result;
    }

    /**
     * @param $data
     */
    public function post($data) {
        $this->curl->post($this->config['url'], $data);
    }
}