<?php
namespace WxPay;

use WxPay\lib\{WxPayBase,CurlException};

/**
 * 支付类
 */
class WxPay extends WxPayBase
{
 	/**
     * 统一下单接口
     * @return [type] [description]
     */
    public function getUnifiedOrderData()
    {
        try {

            if (empty(self::$appid)) {
                throw new WxPayException("appid必填，请使用setAppId方法设置");
            }

            if (empty(self::$merchan_id)) {
                throw new WxPayException("merchan_id必填，请使用setMerchanId方法设置");
            }

            if (empty(self::$trade_type)) {
                throw new WxPayException("trade_type必填，请使用setTradeType方法设置");
            }

            //验证必备参数
            if (self::$trade_type == "JSAPI" && empty($this->data['openid'])) {
                throw new WxPayException("trade_type为JSAPI时，openid为必填参数！使用setOpenId方式设置");
            } else if (self::$trade_type == "NATIVE"  && empty($this->data['product_id'])) {
                throw new WxPayException("trade_type为NATIVE时，product_id为必填参数！使用setProductId方式设置");
            } 

            if ( empty($this->data['notify_url'])) {
                throw new WxPayException("使用setNotifyUrl方法设置异步通知接口");
            }

            if (empty($this->data['body'])) {
                throw new WxPayException("商品描述必填，请使用setBody方法设置");
            }

            if (empty($this->data['out_trade_no'])) {
                throw new WxPayException("订单号必填，请使用setOrderNum方法设置");
            }

            if (empty($this->data['total_fee'])) {
                throw new WxPayException("订单号必填，请使用setTotalFee方法设置（单位为分）");
            }

            


            //添加参数
            $this->data['appid'] = self::$appid;
            $this->data['mch_id'] = self::$merchan_id;
            $this->data['nonce_str'] = $this->setNonceStr();
            $this->data['sign_type'] = self::$sign_type;
            $this->data['trade_type'] = self::$trade_type;
            $this->data['spbill_create_ip'] = $this->getIp();
            //签名
            $this->data['sign'] = $this->makeSign($this->data);

            list($sign,$return_data) = $this->curl->unifiedOrder($this->data,$this->curl_timeout);

            if ($return_data['appid'] != self::$appid || $return_data['mch_id'] != self::$merchan_id) {
                throw new WxPayException("不明来源的数据");
            }

            $cal_sign = self::MakeSign($return_data);
            
            if ($cal_sign != $sign) {
                throw new WxPayException('签名验证失败，数据异常');
            }

            if ($return_data['result_code'] != 'SUCCESS') {
                if (isset($return_data['err_code_des']) && isset($return_data['err_code'])) {
                    throw new WxPayException('请求失败，错误信息：'.$return_data['err_code_des'].'错误代码:'.$return_data['err_code']);
                } else {
                    throw new WxPayException('请求失败，未知原因');
                }
            }

            return $return_data;
        } catch (CurlException $e) {
            throw new WxPayException($e->getMessage());
        }      
    }
}

/**
 * 支付异常类
 */
class WxPayException Extends \Exception 
{

}