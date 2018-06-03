<?php

trait rows
{
    public $rows = [];
    public $rowScama = [];
    public $columnScama = [];
    public $rowMap = [];

    function set($title)
    {
        $this->rowMap = range('A','Z');
        $this->rows = $this->getRows($title);
        var_dump($this->countMerge($title));
    }


    public function countMerge($scama,$row = 1,$column = 1)
    {
        $tmp_width = 0;
        foreach ( $scama as $k => $v) {
            if (is_array($v)) {
                if (!$offset = $this->setOffset($k,$v,$column,$row)) {
                    $offset = 0;
                }
                $width = $this->countMerge($v,$row+1,$column + $offset);
                if ($row == 1) {
                    echo $width;
                }
                $this->setTop($k,$column,$row,$width-1,$offset);
                $tmp_width += $width;
                $column += $tmp_width - 1;
                if ($row == 1) {
                    echo '/',$width,'/',$column,'/',$tmp_width;
                    $tmp_width = 0;
                }

            } else {
                if ($row == 1) {
                    echo $column;
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

    public function setOffset($key,$value,$column,$row)
    {
        return false;
    }

    public function setTop($value,$column_th,$row_th,$combine_width,$combine_height)
    {
        $first = $this->rowMap[$column_th-1].$row_th;
        $last = $this->rowMap[$column_th+$combine_width-1].($row_th + $combine_height);

        $this->sheet->getCell($first)->setValue($value);
        $this->sheet->mergeCells($first.':'.$last);
    }


    function getRows($scama)
    {
        $row = 1;
        $res = 1;
        foreach ( $scama as $v ) {
            if (is_array($v)) $res += $this->getRows($v);
           $row = max($row,$res);
           $res = 1;
        }

        return $row;
    }

}