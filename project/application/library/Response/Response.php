<?php
namespace Response;

use \Response\ResponseException;

class Response
{
	protected static $format = 'Response\Factory\Json';
	protected static $obj = null;
	protected static $successCode = 1;
	protected static $errorCode = 2;
	protected static $self = null;
	protected static $params = [];
	protected static $msg = '';

	protected function __construct(){}

	public static function setDefaultFormat($format)
	{
		self::$format = 'Response\Factory\\'.ucfirst($format);
	}

	/**
	 * 响应
	 * @param  [type] $func  [description]
	 * @param  [type] $param [description]
	 * @return [type]        [description]
	 */
	public static function __callStatic($func,$param)
	{
		if (self::$obj === null) {
			self::$obj = new self::$format();
			self::$self = new self();
		}
		
		if (method_exists(self::$self,$func)) {
			call_user_func_array([self::$self,$func],$param);
		} else if (method_exists(self::$obj,$func)) {

			if ($func == 'success') {

				if (isset($param[0])) {
					$this->setData($param[0]);
				}

				$param[0] = self::$params;

				if (isset($param[1]) && is_int($param[1] + 0)) {
					self::$errorCode = $param[1];
				} 

				$param[1] = self::$errorCode;

				if (isset($param[2]) && is_string($param[2])) {
					$this->setMsg($param[2]);
				}

				$param[2] = self::$msg;
				
				$res = call_user_func_array([self::$obj,$func],$param);
				$this->response($res);

			} else if ($func == 'error'){

				$res = call_user_func_array([self::$obj,$func],$param);
				$this->response($res);

			} else {

				if (isset($param[0]) && is_string($param[0])) {
					$this->setMsg($param[0]);
				}

				$param[0] = self::$msg;

				

				if (isset($param[1]) && is_int($param[1] + 0)) {
					self::$errorCode = $param[1];
				} 

				$param[1] = self::$errorCode;

				if (isset($param[2])) {
					$this->setData($param[2]);
				}

				$param[2] = self::$params;

				call_user_func_array([self::$obj,$func],$param);

			}
			
			
		} else {
			throw new ResponseException("Response 未有{$func}该方法");
		}
		
		return self::$self;
	}

	/**
	 * 响应
	 * @param  [type] $func  [description]
	 * @param  [type] $param [description]
	 * @return [type]        [description]
	 */
	public function __call($func,$param)
	{

		if (method_exists(self::$self,$func)) {
			call_user_func_array([self::$self,$func],$param);
		} else if (method_exists(self::$obj,$func)) {

			if ($func == 'success') {

				if (isset($param[0])) {
					$this->setData($param[0]);
				}

				$param[0] = self::$params;

				if (isset($param[1]) && is_int($param[1] + 0)) {
					self::$errorCode = $param[1];
				} 

				$param[1] = self::$errorCode;

				if (isset($param[2]) && is_string($param[2])) {
					$this->setMsg($param[2]);
				}

				$param[2] = self::$msg;

				$res = call_user_func_array([self::$obj,$func],$param);
				$this->response($res);

			} else if ($func == 'error'){

				if (isset($param[0]) && is_string($param[0])) {
					$this->setMsg($param[0]);
				}

				$param[0] = self::$msg;

				

				if (isset($param[1]) && is_int($param[1] + 0)) {
					self::$errorCode = $param[1];
				} 

				$param[1] = self::$errorCode;

				if (isset($param[2])) {
					$this->setData($param[2]);
				}

				$param[2] = self::$params;

				$res = call_user_func_array([self::$obj,$func],$param);
				$this->response($res);

			} else {

				call_user_func_array([self::$obj,$func],$param);

			}
				
		} else {
			throw new ResponseException("Response 未有{$func}该方法");
		}

		

		return self::$self;
	}

	/**
	 * 设置成功码
	 * @param [type] $code [description]
	 */
	protected static function setSuccessCode($code)
	{
		self::$successCode = $code;
	}

	/**
	 * 设置错误码
	 * @param [type] $code [description]
	 */
	protected static function setErrorCode($code)
	{
		
		self::$errorCode = $code;
	}

	/**
	 * 设置返回数据
	 * @param [type] $params [description]
	 * @param [type] $key    [description]
	 */
	protected static function setData($params,$key = null)
	{
		if ($key) {
			$params = [$key => $params];
		}

		if (is_array(self::$params) && is_array($params)) {
			self::$params = self::$params + $params;
		} else {
			self::$params = $params;
		}
		
	}

	/**
	 * 设置返回信息
	 * @param string $msg [description]
	 */
	protected static function setMsg(string $msg)
	{
		if ($msg) {
			self::$msg = $msg;
		}
		
	}

	/**
	 * 响应
	 * @param  string $data       [description]
	 * @param  [type] $auth_token [description]
	 * @return [type]             [description]
	 */
	protected static function response(string $data)
	{
		echo $data;
		exit;
	}

	/**
	 * 设置auth
	 * @param  string $auth_token [description]
	 * @return [type]             [description]
	 */
	protected static function auth(string $auth_token)
	{
		header('x-auth-token:'.$auth_token);
	}


}