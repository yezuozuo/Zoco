<?php

namespace Zoco;

require_once LIBPATH . '/module/phpexecl/PHPExcel.php';

/**
 * Class Excel
 *
 * @package Zoco
 */
class Excel {
    /**
     * 列号
     *
     * @var array
     */
    private $rowNum;

    /**
     * 列名
     *
     * @var array
     */
    private $rowName;

    /**
     * excel内容
     *
     * @var array
     */
    private $content;

    /**
     * @var \PHPExcel
     */
    private $excel;

    /**
     * @var string
     */
    private $path;

    /**
     * @param null $rowName
     * @param null $path
     */
    public function __construct($rowName = null, $path = null) {
        $this->rowNum = array(
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            'AA',
            'AB',
            'AC',
            'AD',
            'AE',
            'AF',
            'AG',
            'AH',
            'AI',
            'AJ',
            'AK',
            'AL',
            'AM',
            'AN',
            'AO',
            'AP',
            'AQ',
            'AR',
            'AS',
            'AT',
            'AU',
            'AV',
            'AW',
            'AX',
            'AY',
            'AZ'
        );
        $this->excel  = new \PHPExcel();

        if (empty($rowName) || !is_array($rowName)) {
            $this->rowName = array(
                '订单号',
                '下单时间',
                '城市',
                '地区',
                '客户名称',
                '收货人',
                '联系电话',
                '收货地址',
                'ERP客户名称',
                '物流系统客户名称',
                '活动项目',
                '品牌',
                '型号',
                '颜色',
                '物流系统型号',
                '订货量',
                '单价',
                '代收货款',
                '红包',
                '价保返利',
                '运费',
                '实收金额',
                '付款方式',
                '订单来源',
                '上游厂商',
                '是否在仓',
                '快递面单号',
                '订单状态',
                '确认时间',
                '末次状态确认时间',
                '描述',
                '对应业务',
                '对应客服',
                '商家留言',
                '下单摘要',
                '业务员',
                '联系方式',
            );
        }

        if (empty($path) || !is_dir($path)) {
            $this->path = WEBPATH . '/data/excel/';
        } else {
            $this->path = $path;
        }
    }

    /**
     * @param $path
     */
    public function setPath($path) {
        if (is_dir($path)) {
            $this->path = $path;
        } else {
            echo Error::info('error', $path . ' not exists.');
        }
    }

    /**
     * @param $content
     */
    public function setContent($content) {
        $this->content = $content;
    }

    /**
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function run() {
        /**
         * 设置换行
         */
        $this->excel->getActiveSheet()->getStyle('H')->getAlignment()->setWrapText(true);
        $this->excel->getActiveSheet()->getStyle('Y')->getAlignment()->setWrapText(true);

        /**
         * 设置相应列的宽度
         */
        $this->excel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(15);
        $this->excel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(20);
        $this->excel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(15);
        $this->excel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(40);
        $this->excel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(15);
        $this->excel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(15);

        /**
         * 输出第一行
         */
        $this->excel->setActiveSheetIndex(0)
                    ->setCellValue('A1', $this->rowName[0])
                    ->setCellValue('B1', $this->rowName[1])
                    ->setCellValue('C1', $this->rowName[2])
                    ->setCellValue('D1', $this->rowName[3])
                    ->setCellValue('E1', $this->rowName[4])
                    ->setCellValue('F1', $this->rowName[5])
                    ->setCellValue('G1', $this->rowName[6])
                    ->setCellValue('H1', $this->rowName[7])
                    ->setCellValue('I1', $this->rowName[8])
                    ->setCellValue('J1', $this->rowName[9])
                    ->setCellValue('K1', $this->rowName[10])
                    ->setCellValue('L1', $this->rowName[11]);

        /**
         * 输出内容
         */
        $count = count($this->content);
        for ($i = 0; $i < $count; $i++) {
            $this->excel->setActiveSheetIndex(0)
                        ->setCellValue($this->rowNum[0] . ($i + 2), $this->content[$i]['order_sn'])
                        ->setCellValue($this->rowNum[1] . ($i + 2), $this->content[$i]['add_time'])
                        ->setCellValue($this->rowNum[2] . ($i + 2), $this->content[$i]['city'])
                        ->setCellValue($this->rowNum[3] . ($i + 2), $this->content[$i]['region_name'])
                        ->setCellValue($this->rowNum[4] . ($i + 2), $this->content[$i]['company'])
                        ->setCellValue($this->rowNum[5] . ($i + 2), $this->content[$i]['consignee'])
                        ->setCellValue($this->rowNum[6] . ($i + 2), $this->content[$i]['mobile'])
                        ->setCellValue($this->rowNum[7] . ($i + 2), $this->content[$i]['address'])
                        ->setCellValue($this->rowNum[8] . ($i + 2), '')
                        ->setCellValue($this->rowNum[9] . ($i + 2), '')
                        ->setCellValue($this->rowNum[10] . ($i + 2), '')
                        ->setCellValue($this->rowNum[11] . ($i + 2), $this->content[$i]['brand_name']);
        }

        /**
         * 目录名
         */
        $dirName = date("Ymd");

        /**
         * 文件名
         */
        $fileName = date("YmdHis");

        $this->excel->getActiveSheet()->setTitle($fileName);

        /**
         * Set active sheet index to the first sheet, so Excel opens this as the first sheet
         */
        $this->excel->setActiveSheetIndex(0);

        if (!opendir($this->path . $dirName)) {
            mkdir($this->path . $dirName);
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $begin     = Tool::getCurrentTime();
        $objWriter->save($this->path . $dirName . '/' . $fileName . '.xlsx');
        $end   = Tool::getCurrentTime();
        $spend = $end - $begin;
        if ($spend > 30) {
            JS::echojs('if(confirm("执行超时！")){ window.history.back(-1);}');
        }
    }
}