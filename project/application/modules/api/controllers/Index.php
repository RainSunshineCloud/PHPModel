<?php
use Response\Response;
use Token\JWT;
class IndexController extends Yaf\Controller_Abstract
{
	public function indexAction()
	{
		// Response::error('错误',2);
		// $list = ['id'=>1,'code'=>2];
		
		$data = [];
		$token = isset($_SERVER['HTTP_X_AUTH_TOKEN'])?$_SERVER['HTTP_X_AUTH_TOKEN']:'';
		if (!empty($token)) {
			$data = JWT::decode($token);
			$res = JWT::encode(1,['中国']);
		} else {
			$res = JWT::encode(1,['中国']);
		}
		
		print_r($data);

		Response::setErrorCode('2')
			->auth($res)
			// ->setMsg('水电费')
			// ->setMsg('滚')
			// ->setData('2')
			->success(['1'=>1]);

		//Array ( [type] => JWT [algo] => sha256 [st] => 1540636899 [et] => 1540637899 [ft] => 1540646899 )
		//Array ( [type] => JWT [algo] => sha256 [st] => 1540636756 [et] => 1540637756 [ft] => 1540646756 )
		//
		// $this->redirect('http://www.baidu.com');
	}
}