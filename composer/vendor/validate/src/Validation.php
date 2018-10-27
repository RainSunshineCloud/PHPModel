<?php
namespace RainSunshineCloud\Validation;

use RainSunshineCloud\Validation\Utils\Helper;
use RainSunshineCloud\Validation\ValidateFunc;
use RainSunshineCloud\Validation\FilterFunc;

class Validation
{
    //验证规则
	protected $publicValide = [
		'name' 	=> 'string:0,20',
		'id'   	=> 'int',
		'ip'	=> 'ip',
		'price' => 'man',
	];

    //过滤规则
    protected $publicFilter = [
        'name'  => 'strtotime'
    ];

    //验证错误信息
	protected $errMsg = [
		'name'	=> '用户名必须是字符串',
        'id'    => '必须是非0整数'
	];

    //验证规则项目
	protected $scope = [
		'edit' => [
			'name',
			'id',
			['id','max:20','最大值为20']
		],

	];

    //临时过滤规则
    protected $tmpFilter = [];
    //单例
    protected static $instance = null;
    //临时过滤规则
    protected $tmpValidate = [];
    //数据
	protected $data = [];

    /**
     * 验证规则
     * @param  string      $field   字段名
     * @param  array|null  $ruleArr 验证规则
     * @param  string|null $errMsg  错误信息           
     */
    protected function parseRule(string $field, array $ruleArr = null,string $errMsg = null)
    {

        if (is_null($ruleArr)) {
            if (empty($this->publicValide[$field])) {
                throw new ValidateException("该场景{$field}未有验证规则",1001);
            }

            if (!isset($this->data[$field])) {
                throw new TypeException($field.'必须',1001);
            }

            $ruleStr =  $this->publicValide[$field];
            $ruleArr = Helper::explode($ruleStr,'|');
        } 
       
                
        foreach ($ruleArr as $rule) {
            if (is_array($rule)) {
                if (!class_exists($rule[0])) {
                   throw new ValidateException("未有{$rule[0]}验证器",1002);
                }
                 $class = new $rule[0]();
                $rule = $rule[1];
            } else {
                $class = get_called_class();
            }

        	if (\strpos($rule,':')) {
        		list($name, $args) = \explode(':', $rule, 2);
        		$name = \trim($name);
        		$args = \explode(',', $args);
        		array_unshift($args,$this->data[$field]);
        	} else {
        		$name = \trim($rule);
        		$args = [$this->data[$field]];
        	}

        	
        	if (method_exists($class,$name)) {
        		if (!call_user_func_array([$class,$name],$args)) {
                    if (is_object($class)) {
                        unset($class);
                    }
                    $this->returnErr($field,$name,$errMsg);
                }
                if (is_object($class)) {
                    unset($class);
                }
                return true;
        	} else if (method_exists('RainSunshineCloud\Validation\ValidateFunc',$name)){
                if (is_object($class)) {
                    unset($class);
                }
                if (!call_user_func_array(['RainSunshineCloud\Validation\ValidateFunc',$name],$args)) {
                    $this->returnErr($field,$name,$errMsg);
                }
                return true;
            } else {
                if (is_object($class)) {
                    unset($class);
                }
                
        		throw new ValidateException("该验证器未有{$name}方法",1003);
        	}
        }
    }

    /**
     * 过滤规则   
     */
    protected function parseFilter()
    {
        $rule =  array_merge($this->publicFilter,$this->tmpFilter);
        foreach ($rule as $field => $ruleStr) {
            $ruleArr = Helper::explode($ruleStr,'|');
            if (isset($this->data[$field])) {
                $tmp_data = $this->data[$field];
            } else {
                $this->data[$field] = null;
                continue;
            }
            
            foreach ($ruleArr as $rule) {
                $class = get_called_class();

                if (\strpos($rule,':')) {
                    list($name, $args) = \explode(':', $rule, 2);
                    $name = \trim($name);
                    $args = \explode(',', $args);
                    array_unshift($args,$tmp_data);
                } else {
                    $name = \trim($rule);
                    $args = [$tmp_data];
                }
                
                if (method_exists('RainSunshineCloud\Validation\FilterFunc',$name)){
                    $tmp_data = call_user_func_array(['RainSunshineCloud\Validation\FilterFunc',$name],$args);  
                } else {
                    throw new ValidateException(sprintf('Filter=>%s方法不存在',$name),1008);
                }

            }
            $this->data[$field] = $tmp_data;
        } 
    }

    /**
     * 错误信息返回
     * @param   string      $field 字段名
     * @param   string      $name  验证方法名
     * @param   string      $msg   错误信息
     * 
     */
    protected function returnErr(string $field,string $name,string $msg = null)
    {
        if (is_null($msg)) {
            if (!isset($this->errMsg[$field])) {
                throw new ValidateException($field.'错误信息必须',1004);
            } 

            if (is_array($this->errMsg[$field]) ) {
               if (isset($this->errMsg[$field][$name])) {
                    $msg = $this->errMsg[$field][$name];
               } else {
                    throw new ValidateException(sprintf('%s=>%s错误信息必须',$field,$name),1005);
               }
               
            }  else {
                $msg = $this->errMsg[$field];
            }
        }
        


        throw new TypeException($msg,1002);
    }

    /**
     * 检查
     * @param  [string] $scope  应用场景
     */
    public function check(string $scope)
    {
    	if (empty($this->scope[$scope]) || !is_array($this->scope[$scope])) {
    		throw new ValidateException('未有该验证类型',1006);
    	}

        $this->parseFilter();
    	$scopeArr = $this->scope[$scope];
        $scopeArr = array_merge($scopeArr,$this->tmpValidate);

    	foreach ($scopeArr as $scope) {
            
    		if (is_array($scope) ) {
                if (isset($scope[0]) && isset($scope[1]) && isset($scope[2])) {
                    $res = $this->parseRule($scope[0],[$scope[1]],$scope[2]);
                } else {
                    throw new ValidateException('scope必须是数量为3的索引数组',1007);
                }
               
            } else {
                $res = $this->parseRule($scope);
            }
    		
    	}
        $this->tmpValidate = [];
        $this->tmpFilter = [];
        return $this->data;
    }


    /**
     * 临时验证规则
     * @param   [string]  $field  [字段名]
     * @param   [string]  $rule   [规则]
     * @param   [string]  $errMsg [错误信息]
     * @return obj
     */
    public function validate(string $field,string $rule,string $errMsg)
    {
        if (strpos($rule,'.')) {
            $rule = Helper::explode($rule,'.');
        } 
        array_push($this->tmpValidate,[$field,$rule,$errMsg]);
        return $this;
    }

    /**
     * 数据
     * @param  array  $data 
     * @return obj
     */
    public function data(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 单例
     * @return [type] [description]
     */
    public static function instance()
    {
        if (!self::$instance) {
            $class = get_called_class();
            return self::$instance = new $class();
        }
        
        return self::$instance;
    }

    /**
     * 过滤器
     * @param  string $field  键名
     * @param  string $rule   规则
     * @return obj
     */
    public function filter(string $field,string $rule)
    {
        $this->tmpFilter[$field] = $rule;
        return $this;
    }

}



