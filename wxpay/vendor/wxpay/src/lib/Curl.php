<?php
namespace WxPay\lib;

class Curl
{
	private $ch = null;

	/**
	 * 设置ssl证书
	 * @param [type] $ssl_cert_path [description]
	 * @param [type] $ssl_key_path  [description]
	 */
	public function setSSL($ssl_cert_path,$ssl_key_path)
	{
		if (!$this->ch) {
			$this->ch = curl_init();
		}
		
		curl_setopt($this->ch,CURLOPT_SSLCERTTYPE,'PEM');
		curl_setopt($this->ch,CURLOPT_SSLKEYTYPE,'PEM');
		curl_setopt($this->ch,CURLOPT_SSLCERT, $ssl_cert_path);
		curl_setopt($this->ch,CURLOPT_SSLKEY, $ssl_key_path);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
		return $this;
	}

	/**
	 * post请求
	 * @param  string $url  [description]
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function post(string $url, array $data = [], int $timeout)
	{
		if (!$this->ch) {
			$this->ch = curl_init();
		}
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($this->ch, CURLOPT_HEADER, FALSE);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($this->ch, CURLOPT_POST, TRUE);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($this->ch,CURLOPT_URL, $url);
		$res = curl_exec($this->ch);

		if ($res) {
			curl_close($this->ch);
			return $res;
		}

		$error = curl_errno($this->ch);
		curl_close($this->ch);
		return 'skdfjksfjk';
		throw new CurlException("curl出错，错误码:$error");
	}

	/**
	 * get请求
	 * @param  string $url  [description]
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function get(string $url, array $data = [] ,int $timeout)
	{

		if (!$this->ch) {
			$this->ch = curl_init();
		}
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($this->ch, CURLOPT_HEADER, FALSE);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);

		if ($data) {
			if (strpos($url,'?')) {
				$url .= '&' . http_build_query($data);
			} else {
				$url .= '?' . http_build_query($data);
			}
		}

		$res = curl_exec($this->ch);

		if ($res) {
			curl_close($this->ch);
			return $res;
		}

		$error = curl_errno($this->ch);
		curl_close($this->ch);
		throw new CurlException("curl出错，错误码:$error");
			
	}
}


class CurlException extends \Exception {}