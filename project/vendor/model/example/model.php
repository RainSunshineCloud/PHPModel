<?php

include '../src/driver/PDODriver.php';
include '../src/Sql.php';
include '../src/Model.php';
include '../src/ModelException.php';

try{
	$model = Model::table('users');
	$model1 = clone $model;
	/*$res = $model->field('name,id,password')->select();
	$res1 = $model1->field('name,id,password')->select('Group');*/
	// Model::beginTransaction();
	
	//select
	
	//原生
	// $res1 = $model1->where('exists (select 1,10,5 from users where `id` = 1)')
	// 			   ->field('"sdkfjsk","ksdfjskl","skdfjsk"',true)
	// 			   ->limit(1)->select();

	//$res = $model1->field('name,email')->where('id','in',[1])->select('acolumn');
	// $res = $model1->field('name,email')->where('id','=',1)->select('group');
	// $res = $model1->field('name,email')->where('id','=',1)->select('gcolumn');
	// $res = $model1->field('name,email')->where('id','between',[1,2])->select('gcolumn');
	// $res = $model1->field('name,email')->where(['id'=>1,'name'=>'Tito Koch'])->select('gcolumn');
	
	//where 子查询
	$model->where('id','=','1');
	var_dump($model->getSql());
	$res = $model1->field('name,email')->where('id','=',1)->where('exists',$model->getSql())->select();
	var_dump($model->getSql());

	var_dump($model->getSql());

	

    //insert + select where
	// $res = $model->insert($res1->getSql(),true,['name','email','password']);

	//insert
	//$res = $model->insert(['name'=>2,'email'=>'df','password'=>'b'],true);

	//insert multiple
	//$res = $model->insertAll([['name'=>3,'email'=>'dff','password'=>'b'],['name'=>4,'email'=>'dfs','password'=>'b']]);
	
	//update 
	//$res = $model->where('id','=',767)->update(['name'=>'wew','email'=>'1212']);

	//delete 
	//$res = $model->where('id','=',767)->delete();

	// Model::rollback();
	

	
	var_export($res);
	var_export($model::getLastSql(true));
	echo PHP_EOL;


} catch(ModelException $e) {
	var_export($model::getLastSql());
	exception_handle($e);
} catch (PDOException $e) {
	var_export($model::getLastSql());
	exception_handle($e);
} catch (\Exception $e) {
	var_export($model::getLastSql());
	exception_handle($e);
} catch (\Throwable $t) {
	var_export($model::getLastSql());
	error_handle($t);
}


function exception_handle($e) {
	var_export($e->getMessage());
	echo PHP_EOL;
	var_export($e->getLine());
	echo PHP_EOL;
	var_export($e->getFile());
	echo PHP_EOL;
	var_export($e->getCode());
	echo PHP_EOL;
}

function error_handle($t) {
	var_export($t->getMessage());
	echo PHP_EOL;
	var_export($t->getLine());
	echo PHP_EOL;
	var_export($t->getFile());
	echo PHP_EOL;
	var_export($t->getCode());
	echo PHP_EOL;
}





