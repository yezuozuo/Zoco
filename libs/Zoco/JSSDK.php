<?php

namespace Zoco;

/**
 * Class JSSDK
 *
 * @package Zoco
 */
class JSSDK extends Object {

    /**
     * @var ModelLoader
     */
    protected $model;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var
     */
    private $appId;
    /**
     * @var
     */
    private $appSecret;

    /**
     * @param \Zoco $zoco
     */
    public function __construct(\Zoco $zoco) {
        $this->zoco   = $zoco;
        $this->model  = $zoco->model;
        $this->config = $zoco->config;
    }

    /**
     * @param $appId
     * @param $appSecret
     */
    public function setConfig($appId, $appSecret) {
        $this->appId     = $appId;
        $this->appSecret = $appSecret;
    }

    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();

        /**
         * 注意 URL 一定要动态获取，不能 hardCode
         */
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url      = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr  = $this->createNonceStr();

        /**
         * 这里参数的顺序要按照 key 值 ASCII 码升序排序
         */
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );

        return $signPackage;
    }

    /**
     * @return mixed
     */
    private function getJsApiTicket() {
        $data = $this->getData('wx_ticket');
        if (($data == false) || ($data['expire'] < time())) {
            $accessToken = $this->getAccessToken();
            /**
             * 如果是企业号用以下 URL 获取 ticket
             * $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
             */
            $url    = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res    = json_decode($this->httpGet($url));
            $ticket = $res->ticket;
            if ($ticket) {
                $tmp            = $data;
                $data['expire'] = time() + 7000;
                $data['ticket'] = $ticket;
                if ($tmp != false) {
                    $this->setData('wx_ticket', $data);
                } else {
                    $this->initData('wx_ticket', $data);
                }
            }
        } else {
            $ticket = $data['ticket'];
        }

        return $ticket;
    }

    /**
     * @param $table
     * @return mixed
     */
    private function getData($table) {
        $this->selectDb = new \Zoco\SelectDB($this->db);
        $this->selectDb->select('*');
        $this->selectDb->from($table);
        $res = $this->selectDb->getAll();

        if (!empty($res)) {
            return $res[0];
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getAccessToken() {
        $data = $this->getData('wx_token');
        if (($data == false) || ($data['expire'] < time())) {
            /**
             * 如果是企业号用以下URL获取access_token
             * $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
             */
            $url   = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res   = json_decode($this->httpGet($url));
            $token = $res->access_token;
            if ($token) {
                $tmp            = $data;
                $data['expire'] = time() + 7000;
                $data['token']  = $token;
                if ($tmp != false) {
                    $this->setData('wx_token', $data);
                } else {
                    $this->initData('wx_token', $data);
                }
            }
        } else {
            $token = $data['token'];
        }

        return $token;
    }

    /**
     * @param $url
     * @return mixed
     */
    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        /**
         * 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
         * 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
         */
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    /**
     * @param $table
     * @param $content
     */
    private function setData($table, $content) {
        if ($table == 'wx_ticket') {
            $arr = array(
                'ticket' => $content['ticket'],
            );
        } else {
            $arr = array(
                'token' => $content['token'],
            );
        }

        $arr['expire'] = $content['expire'];

        $this->db->update(1, $arr, $table);
    }

    /**
     * @param $table
     * @param $content
     */
    private function initData($table, $content) {
        if ($table == 'wx_ticket') {
            $arr = array(
                'ticket' => $content['ticket'],
            );
        } else {
            $arr = array(
                'token' => $content['token'],
            );
        }

        $arr['expire'] = $content['expire'];

        $this->db->insert($arr, $table);
    }

    /**
     * @param int $length
     * @return string
     */
    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str   = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return $str;
    }
}