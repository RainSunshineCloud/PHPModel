<?php
namespace Response\Factory;

class Json
{
	public $successCode = null;
	public $errorCode = null;
	public $params = [];

	/**
	 * [success description]
	 * @param  [type] $data [description]
	 * @param  [type] $code [description]
	 * @return [type]       [description]
	 */
	public function success($data = [],int $code = null,string $msg = '')
	{
		$res = [];

		if ($code) {
			$res['code'] = $code;
		} else {
			$res['code'] = $this->successCode;
		}

		$res['data'] = $data;
		$res['msg'] = $msg;
		
		$res = json_encode($res,JSON_UNESCAPED_UNICODE);
		return $res;
	}

	public function error(string $msg = '',int $code = null,$data = null)
	{
		$res = [];

		if ($code) {
			$res['code'] = $code;
		} else {
			$res['code'] = $this->errorCode;
		}

		$res['data'] = $data;
		$res['msg'] = $msg;
		return json_encode($res,JSON_UNESCAPED_UNICODE);
	}


}