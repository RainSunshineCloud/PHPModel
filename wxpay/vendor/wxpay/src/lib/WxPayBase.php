<?php
namespace WxPay\lib;
use WxPay\WxPayException;

class WxPayBase
{
    protected static $appid = null;
    protected static $merchan_id = null;
    protected static $trade_type = null;
    protected static $sign_type = 'MD5';
    protected static $app_secret = null;
    protected static $key = null;
    protected $curl_timeout = null;
    protected $curl = null;


    public function __construct(int $curl_timeout = 60) 
    {
        $this->curl_timeout = $curl_timeout;
        $this->curl = new WxPayCurl();
    }

    /**
     * 签名
     * @return  $this
     */
    public function makeSign(array $data)
    {
        ksort($data);

        $string = "";

        foreach ($data as $k => $v)
        {           
            $string .= $k . "=" . $v . "&";
        }
        
        $string = trim($string, "&");

        $string = $string.'&key='.self::$key;

        switch (self::$sign_type) {
            case 'MD5':
                $string = md5($string);
                break;
            case 'HMAC-SHA256':
                $string = hash_hmac("sha256",$string ,self::$key);
                break;
            default:
                throw new WxPayException("签名类型不支持");
                break;
        }

        return strtoupper($string);
    }

    /**
     * 获取随机字符串
     * @param int|integer $length [长度]
     * @return  string 随机字符串
     */
    public function setNonceStr(int $length = 32) 
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {  
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
        } 
        return $str;
    }

    /**
     * 获取ip地址
     * @return string ip;
     */
    public function getIp()
    {
        $addr = ['HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','HTTP_X_FORWARDED','HTTP_X_CLUSTER_CLIENT_IP','HTTP_FORWARDED_FOR','HTTP_FORWARDED','REMOTE_ADDR'];

        foreach ($addr as $key) {
            if (array_key_exists($key, $_SERVER)) {

                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    // if ((bool) filter_var($ip, FILTER_VALIDATE_IP,FILTER_FLAG_IPV4|FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE)) {
                    //     return $ip;
                    // }
                    return $ip;
                }
            }
        }

        throw new WxPayException("无法获取ip地址");
    }

    /**
     * 设置商户号
     * @param string $mch_id [商户号]
     */
    public static function setMerchanId(string $mch_id)
    {
        if (mb_strlen($mch_id) > 32) {
            throw new WxPayException('不合法的appid');
        }

        self::$merchan_id = $mch_id;
    }

    /**
     * 设置小程序
     * @param string $trade_type [产品好]
     */
    public static function setTradeType(string $trade_type)
    {
        if (!in_array($trade_type,['NATIVE','JSAPI','APP','MWEB'])) {
            throw new WxPayException('不合法的trade_type,现包含如下类型:NATIVE(native支付) | JSAPI(jsapi或小程序支付) | APP(app支付) | MWEB(H5支付) ');
        }

        self::$trade_type = $trade_type;
    }

    /**
     * 设置appid
     * @param string $appid [description]
     */
    public static function setAppid(string $appid)
    {
        if (mb_strlen($appid) > 32) {
            throw new WxPayException('不合法的appid');
        }

        self::$appid = $appid;
    }

    /**
     * 设置signtype
     * @param string $sign_type [description]
     */
    public static function setSignType(string $sign_type)
    {
        if (!in_array($sign_type,['MD5','HMAC-SHA256'])) {
            throw new WxPayException('不合法的sign_type,现包含如下类型:MD5| HMAC-SHA256');
        }

        self::$sign_type = $sign_type;
    }

    /**
     * app_secret
     * @param string $app_secret [description]
     */
    public static function setAppSecret(string $app_secret)
    {
        self::$app_secret = $app_secret;
    }    

    /**
     * app_key
     * @param string $app_key [description]
     */
    public static function setAppKey(string $app_key)
    {
        self::$key = $app_key;
    }

    public function setDeviceInfo(string $device_info)
    {
        if (mb_strlen($device_info) > 32) {
            throw new WxPayException('不合法的deviceinfo');
        }

        $this->data['device_info'] = $device_info;
        return $this;
    }

    public function setBody(string $body)
    {
        if (mb_strlen($body) > 128) {
            throw new WxPayException('不合法的body');
        }

        $this->data['body'] = $body;
        return $this;
    }

    public function setDetail(string $detail)
    {
        if (mb_strlen($detail) > 60000) {
            throw new WxPayException('不合法的detail');
        }

        $this->data['detail'] = $detail;
        return $this;
    }


    public function setOrderNum(string $out_trade_no)
    {
        if (strlen($out_trade_no) > 32 || !preg_match('/^[\w\-\|\*]+$/',$out_trade_no)) {
            throw new WxPayException('不合法的out_trade_no');
        }

        $this->data['out_trade_no'] = $out_trade_no;
        return $this;
    }

    public function setFeeType(string $fee_type)
    {
        $this->data['fee_type'] = $fee_type;
        return $this;
    }

    public function setTotalFee(int $total_fee)
    {
        $this->data['total_fee'] = $total_fee;
        return $this;
    }

    public function setNotifyUrl(string $url)
    {
        if ( strpos($url,'?') !== false || mb_strlen($url) > 256 ) {
            throw new WxPayException('不合法的notify_url');
        }

        $this->data['notify_url'] = $url;
        return $this;

    }

    public function setProductId(string $product_id)
    {
        if (strlen($product_id) > 32) {
            throw new WxPayException('不合法的product_id');
        }

        $this->data['product_id'] = $product_id;
        return $this;
    }


    public function setLimitPay()
    {
        $this->data['limit_pay'] = 'no_credit';
        return $this;
    }

    public function setOpenId(string $openid)
    {
        if (strlen($openid) > 128) {
            throw new WxPayException('不合法的openid');
        }

        $this->data['openid'] = $openid;
        return $this;
    }

    /**
     * 设置额外数据
     * @param array $arr [description]
     */
    public function setAttach(string $attach) 
    {
        if (mb_strlen($attach) > 127) {
            throw new WxPayException('不合法的attach');
        }

        $this->data['attach'] = $attach;
        return $this;
    }
}
