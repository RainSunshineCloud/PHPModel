<?php
include '../src/Sql.php';
include '../src/SqlException.php';
$model = Sql::table('table');
$sql = $model->where('id','=',1)
			->update(['name'=>2,'id'=>1]);
		
var_export($sql);


$sql = $model->where('id','=',1)->where('id','=','2');

$sql = Sql::table('s')->where('id','=',$sql)->update(['name'=>1,'id'=>1]);
var_export($sql);