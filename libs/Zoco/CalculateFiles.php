<?php

namespace Zoco;

/**
 * 执行目录中文件的统计（包括文件数及总行数
 * 1、跳过文件的时候：
 * 匹配的规则只是从文件名上着手，匹配的规则也仅限在开头。
 * 2、跳过文件中的注释行：
 * 匹配的规则只是从注释段落的头部匹配，如果出现// 及 *及 #及/*开头的行及空行会被跳过。所以类似/*这种多汗注释，每行的开头都必须加上*号，否则无法匹配到这种的注释。
 * 3、目录过滤：
 * 匹配的规则是从目录名的全名匹配
 * Class CalculateFiles
 *
 * @package Zoco
 */
class CalculateFiles {
    /**
     * @var bool
     */
    static $del = false;

    /**
     * 统计的后缀
     *
     * @var string
     */
    private $ext = ".php";

    /**
     * 是否显示每个文件的统计数
     *
     * @var bool
     */
    private $showEveryFile = true;

    /**
     * 文件的的跳过规则
     *
     * @var array
     */
    private $fileSkip = array();

    /**
     * 统计的跳过行规则
     *
     * @var array
     */
    private $lineSkip = array("*", "/*", "//", "#");

    /**
     * 统计跳过的目录规则
     *
     * @var array
     */
    private $dirSkip = array(".", "..", '.svn');

    /**
     * @param string    $ext
     * @param string    $dir
     * @param bool|true $showEveryFile
     * @param array     $dirSkip
     * @param array     $lineSkip
     * @param array     $fileSkip
     */
    public function __construct(
        $ext = '',
        $dir = '',
        $showEveryFile = true,
        $dirSkip = array(),
        $lineSkip = array(),
        $fileSkip = array()
    ) {
        $this->setExt($ext);
        $this->setDirSkip($dirSkip);
        $this->setFileSkip($fileSkip);
        $this->setLineSkip($lineSkip);
        $this->setShowFlag($showEveryFile);
        $this->run($dir);
    }

    /**
     * @param $ext
     */
    public function setExt($ext) {
        trim($ext) && $this->ext = strtolower(trim($ext));
    }

    /**
     * @param $dirSkip
     */
    public function setDirSkip($dirSkip) {
        $dirSkip && is_array($dirSkip) && $this->dirSkip = $dirSkip;
    }

    /**
     * @param $fileSkip
     */
    public function setFileSkip($fileSkip) {
        $this->fileSkip = $fileSkip;
    }

    /**
     * @param $lineSkip
     */
    public function setLineSkip($lineSkip) {
        $lineSkip && is_array($lineSkip) && $this->lineSkip = array_merge($this->lineSkip, $lineSkip);
    }

    /**
     * @param bool|true $flag
     */
    public function setShowFlag($flag = true) {
        $this->showEveryFile = $flag;
    }

    /**
     * 执行统计
     *
     * @param string $dir
     */
    public function run($dir = '') {
        if ($dir == '') {
            return;
        }
        if (!is_dir($dir)) {
            exit("Path $dir error!");
        }
        $this->dump($dir, $this->readDir($dir));
    }

    /**
     * 显示统计结果
     *
     * @param $dir
     * @param $result
     */
    private function dump($dir, $result) {
        $totalLine = $result['totalLine'];
        $lineNum   = $result['lineNum'];
        $fileNum   = $result['fileNum'];
        echo "*************************************************************\r\n";
        echo $dir . ":\r\n";
        echo "TotalLine: " . $totalLine . "\r\n";
        echo "TotalLine with no comment and empty: " . $lineNum . "\r\n";
        echo "Code Rate " . sprintf('%.2f', $lineNum / $totalLine * 100) . "%\r\n";
        echo 'TotalFiles:' . $fileNum . "\r\n";
    }

    /**
     * 读取目录
     *
     * @param $dir
     * @return array
     */
    private function readDir($dir) {
        $num = array(
            'totalLine' => 0,
            'lineNum'   => 0,
            'fileNum'   => 0,
        );
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($this->skipDir($file)) {
                    continue;
                }
                if (is_dir($dir . '/' . $file)) {
                    $result = $this->readDir($dir . '/' . $file);
                    $num['totalLine'] += $result['totalLine'];
                    $num['lineNum'] += $result['lineNum'];
                    $num['fileNum'] += $result['fileNum'];
                } else {
                    if ($this->skipFile($file)) {
                        continue;
                    }
                    list($num1, $num2) = $this->readFiles($dir . '/' . $file);
                    $num['totalLine'] += $num1;
                    $num['lineNum'] += $num2;
                    $num['fileNum']++;
                }
            }
            closedir($dh);
        } else {
            echo 'open dir <' . $dir . '> error!' . "\r";
        }

        return $num;
    }

    /**
     * 执行跳过的目录规则
     *
     * @param $dir
     * @return bool
     */
    private function skipDir($dir) {
        if (in_array($dir, $this->dirSkip)) {
            return true;
        }

        return false;
    }

    /**
     * 执行跳过的文件规则
     *
     * @param $file
     * @return bool
     */
    private function skipFile($file) {
        if (strtolower(strrchr($file, '.')) != $this->ext) {
            return true;
        }
        if (!$this->fileSkip) {
            return false;
        }
        foreach ($this->fileSkip as $skip) {
            if (strpos($file, $skip) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * 读取文件
     *
     * @param $file
     * @return array
     */
    private function readFiles($file) {
        $str     = file($file);
        $lineNum = 0;
        $newFile = '';
        foreach ($str as $value) {
            if ($this->skipLine(trim($value))) {
                continue;
            }
            if (self::$del !== false) {
                $newFile .= $value;
            }
            $lineNum++;
        }
        $totalNum = count(file($file));

        if (self::$del !== false) {
            file_put_contents($file, $newFile);
        }
        if (!$this->showEveryFile) {
            return array($totalNum, $lineNum);
        }
        echo $file . "\r\n";
        echo 'TotalLine in the file:' . $totalNum . "\r\n";
        echo 'TotalLine with no comment and empty in the file:' . $lineNum . "\r\n";

        return array($totalNum, $lineNum);
    }

    /**
     * 执行文件中行的跳过规则
     *
     * @param $string
     * @return bool
     */
    private function skipLine($string) {
        if ($string == '') {
            return true;
        }
        foreach ($this->lineSkip as $tag) {
            if (strpos($string, $tag) === 0) {
                return true;
            }
        }

        return false;
    }
}
