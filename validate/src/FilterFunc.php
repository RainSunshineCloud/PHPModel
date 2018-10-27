<?php
namespace RainSunshineCloud\Validation;
/**
 * 数据转化接口，用于将外部信息，转化为标准格式的信息
 */
class FilterFunc
{
	public static function decodeId($val)
	{
		if (!is_string($val)) {
			return $val;
		}
		return str_ireplace(['a','b','c'],[1,2,3],$val);
	}

	public static function strtotime($val)
	{
		if (!is_string($val)) {
			return $val;
		}
		return str_ireplace(['a','b','c'],[1,2,3],$val);
	}
}


