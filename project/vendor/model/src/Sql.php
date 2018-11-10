<?php

class Sql
{
    protected $select = '';
    protected $table = null;
    protected $prefix = null;
    protected $where = '';
    protected $join = '';
    protected $order = '';
    protected $group = '';
    protected $having = '';
    protected $limit = '';
    protected $alise = '';
    protected $prepare = true;
    protected $prepareData = [];
    protected $preparesignal = null;
    protected function __construct() {}


    /**
     *join 表
     * @param mix    $join_table 
     * @param string $join 连接方式，默认左连接
     */
    protected function join( $join_table,$on,$alise = '',$join = 'left')
    {
        if ($join_table instanceof sql && !empty($alise)) {
            $join_table = sprintf('(%s) AS `%s`',$join_table -> get(),$alise);
        } elseif (!empty($alise)) {
            $join_table = sprintf('`%s` AS `%s`',$join_table,$alise);
        } else {
            $join_table = sprintf('`%s`',$join_table);
        }
        $on = explode('=',$on);
        $on[0] = $this->getField($on[0]);
        $on[1] = $this->getField($on[1]);
        $this->join .= sprintf(' %s join %s on %s = %s',$join,$join_table,$on[0],$on[1]);
    }

    protected function alise (string $alise) 
    {
        $this->alise = sprintf(' AS `%s`', $alise);
    }
    /**
     * where 条件
     */
    protected function where($field,$op = null,$value = null,$logic = 'and')
    {
        $logic = trim(strtoupper($logic));
        if (!in_array($logic,['AND','OR'])) {
            throw new ModelException('logic错误',1001);
        }

        if (is_array($field) ) { //数组

            foreach ($field as $k => $v) {
                $where = $this->getWhere($k,'=',$v);
                if ($this->where) {
                    $this->where .= sprintf(' %s %s',$logic,$where);
                } else {
                    $this->where .= sprintf(' WHERE %s',$where);
                } 
            }

            return null;

        }else if ($field instanceof Closure){//闭包

            $where = $this->getWhere('','FUNC',$field);
            return null;

        } else if (is_string($field) &&  ($op instanceof Sql)){//子查询

            $where = $this->getWhere('','child',$op,$field);

        } else if ( is_string($field) && is_null($value)) {//原生Sql

            if (is_array($op)) {
                if (array_intersect_key($this->prepareData,$op)) {
                    throw new ModelException('where 参数错误',1003);
                }
                $this->prepareData += $op;
            } else if (!is_null($op)) {
                throw new ModelException('where 参数错误',1003);
            }
            $where = $field;

        } else if ( is_string($field) && !is_null($op) && ($value instanceof Sql) ) { //子查询
            
            $where = $this->getWhere($field,'child',$value,$op);

        } else if (is_string($field) && !is_null($op) && !is_null($value) && !is_object($value)) { // 普通操作
            
            $where = $this->getWhere($field,$op,$value);

        } else { //错误

            throw new ModelException('where 参数错误',1003);

        }

        if ($this->where) {
            $this->where .= sprintf(' %s %s',$logic,$where);
        } else {
            $this->where .= sprintf(' WHERE %s',$where);
        }   
    }

    protected function orWhere(array $field)
    {
        $this->where($field,null,null,'or');
    }
    /**
     * where 条件
     */
    protected function getWhere($field,$op,$values,$childOp = '=')
    { 
        $op = trim(strtoupper($op));
        $field = $this->getField([$field]);
        switch ($op) {
            case 'CHILD': //子查询
                $this->prepareData += $values->getPrepareData();//获取子查询的值
                $childOp = strtoupper($childOp);
                if (in_array($childOp,['EXISTS','NOT EXISTS'])) {
                    return sprintf('%s (%s)',$childOp,$values->get());
                }
                return sprintf('%s %s (%s)',$field,$childOp,$values->get());
            case 'FUNC':
                return $values($this);
            case 'IN':
                if (is_array($values)) {
                    $values = join(',',$values);
                } else if (!is_string($values)) {
                    throw new ModelException('In操作必须是字符串或数组',1004);
                }
                $values = $this->prepare($values);
                return sprintf('%s IN (%s)',$field,$values);
            case 'BETWEEN':
                if (is_array($values) && count($values) >= 2) {
                    $values[0] = $this->prepare($values[0]);
                    $values[1] = $this->prepare($values[1]);
                    return sprintf('%s BETWEEN %s AND %s',$field,$values[0],$values[1]);
                } else {
                    throw new ModelException('Between 参数错误',1005);
                }
            case 'IS':
                return sprintf('%s %s %s',$field,$op,$values);
                break;
            default:
                if (!is_string($values) && !is_numeric($values)) {
                    throw new ModelException('不是字符串或数值',1006);
                }
                $values = $this->prepare($values);
                return sprintf('%s %s %s',$field,$op,$values);
        }
    }

    /**
     * 字段名
     * @param  array|string $select 
     * @return 
     */
    protected  function field($select = [],$is_origin = false)
    {
        if (!is_array($select) && !is_string($select) ) {
            throw new ModelException('select错误','1007');
        }

        $select = str_replace("'", '"', $select);
        if (!empty($this->select)) {
            $this->select .= ',';
        }
        if ($is_origin == true && is_string($select)) {
            $this->select = $select;
        } else if ($select == []) {
            $this->select = '*';
        } else {
            $this->select .= $this->getField($select);
        }
    }


    /**
     *select Sql
     *
     * @return string
     */
    protected function get()
    {

        if (!$this->select) $this->select = '*';
        if (!$this->table) {
            throw new ModelException('table 未定义',1008);
        }

        $sql = sprintf('SELECT %s FROM %s%s%s%s%s%s%s%s%s',
            $this->select,
            $this->prefix,
            $this->table,
            $this->alise,
            $this->join,
            $this->where,
            $this->group,
            $this->having,
            $this->order,
            $this->limit);
        return trim($sql);
    }

    /**
     *表名
     */
    protected static function table($table,$alise = null)
    {
        if ($table instanceof Sql) {
            $this->table = sprintf('(%s)',$table->get());
        } else {
            $this->table = sprintf('`%s`',$table);
        }
        
        if (isset($alise)) {
            $this->alise($alise);
        }
    }

    /**
     *表前缀
     * User: RyanWu
     * Date: 2018/6/1
     * Time: 20:18
     *
     * @param $prefix
     */
    protected static function prefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     *Sql update
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:21
     *
     * @param array  $data 要更新的数据
     * @param array  $func 处理字段的匿名函数
     */
    protected static function update($data,$func = '')
    {
        $sql = '';
       foreach ($data as $k=>$v) {
            if ($func instanceof Closure) {
                $v = $func($v);
            }
           $sql .= sprintf('%s = %s,',$this->getField($k),$this->prepare($v));
       }
       $sql = rtrim($sql,',');
       return sprintf('update %s%s set %s%s',$this->prefix,$this->table,$sql ,$this->where);
    }

     /**
     *Sql delete
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:21
     */
    protected static function delete()
    {
       return sprintf('DELETE FROM %s%s%s',$this->prefix,$this->table,$this->where);
    }

   /**
    * insert Sql
    * @param  array  $data   二维数组
    * @param  array  $field  字段
    * @param  string $func   回调函数处理每个字段值
    * @return [type]        
    */
    protected  function insertAll(array $data,array $field = [],$func = '')
    {
        if ( !is_array($data)|| !$one = reset($data) || !is_array($one)) {
            throw new ModelException('insert 必须是二维数组',1009);
        }
        $sql = '';
        foreach($data as $v) {
            $tmp = '';
            ksort($v);
            foreach ($v as $vv) {
                if ($func instanceof Closure) {
                    $vv = call_user_func($func,$vv);
                }
                $tmp .= $this->prepare($vv).',';
            }
            $sql .= sprintf('(%s),',trim($tmp,','));
        }

        if (!$field) {
            $field = array_keys($v);
        }

        $field = $this->getField($field);
        $sql = rtrim($sql,',');
        return sprintf('INSERT INTO %s%s(%s) VALUES%s',$this->prefix,$this->table,$field,$sql);
    }

    /**
     * insert Sql
     * @param  array|object  $data  一维数组
     * @param  string  | array      $func  回调函数 | $data 为 object时insert的字段
     * @return
     */
    protected  function insert($data,$func = '')
    {
        if ($data instanceof Sql) {
            $sql = $data->get();

            if(!is_array($func) && !is_string($func)) {
                 throw new ModelException('当$data 为对象时 func 必须是数组后字符串',1009);
            }
            
            $field = $this->getField($func);
            if ($field) {
                return sprintf("INSERT INTO %s%s(%s) (%s)",$this->prefix,$this->table,$field,$sql);
            } else {
                return sprintf("INSERT INTO %s%s (%s)",$this->prefix,$this->table,$sql);
            }
            
        } else if (!is_array($data)) {
            throw new ModelException('insert 必须是一维数组',1009);
        } else {
            $field = $this->getField(array_keys($data));
            foreach ($data as $k => $v) {

                if ($func instanceof Closure) {
                    $v = $func($v);
                }
                $data[$k] = $this->prepare($v);
            }
            return sprintf("INSERT INTO %s%s(%s) VALUES(%s)",$this->prefix,$this->table,$field,join(',',$data));
        }  
    }

    /**
     *外部统一调用方式
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:30
     *
     * @param $func
     * @param $arg
     * @return bool|Sql
     */
    public static function __callStatic($func,$arg)
    {

        $class = get_called_class();
        $obj = new $class();
        $obj->preparesignal = sprintf(':%s_',uniqid());
        if (method_exists(get_called_class(),$func)) {
            call_user_func_array([$obj,$func],$arg);
            return $obj;
        }
        return new ModelException('Sql 未有该方法',1002);
       
        
    }

    /**
     *外部统一调用方式
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:31
     *
     * @param $func
     * @param $arg
     * @return $this|mixed
     */
    public function __call($func,$arg)
    {

        if (method_exists(get_called_class(),$func)) {
            $sql = call_user_func_array([$this,$func],$arg);
            if ($sql) {
                $data = $this->getPrepareData();
                $this->clear();
                return ['sql'=>$sql,'data'=>$data];
            }
            return $this;
        } else {
            array_unshift($arg,$func);
            call_user_func_array([$this,'func'],$arg);
            return $this;
        }
        
        
    }

    /**
     *群组，直接传
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:31
     *
     * @param $group string 传入字段
     */
    protected  function group($group)
    {
        if (!$this->group) $this->group = ' GROUP BY ';

        $this->group .= $this->getField($group);
    }

    /**
     * 字段转换
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    private function getField($field)
    {
        if (is_string($field)) {
            if (strpos($field,',')) {
                $fieldArray = explode(',',$field);
            } else {
                $fieldArray = [$field];
            }
        }else if (is_array($field)) {
            $fieldArray = $field;
        } else {
            throw new ModelException('field 错误',1010);
        }


        $list = [];
        foreach ($fieldArray as $v) {
            $v = trim($v);
            if (strpos($v,' ')) {
                $tmp = explode(' ',$v);
                $v = trim(reset($tmp));
                $alise = end($tmp);
            }
           
            if (strpos($v,'.')) {
                $tmp = explode('.',$v);
                $res = sprintf('`%s`.`%s`',trim($tmp[0]),trim($tmp[1]));
            } else {
                 $res = sprintf('`%s`',trim($v));
            }

            if (isset($alise)) {
                $list[] = sprintf('`%s AS %s',$res , $alise);
                unset($alise);
            } else {
                $list[] = $res;
            }

        }


        return join(',',$list);
    }

    /**
     * 排序
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    protected  function order($order)
    {
        if (!$this->order) $this->order = ' ORDER BY ';
        $this->order .= $order;
    }

    /**
     * [limit description]
     * @param  [type] $pagenum [description]
     * @param  string $offset  [description]
     * @return [type]          [description]
     */
    protected  function limit($pagenum,$offset = '')
    {

        if (is_array($pagenum)) $limit = join($pagenum,',');
        else if ($offset) $limit = $offset .','.$pagenum;
        else $limit = $pagenum;
        $this->limit .= ' LIMIT '.$limit;
    }

    /**
     * having
     */
    protected function having($field,$op = null,$value = '',$logic = 'and')
    {
        $logic = trim(strtoupper($logic));
        if (!in_array($logic,['AND','OR'])) {
            throw new ModelException('logic错误',1001);
        }

        if (is_array($field) ) {

            foreach ($field as $k => $v) {
                $having = $this->getWhere($k,'=',$v);
                if ($this->having) {
                    $this->having .= sprintf(' %s %s',$logic,$having);
                } else {
                    $this->having .= sprintf(' HAVING %s',$having);
                } 
            }

            return null;
        } else if ($field instanceof Closure){

            $having = $this->getWhere('','FUNC',$field);
            return null;

        } else if (is_string($field) &&  ($op instanceof Sql)){//子查询

            $where = $this->getWhere('','child',$op,$field);

        } else if ( is_string($field) && is_null($op)){

            if (is_array($op)) {
                if (array_intersect_key($this->prepareData,$op)) {
                     throw new ModelException('having 参数错误',1014);
                }
                $this->prepareData += $op;
            } else if (!is_null($op)) {
                throw new ModelException('having 参数错误',1014);
            }
            $having = $field;

        } else if (is_string($field) && !is_null($op) && ($value instanceof Sql)) {
            
            $having = $this->getWhere($field,'child',$value,$op);

        } else if (is_string($field) &&  ($op instanceof Sql)){//子查询

            $having = $this->getWhere('','child',$value,$op);

        } else if(is_string($field) && !is_null($op) && !is_null($value)) {

            $having = $this->getWhere($field,$op,$value);

        } else {

            throw new ModelException('having 参数错误',1014);

        }

        if ($this->having) {
            $this->having .= sprintf(' %s %s',$logic,$having);
        } else {
            $this->having .= sprintf(' HAVING %s',$having);
        }  
    }

    /**
     *其他传入的函数，会放置在select 里面
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:35
     *
     * @param $func
     * @param $arg
     */
    protected function func($func,$arg,$other,$alise= '')
    {
        $func = $this->getFunc($func,$arg,$other);
        if ($this->select) {
            $this->select .= ','.$func;
        } else {
            $this->select .= $func;
        }
        
    }

    /**
     * 构造函数
     * @return [type] [description]
     */
    private function getFunc($func,$arg,$other)
    {
        $arg = $this->getField($arg);
        switch ($func) {
            case 'from_unixtime':
                return $func = sprintf('%s(%s,"%s")  %s',$func,$arg,$other,$alise);
            default:
               return $func = sprintf('%s(%s) as %s',$func,$arg,$other);
        }
    }

    /**
     *返回字符串后清空相关数据，用于重用对象
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:59
     */
    protected function clear()
    {
         $this->select = '';
         $this->where = '';
         $this->join = '';
         $this->order = '';
         $this->group = '';
         $this->having = '';
         $this->limit = '';
         $this->prepareData = [];
    }

    /**
     * 转换预查询数据
     * @param  string $data [description]
     * @return [type]       [description]
     */
    protected function prepare(string $data)
    {
        if (!$this->prepare) return $data;
        $key = $this->preparesignal.uniqid();
        $this->prepareData[$key] = $data;
        return $key;

    }

    /**
     * 获取预查询数据
     * @return [type] [description]
     */
    protected function getPrepareData()
    {
        return $this->prepareData;
    }

    /**
     * 设置预查询
     * @param boolean $p [description]
     */
    protected function setPrepare(bool $p = false)
    {
        $this->prepare = $p;
    }
}

