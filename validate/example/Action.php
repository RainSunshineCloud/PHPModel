<?php
use RainSunshineCloud\Validation\{TypeException,ValidateException};

include '../src/Utils/Helper.php';
include '../src/TypeException.php';
include '../src/ValidateException.php';
include '../src/validateFunc.php';
include '../src/validation.php';
include './User.php';
include './Admin.php';

try{
	$var_dump = User::instance()
				->data(['name'=>'sdfs','id'=> 10])
				->validate('id','admin.max:10','最大值为10')
				->check('edit');
} catch (TypeException $e) {
	echo $e->getMessage();
} catch (ValidateException $e) {
	echo $e->getMessage();
} catch (\Exception $e) {
	echo $e->getMessage();
}

