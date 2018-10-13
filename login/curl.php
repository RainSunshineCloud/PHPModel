<?php


$match = [];
$arr = mb_convert_encoding(getUrl('http://www.rrbj.net/'),'UTF-8', 'GB2312');

file_put_contents('info.html',$arr);

function getUrl(string $url, array $field =[], bool $isReturn = true, $method = 'get', array &$error=[])
{
	//初始化
	if (! $ch = curl_init($url) ) {
	 	$error = [
			'number'=>curl_errno($ch),
			'msg'=>'初始化失败',
		];
		return false;
	}

	$opt = [];//设置参数
	
	//是否直接显示
	if (! in_array($isReturn,[true,false],true)) {
		$error = [
			'number'=>100,
			'msg'=>'是否直接显示（isReturn）只能填写true 或者 false',
		];
		return false;
	}

	//请求方式
	$method = strtoupper($method);
	if (! in_array($method,['GET','POST','DELETE','PUT'])) {
		$error = [
			'number'=>'101',
			'msg'=>'请求方法只能是以下四种：get,post,delete,put',
		];
		return false;
	}
	
	//判断是否有用户名和密码
	if (isset($field['pwd']) && isset($field['user'])) {//验证用户名和密码

		$opt[CURLOPT_USERPWD] = $field['user'].':'.$field['pwd'];
		unset($field['user']);
		unset($field['pwd']);

	} else if (isset($field['user'])) {//验证使用中的密码

		$opt[CURLOPT_USERNAME] = $field['user'];
		unset($field['user']);

	} else if ( isset($field['pwd']) ) {//仅仅设置密码

		$error = [
			'number' => '102',
			'msg'=>'密码验证时用户名不能为空'
		];
		return false;

	}

	$opt[CURLOPT_RETURNTRANSFER] = $isReturn;
	$opt[CURLOPT_CUSTOMREQUEST] = $method;
	$opt[CURLOPT_POSTFIELDS] = http_build_query($field);
	$opt[CURLOPT_COOKIEJAR] = 'cookie.txt';
	curl_setopt_array($ch,$opt);
	//执行
	$res = curl_exec($ch);

	if ($res === false) {
		$error = [
			'number'=>curl_errno($ch),
			'msg'=>curl_error($ch),
		];
		return false;

	}
	//关闭资源
	curl_close($ch);
	return $res;
}