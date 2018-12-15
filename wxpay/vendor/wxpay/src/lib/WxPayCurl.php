<?php
namespace WxPay\lib;

class WxPayCurl extends Curl
{


	protected function toXml(array $data)
	{
		$str = '<xml>';
		foreach ($data as $k => $v) {
			if (is_numeric($v)) {
				$str .= sprintf('<%s>%s</%s>',$k,$v,$k);
			} else {
				$str .= sprintf('<%s>[CATAD[%s]]</%s>',$k,$v,$k);
			}
		}
		$str .= '<xml>';

		return $str;
	}

	protected function fromXml($data)
	{
		return [
			'sign' => 'ksdjf',
			'nonce_str' => 'ksdjfk',
			'appid' => 'sjkdj',
			'mch_id' => 'sdksj',
			'return_code' => 'SUCCESS',
			'return_msg' => 'ksdjfk',
			'result_code'=> 'SUCCESS',
			'sign' => '282E6A409301C296D70BE73B53B49688',
		];
	}

	/**
	 * 统一下单
	 * @param  integer $timeOut [description]
	 * @return [type]           [description]
	 */
	public function unifiedOrder($data,$time_out = 6)
	{
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";

		$xml = $this->toXml($data);
		$response = $this->post($url,[$xml],$time_out);
		$result = $this->fromXml($response);

		if (empty($result) || empty($result['return_code'])  ) {
			throw new CurlException("请求异常");
		}

		if ($result['return_code'] != 'SUCCESS') {
			if (empty($result['return_msg'])) {
				throw new CurlException('请求异常，key可能泄露');
			} else {
				throw new CurlException($result['return_msg']);
			}
		} else if (empty($result['sign']) || empty($result['nonce_str']) || empty($result['appid']) || empty($result['mch_id']) || empty($result['result_code'])) {
			throw new CurlException('异常数据，key可能泄露');
		}

		$sign = $result['sign'];
		unset($result['sign']);
		return [$sign , $result];
	}
	
	/**
	 * 订单查询
	 * @param  string  $trade_no [description]
	 * @param  integer $timeOut  [description]
	 * @return [type]            [description]
	 */
	public static function orderQueryByTradeNumber(string $trade_no, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/pay/orderquery";
		//检测必填参数
		if (empty($trade_no)) {
			throw new WxPayException("out_trade_no 必填");
		}

		$data = self::$data_model->getUnifiedOrderData();
		$xml = Utils::ToXml($data);
		$response = Utils::postXmlCurl($xml, $url, $data['merchan_id'],false, $timeOut);
		$result = utils::FromXml($response);
		if($result['return_code'] != 'SUCCESS') {
			throw new WxPayException('下单失败!');
			return false;
		} else {

			if (empty($result['sign'])) {
				throw new WxPayException('异常数据');
				return false;
			} 

			if (!Utils::CheckSign($result,self::$data_model->getKey())) {
				throw new WxPayException('签名验证失败，数据异常!');
			}
		}
		
		return $result;
	}

	public static function login ($code,$timeOUt = 6)
	{
		$url = 'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=%s';
		$data = self::$data_model -> getLoginData();
		$url = sprintf($url,$data['app_id'],$data['secret'],$code,$data['grant_type']);
	}
}

