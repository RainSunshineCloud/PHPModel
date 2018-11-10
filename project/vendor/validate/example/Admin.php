<?php
use RainSunshineCloud\Validation\{Validation,ValidateException};

class Admin extends Validation
{
	public static function max($val)
	{
		return false;
	}
}