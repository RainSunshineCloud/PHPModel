<?php
namespace Driver;

use ModelException;

class PDODriver extends \PDO
{
	protected static $config = [
		'uri' 		=> 'mysql',
		'db'  		=> 'test1',
		'host' 		=> 'localhost',
		'port'		=> '3306',
		'user'		=> 'root',
		'password' 	=> '12',	
	];

	protected static $driver = null;

	public $result = null;

	public function __construct()
	{
		$dsn = sprintf('%s:dbname=%s;host=%s;port=%s',self::$config['uri'],
			self::$config['db'],self::$config['host'],self::$config['port']);
		return parent::__construct($dsn,self::$config['user'],self::$config['password']);
	}

	/**
	 * 设置参数
	 * @param array
	 */
	public static function setConfig(array $config)
	{
		self::$config = array_merge(self::$config,$config);
	}

	/**
	 * 查询
	 * @param  string $sql    [sql]
	 * @param  array  $params [参数]
	 * @param  string $mode   [模式]
	 * @return array
	 */
	public function query(string $sql,array $params,string $mode = '')
	{

		if (!$result = $this->prepare($sql)) {
			throw new ModelException('PDODriver预查询错误',1015);
		}

		if (!$result->execute($params)) {
			$error = $result->errorInfo();
			throw new ModelException($error[2],$error[1]);
		}

		switch (strtoupper($mode) ) {
			case 'OBJ':
				return $result;
			case 'GROUP':
				return $result->fetchAll(\PDO::FETCH_ASSOC | \PDO::FETCH_GROUP);
			case 'GCOLUMN': //类似
				return $result->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_COLUMN);
			case 'ACOLUMN': //类似array_column
				return $result->fetchAll(\PDO::FETCH_UNIQUE | \PDO::FETCH_COLUMN);
			default:
				return $result->fetchAll(\PDO::FETCH_ASSOC);

		}
		
	}

	/**
	 * 更新/插入
	 * @param  string       $sql       [sql语句]
	 * @param  array        $bind      [数组]
	 * @param  bool|boolean $getLastId [最后的Id]
	 * @return bool | int
	 */
	public function execute(string $sql,array $bind,bool $getLastId = false,$exec = 'update')
	{
		if (!$result = $this->prepare($sql)) {
			throw new ModelException('PDODriver预查询错误',1015);
		}

		if (! $res = $result->execute($bind)) {
			return false;
		}
		$res = stripos(trim($sql),'insert');
		
		if ($res == 0 && $getLastId == true) {
			$lastId = $this->lastInsertId();
			return  $lastId ? $lastId : false;
			
		} else {
			if (! $result->rowCount()) {
				return false;
			} else {
				return true;
			}
		}
	}

}