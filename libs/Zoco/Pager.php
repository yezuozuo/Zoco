<?php

namespace Zoco;

/**
 * 分页类
 * 根据提供的数据，产生分页代码
 * Class Pager
 *
 * @package Zoco
 */
class Pager {
    /**
     * page标签，用来控制url页。比如说xxx.php?page=2中的page
     *
     * @var string
     */
    public $pageName = "page";

    /**
     * @var string
     */
    public $nextPage = '下一页';

    /**
     * @var string
     */
    public $prePage = '上一页';

    /**
     * @var string
     */
    public $firstPage = '首页';

    /**
     * @var string
     */
    public $lastPage = '尾页';

    /**
     * @var string
     */
    public $preBar = '上一分页条';

    /**
     * @var string
     */
    public $nextBar = '下一分页条';

    /**
     * @var string
     */
    public $formatLeft = '';

    /**
     * @var string
     */
    public $formatRight = '';

    /**
     * @var string
     */
    public $pageTpl = '';

    /**
     * @var
     */
    public $fragment;

    /**
     * @var array
     */
    public $spanOpen = array('first', 'last', 'next', 'previous');

    /**
     * @var array
     */
    public $pageSizeGroup = array(10, 20, 50);

    /**
     * @var
     */
    public $spanClass;

    /**
     * 控制记录条的个数
     *
     * @var int
     */
    public $pageBarNum = 10;

    /**
     * @var float|int
     */
    public $totalPage = 0;

    /**
     * @var int
     */
    public $pageSize = 10;

    /**
     * @var int
     */
    public $total = 0;

    /**
     * @var string
     */
    public $ajaxActionName = '';

    /**
     * 当前页
     *
     * @var int
     */
    public $page = 1;

    /**
     * @var int
     */
    public $offset = 0;

    /**
     * @var
     */
    public $style;

    /**
     * @param $array
     */
    public function __construct($array) {
        if (is_array($array)) {
            if (!isset($array['total'])) {
                echo Error::info(__FUNCTION__, 'need a param of total');
                exit();
            }

            $total    = intval($array['total']);
            $perPage  = isset($array['perPage']) ? intval($array['perPage']) : 10;
            $nowIndex = isset($array['nowIndex']) ? intval($array['nowIndex']) : '';
        } else {
            $total    = $array;
            $perPage  = 10;
            $nowIndex = '';
        }

        if (!empty($array['pageName'])) {
            $this->set('pageName', $array['pageName']);
        }

        $this->pageSize = $perPage;
        $this->setNowIndex($nowIndex);
        $this->totalPage = ceil($total / $perPage);
        $this->total     = $total;
        $this->offset    = ($this->page - 1) * $perPage;
    }

    /**
     * 设定类中指定变量名的值
     *
     * @param $var
     * @param $value
     */
    public function set($var, $value) {
        if (in_array($var, get_object_vars($this))) {
            $this->$var = $value;
        } else {
            echo Error::info(__FUNCTION__, $var . " does not belong to Pager!");
            exit();
        }
    }

    /**
     * 设置当前页面
     *
     * @param $nowIndex
     */
    private function setNowIndex($nowIndex) {
        if (empty($nowIndex)) {
            /**
             * 系统获取
             */
            if (isset($_GET[$this->pageName])) {
                $this->page = intval($_GET[$this->pageName]);
            }
        } else {
            /**
             * 手动设置
             */
            $this->page = intval($nowIndex);
        }
    }

    /**
     * @param $span
     * @param $className
     */
    public function setClass($span, $className) {
        $this->spanClass[$span] = $className;
    }

    /**
     * 获取mysql语句中limit需要的值
     *
     * @return int
     */
    public function offset() {
        return $this->offset;
    }

    /**
     * 控制分页显示风格（你可以增加相应的风格）
     *
     * @param null $mode
     * @return string
     */
    public function render($mode = null) {
        $pagerHtml = "<div class='pager'>";
        if ($mode == null) {
            if (in_array('first', $this->spanOpen)) {
                $pagerHtml .= $this->firstPage();
            }
            if (in_array('previous', $this->spanOpen)) {
                $pagerHtml .= $this->prePage();
            }
            $pagerHtml .= $this->nowBar();
            if (in_array('next', $this->spanOpen)) {
                $pagerHtml .= $this->nextPage();
            }
            if (in_array('last', $this->spanOpen)) {
                $pagerHtml .= $this->lastPage();
            }
            if (in_array('pageSize', $this->spanOpen)) {
                $pagerHtml .= $this->setPageSize();
            }
            $pagerHtml .= '</div>';

            return $pagerHtml;
        }
        $pagerHtml .= '</div>';

        return $pagerHtml;
    }

    /**
     * 获取显示“首页”的代码
     *
     * @return string
     */
    public function firstPage() {
        $style = @$this->spanClass['first'];
        if ($this->page == 1) {
            return '<span class="' . $style . '">' . $this->firstPage . '</span>';
        }

        return $this->getLink($this->getUrl(1), $this->firstPage, $style);
    }

    /**
     * 获取链接地址
     *
     * @param        $url
     * @param        $text
     * @param string $style
     * @return string
     */
    private function getLink($url, $text, $style = '') {
        $style = (empty($style)) ? '' : 'class="' . $style . '"';

        return '<a ' . $style . 'href="' . $url . '">' . $text . '</a>';
    }

    /**
     * 为指定的页面返回地址值
     *
     * @param int $pageNo
     * @return string
     */
    private function getUrl($pageNo = 1) {
        if (empty($this->pageTpl)) {
            return Tool::urlMerge('page', $pageNo, 'mvc,q');
        } else {
            return sprintf($this->pageTpl, $pageNo);
        }
    }

    /**
     * 获取显示“上一页”的代码
     *
     * @return string
     */
    public function prePage() {
        $style = @$this->spanClass['previous'];
        if ($this->page > 1) {
            return $this->getLink($this->getUrl($this->page - 1), $this->prePage, $style);
        }

        return '<span class="' . $style . '">' . $this->prePage . '</span>';
    }

    /**
     * @return string
     */
    public function nowBar() {
        $style = $this->style;
        $plus  = ceil($this->pageBarNum / 2);
        if ($this->pageBarNum - $plus + $this->page > $this->totalPage) {
            $plus = ($this->pageBarNum - $this->totalPage + $this->page);
        }
        $begin  = $this->page - $plus + 1;
        $begin  = ($begin >= 1) ? $begin : 1;
        $return = '';
        for ($i = $begin; $i < $begin + $this->pageBarNum; $i++) {
            if ($i <= $this->totalPage) {
                if ($i <= $this->totalPage) {
                    $return .= $this->getText($this->getLink($this->getUrl($i), $i, $style));
                } else {
                    $return .= $this->getText('<span class"current">' . $i . '</span>');
                }
            } else {
                break;
            }
            $return .= "\n";
        }
        unset($begin);

        return $return;
    }

    /**
     * 获取分页显示文字
     *
     * @param $str
     * @return string
     */
    private function getText($str) {
        return $this->formatLeft . $str . $this->formatRight;
    }

    /**
     * 获取显示"下一页"的代码
     *
     * @return string
     */
    public function nextPage() {
        $style = @$this->spanClass['next'];
        if ($this->page < $this->totalPage) {
            return $this->getLink($this->getUrl($this->page + 1), $this->nextPage, $style);
        }

        return '<span class="' . $style . '">' . $this->nextPage . '</span>';
    }

    /**
     * 获取显示“尾页”的代码
     *
     * @return string
     */
    public function lastPage() {
        $style = @$this->spanClass['last'];
        if ($this->page == $this->totalPage) {
            return '<span class="' . $style . '">' . $this->lastPage . '</span>';
        }

        return $this->totalPage ? $this->getLink($this->getUrl($this->totalPage), $this->lastPage, $style) : '<span>' . $this->lastPage . '</span>';
    }

    /**
     * @return string
     */
    public function setPageSize() {
        $str = '<div class="pageSize"><span>每页显示：</span>';
        foreach ($this->pageSizeGroup as $p) {
            if ($p == $this->pageSize) {
                $str .= "<span class='ps_cur'>$p</span>";
            } else {
                $str .= "<span class='ps'>$p</span>";
            }
        }

        return $str . '</div>';
    }
}