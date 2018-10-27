<?php
use RainSunshineCloud\Validation\{TypeException,ValidateException};

include '../src/Utils/Helper.php';
include '../src/TypeException.php';
include '../src/ValidateException.php';
include '../src/validateFunc.php';
include '../src/FilterFunc.php';
include '../src/validation.php';
include './User.php';
include './Admin.php';

try{
	$data = User::instance()
				->data(['name'=>'sdfs'])
				->validate('id','user.max:1000','最大值为1000')
				->filter('id','decodeId')
				->check('edit');
	var_dump($data);
} catch (TypeException $e) {
	echo $e->getMessage();
} catch (ValidateException $e) {
	// echo $e->getMessage();
} catch (\Exception $e) {
	// echo $e->getMessage();
}

