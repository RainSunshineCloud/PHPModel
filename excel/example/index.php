<?php
include '../lib/Title.php';
include '../lib/main.php';
include '../vendor/autoload.php';
class obj
{
    use Title;
    use main;

    public $obj = null;
    public $sheet = null;

    public function __construct()
    {
        $this->obj = new PHPExcel();
        $this->sheet = $this->obj->getActiveSheet();
    }

    /**
     *输出
     * User: qing
     * Date: 2018/6/10
     * Time: 下午3:07
     *
     * @param        $file
     * @param string $format
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    public function publish($file,$format='Excel5')
    {
        PHPExcel_IOFactory::createWriter($this->obj, $format)->save($file);
    }
}



$obj = new obj();
$title = ['姓名','年龄','地址'=>['省','市','县']];
$data = [
        ['a'=>'张三','b'=>'13','c'=>'附件','d'=>'d','e'=>'sd'],
        ['a'=>'张三','b'=>'16','c'=>'附件','d'=>'d','e'=>'sd'],
    ];
$binding = ['a'=>'A','b'=>'B','c'=>'C','d'=>'D','e'=>'E'];
$obj->bind($binding)->get($data)->publish('test.xls');


