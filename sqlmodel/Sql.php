<?php
class Sql
{
    public $select = '';
    public $table = null;
    public  $prefix = '';
    public $obj  = null;
    public $where = '';
    public $join = '';
    public $update = '';
    public $insert = '';
    public temTable = '';

    protected static __construct() {}

    protected  static function join(array $join_table,$join = 'left')
    {
        if(isset($join_table[0]))
        {
            $this->temTable($join_table[0]);
            unset($join_table[0]);
        }

        foreach ($join_table as $k => $v) {
            $this->join .= $join.'join '.$k.' on '.$v;
        }
        return $obj;
    }

    protected static function where($where)
    {
         $this->where = trim($this->getWhere($where),')(');

    }

    private static function getWhere ($where)
    {
        foreach ($where as $k=>$v) {

            if (is_array($v)) {
                if (!is_string($k)) $k = 'and';
                $k = ' '.$k.' ';
                $arr[$k] = $this->getWhere($v);
            } else {
                return join($where,'');
            }

        }
        if (count($arr) > 1) return '('.join($arr,$k) .')';
        return join($arr,$k);
    }

    protected static function select($selct)
    {
        $this->select = join($v,',');
    }

    protected static function get($obj = [])
    {
        if (!$this->temTable) $this->temTable = $this->table;
        $this->childQuery($obj);
        return $this->sql .= $this->select .' from '.$this->temTable.$this->join.$this->where;
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


    protected static function update($data,$obj = [])
    {
       foreach ($data as $k=>$v) {
           $sql .= $k.'='.$v.' ';
       }

       if (isset($obj['where'])) $this->childQuery($obj);
       $this->update = 'update '.$this->table.' set '.$sql . $this->where;
    }

    protected static function childQuery($obj = [])
    {
        $class = __CLASS__;
         foreach ($obj as $k => $v) {
             $parm = ($k == 'from') ? $v[3] :$v;
             if (!$parm instanceof $class) {
                 $this->err = "必须是Sql类或继承Sql类";
                 exit;
             }

             switch ($k) {
                 case 'where':
                     $this->where .= join($v);
                 case 'from':
                     $this->temTable = $v;
                 case 'having':
                     $this->having .= join($v);
                 case 'group':
                     $this->order .= join($v);
                 default:
                     return "子查询设置有误";
             }
         }
    }

    protected static function insert($data,$field = [])
    {
        if ($field == []) return $this->insertOne($data);
        $sql = '';
        foreach($data as $v) {
            $sql .= ' ('.join($v,',').'),';
        }
        $sql = rtrim($sql,',').';';

        return ('insert into '.$this->table.'('.join($field,',').') values'.$sql);
    }

    protected static function insertOne($data)
    {
        return 'insert into'.$this->table.'('.join(array_values($data),',').') values ('.join($sql,1).');';
    }

    public static function __callStatic($func,$arg)
    {
        if ($this->select) $this->select .= 'select ';
        if (method_exists(get_called_class(),$func)) {
            return call_user_func_array([$this,$func],$arg)
        } else {

        }
    }

    public static function group()
    {

    }

    public static function order()
    {

    }

    public static function limit()
    {

    }

    public static function otherFunction($func,$arg)
    {
        if (is_array($arg)) $str = join($arg,',');
        $this->select .= $func.'('.$str.')';
    }

}
