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
    protected $temTable = null;
    protected $having = '';
    protected $sql = null;
    protected $limit = '';
    protected $begintransaction = false;

    protected function __construct() {}

    /**
     *连接
     * User: qing
     * Date: 2018/6/2
     * Time: 下午7:50
     *
     * @param array  $join_table 连接的表 可用形式有['table','join table'=>'on',...] ['join table'=>'on',...]
     * @param string $join 连接方式，默认左连接
     */
    protected  function join(array $join_table,$join = 'left')
    {

        if(isset($join_table[0]))
        {
            $this->temTable = $join_table[0];
            unset($join_table[0]);
        }

        foreach ($join_table as $k => $v) {
            $this->join .= ' '.$join.' join '.$k.' on '.$v;
        }

    }

    /**
     *查询条件
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:01
     *
     * @param        $where 查询条件
     *                      方式1：['字段'，'操作'，'值'] 操作省略默认为= 如 ['id','in',[1,2,3]]=>id in (1,2,3)
     *                      方式2：['逻辑词'=>[方式1,方式1]，逻辑词省略默认为and
     *                      方式2：['字段'=>'1'] ; 字段=1, 逻辑词默认为and 操作默认为 =
     *                      方式3： 连环嵌套，自动加括号
     *                      方式4： 四种可混用
     *                      方式5： 可在值上传入子查询
     * @param string $and
     */
    protected  function where($where,$and = 'and')
    {
        if (!$this->where) $this->where .= ' where ';
        else $this->where .= ' '.$and.' ';
        $where = $this->getWhere($where,$and);

        if ($where[0] == '(' ) {
            $where = str_split($where,1);
            array_pop($where);
            unset($where[0]);
            $where = join($where,'');
        }
        $this->where .= $where;


    }

    /**
     *where 和 having 条件生成方法，不建议直接调用
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:11
     *
     * @param $where 条件
     * @param $and  逻辑词
     * @return string 生成条件
     */
    protected function getWhere ($where,$and)
    {

        $arr = [];
        foreach ($where as $k=>$v) {
            if (is_array($v)) {
                if (!is_string($k)) $k = 'and';
                $arr[] = $this->getWhere($v,$k);
            } else {
                if (!isset($where[0])) {
                   return $this->arrayTransform($where);
                }
                if (count($where) == 2) {
                    return $where[0].' = '.$where[1];
                }
                $this->getOperator($where);
                return join($where,' ');
            }

        }

        if (count($arr) > 1) return '('.join($arr,' '.$and.' ').')';
        return '('.join($arr,' '.$and.' ').')';
    }

    /**
     *操作生成方法 不建议直接调用
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:12
     *
     * @param $where 查询字段
     * @return string 操作
     */
    protected  function getOperator(&$where) {


        switch ($where[1]) {
            case '=':
            case '<':
            case '>=':
            case '<=':
            case '>':
                return '';
            default :
                if (is_array($where[2])) $where[2] = '('.join($where[2],',').')';
                else $where[2] = '('.$where[2].')';

        }
    }

    /**
     *查询字段，不传入或者不调用则为 '*'
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:13
     *
     * @param $select
     */
    protected  function select($select = '*')
    {
        if (!$this->select) $this->select = ' select ';
        if (is_string($select) ) $this->select .= $select;
        if (is_array($select))$this->select .= join($select,',');
    }

    /**
     *获取查询字符串
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:15
     *
     * @param array $obj 可传入子查询，共有4种可传入多个子查询各自对应['from'=>[],'where'=>[]]
     *                   ['from'=>$obj]
     *                   ['where'=>where方法五]
     *                   ['having'=>having方法五]
     *                   [where方法五] 这种方式默认为where
     * @return string
     */
    protected function get($obj = [])
    {

        if (!$this->temTable) $this->temTable = $this->prefix.$this->table;
        if (!$this->select) $this->select = 'select *';
        $this->childQuery($obj);
        return $this->sql .= $this->select.' from '.$this->temTable.$this->join.$this->where.$this->group.$this->having.$this->order.$this->limit.';';
    }

    /**
     *表名
     * User: RyanWu
     * Date: 2018/6/1
     * Time: 20:28
     *
     * @param $table
     */
    protected static function table($table)
    {
        $this->table = $table;

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
     *更新操作
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:21
     *
     * @param       $data 要更新的数据,只能一条一条更新
     * @param array $obj where 子句是否传入子查询
     * @return string
     */
    protected static function update($data,$obj = [])
    {
        $sql = '';
       foreach ($data as $k=>$v) {
           $sql .= ' '.$k.'='.$v.' and';
       }
       $sql = rtrim($sql,'and');
       if (!$this->temTable) $this->temTable = $this->prefix.$this->table;
       if (!empty($obj)) $this->childQuery($obj);

       return $this->sql = 'update '.$this->temTable.' set '.$sql . $this->where.';';
    }

    /**
     *子查询 不建议直接操作子查询 , 这样查询条件就很变态了，极度影响效率
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:23
     *
     * @param array $obj 和子查询传入方法一样
     * @param string $and 逻辑操作符，仅仅对where和having有效
     */
    protected  function childQuery(array $obj = [],$and = 'and')
    {
        if (isset($obj[0]) && !is_array($obj[0])) $obj = [$obj];
        $class = __CLASS__;
         foreach ($obj as $k => $v) {
             if (!$k) $k = 'where';
             $parm = ($k == 'from') ? $v :$v[2];
             $parm = $parm instanceof $class ? $parm->get(): $parm;
             $parm = '('.rtrim($parm,';').')';
             switch ($k) {
                 case 'from':
                     $this->temTable = $parm;
                     break;
                 case 'having':
                     $v[2] = $parm;
                     $this->having($v,$and);
                     break;
                 default:
                     $v[2] = $parm;
                     $this->where($v,$and);
             }
         }

    }

    /**
     *插入
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:26
     *
     * @param        $data 共有三种方式;
     *                     方式1插入一条: ['id'=>'1']
     *                           方式2插入多条：[['id']=>'1']
     *                           方式3插入多条：[[1,2,3],[3,4,5]],$field
     * @param array  $field 字段名，会按字段名.
     * @param string $func 会将值传入，可自定义回调函数进行处理传入的值
     * @return string 更新语句
     */
    protected  function insert($data,$field = [],$func = '')
    {
        if (!$this->temTable) $this->temTable = $this->prefix.$this->table;

        $sql = '';
        foreach($data as $v) {
            if ($func) call_user_func($func,$v);
            if ($field == [] && !is_array($v)) return $this->insertOne($data);
            if($field == [] && is_array($v)) {
                ksort($v);
            }
            $sql .= ' ('.join($v,',').'),';
        }
        if (!$field) $field = array_keys($v);
        $sql = rtrim($sql,',');

        return $this->sql = ('insert into '.$this->prefix.$this->table.'('.join($field,',').') values'.$sql).';';
    }

    /**
     *插入一条相当于insert的第一种方式
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:30
     *
     * @param $data
     * @return string
     */
    protected  function insertOne($data)
    {
        return $this->sql = 'insert into'.$this->temTable.'('.join(array_keys($data),',').') values ('.join($data,',').');';
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
        $obj->connect();
        if (method_exists(get_called_class(),$func)) {
            call_user_func_array([$obj,$func],$arg);
            return $obj;
        }
        return false;
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
    public  function __call($func,$arg)
    {
        if (method_exists(get_called_class(),$func)) {
            $res = call_user_func_array([$this,$func],$arg);
            if ($res) {
                $this->clear();
                return $res;
            }

            return $this;
        } else {
            $this->otherFunction($func,$arg);
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
        if (!$this->group) $this->group = ' group by ';
        $this->group .= $group;
    }

    /**
     *直接传
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:32
     *
     * @param $order
     */
    protected  function order($order)
    {
        if (!$this->order) $this->order = ' order by ';
        $this->order .= $order;
    }

    /**
     *有三种方式
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:32
     *方式1：[1,1]；方式2：1，1 方式3 （1，1）
     * @param        $pagenum  每页的数量
     * @param string $offset 跳过多少条
     */
    protected  function limit($pagenum,$offset = '')
    {

        if (is_array($pagenum)) $limit = join($pagenum,',');
        else if ($offset) $limit = $offset .','.$pagenum;
        else $limit = $pagenum;
        $this->limit .= ' limit '.$limit;
    }

    /**
     * having 子句，用法和where 一样
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:35
     *
     * @param        $having
     * @param string $and
     */
    protected  function having($having,$and = 'and')
    {

        if (!$this->having) $this->having .= ' having ';
        else $this->having .= ' '.$and.' ';
        $having = $this->getWhere($having,$and);

        if ($having[0] == '(' ) {
            $having = str_split($having,1);
            array_pop($having);
            unset($having[0]);
            $having = join($having,'');
        }
        $this->having .= $having;
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
    protected  function otherFunction($func,$arg)
    {
        if (is_array($arg)) $str = join($arg,',');
        if (!$this->select ) $this->select .= 'select ';
        $this->select .= $func.'('.$str.')';
    }

    /**
     *where 和 having 传入键值对处理方式，不建议直接调用。通过where或having调用
     * User: qing
     * Date: 2018/6/2
     * Time: 下午8:36
     *
     * @param $where
     * @return string
     */
    protected function arrayTransform($where)
    {
        $res = [];
        $temp = [];
        foreach ($where as $k=>$v) {
             $temp[0] = $k;
             $temp[1] = '=';
             $temp[2] = $v;
             $res[] = $temp;
        }
        return $this->getWhere($res,'and');
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
         $this->temTable = null;
         $this->having = '';
         $this->limit = '';
    }

    /**
     *数据库连接实现逻辑
     * User: qing
     * Date: 2018/6/2
     * Time: 下午9:17
     */
    protected function connect()
    {

    }
}


/**
 * Class Mysql 例子，主要是要使用单一入口，可搭建事务模型，减少if这一类的判断
 */
class Mysql
{
    protected  $connect = null;

    function __construct()
    {

    }

    function get(Sql $sql)
    {
        $sql->get()
    }

    function update(Sql $sql)
    {
        $sql->update()
    }

    function insert()
    {

    }

    /**
     *侧重点，可通过构建和sql类似的模型来建立事务模型（因为事务是建立在结果的基础上，所以最好建立在具体的数据库模型上，可通过接口建立
     * User: qing
     * Date: 2018/6/2
     * Time: 下午9:36
     */
    protected function begin()
    {

    }

    protected function end()
    {

    }
}

var_dump(Mysql::table('df'));