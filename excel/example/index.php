<?php
include '../lib/rows.php';
include '../vendor/autoload.php';
class obj
{
    use rows;
    public $obj = null;
    public $sheet = null;
    function __construct()
    {
        $this->obj = new PHPExcel();
        $this->sheet = $this->obj->getActiveSheet();
    }
}

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
$obj = new obj();
$title = ['张三','张三','s'=>['王五','王六'=>['王五','王六']],'张三','s1'=>['王六'=>['王五','王六']],'王六'=>['王五','王六']];
$obj->set($title);



$objWriter = PHPExcel_IOFactory::createWriter($obj->obj, 'Excel5');
$objWriter->save(str_replace('.php', '.xls', __FILE__));

echo date('H:i:s') , " File written to " , str_replace('.php', '.xls', pathinfo(__FILE__, PATHINFO_BASENAME)) , EOL;
// Echo memory usage
echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;


// Echo memory peak usage
echo date('H:i:s') , " Peak memory usage: " , (memory_get_peak_usage(true) / 1024 / 1024) , " MB" , EOL;

// Echo done
echo date('H:i:s') , " Done writing files" , EOL;
echo 'Files have been created in ' , getcwd() , EOL;