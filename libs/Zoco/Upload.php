<?php

namespace Zoco;

class Upload {
    public $mimes;
    public $maxSize = 0;
    public $allow = array('jpg', 'gif', 'png');
    public $nameType = '';
    public $baseDir;
    public $baseUrl;
    public $subDir;
    public $shardType = 'date';
    public $shardArgv;
    public $filenameType = 'randomKey';
    public $existCheck = true;
    public $overWrite = true;
    public $maxWidth = 0;
    public $maxHeight;
    public $maxQuality = 80;
    public $thumbDir;
    public $thumbWidth = 0;
    public $thumbHeight;
    public $thumbQuality = 100;
    public $errMsg;
    public $errCode;
    public function __construct($config) {
        if (empty($config['baseDir']) || empty($config['baseUrl'])) {
            throw new \Exception(__CLASS__ . ' require baseDir and baseUrl');
        }
        $this->baseDir = $config['baseDir'];
        if (Tool::endChar($this->baseDir) != '/') {
            $this->baseDir .= '/';
        }

        $this->baseUrl = $config['baseUrl'];
        $mimes         = require LIBPATH . '/data/mimes.php';
        $this->mimes   = $mimes;
    }

    public function errorMsg() {
        return $this->errMsg;
    }


    public function saveAll() {
        if (!empty($_FILES)) {
            foreach ($_FILES as $key => $value) {
                if (!empty($_FILES[$key]['type'])) {
                    $_POST[$key] = $this->save($key);
                }
            }
        }
    }

    /**
     * @param      $name
     * @param null $fileName
     * @return bool
     */
    public function save($name, $fileName = null) {
        if (empty($_FILES[$name]['type'])) {
            $this->errMsg  = "No upload file '$name'";
            $this->errCode = 0;

            return false;
        }
        $baseDir = empty($this->subDir) ? $this->baseDir : $this->baseDir . $this->subDir . '/';
        if ($this->shardType == 'randomKey') {
            if (empty($this->shardArgv)) {
                $this->shardArgv = 0;
            }
            $subDir = RandomKey::randmd5($this->shardArgv);
        } else {
            if ($this->shardType == 'user') {
                $subDir = $this->shardArgv;
            } else {
                if (empty($this->shardArgv)) {
                    $this->shardArgv = 'Ym/d';
                }
                $subDir = date($this->shardArgv);
            }
        }
        $path = $baseDir . $subDir;
        if (!is_dir($path)) {
            if (mkdir($path, 0777, true) === false) {
                $this->errMsg = "mkdir path=$path fail.";

                return false;
            }
        }
        $mime     = $_FILES[$name]['type'];
        $fileType = $this->getMimeType($mime);
        if ($fileType === 'bin') {
            $fileType = self::getFileExt($_FILES[$name]['type']);
        }

        if ($fileType === false) {
            $this->errMsg  = "File mime '$mime' unknown";
            $this->errCode = 1;

            return false;
        } else {
            if (!in_array($fileType, $this->allow)) {
                $this->errMsg  = "File type '$fileType' not allow upload!";
                $this->errCode = 2;

                return false;
            }
        }
        if ($fileName == null) {
            $fileName = RandomKey::randTime();
            while ($this->existCheck && is_file($path . '/' . $fileName . '.' . $fileType)) {
                $fileName = RandomKey::randTime();
            }
        } else {
            if ($this->overWrite === false && is_file($path . '/' . $fileName . '.' . $fileType)) {
                $this->errMsg  = "File '$path/$fileName.$fileType' existed!";
                $this->errCode = 1;

                return false;
            }
        }

        $fileName .= '.' . $fileType;

        $fileSize = filesize($_FILES[$name]['tmp_name']);
        if ($this->maxSize > 0 && $fileSize > $this->maxSize) {
            $this->errMsg  = "File size go beyond the max_size";
            $this->errCode = 4;

            return false;
        }
        $saveFileName = $path . '/' . $fileName;

        if (self::moveUploadFile($_FILES[$name]['tmp_name'], $saveFileName)) {
            if ($this->thumbWidth && in_array($fileType, array('gif', 'jpg', 'jpeg', 'bmp', 'png'))) {
                $thumbFile = str_replace('.' . $fileType, '_' . $this->thumbWidth . 'x' . $this->thumbHeight . '.' . $fileType, $fileName);
                Image::thumbnail($saveFileName, $path . '/' . $thumbFile, $this->maxWidth, $this->maxHeight, $this->maxQuality);
                Image::thumbnail($saveFileName, $path . '/' . $thumbFile, $this->thumbWidth, $this->thumbHeight, $this->thumbQuality);
                $return['thumb'] =  "{$this->baseUrl}/{$this->subDir}/{$subDir}/{$thumbFile}";
            }
            if ($this->maxWidth and in_array($fileType, array('gif', 'jpg', 'jpeg', 'bmp', 'png'))) {
                Image::thumbnail($saveFileName,
                    $saveFileName,
                    $this->maxWidth,
                    $this->maxHeight,
                    $this->maxQuality);
            }

            $return['url']  = "{$this->baseUrl}/{$this->subDir}/{$subDir}/{$fileName}";
            $return['size'] = $fileSize;
            $return['type'] = $fileType;

            return $return;
        } else {
            $this->errMsg  = "move upload file fail. tmpName={$_FILES[$name]['tmpName']}|destName={$saveFileName}";
            $this->errCode = 2;

            return false;
        }
    }

    /**
     * @param $mime
     * @return bool
     */
    public function getMimeType($mime) {
        if (isset($this->mimes[$mime])) {
            return $this->mimes[$mime];
        } else {
            return false;
        }
    }

    /**
     * @param $file
     * @return string
     */
    static public function getFileExt($file) {
        return strtolower(trim(substr(strrchr($file, '.'), 1)));
    }

    /**
     * @param $tmpFile
     * @param $newFile
     * @return bool
     */
    static public function moveUploadFile($tmpFile, $newFile) {
        return rename($tmpFile, $newFile);
    }
}