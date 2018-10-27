<?php


class Model 
{
	protected static $driver = null;
	protected static $drive = 'PDO';
	protected static $lastSql = [];

	private    $boot = false;
	protected  $prefix = null;
	protected  $table = null;
	private    $Sql = null;

	/**
	 * 静态调用生成驱动和对象
	 */
	public static function __callStatic($methods,$args)
	{
		
		if (!self::$driver) {//驱动设置在静态属性
			$model = sprintf('Driver\%sDriver',self::$drive);
			self::$driver = new $model();
		}
		$method_up = strtoupper($methods); 
		if ($method_up == 'QUERY') {
			return call_user_func_array([$this,'query'], $args);
		} else if ($method_up == 'GETLASTSQL') {
			return self::getLastSql($args);
		} else if (in_array($method_up ,['BEGINTRANSACTION','ROLLBACK','COMMIT'])) {
			return call_user_func_array([self::$driver,$methods], $args);
		} 
		$model = get_called_class();
		$obj = new $model();
		$obj->Sql = call_user_func_array(['Sql',$methods], $args);
		if ($obj->boot == false) {
			$obj->init($method_up,$methods);
		}
		return $obj;
	}

	/**
	 * 初始化
	 * @return [type] [description]
	 */
	protected function init($method_up,$methods)
	{
		//初始化表
		
		if ($method_up != 'TABLE') {
			if ($this->table) {
				$this->Sql = call_user_func_array([$this->Sql,'table'], [$this->table]);
			} else if (($table = get_called_class()) && strtoupper($table) != 'MODEL') {
				
				$table = substr($table,0,-5);
				if ($table) {
					$this->Sql = call_user_func_array([$this->Sql,'table'], [strtolower($table)]);
				}
			}
		} 

		//初始化表前缀
		if ($method_up != 'PREFIX') {
			if ($this->prefix) {
				$this->Sql = call_user_func_array([$this->Sql,'prefix'], [$this->prefix]);
			}
		} 
		echo 1;
		$this->boot = true;

	}

	/**
	 * 静态调用(注意，没有值时默认传入空数组)
	 */
	public function __call($methods,$args)
	{
		if (!self::$driver) {//驱动设置在静态属性
			$model = sprintf('Driver\%sDriver',self::$drive);
			self::$driver = new $model();
		}
		if (in_array($methods,['get'])) {
			return $this->select($args);
		} else if (method_exists($this, $methods)) {
			return call_user_func_array([$this,$methods], $args);
		} else if (method_exists(self::$driver,$methods)){ //调用DRIVER
		    return call_user_func_array([self::$driver,$methods], $args);
		} else if (!$this->Sql) {
			$this->Sql = call_user_func_array(['Sql',$methods], $args);
		} else {
			$this->Sql = call_user_func_array([$this->Sql,$methods], $args);
		} 
		if ($this->boot == false) {
			$this->init(strtoupper($methods),$methods);
		}
		return $this;
	}

	/**
	 *查找
	 *模式有如下几种,默认正常返回二维数组
	 *OBJ 返回预处理后的Driver对象
	 *GROUP 返回第一个字段做群组,剩余字段为value的三维数组
	 *GCOLUMN 返回第一个字段做群组,第二个字段为value的二维数组
	 *ACOLUMN 返回第一个字段为值的一维数组
	 *
	 * @param  string    $mode 模式
	 * @return array
	 */
	protected function select($mode = '')
	{	
		$res = $this->Sql->get();
		self::$lastSql = $res;
		$res = self::$driver->query($res['sql'],$res['data'],$mode);
		return $res;
	}

	/**
	 * 查找一条数据
	 */
	protected function find()
	{
		$res = $this->Sql->limit(1)->get();
		self::$lastSql = $res;
		$res = self::$driver->query($res['sql'],$res['data']);
		if (empty($res) && empty($res[0])){
			return [];
		}else {
			return $res[0];
		}

	}

	/**
	 * 插入
	 * @param  array   $data  插入数据(二维数据)
	 * @param  Closure $func  回调函数，传入每个插入值
	 * @param  array   $field 更新字段(对应索引数组)
	 * @return bool
	 */
	protected function insertAll(array $data,$func = '',$field = [])
	{
		$res = $this->Sql->insertAll($data,$field,$func);
		self::$lastSql = $res;
		return self::$driver->execute($res['sql'],$res['data']);


	}

	/**
	 * 插入
	 * @param  array | $obj $data  插入数据
	 * @param  bool|boolean $getId 更新字段
	 * @param  Closure | array | string  
	 * @return bool|int;
	 */
	protected function insert($data,bool $getId = false,$func = '')
	{
		$res = $this->Sql->insert($data,$func);
		self::$lastSql = $res;
		return self::$driver->execute($res['sql'],$res['data'],$getId);
	}

	/**
	 * 更新
	 * @param  array   $data 更新数据
	 * @param  Closure $func 回调函数，传入每个更新值
	 * @return bool
	 */
	protected function update(array $data,$func = '')
	{
		$res = $this->Sql->update($data,$func);
		self::$lastSql = $res;
		return self::$driver->execute($res['sql'],$res['data']);
	}

	/**
	 * 删除
	 */
	protected function delete()
	{
		$res = $this->Sql->delete();
		self::$lastSql = $res;
		return self::$driver->execute($res['sql'],$res['data']);
	}

	/**
	 * 设置驱动
	 */
	public static function setDrive($drive)
	{
		self::$drive = $drive;
	}

	/**
	 * 获取最后的Sql语句
	 * @return 获取最后参数
	 */
	protected static function getLastSql($is_string = [])
	{
		if ($is_string && self::$lastSql) {
			return str_replace(array_keys(self::$lastSql['data']),self::$lastSql['data'],self::$lastSql['sql']);
		}

		return self::$lastSql;
	}


	/**
	 * 获取Sql模型
	 * @return Obj;
	 */
	protected function getSql()
	{
		return $this->Sql;
	}
	
	/**
	 * 克隆时克隆Sql模型
	 */
	public function __clone()
	{
		$this->Sql = clone $this->Sql;
	}

	/**
	 * 清空对象，并关闭模型
	 * @return [type] [description]
	 */
	protected function close ()
	{
		self::$driver = null;
		$this->Sql = null;
	}
	
}