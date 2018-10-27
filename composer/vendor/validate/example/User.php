<?php
use RainSunshineCloud\Validation\Validation;
class User extends Validation
{
	public static function max($val)
	{
		return true;
	}
}