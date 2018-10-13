<?php

class PassPort
{
	public function __construct()
	{

	}

	/**
	 * 验证器
	 * @return [type] [description]
	 */
	public function validate($input)
	{

	}

	public static function getPwd ();

	public static function verifyPwd();

	public  function Login($data) {

		if (self::validate($data)) {//验证器

		}
		if (self::verifyPwd()) {//

		}
	}
}