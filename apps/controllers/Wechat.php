<?php

namespace App\Controller;

use App;
use Zoco;

class Wechat extends Zoco\Controller {

    public function __construct($zoco) {
        parent::__construct($zoco);
        $this->session->start();
    }

    /**
     * 主页
     */
    public function index() {
        $code     = $_GET['code'];
        $userInfo = $this->getUserInfo($code);
        var_dump($userInfo);
    }

    /**
     * 获取用户基本信息
     *
     * @param $code
     * @return mixed
     */
    private function getUserInfo($code) {
        $appId     = "wxa38d5e85c3eedbef";
        $appSecret = "f26be67324e1a04997f736fc3a776e8f";

        $accessTokenUrl   = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appId&secret=$appSecret&code=$code&grant_type=authorization_code";
        $accessTokenJson  = $this->httpsRequest($accessTokenUrl);
        $accessTokenArray = json_decode($accessTokenJson, true);
        $openid           = $accessTokenArray['openid'];

        $newAccessTokenUrl   = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$appSecret";
        $newAccessTokenJson  = $this->httpsRequest($newAccessTokenUrl);
        $newAccessTokenArray = json_decode($newAccessTokenJson, true);
        $newAccessToken      = $newAccessTokenArray['access_token'];

        $userInfoUrl   = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$newAccessToken&openid=$openid";
        $userInfoJson  = $this->httpsRequest($userInfoUrl);
        $userInfoArray = json_decode($userInfoJson, true);

        return $userInfoArray;
    }


    /**
     * http 的请求
     *
     * @param $url
     * @return mixed|string
     */
    private function httpsRequest($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'ERROR ' . curl_error($curl);
        }
        curl_close($curl);

        return $data;
    }

    /**
     * 上传微信图片
     *
     * @return string
     */
    public function upload() {
        $serverId = $_POST['serverId'];
        $jssdk    = new Zoco\JSSDK($this->zoco);
        $jssdk->setConfig("wxa38d5e85c3eedbef", "f26be67324e1a04997f736fc3a776e8f");
        $accessToken = $jssdk->getAccessToken();

        $targetName = WEBPATH . '/data/upload/represent/' . date('YmdHis') . '.jpg';
        $returnName = '/xiaohong/app/data/upload/represent/' . date('YmdHis') . '.jpg';
        $ch         = curl_init("http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$accessToken}&media_id={$serverId}");
        $fp         = fopen($targetName, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        $logo = WEBPATH . '/data/upload/represent/111.png';
        $type = 9;

        Zoco\Image::waterMark2($targetName, $type, $logo);

        return json_encode($returnName);
    }
}