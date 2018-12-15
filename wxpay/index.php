<?php

include __DIR__.'/vendor/autoload.php';

use \WxPay\{WxPay,WxPayException};


try {

	WxPay::setAppId('sjkdj');
	WxPay::setMerchanId('sdksj');
	WxPay::setTradeType('JSAPI');

	$wx = new WxPay();
	$wx->setNotifyUrl('sdkfjskj')->setOpenId('sjdkf')->setBody('sdfskfj')->setOrderNum('sdjfskjk')->setTotalFee(3242342)->getUnifiedOrderData();
} catch (WxPayException $e) {
	var_dump($e->getMessage());
}

// $pay_model = new WxPayData('sdf','sdf','JSAPI','md5','sdfs','sdfsd','sdfs');
// $pay_model->setUnifiedOrderData([
// 	'openid' => 'sdkf',
// 	'notify_url' => 'sdkfj',
// ]);


// WxPayApi::setDataModel($pay_model);
// try {
// 	// WxPayApi::unifiedOrder();
// 	WxPayApi::orderQueryByTradeNumber('342');
// } catch (Exception $e) {
// 	echo $e->getMessage();
// }
