<?php

include '../src/driver/PDODriver.php';
include '../src/Sql.php';
include '../src/Model.php';
include '../src/ModelException.php';

class UserModel extends Model
{
	protected $table = null;
	public function __construct() {
		
	}
}

// $res = UserModel::where('id','=',1)->where('id','=',2);

// $res = Model::where('id','=',1);
