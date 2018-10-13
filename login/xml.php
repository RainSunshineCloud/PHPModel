<?php
include "./simple_html_dom.php";

getContentByDom('div#plandt');


// $plandt = $plandt;
// $span = explode('<br>',$plandt);
// $res = [];

// foreach ($span as $k => $v) {
// 	$code = [];
// 	$times = [];
// 	$res[$k] = [];
	
// 	preg_match_all('/(\d+\-\d+期)\s*冠军计划\s*【(.+)】\s*(\d+期)\s*([\x{4e00}-\x{9fa5}])/u',$v,$code);
// 	if (count($code) == 5) {
// 		$res[$k]['times'] = $code[1][0];
// 		$res[$k]['code'] = $code[2][0];
// 		$res[$k]['this'] = $code[3][0];
// 		$res[$k]['res'] = $code[3][0];
// 	}
// }

var_dump($res);exit;


function getContentByDom($span)
{
	$lang = "UTF-8";
	$html = new simple_html_dom();
	$html->load_file('http://www.rrbj.net/');

	$obj = $html->find('meta[http-equiv="Content-Type"]',0);
	if ($obj) {
		$attr = $obj->attr;
	}

	if ($attr && isset($attr['content'])) {
		 $lang = trim(strchr($attr['content'],'='),'=');
	} 
	
	
	$context = $html->find($span);
	foreach($context as $v) {
		var_dump($v->plaintext);
	}


	$html->clear();
	$plandt = mb_convert_encoding($plandt,'UTF-8', $lang);

	foreach($context as $v) {
		var_dump($v);exit;
	}
}
     