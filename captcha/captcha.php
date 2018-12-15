<?php

class Captcha
{

	//字体文件
	protected static $fontfile = __DIR__.'/font/VeraSansBold.ttf';
	//字体大小
	protected static $size = 60;
	//验证码
	protected static $choose_text = '';
	//可选择文字
	protected static $text_arr = [
		'number' => [0,1,2,3,4,5,6,7,8,9],
		'alpha' => ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'],
		'lower_alpha-number' => [2,3,4,6,7,8,9,'a','b','c','d','e','f','g','h','j','k','m','n','p','r','s','t','u','v','w','x','y'],
		'all_alpha-number' => [2,3,4,6,7,8,9,'a','b','d','e','f','g','h','j','m','n','r','t','u','y','A','B','D','E','F','G','H','J','M','N','Q','R','T','U','Y'],
	];
	//选择文字
	protected static $text = 'all_alpha-number';
	//宽度
	protected static $width = 120;
	//高度
	protected static $height = 40;
	//背景颜色
	protected static $background = [128,128,128];
	//字体个数
	protected static $num = 4;
	//画布
	private static $im;
	//画点个数
	protected static $pixel_num = 60;
	//划线个数
	protected static $line_num = 2;
	//旋转范围
	protected static $angle = [0,2,4,6,7,9,11,13,16,17,19,23,25,28,333,336,339,342,345,347,348,350,353,356,360];
	//字符串
	protected static $error = '';

	/**
	 * 创建验证码
	 * @param  string
	 * @return string 验证码文本
	 */
	public static function create($filepath = '')
	{
		self::createFontImages();
		self::createDot(self::$pixel_num);
		self::createLine(self::$line_num);
		self::printOut($filepath);
		return self::$choose_text;
	} 

	/*
	*生成字体图像
	*/
	protected static function createFontImages()
	{
		$rate = intval(self::$size / 20);
		$width = mt_rand(5,10) * $rate;
		$height = [];
		$i = 0;
		$text_arr = self::$text_arr[self::$text];

		while($i < self::$num) {
			$text = $text_arr[array_rand($text_arr)];
			$angle = self::$angle[array_rand(self::$angle)];
			self::$choose_text .= $text;

			$p_array = imagettfbbox(self::$size,$angle,self::$fontfile,$text);
			$now_height = max($p_array[1],$p_array[3],$p_array[5],$p_array[7]);
			$text_tmp[] = [$angle,$width, $now_height, $text];
			$width += max($p_array[0],$p_array[2],$p_array[4],$p_array[6]) - min($p_array[0],$p_array[2],$p_array[4],$p_array[6]) + mt_rand(2,5) * $rate;
			$height = array_merge($height,[$p_array[1],$p_array[3],$p_array[5],$p_array[7]]);
			$i++ ;
		}


		$height = max($height) - min($height);
		self::$im = imagecreate($width,$height);
		list($red,$green,$blue) = self::$background;
		$int = imagecolorallocate(self::$im,$red,$green,$blue);
		$tmp_height = $height - self::$size/10;
		foreach ($text_tmp as $array) {
			$tmp_red = mt_rand($red + 30,250);
			$tmp_blue =  mt_rand(0,$blue - 30);
			$tmp_green = mt_rand($tmp_blue,$tmp_red);
			$color = imagecolorallocate(self::$im,$tmp_red,$tmp_green,$tmp_blue);
			$p_array = imagettftext(self::$im,self::$size,$array[0],$array[1],intval($tmp_height - $array[2] ),$color,self::$fontfile,$array[3]);
		}

		self::resizeImage($width,$height);
	}

	/**
	 *调整图片大小至目标大小
	 * @param  [int] 源图像宽度
	 * @param  [int] 源图像高度
	 */
	protected static function resizeImage(int $width,int $height)
	{

		if (self::$width == $width && self::$height == $height) {
			return self::$im;
		}

		$im = imagecreate(self::$width,self::$height);
		imagecopyresampled($im,self::$im,0,0,0,0,self::$width,self::$height,$width,$height);
		imagedestroy(self::$im);
		self::$im = $im;
	}

	/**
	 * 花点
	 * @param  int
	 * @return [type]
	 */
	protected static function createDot(int $num)
	{
		$i = 0;
		while ($i < $num) {
			$color = imagecolorallocate(self::$im,mt_rand(0,255),mt_rand(0,255), mt_rand(0,255));
			imagesetpixel(self::$im,mt_rand(0,self::$width),mt_rand(0,self::$height),$color);
			$i++;
		}
		
	}

	/**
	 * 划线
	 * @param  int
	 * @return [type]
	 */
	protected static function createLine(int $num)
	{
		$width_mid = self::$width/2;
		$height_mid = self::$height/2;
		$i = 0;
		while ($i < $num) {
			$w   = imagecolorallocate(self::$im, mt_rand(0,255),mt_rand(0,255), mt_rand(0,255));
			$red = imagecolorallocate(self::$im, mt_rand(0,255),mt_rand(0,255), mt_rand(0,255));
			$style = array($red, $red, $red, $red, $red, $w, $w, $w, $w, $w);
			imagesetstyle(self::$im, $style);
			imageline(self::$im,mt_rand(0,$width_mid),mt_rand(0,$height_mid),mt_rand($width_mid,self::$width), mt_rand($height_mid,self::$height), IMG_COLOR_STYLED);
			$i++;
		}
	}

	/**
	 * 输出
	 * @param  string
	 * @return [type]
	 */
	protected static function printOut(string $filepath = '')
	{
		if ($filepath) {
			return imagepng(self::$im,$filepath);
		}
		header('Content-Type: image/png');
		imagepng(self::$im);
		imagedestroy(self::$im);
	}

	/**
	 * 设置字体大小
	 * @param int
	 */
	public static function setSize(int $size)
	{
		self::$size = $size;
	}

	/**
	 * 设置图片宽度
	 * @param int
	 */
	public static function setWidth(int $width)
	{
		self::$width = $width;
	}

	/**
	 * 设置图片高度
	 * @param int
	 */
	public static function setHeight(int $height)
	{
		self::$height = $height;
	}

	/**
	 * 设置背景颜色
	 * @param array
	 */
	public static function setBackgorund(array $background)
	{
		self::$background = $background;
	}

	/**
	 * 设置可选择的code 文本
	 * @param string
	 * @param array
	 */
	public static function setCodeArr(string $key,array $code)
	{
		self::$text_arr[$key] = $code;
	}

	/**
	 * 设置要从CodeArr选择的文本
	 * @param string
	 */
	public static function setChooseText(string $key)
	{
		self::$text = $key;
	}

	/**
	 * 设置验证码数量
	 * @param int
	 */
	public static function setTextnum(int $num)
	{
		self::$num = $num;
	}

	/**
	 * 设置干扰点的数量
	 * @param int
	 */
	public static function setPixelNum(int $num)
	{
		self::$pixel_num = $num;
	}

	/**
	 * 这只干扰线的数量
	 * @param int
	 */
	public static function setLineNum (int $num)
	{
		self::$line_num = $num;
	}

	/**
	 * 设置随机角度的范围
	 * @param int
	 */
	public static function setAngle(array $arr)
	{
		self::$num = $arr;
	}

	public static function __callStatic($methods,$params)
	{
		$count = 1;
		$method = strtolower(str_replace('get','',$methods,$count));
		if ( is_string($method) && isset(self::${$method})) {
			return self::${$method};
		}

		return false;
	}

}

