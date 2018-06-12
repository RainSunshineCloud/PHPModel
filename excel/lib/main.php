<?php
/**
 * Created by PhpStorm.
 * User: RyanWu
 * Date: 2018/6/11
 * Time: 10:23
 */

trait main
{
    protected $bind = [];
    protected $prev = [];

    public function get($data)
    {
        array_push($data,false);
        array_walk_recursive($data,[$this,'rowFunc']);
        var_dump($this->prev);
        return $this;
    }

    protected function rowFunc($val,$key)
    {

        if ($val === false) {//最后一个值
            foreach ($this->prev as $k=>$v ) {
                $this->mergeCell($v['rowth'],$v['merge_row'],$k,$v['val']);
            }
            return;
        }

        if (isset($this->prev[$key]) && $this->verify($val,$key) && $this->belonging()) {
            $rowth = $this->prev[$key]['rowth'];
            $merge_row = $this->prev[$key]['merge_row'];
            $v = $this->prev[$key]['val'];
            $this->mergeCell($rowth,$merge_row,$key,$v);
            //将当前值设为下一次的值
            $this->prev[$key]['val'] = $val;
            $this->prev[$key]['rowth'] += $this->prev[$key]['merge_row'] + 1;
            $this->prev[$key]['merge_row'] = 0;
        } else {
            $this->prev[$key]['merge_row'] = isset($this->prev[$key]['merge_row'])?$this->prev[$key]['merge_row'] + 1 : 0;
        }
        //初始化
        if (!isset($this->prev[$key]['rowth'])) {
            $this->prev[$key]['rowth'] = 1;
        }
        //初始化
        if (!isset($this->prev[$key]['val'])) {
            $this->prev[$key]['val'] = $val;
        }
    }

    /**
     *设置从属关系
     * User: qing
     * Date: 2018/6/12
     * Time: 下午9:46
     */
    public function setBelongTo ($arr)
    {
        foreach ($arr as $k => $v) {
            $this->belong[$k]['be'] = $v;
            $this->belong[$k]['combine'] = false;
        }

    }

    public function belonging($val,$key)
    {
        if (isset($this->belong[$key]) && $this->belong[$key]['be'])
        $val !== $this->prev[$key]['val'];
    }

    public function verify($val,$key)
    {
        if (isset($this->callback[$key])) {
            return ($this->callback[$key])($val,$key);
        }
    }

    public function mergeCondition($funcArr)
    {

        $this->callback = $funcArr;
    }

    protected function mergeCell($rowth,$merge_row,$key,$val)
    {
        $colth = $this->bind[$key];
        $first = $colth.($rowth);
        $last = $colth.($rowth + $merge_row);
        $this->sheet->getCell($first)->setValue($val);
        $this->sheet->mergeCells($first.':'.$last);
    }

    public function bind($arr)
    {
        $this->bind = $arr;
        return $this;
    }
}
