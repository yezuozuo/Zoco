<?php

namespace Zoco;

/**
 * 图像处理类
 * Class Image
 *
 * @package Zoco
 */
class Image {
    /**
     * 剪裁图像
     *
     * @param      $sourcePic
     * @param      $destPic
     * @param      $width
     * @param null $height
     * @param int  $quality
     * @return bool
     */
    static public function cut($sourcePic, $destPic, $width, $height = null, $quality = 100) {
        $im = imagecreatefromjpeg($sourcePic);

        if (imagesx($im) > $width) {
            $oldWidth  = imagesx($im);
            $oldHeight = imagesy($im);

            /**
             * 按比例缩放
             */
            if ($height === null) {
                $scale  = $oldWidth / $oldHeight;
                $height = $width * $scale;
            }

            $newIm = imagecreatetruecolor($width, $height);
            imagecopyresampled($newIm, $im, 0, 0, 0, 0, $width, $height, $oldWidth, $oldHeight);
            imagejpeg($newIm, $destPic, $quality);
            imagedestroy($im);

            return true;
        } else {
            if ($sourcePic != $destPic) {
                return copy($sourcePic, $destPic);
            }
        }

        return false;
    }

    /**
     * 压缩图像
     *
     * @param           $sourcePic
     * @param           $destPic
     * @param           $maxWidth
     * @param null      $maxHeight
     * @param int       $quality
     * @param bool|true $copy
     */
    static public function thumbnail($sourcePic, $destPic, $maxWidth, $maxHeight = null, $quality = 100, $copy = true) {
        $im        = self::readFile($sourcePic);
        $oldWidth  = imagesx($im);
        $oldHeight = imagesy($im);

        if ($maxHeight == null) {
            $maxHeight = $maxWidth;
        }

        if ($oldWidth > $maxWidth || $oldHeight > $maxHeight) {
            $scaleWidthToHeight = $oldWidth / $oldHeight;
            $scaleHeightToWidth = $oldHeight / $oldWidth;

            if ($scaleWidthToHeight > $scaleHeightToWidth) {
                $width  = $maxWidth;
                $height = $width * $scaleHeightToWidth;
            } else {
                $height = $maxHeight;
                $width  = $height * $scaleWidthToHeight;
            }

            $newIm = imagecreatetruecolor($width, $height);
            imagecopyresampled($newIm, $im, 0, 0, 0, 0, $width, $height, $oldWidth, $oldHeight);
            imagejpeg($newIm, $destPic, $quality);
            imagedestroy($im);
        } else {
            if ($sourcePic != $destPic && $copy) {
                copy($sourcePic, $destPic);
            }
        }
    }

    /**
     * 读取图像
     *
     * @param $pic
     * @return bool|resource|string
     */
    static public function readFile($pic) {
        $imageInfo = getimagesize($pic);
        $im        = '';
        if ($imageInfo['mime'] == 'image/jpeg' || $imageInfo['mime'] == 'image/gif' || $imageInfo['mime'] == 'image/png') {
            switch ($imageInfo['mime']) {
                case 'image/jpeg':
                    $im = imagecreatefromjpeg($pic);
                    break;
                case 'image/gif':
                    $im = imagecreatefromgif($pic);
                    break;
                case 'image/png':
                    $im = imagecreatefrompng($pic);
                    break;
            }

            return $im;
        }

        return false;
    }

    /**
     * 加给图片加水印
     *
     * @param string $groundImage 要加水印的地址
     * @param int    $waterPos    水印位置
     * @param string $waterImage  水印图片的地址
     * @param string $waterText   文本文字
     * @param int    $textFon     文本大小
     * @param string $textColor   文字颜色
     * @param string $minWidth    小于此值不加水印
     * @param string $minHeight   小于此值不加水印
     * @param float  $alpha       透明度
     */
    static public function waterMark(
        $groundImage,
        $waterPos = 0,
        $waterImage = '',
        $waterText = '',
        $textFont = 15,
        $textColor = '#FFFFFF',
        $minWidth = '100',
        $minHeight = '100',
        $alpha = 0.9
    ) {
        $water    = null;
        $bg       = null;
        $bgHeight = $bgWidth = $waterWidth = $waterHeight = 0;

        /**
         * 获取背景图的高
         */
        if (is_file($groundImage) && !empty($groundImage)) {
            $bg = new \Imagick();
            $bg->readImage($groundImage);
            $bgHeight = $bg->getImageHeight();
            $bgWidth  = $bg->getImageWidth();
        }

        /**
         * 获取水印图的高，宽
         */
        if (is_file($waterImage) && !empty($waterImage)) {
            $water       = new \Imagick();
            $waterHeight = $water->getImageHeight();
            $waterWidth  = $water->getImageWidth();
        }

        /**
         * 如果背景图的高宽小于水印图的高宽或指定的高和宽则不加水印
         */
        if ($bgHeight < $minHeight || $bgWidth < $minWidth || $bgHeight < $waterHeight || $bgWidth < $waterWidth) {
            return false;
        } else {
            $isWaterImg = true;
        }

        /**
         * 加水印
         */
        if ($isWaterImg) {
            $waterDraw = new \ImagickDraw();

            /**
             * 加图片水印
             */
            if (is_file($waterImage)) {
                $water->setImageOpacity($alpha);
                $waterDraw->setGravity($waterPos);
                $waterDraw->composite($water->getImageCompose(), 0, 0, 50, 0, $water);
                $bg->drawImage($waterDraw);

                if (!$bg->writeImage($groundImage)) {
                    return false;
                }
            } else {
                $waterDraw->setFontSize($textFont);
                $waterDraw->setFillColor($textColor);
                $waterDraw->setGravity($waterPos);
                $waterDraw->setFillAlpha($alpha);
                $waterDraw->annotation(0, 0, $waterText);
                $bg->drawImage($waterDraw);

                if (!$bg->writeImage($groundImage)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * PHP图片水印 (水印支持图片或文字)
     * 注意：Support GD 2.0，Support FreeType、GIF Read、GIF Create、JPG 、PNG
     *      $waterImage 和 $waterText 最好不要同时使用，选其中之一即可，优先使用 $waterImage。
     *      当$waterImage有效时，参数$waterString、$stringFont、$stringColor均不生效。
     *      加水印后的图片的文件名和 $groundImage 一样。
     *
     * @param string $groundImage 要加水印的地址
     * @param int    $waterPos    水印位置
     * @param string $waterImage  水印图片的地址
     * @param string $waterText   文本文字
     * @param int    $textFon     文本大小
     * @param string $textColor   文字颜色
     * @param string $minWidth    小于此值不加水印
     * @param string $minHeight   小于此值不加水印
     */
    static public function waterMark2(
        $groundImage,
        $waterPos = 0,
        $waterImage = '',
        $waterText = '',
        $textFont = 5,
        $textColor = '#FF0000',
        $minWidth = '100',
        $minHeight = '100'
    ) {
        $isWaterImage = false;
        $formatMsg    = '暂不支持该文件格式，请用图片处理软件将图片转换为GIF、JPG、PNG格式。';

        $waterHeight = null;
        $waterWidth  = null;
        $waterIm     = null;

        /**
         * 读取水印文件
         */
        if (!empty($waterImage) && file_exists($waterImage)) {
            $isWaterImage = true;
            $waterInfo    = getimagesize($waterImage);
            $waterHeight  = $waterInfo[1];
            $waterWidth   = $waterInfo[0];

            /**
             * 取得水印图片的格式
             */
            switch ($waterInfo[2]) {
                case 1:
                    $waterIm = imagecreatefromgif($waterImage);
                    break;
                case 2:
                    $waterIm = imagecreatefromjpeg($waterImage);
                    break;
                case 3:
                    $waterIm = imagecreatefrompng($waterImage);
                    break;
                default:
                    die($formatMsg);
            }
        }

        /**
         * 读取背景图片
         */
        if (!empty($groundImage) && file_exists($groundImage)) {
            $groundInfo   = getimagesize($groundImage);
            $groundWidth  = $groundInfo[0];
            $groundHeight = $groundInfo[1];

            switch ($groundInfo[2]) {
                case 1:
                    $groundIm = imagecreatefromgif($groundImage);
                    break;
                case 2:
                    $groundIm = imagecreatefromjpeg($groundImage);
                    break;
                case 3:
                    $groundIm = imagecreatefrompng($groundImage);
                    break;
                default:
                    die($formatMsg);
            }
        } else {
            die("需要加水印的图片不存在");
        }

        /**
         * 水印位置
         */
        /**
         * 图片水印
         */
        if ($isWaterImage) {
            $width  = $waterWidth;
            $height = $waterHeight;
            $label  = '图片的';
        } /**
         * 文字水印
         */
        else {
            /**
             * 取得使用 TrueType 字体的文本的范围
             */
            $temp   = imagettfbbox(ceil($textFont * 2.5), 0, WEBPATH . '/static/font/Jura.ttf', $waterText);
            $width  = $temp[2] - $temp[6];
            $height = $temp[3] - $temp[7];
            unset($temp);
            $label = '文字区域';
        }

        /**
         * 如果背景图的高宽小于水印图的高宽或指定的高和宽则不加水印
         */
        if (($groundWidth < $width) || ($groundHeight < $height) || ($groundWidth < $minWidth) || ($groundHeight < $minHeight)) {
            echo "需要加水印的图片的长度或宽度比水印" . $label . "还小，无法生成水印！";

            return false;
        }

        switch ($waterPos) {
            /**
             * 随机
             */
            case 0:
                $posX = rand(0, ($groundWidth - $width));
                $posY = rand(0, ($groundHeight - $height));
                break;
            /**
             * 1为顶端居左
             */
            case 1:
                $posX = 0;
                $posY = 0;
                break;
            /**
             * 2为顶端居中
             */
            case 2:
                $posX = ($groundWidth - $width) / 2;
                $posY = 0;
                break;
            /**
             * 3为顶端居右
             */
            case 3:
                $posX = $groundWidth - $width;
                $posY = 0;
                break;
            /**
             * 4为中部居左
             */
            case 4:
                $posX = 0;
                $posY = ($groundHeight - $height) / 2;
                break;
            /**
             * 5为中部居中
             */
            case 5:
                $posX = ($groundWidth - $width) / 2;
                $posY = ($groundHeight - $height) / 2;
                break;
            /**
             * 6为中部居右
             */
            case 6:
                $posX = $groundWidth - $width;
                $posY = ($groundHeight - $height) / 2;
                break;
            /**
             * 7为底部居左
             */
            case 7:
                $posX = 0;
                $posY = $groundHeight - $height;
                break;
            /**
             * 8为底部居中
             */
            case 8:
                $posX = ($groundWidth - $width) / 2;
                $posY = $groundHeight - $height;
                break;
            /**
             * 7为底部居右
             */
            case 9:
                $posX = $groundWidth - $width;
                $posY = $groundHeight - $height;
                break;
            /**
             * 随机
             */
            default:
                $posX = rand(0, ($groundWidth - $width));
                $posY = rand(0, ($groundHeight - $height));
                break;
        }

        /**
         * 设定图像的混色模式
         */
        imagealphablending($groundIm, true);

        /**
         * 图片水印
         */
        if ($isWaterImage) {
            /**
             * 拷贝水印到目标
             */
            imagecopy($groundIm, $waterIm, $posX, $posY, 0, 0, $waterWidth, $waterHeight);
        } /**
         * 文字水印
         */
        else {
            if (!empty($textColor) && (strlen($textColor) == 7)) {
                $R = hexdec(substr($textColor, 1, 2));
                $G = hexdec(substr($textColor, 3, 2));
                $B = hexdec(substr($textColor, 5));
            } else {
                die("水印文字颜色格式不正确");
            }
            imagestring($groundIm, $textFont, $posX, $posY, $waterText, imagecolorallocate($groundIm, $R, $G, $B));
        }

        /**
         * 生成水印后的图片
         */
        @unlink($groundImage);

        switch ($groundInfo[2]) {
            case 1:
                imagegif($groundIm, $groundImage);
                break;
            case 2:
                imagejpeg($groundIm, $groundImage);
                break;
            case 3:
                imagepng($groundIm, $groundImage);
                break;
            default:
                die('produce pic error!');
        }

        /**
         * 释放内存
         */
        if (isset($waterInfo)) {
            unset($waterInfo);
        }
        if (isset($waterIm)) {
            imagedestroy($waterIm);
        }
        unset($groundInfo);
        imagedestroy($groundIm);

        return true;
    }

    /**
     * 生成验证码使用GD
     *
     * @param int $imgWidth
     * @param int $imgHeight
     */
    static public function verifyCodeGd($imgWidth = 80, $imgHeight = 30) {
        if (!function_exists('imagepng')) {
            die('verify code by GD failed,not install png module');
        }
        $authNum = '';
        srand(microtime() * 100000);
        for ($i = 0; $i < 4; $i++) {
            $authNum .= dechex(rand(0, 15));
        }

        $authNum = strtoupper($authNum);

        $_SESSION['authcode'] = $authNum;

        /**
         * 生成图片
         */
        $img = imagecreate($imgWidth, $imgHeight);

        /**
         * 图片底色，ImageColorAllocate第1次定义颜色PHP就认为是底色了
         */
        imagecolorallocate($img, 255, 255, 255);

        /**
         * 下面该生成雪花背景了，其实就是在图片上生成一些符号
         * 其实也不是雪花，就是生成＊号而已。
         * 为了使它们看起来"杂乱无章、5颜6色"，
         * 就得在1个1个生成它们的时候，让它们的位置、颜色，甚至大小都用随机数，
         * rand()或mt_rand都可以完成。
         */
        for ($i = 1; $i <= 128; $i++) {
            imagestring($img, 1, mt_rand(1, $imgWidth), mt_rand(1, $imgHeight), '*', imagecolorallocate($img, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255)));
        }
        for ($i = 0; $i < strlen($_SESSION['authcode']); $i++) {
            imagestring($img, mt_rand(8, 12), $i * $imgWidth / 4 + mt_rand(1, 8), mt_rand(1, $imgHeight / 4), $_SESSION['authcode'][$i], imagecolorallocate($img, mt_rand(0, 100), mt_rand(0, 150), mt_rand(0, 200)));
        }

        imagepng($img);
        imagedestroy($img);
    }

    static public function verifyCodeIm() {
        if (!class_exists('ImagickPixel')) {
            die('verify code by Im failed,not install imagickPixel');
        }
        if (empty($_SESSION)) {
            session_start();
        }
        $authNum = '';
        srand(microtime() * 100000);
        $_SESSION['authcode'] = '';

        /**
         * 背景对象
         */
        $bg = new \ImagickPixel();
        $bg->setColor('rgb(235,235,235)');

        /**
         * 画刷
         */
        $imageDraw = new \ImagickDraw();
        $imageDraw->setFont(WEBPATH . 'static/font/Jura.ttf');
        $imageDraw->setFontSize(24);
        $imageDraw->setFillColor($bg);

        /**
         * 生成数字和字母混合的验证码方法
         */
        $chars = "0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";
        $list  = explode(',', $chars);
        for ($i = 0; $i < 4; $i++) {
            $randnum = rand(0, 35);
            $authNum .= $list[$randnum];
        }

        $authNum              = strtoupper($authNum);
        $_SESSION['authcode'] = $authNum;

        $imagick = new \Imagick();
        $imagick->newImage(60, 24, $bg);
        $imagick->annotateImage($imageDraw, 4, 20, 0, $authNum);
        $imagick->drawImage($imageDraw);
        $imagick->setImageFormat('png');
        echo $imagick->getImageBlob();
    }

    /**
     * 生成验证码，使用TTF字体
     *
     * @param     $font
     * @param int $width
     * @param int $height
     */
    static public function verifyTTF($font, $width = 180, $height = 130) {
        if (empty($_SESSION)) {
            session_start();
        }
        $length = 4;
        $code   = '';
        for ($i = 0; $i < 4; $i++) {
            $code .= RandomKey::getChineseCharacter();
        }

        $width                = ($length * 45) > $width ? $length * 45 : $width;
        $_SESSION['authcode'] = md5($code);
        $im                   = imagecreatetruecolor($width, $height);
        $borderColor          = imagecolorallocate($im, 100, 100, 100);
        $bkColor              = imagecolorallocate($im, 250, 250, 250);

        imagefill($im, 0, 0, $bkColor);
        imagerectangle($im, 0, 0, $width - 1, $height - 1, $borderColor);

        /**
         * 干扰
         */
        for ($i = 0; $i < 15; $i++) {
            $fontColor = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagearc($im, mt_rand(-10, $width), mt_rand(-10, $height), mt_rand(30, 300), mt_rand(20, 200), 55, 44, $fontColor);
        }
        for ($i = 0; $i < 255; $i++) {
            $fontColor = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $fontColor);
        }
        for ($i = 0; $i < $length; $i++) {
            /**
             * 这样保证随机出来的颜色较深
             */
            $fontColor = imagecolorallocate($im, mt_rand(0, 120), mt_rand(0, 120), mt_rand(0, 120));
            $codex     = self::msubstr($code, $i, 1);
            imagettftext($im, mt_rand(16, 20), mt_rand(-60, 60), 40 * $i + 20, mt_rand(30, 35), $fontColor, $font, $codex);
        }
        /**
         * 告诉浏览器，下面的数据是图片
         */
        header("Content-type: image/png");
        imagepng($im);
        imagedestroy($im);
    }

    /**
     * @param           $str
     * @param int       $start
     * @param           $length
     * @param string    $charset
     * @param bool|true $suffix
     * @return string
     */
    static public function msubstr($str, $start = 0, $length, $charset = 'utf-8', $suffix = true) {
        if (function_exists('mb_substr')) {
            if ($suffix && strlen($str) > $length) {
                return mb_substr($str, $start, $length, $charset) . '...';
            } else {
                return mb_substr($str, $start, $length, $charset);
            }
        } else {
            if (function_exists('iconv_substr')) {
                if ($suffix && strlen($str) > $length) {
                    return iconv_substr($str, $start, $length, $charset) . '...';
                } else {
                    return iconv_substr($str, $start, $length, $charset);
                }
            }
        }

        $re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
        if ($suffix) {
            return $suffix . '...';
        }

        return $slice;
    }

    /**
     * @param        $fileName
     * @param string $insert
     * @return string
     */
    static public function thumbName($fileName, $insert = 'thumb') {
        $dirName  = dirname($fileName);
        $fileName = basename($fileName);
        $extend   = explode('.', $fileName);

        return $dirName . '/' . $extend[0] . '_' . $insert . '.' . $extend[count($extend) - 1];
    }

    /**
     * 获得任意大小图像，不足地方拉伸，不产生变形，不留下空白
     *
     * @param     $srcFile
     * @param     $dstFile
     * @param int $newWidth
     * @param int $newHeight
     */
    static public function zocoImageResize($srcFile, $dstFile, $newWidth, $newHeight = null) {
        $newWidth = intval($newWidth);
        if ($newHeight === null) {
            $newHeight = $newWidth;
        }

        if ($newHeight < 1 || $newWidth < 1) {
            echo "params width or height error !";
            exit();
        }

        if (!file_exists($srcFile)) {
            echo $srcFile . " is not exists !";
            exit();
        }

        /**
         * 图像类型
         */
        $type        = exif_imagetype($srcFile);
        $supportType = array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF);

        if (!in_array($type, $supportType, true)) {
            echo "this type of image does not support! only support jpg , gif or png";
            exit();
        }

        /**
         * Load image
         */
        switch ($type) {
            case IMAGETYPE_JPEG :
                $srcImg = imagecreatefromjpeg($srcFile);
                break;
            case IMAGETYPE_PNG :
                $srcImg = imagecreatefrompng($srcFile);
                break;
            case IMAGETYPE_GIF :
                $srcImg = imagecreatefromgif($srcFile);
                break;
            default:
                echo "Load image error!";
                exit();
        }

        $oldWidth    = imagesx($srcImg);
        $oldHeight   = imagesy($srcImg);
        $ratioWidth  = 1.0 * $newWidth / $oldWidth;
        $ratioHeight = 1.0 * $newHeight / $oldHeight;

        /**
         * 生成的图像的高宽比原来的都小，或都大，原则是取大比例放大，取大比例缩小（缩小的比例就比较小了）
         */
        if (($ratioWidth < 1 && $ratioHeight < 1) || ($ratioWidth > 1 && $ratioHeight > 1)) {
            if ($ratioWidth < $ratioHeight) {
                /**
                 * 情况一，宽度的比例比高度方向的小，按照高度的比例标准来裁剪或放大
                 */
                $ratio = $ratioHeight;
            } else {
                $ratio = $ratioWidth;
            }
            /**
             * 定义一个中间的临时图像，该图像的宽高比 正好满足目标要求
             */
            $interWidth  = (int)($newWidth / $ratio);
            $interHeight = (int)($newHeight / $ratio);
            $interImg    = imagecreatetruecolor($interWidth, $interHeight);
            imagecopy($interImg, $srcImg, 0, 0, 0, 0, $interWidth, $interHeight);
            /**
             * 生成一个以最大边长度为大小的是目标图像$ratio比例的临时图像
             * 定义一个新的图像
             */
            $newImg = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImg, $interImg, 0, 0, 0, 0, $newWidth, $newHeight, $interWidth, $interHeight);

            /**
             * 存储图像
             */
            switch ($type) {
                case IMAGETYPE_JPEG :
                    imagejpeg($newImg, $dstFile, 100);
                    break;
                case IMAGETYPE_PNG :
                    imagepng($newImg, $dstFile, 0);
                    break;
                case IMAGETYPE_GIF :
                    imagegif($newImg, $dstFile);
                    break;
                default:
                    break;
            }
        } /**
         * 目标图像 的一个边大于原图，一个边小于原图 ，先放大平普图像，然后裁剪
         */
        else {
            /**
             * 取比例大的那个值
             */
            $ratio = $ratioHeight > $ratioWidth ? $ratioHeight : $ratioWidth;

            /**
             * 定义一个中间的大图像，该图像的高或宽和目标图像相等，然后对原图放大
             */
            $interWidth  = (int)($oldWidth * $ratio);
            $interHeight = (int)($oldHeight * $ratio);
            $interImg    = imagecreatetruecolor($interWidth, $interHeight);

            /**
             * 将原图缩放比例后裁剪
             */
            imagecopyresampled($interImg, $srcImg, 0, 0, 0, 0, $interWidth, $interHeight, $oldWidth, $oldHeight);
            /**
             * 定义一个新的图像
             */
            $newImg = imagecreatetruecolor($newWidth, $newHeight);
            imagecopy($newImg, $interImg, 0, 0, 0, 0, $newWidth, $newHeight);
            switch ($type) {
                case IMAGETYPE_JPEG :
                    imagejpeg($newImg, $dstFile, 100); // 存储图像
                    break;
                case IMAGETYPE_PNG :
                    imagepng($newImg, $dstFile, 100);
                    break;
                case IMAGETYPE_GIF :
                    imagegif($newImg, $dstFile);
                    break;
                default:
                    break;
            }
        }
    }
}