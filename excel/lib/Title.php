<?php

trait Title
{
    public $rows = null;
    public $rowMap = [];
    public $callback = false;

    /**
     *对外设置的行数
     * User: qing
     * Date: 2018/6/10
     * Time: 下午2:23
     *
     * @param $data 获取的数据
     */
    public function set($data)
    {
        $this->rowMap = range('A','Z');
        $this->rows = $this->getRows($data);
        $this->countMerge($data);
        return $this;
    }

    /**
     *合并操作算法
     * User: qing
     * Date: 2018/6/10
     * Time: 下午2:25
     *
     * @param     $data 标题数组
     * @param int $row  行号
     * @param int $column  列号
     * @return int  当前列合并的列数
     */
    protected function countMerge($data,$row = 1,$column = 1)
    {
        $tmp_width = 0;
        foreach ( $data as $k => $v) {
            if (is_array($v)) { //数组则递归
                $width = $this->countMerge($v,$row+1,$column);
                $this->setTop($k,$column,$row,$width-1,0);

                $tmp_width += $width; //列偏移
                $column += $tmp_width - 1; //下一个列所在的位置
                if ($row == 1) { //如果是第一行，则重置$tmp_width
                    $tmp_width = 0;
                }

            } else { //不是数组
                if ($row == 1) {//如果是第一行，则重置$tmp_width
                    $tmp_width = 0;
                }
                $offset = $this->rows - $row;
                $this->setTop($v,$column,$row,0,$offset);
                $tmp_width++;
                $column++;
            }


        }

        return $tmp_width;
    }

    /**
     *设置值
     * User: qing
     * Date: 2018/6/10
     * Time: 下午2:32
     *
     * @param $value 值
     * @param $column_th 第几列
     * @param $row_th 第几行
     * @param $combine_width 合并的列数
     * @param $combine_height 合并的行数
     */
    protected function setTop($value,$column_th,$row_th,$combine_width,$combine_height)
    {
        $first = $this->rowMap[$column_th-1].$row_th;
        $last = $this->rowMap[$column_th+$combine_width-1].($row_th + $combine_height);

        $this->sheet->getCell($first)->setValue($value);
        $this->sheet->mergeCells($first.':'.$last);
    }

    /**
     *获取该数组共占多少行
     * User: qing
     * Date: 2018/6/10
     * Time: 下午2:22
     * @param $data 标题数据
     * @return int|mixed
     */
    protected function getRows(array $data)
    {
        $row = 1;
        $res = 1;
        foreach ( $data as $v ) {
            if (is_array($v)) $res += $this->getRows($v);
           $row = max($row,$res);
           $res = 1;
        }

        return $row;
    }

}