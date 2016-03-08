<?php

namespace Zoco;

/**
 * Class DB
 */
class ZDB {
    /**
     * 这三个是传递给insert方法的合法标志
     */
    const DB_INSERT  = 1;
    const DB_REPLACE = 2;
    const DB_STORE   = 3;

    /**
     * 定义哈希表的桶的大小
     * 262144 = 256 * 1024
     */
    const DB_BUCKET_SIZE = 262144;

    /**
     * 定义键的长度
     */
    const DB_KEY_SIZE = 128;

    /**
     * 定义一条索引记录的长度
     * 加12是加3个其他字段的大小
     * 链表指针和数据偏移量和数据记录长度
     * DB_KEY_SIZE + 12;
     */
    const DB_INDEX_SIZE = 140;

    /**
     * 调用insert方法时，如果键重复就会返回这个变量
     */
    const DB_KEY_EXISTS = 1;

    /**
     * 定义方法调用失败时的返回值
     */
    const DB_FAILURE = -1;

    /**
     * 定义方法调用成功时的返回值
     */
    const DB_SUCCESS = 0;
    /**
     * @var string
     */
    public $path;
    /**
     * @var bool
     */
    public $isOpen = false;
    /**
     * 保存索引文件的句柄
     *
     * @var
     */
    private $idx_fp;
    /**
     * 保存数据文件的句柄
     *
     * @var
     */
    private $dat_fp;
    /**
     * 记录数据库是否已经关闭
     *
     * @var
     */
    private $closed;

    /**
     * @param null $path
     */
    public function __construct($path = null) {
        if (empty($path) || !is_dir($path)) {
            $this->path = WEBPATH . '/data/zdb/';
        } else {
            $this->path = $path;
        }
    }

    /**
     * 打开数据库
     *
     * @param $pathname
     * @return int
     */
    public function open($pathname) {
        $idx_path = $this->path . $pathname . '.idx';
        $dat_path = $this->path . $pathname . '.dat';

        if (!file_exists($idx_path)) {
            /**
             * 没有初始化
             */
            $init = true;

            /**
             * 文件模式为写二进制
             * 需要将索引块写入到文件中
             */
            $mode = "w + b";
        } else {
            $init = false;

            /**
             * 文件存在的话文件模式为读二进制
             */
            $mode = "r + b";
        }

        $this->idx_fp = fopen($idx_path, $mode);
        if (!$this->idx_fp) {
            return self::DB_FAILURE;
        }

        /**
         * 没初始化过的话
         */
        if ($init) {
            /**
             * 将262144个数值为0的长整数（占4字节）写入文件，占1MB的空间
             */
            $elem = pack('L', 0x00000000);
            for ($i = 0; $i < self::DB_BUCKET_SIZE; $i++) {
                fwrite($this->idx_fp, $elem, 4);
            }
        }

        $this->dat_fp = fopen($dat_path, $mode);
        if (!$this->dat_fp) {
            return self::DB_FAILURE;
        }

        $this->isOpen = true;

        return self::DB_SUCCESS;
    }

    /**
     * 根据制定的键从数据库中查询到指定的一条记录
     *
     * @param $key
     * @return null|string
     */
    public function fetch($key) {

        if ($this->isOpen === false) {
            echo Error::info('error', 'file not open');
            exit;
        }

        /**
         * 通过计算出的hash值计算出记录所在的Hash链的文件偏移量
         * 先对桶的大小取余，再乘以4（4为每个链表指针的大小）
         */
        $offset = ($this->_hash($key) % self::DB_BUCKET_SIZE) * 4;

        /**
         * 把文件指针移动到目标位置
         */
        fseek($this->idx_fp, $offset, SEEK_SET);

        /**
         * 通过fread函数读取4字节，这四个字节就是目标hash链表的文件偏移量
         * 这个得到的就是链表指针（int型）
         */
        $pos = unpack('L', fread($this->idx_fp, 4));

        $pos     = $pos[1];
        $found   = false;
        $dataoff = '';
        $datalen = 0;

        /**
         * 用来测试while循环的次数
         */
        while ($pos) {
            fseek($this->idx_fp, $pos, SEEK_SET);

            /**
             * 读取一整个索引记录的值
             */
            $block = fread($this->idx_fp, self::DB_INDEX_SIZE);

            /**
             * 从第4个字符开始读取128个字符，也就是获得了key的值
             */
            $cpkey = substr($block, 4, self::DB_KEY_SIZE);

            /**
             * 比较当前索引记录的key与要查找的key
             * 如果相等的话
             */
            if (!strncmp($key, $cpkey, strlen($key))) {
                /**
                 * 把链表指针和KEY越过获得数据偏移量
                 */
                $dataoff = unpack('L', substr($block, self::DB_KEY_SIZE + 4, 4));
                $dataoff = $dataoff[1];

                /**
                 * 把链表指针和KEY和数据偏移量越过获得数据记录长度
                 */
                $datalen = unpack('L', substr($block, self::DB_KEY_SIZE + 8, 4));

                $datalen = $datalen[1];
                $found   = true;
                break;
            }

            /**
             * 如果链表指针不为0的话说明查找的记录在这一个链表山
             * 获得当前链表指针的值，也就是下一个指针的偏移量
             */
            $pos = unpack('L', substr($block, 0, 4));
            $pos = $pos[1];
        }

        /**
         * 如果没有找到的话说明查找失败
         */
        if (!$found) {
            return null;
        }

        /**
         * 执行到这里说明找到了
         * 那么把根据得到的数据偏移量在data文件中定位
         */
        fseek($this->dat_fp, $dataoff, SEEK_SET);

        /**
         * 读取数据然后返回
         */
        $data = fread($this->dat_fp, $datalen);

        return $data;
    }

    /**
     * 给给定的字符串计算哈希值
     *
     * @param $string
     * @return int
     */
    private function _hash($string) {

        if ($this->isOpen === false) {
            echo Error::info('error', 'file not open');
            exit;
        }

        /**
         * 取前8个字符作为计算的串，利用Times33算法把他处理成一个整数。
         * Times33算法的优点是分布比较均匀，而且速度非常快
         */
        $string = substr(md5($string), 0, 8);

        /**
         * 这个$hash的初始值是可以改变的
         */
        $hash = 0;
        for ($i = 0; $i < 8; $i++) {
            /**
             * 这里多次计算最好用移位操作
             * 实践证明没有优化多少，是插入10000个数据太少了么
             */
            $hash += ($hash << 5) + $hash + ord($string[$i]);
        }

        return $hash & 0x7FFFFFFF;
    }

    /**
     * 插入
     *
     * @param $key
     * @param $data
     * @return int
     */
    public function insert($key, $data) {

        if ($this->isOpen === false) {
            echo Error::info('error', 'file not open');
            exit;
        }

        $offset = ($this->_hash($key) % self::DB_BUCKET_SIZE) * 4;

        /**
         * 获取文件状态
         */
        $idxoff = fstat($this->idx_fp);

        /**
         * 得到文件的大小
         */
        $idxoff = intval($idxoff['size']);

        $dataoff = fstat($this->dat_fp);
        $dataoff = intval($dataoff['size']);
        $keylen  = strlen($key);

        /**
         * 如果键的大小比128个字符都要大的话返回失败
         */
        if ($keylen > self::DB_KEY_SIZE) {
            return self::DB_FAILURE;
        }

        /**
         * 构造一个记录索引快
         * 把指向下一条索引记录的指针填充为一个类型为长整数的数字0，所示已经没有下一条记录了，
         * 也就是说当前索引记录是Hash链的最后一个记录
         */
        $block = pack('L', 0x00000000);

        /**
         * 在键域填充要插入的$key,但是如果$key没有达到最大长度的话就用0来填充知道到达最大长度
         */
        $block .= $key;
        $space = self::DB_KEY_SIZE - $keylen;
        for ($i = 0; $i < $space; $i++) {
            $block .= pack('C', 0x00);
        }

        $block .= pack('L', $dataoff);
        $block .= pack('L', strlen($data));

        /**
         * 定位到哈希之后的位置
         */
        fseek($this->idx_fp, $offset, SEEK_SET);

        /**
         * 获取链表指针的数值
         */
        $pos = unpack('L', fread($this->idx_fp, 4));
        $pos = $pos[1];

        /**
         * 表示链表为空
         */
        if ($pos == 0) {
            /**
             * 在前1024个块中写入偏移量
             * 就是说前1024个索引块只是存储下一个索引记录的指针，并不存储实际的索引记录
             */
            fseek($this->idx_fp, $offset, SEEK_SET);
            fwrite($this->idx_fp, pack('L', $idxoff), 4);

            /**
             * 将实际的索引记录存放到索引文件的末尾
             */
            fseek($this->idx_fp, 0, SEEK_END);
            fwrite($this->idx_fp, $block, self::DB_INDEX_SIZE);

            /**
             * 将对应的数据文件也存放到数据文件的末尾
             */
            fseek($this->dat_fp, 0, SEEK_END);
            fwrite($this->dat_fp, $data, strlen($data));

            return self::DB_SUCCESS;
        }

        /**
         * 否则需要使用拉链法
         */
        $found = false;

        $prev = '';

        /**
         * 如果链表不为空的话
         */
        while ($pos) {
            fseek($this->idx_fp, $pos, SEEK_SET);

            /**
             * 获取拉链的暂时的数据块
             */
            $tmp_block = fread($this->idx_fp, self::DB_INDEX_SIZE);
            $cpkey     = substr($tmp_block, 4, self::DB_KEY_SIZE);

            /**
             * 如果获取的这个暂时的数据块的key和要插入的key相等的话，说明键已经存在
             */
            if (!strncmp($key, $cpkey, strlen($key))) {
                /**
                 * 获取对应的数据偏移量和数据块长度
                 */
                $found = true;

                /**
                 * 从循环中出来
                 */
                break;
            }

            /**
             * 继续找下一个数据块
             */
            $prev = $pos;

            /**
             * 获取当前索引记录的链表指针，然后读取下一个索引快
             */
            $pos = unpack('L', substr($tmp_block, 0, 4));
            $pos = $pos[1];
        }

        /**
         * 键已经存在直接退出
         * 这个可以进行进一步的开发，可以增加update函数，在键存在的情况下修改
         */
        if ($found) {
            return self::DB_KEY_EXISTS;
        }

        /**
         * 不存在的话写入
         */
        fseek($this->idx_fp, $prev, SEEK_SET);
        fwrite($this->idx_fp, pack('L', $idxoff), 4);
        fseek($this->idx_fp, 0, SEEK_END);
        fwrite($this->idx_fp, $block, self::DB_INDEX_SIZE);
        fseek($this->dat_fp, 0, SEEK_END);
        fwrite($this->dat_fp, $data, strlen($data));

        return self::DB_SUCCESS;
    }

    /**
     * 删除
     *
     * @param $key
     * @return int
     */
    public function delete($key) {

        if ($this->isOpen === false) {
            echo Error::info('error', 'file not open');
            exit;
        }

        $found  = false;
        $offset = ($this->_hash($key) % self::DB_BUCKET_SIZE) * 4;
        fseek($this->idx_fp, $offset, SEEK_SET);

        /**
         * 获取Hash链表头结点的文件偏移量
         */
        $head = unpack('L', fread($this->idx_fp, 4));
        $head = $head[1];

        /**
         * 将当前结点$curr设置为$head
         */
        $curr = $head;

        /**
         * 前一个结点设置为null
         */
        $prev = 0;

        $next = '';

        /**
         * $curr不为0的情况下说明需要拉链继续向下寻找
         */
        while ($curr) {
            fseek($this->idx_fp, $curr, SEEK_SET);
            $block = fread($this->idx_fp, self::DB_INDEX_SIZE);

            $next = unpack('L', substr($block, 0, 4));
            $next = $next[1];

            $cpkey = substr($block, 4, self::DB_KEY_SIZE);

            /**
             * 在这里，原本只是简单的找到了，而不能真正的将存储的数据删掉
             * 只是通过链表指针象征性的删除，PHP没有指针的坏处在这里就体现出来了
             * 所以造成的后果就是索引文件和数据文件会越来越大，如果想要减少的话需要重新获取数值再重新插入
             * 所以该例子只能作为一个本地的小型缓存数据库来使用，不能用在实际生产中
             */
            if (!strncmp($key, $cpkey, strlen($key))) {
                $found = true;
                break;
            }
            $prev = $curr;
            $curr = $next;
        }

        /**
         * 没有找到，意味着删除失败
         */
        if (!$found) {
            echo "not found" . "<br>";

            return self::DB_FAILURE;
        }

        /**
         * 如果是删除拉链的第一个节点的话，直接在前1024个索引块对应的位置链表指针写入下一个链表指针
         * 变相的删除了当前的索引快（实际上没有删除==）
         */
        if ($prev == 0) {
            fseek($this->idx_fp, $offset, SEEK_SET);
            fwrite($this->idx_fp, pack('L', $next), 4);
        } /**
         * 否则移动到当前的索引快，在链表指针出写入下一个索引块的链表指针的数值
         */
        else {
            fseek($this->idx_fp, $prev, SEEK_SET);
            fwrite($this->idx_fp, pack('L', $next), 4);
        }

        return self::DB_SUCCESS;
    }

    /**
     * 关闭
     */
    public function close() {

        if ($this->isOpen === false) {
            echo Error::info('error', 'file not open');
            exit;
        }

        if (!$this->closed) {
            fclose($this->idx_fp);
            fclose($this->dat_fp);
            $this->closed = true;
        }
    }
}