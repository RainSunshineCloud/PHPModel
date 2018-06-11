<?php
/**
 * Created by PhpStorm.
 * User: RyanWu
 * Date: 2018/6/11
 * Time: 10:23
 */

class datatranform
{
    public function get($data)
    {

        array_walk_recursive($data,[$this,'rowFunc']);
    }

    protected function rowFunc($key,$val)
    {
        if (isset($this->prev[$key]) && $val !== $prev[$key] ) {
            $rowth = $this->prev[$key]['rowth'];
            $merge_row = $this->prev[$key]['merge_row'];
            $val = $this->prev[$key]['val'];
            $this->mergeCell($colth,$merge_row,$key,$val);
            $this->prev[$key]['merge_row'] = 0;
        } else {
            $this->prev[$key]['merge_row'] ++;
        }

        $this->prev[$key]['val'] = $val;
        $this->prev[$key]['rowth'] = isset($this->prev[$key]['rowth'])? $this->prev[$key]['rowth']++ : 1;
    }

    protected function mergeCell($colth,$merge_row,$key,$val)
    {

    }
}
