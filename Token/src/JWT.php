<?php
namespace Token;

use Token\TokenException;

class JWT 
{
	private static $algo = 'sha256';
	private static $keys = 'sdfs';
	private static $openssl_algo = 'AES-256-CBC';
	private static $oppenssl_key = 'sdfsdfsasdfsfsserwedsfsdfsdfewerwf';
	private static $iv = '1q3w12121d2e4242';
	private static $type = 'JWT';
	private static $startTime = 0;
	private static $expire = 1;
	private static $endTime = 0;
	private static $refresh = 20;
	private static $refreshTime = 0;

	public static function encode($payload,$private_payload = null)
	{
		$res = '';

		self::$startTime = self::$startTime ? self::$startTime : $_SERVER['REQUEST_TIME'];
		self::$refreshTime = self::$refreshTime ? self::$refreshTime : self::$startTime + self::$refresh;
		self::$endTime = self::$endTime ? self::$endTime : self::$startTime + self::$expire;
		$header = [
			'type' 	=> self::$type,
			'algo' 	=> self::$algo,
			'st'  	=> self::$startTime,
			'et' 	=> self::$endTime,
			'ft' 	=> self::$refreshTime,
		];
		print_r($header);
		$res .= self::enHeader($header);
		$res .= '.'.self::enPayload($payload);
		
		if ($private_payload){
			$res .= '.'.self::enPrivatePayload($private_payload);
		}

		$res .= '.'.self::enSignature($res);
		return str_replace(['+','/','='],['-','_',''],base64_encode($res));

	}

	/**
	 * 解密
	 * @param  [type] $token [description]
	 * @return [type]        [description]
	 */
	public static function decode(string $token)
	{
		$token = self::urlsafe_b64decode($token);

		if ($token == false) {
			throw new TokenException('decode 失败',1001);
		}

		$res = explode('.',$token);
		$count = count($res);

		if ( $count == 3) {
			$payload = self::dePayload($res[1]);
			$sign = self::deSignature($res);
			$header = self::deHeader($res[0]);
			return $payload;
		} else if ($count == 4) {
			$payload = self::dePayload($res[1]);
			$sign = self::deSignature($res);
			$header = self::deHeader($res[0]);
			$private = self::dePrivatePayload($res[2]);
			return [
				'public' => $payload,
				'private' => $private,
			];
		} else {
			throw new TokenException('decode 失败',1001);
		}
	}

	/**
	 * header信息
	 * @param  array  $header [description]
	 * @return [type]         [description]
	 */
	private static function enHeader(array $header):string
	{
		$header = json_encode($header);

		if ($header === false) {
			throw new TokenException('header 加密失败',1001);
		}

		$res = base64_encode($header);

		if ($res === false) {
			throw new TokenException('header 加密失败',1001);
		}

		return $res;
	}

	/**
	 * 解密
	 * @param  string $header [description]
	 * @return [type]         [description]
	 */
	private static function deHeader(string $header)
	{
		$header = base64_decode($header);

		if ($header === false) {
			throw new TokenException('header 解密失败',1001);
		}

		$res = json_decode($header,true); 

		if (!$res) {
			throw new TokenException('header 解密失败',1001);
		}

		if (empty($res['type']) || $res['type'] != self::$type) {
			throw new TokenException('header 解密失败',1001);
		}

		if (empty($res['type']) || $res['algo'] != self::$algo) {
			throw new TokenException('header 解密失败',1001);
		}

		if (empty($res['st']) || $_SERVER['REQUEST_TIME'] < $res['st'] || empty($res['et']) || empty($res['ft'])) {
			throw new TokenException('header 解密失败',1001);
		}

		if ($_SERVER['REQUEST_TIME'] > $res['ft']) {
			throw new TokenException('token 过期',1002);
		}

		self::$startTime = $res['st'];
		self::$endTime = $res['et'];
		self::$refreshTime = $res['ft'];
		
		if ($_SERVER['REQUEST_TIME'] > $res['et']) {
			self::$startTime = 0;
			self::$endTime = 0;
		}

		return $res;
	}

	/**
	 * 公共信息
	 * payload
	 * @return [type] [description]
	 */
	private static function enPayload($payload)
	{
		if (is_array($payload) || is_object($payload)) {
			$payload = json_encode($header);

			if ($payload === false) {
				throw new TokenException('payload 加密失败',1001);
			}
		}

		$res = base64_encode($payload);

		if ($res === false) {
			throw new TokenException('payload 加密失败',1001);
		}

		return $res;
	}

	/**
	 * 解密公共信息
	 * @param  string $payload [description]
	 * @return [type]          [description]
	 */
	private static function dePayload(string $payload)
	{
		$payload = base64_decode($payload);

		if ($payload === false) {
			throw new TokenException('payload 解密失败',1001);
		}

		$res = json_decode($payload,true); 

		if (is_null($res)) {
			return $payload;
		}

		return $res;
	}

	/**
	 * 签名
	 * @param  string $sign [description]
	 * @return [type]       [description]
	 */
	private static function enSignature(string $sign)
	{
		$res = \hash_hmac(self::$algo,$sign,self::$keys);

		if ($res === false) {
			throw new TokenException('sign 加密失败',1001);
		}

		return $res;
	}

	/**
	 * 验证签名
	 * @param  array  $token [description]
	 * @return [type]        [description]
	 */
	private static function deSignature(array $token)
	{
		$sign = array_pop($token);
		$en_sign = self::enSignature(join('.',$token));

		if ($sign !== $en_sign) {
			throw new TokenException('sign 解密失败',1001);
		}

		return true;
	}

	/**
	 * 加密敏感信息
	 * @param  [type] $content [description]
	 * @return [type]          [description]
	 */
	private static function enPrivatePayload($content)
	{
		if (is_array($content) || is_object($content)) {
			$content = json_encode($content);

			if ($content == false) {
				throw new TokenException('private_payload 加密失败',1001);
			}
		}
		$tag = '';
	    $content = openssl_encrypt($content,self::$openssl_algo,self::$oppenssl_key,0, self::$iv);
	    
	    if ($content == false) {
			throw new TokenException('private_payload 加密失败',1001);
		}

	    return $content;
	}

	/**
	 * 加密
	 */
	private static function dePrivatePayload(string $content)
	{
		$content = openssl_decrypt($content,self::$openssl_algo,self::$oppenssl_key, 0, self::$iv);
		if ($content == false) {
			throw new TokenException('private_payload 解密失败',1001);
		}

		$res = json_decode($content,true);
		if (is_null($res)) {
			return $content;
		} 

		return $res;
	}

	/**
	 * 设置签名加密秘钥
	 * @param [type] $keys [description]
	 */
	public static function setKeys(string $keys)
	{
		self::$keys = $keys;
	}

	/**
	 * 设置私有信息加密向量
	 * @param [type] $iv [description]
	 */
	public static function setIv(string $iv)
	{
		self::$iv = $iv;
	}

	/**
	 * 设置私有信息加密的秘钥
	 * @param [type] $keys [description]
	 */
	public static function setSslKeys(string $keys)
	{
		self::$oppenssl_key = $keys;
	}

	/**
	 * 设置签名加密方法
	 * @param [type] $algo [description]
	 */
	public static function setAlgo(string $algo)
	{
		self::$algo = $algo;
	}

	/**
	 * 这是私有信息加密方法
	 * @param [type] $algo [description]
	 */
	public static function setSslAlgo(string $algo)
	{
		self::$openssl_algo = $algo;
	}

	/**
	 * 安全base
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	private static function urlsafe_b64decode(string $data) {

   		$data = str_replace(array('-','_'),array('+','/'),$data);
   		$mod4 = strlen($data) % 4;

   		if ($mod4) {
       		$data .= substr('====', $mod4);
   		}
   		
   		return base64_decode($data);
 	}

 	private static function expire(int $time)
 	{
 		self::$expire = $time;
 	}

 	private static function refresh(int $time)
 	{
 		self::$refresh = $time;
 	}

}