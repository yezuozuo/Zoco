<?php

namespace App\Controller;

use App;
use Zoco;

class Tool extends Zoco\Controller {
    public function __construct($zoco) {
        parent::__construct($zoco);
        Zoco::$php->session->start();
        Zoco\Auth::loginRequire();
    }

    public function debug() {
        echo $this->showTime();
        echo $this->showTrace();
        echo $this->showTrace(true);
    }

    /**
     * 发送邮件
     */
    public function sendEmail() {
        if (empty($_POST) || !isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['message'])) {
            return;
        }
        $sendMsg = "用户名称：{$_POST['name']}\n用户联系方式：{$_POST['email']}\n反馈信息：{$_POST['message']}\n";
        error_log($sendMsg, 1, 'justyehao@qq.com', "From: report@zoco.com");
        $this->contact();
    }

    /**
     * 联系人
     */
    public function contact() {
        $this->assign('contact', array(
            array(
                'name' => '叶左左',
                'email' => 'justyehao@qq.com',
            )
        ));
        $title = 'zoco';
        $copyright = 'zoco';
        $this->assign('title', $title);
        $this->assign('copyright', $copyright);
        $this->display('head.php');
        $this->display('contact.php');
        $this->display('foot.php');
    }

    /**
     * 上传普通图片
     *
     * @return string
     */
    public function upload() {
        if ($_FILES) {
            $this->upload->thumbWidth = 136;
            $this->upload->thumbHeight = 136;
            $this->upload->thumbQulitity = 100;

            //自动压缩图片
            $this->upload->maxWidth = 600;
            $this->upload->maxHeight = 600;
            $this->upload->maxQulitity = 90;

            $this->upload->shardType = 'user';
            $upPic = $this->upload->save('file');
            if (empty($upPic)) {
                return false;
            }

            return $upPic['url'];
        } else {
            return false;
        }
    }

    /**
     * 将excel显示为页面
     *
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function displayExcel() {
        include WEBPATH . '/libs/module/phpexecl/PHPExcel.php';
        $filename = WEBPATH . '/data/excel/20151124/20160112152508.xlsx';
        $objReader = new \PHPExcel_Reader_Excel2007();
        $objWriteHTML = new \PHPExcel_Writer_HTML($objReader->load($filename));
        $objWriteHTML->save("php://output");
    }

    /**
     * 产生二维码
     * 需要开启session
     * Zoco::$php->session->start();
     */
    public function vcode() {
        header('Content-Type: image/jpeg');
        Zoco\Image::verifyCodeGD();
    }
}