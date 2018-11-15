<?php

class File
{
    //合法的类型
    protected $valid_type = ['text/csv'];
    //最大文件大小
    protected static $max_size = 200;
    //错误码
    protected $error_code = 0;
    //文件夹路径
    protected $dir = './upload/';
    //文件夹权限
    protected $mode = '0755';
    //文件名
    protected $file_name = '';
    //文件前缀
    protected $prefix_name = '';
    //文件后缀
    protected  $ext = '';
    //mime 和文件后缀对照关系
    protected static $mime_arr = [];
    /**
     * 上传
     * @param  string $input_name [长传的input名称]
     * @param  string $path       [文件路径，不传则使用默认路径]
     * @return bool
     */
    protected function upload(string $input_name,string $path = '')
    {
        if (empty($_FILES)) {
            $this->error_code = 4;
            return false;
        }
       
        if (empty($_FILES[$input_name])) {
            $this->error_code = 8;
            return false;
        }

        $file = $_FILES[$input_name];

        if ($file['error'] != 0) {
            $this->error_code = $file['error'];
            return false;
        }

        if (!$this->isValidType($file['type'])) {
            $this->error_code = 9;
            return false;
        }

        if (!$this->isValidSize($file['size'])) {
            $this->error_code = 9;
            return false;
        }

        if (!$this->addDir()) {
            $this->error_code = 12;
            return false;
        }

        $this->ext = strrchr($file['name'],'.');

        return $this->save($file['tmp_name']);
    }

    /**
     * 判断类型是否合理
     * @param  [type]  $type [description]
     * @return boolean       [description]
     */
    public function isValidType($type)
    {
        if (!in_array($type,$this->valid_type)) {
            return false;
        }

        return true;
    }

    /**
     * 判断大小是否合理
     * @param  [type]  $size [description]
     * @return boolean       [description]
     */
    public function isValidSize($size)
    {
        if ($size > self::$max_size) {
            return false;
        } 
        return true;
    }

    /**
     * 获取错误信息
     * @param  [type] $err_code [description]
     * @return [type]           [description]
     */
    public function getErrorMsg($err_code)
    {
        switch ($code) {
            case 1:
            case 2:
                return '文件大小必须小于'.$this->max_size;
            case 3:
                return '文件损坏，只有部分上传成功';
            case 4:
                return '文件上传失败';
            case 6:
                return '找不到临时文件夹';
            case 7:
                return '文件上传失败';
            case 8 :
                return '未找到该文件';
            case 9 :
                return '不合法的文件类型';
            case 10 :
                return '文件上传失败';
            case 11:
                return '类型错误';
            case 12:
                return '创建文件夹失败';
            case 13 :
                return '获取ini设置失败';
            case 14 :
                return '设置文件大小超过phpini设置的大小';

        }
    }
    
    /**
     * 设置可传送的最大值
     * @param int $size [description]
     */
    public function setMaxSize(int $size,bool $check_ini = true)
    {
        if ($check_ini) {
            $post_max_size = $this->getIni('post_max_size');
            
            if ($post_max_size === false) {
                return false;
            }

            if ($size > $post_max_size ) {
                $this->error_code = 14;
                return false;
            }

            $upload_max_filesize = $this->getIni('upload_max_filesize');

            if ($post_max_size === false) {

                return false;
            }

            if ($size > $upload_max_filesize) {
                $this->error_code = 14;
                return false;
            }

        }

        self::$max_size = $size;
        return true;
    }


    public function getIni($name)
    {
        $post_max_size = ini_get($name);

        preg_match('/(^[0-9\.]+)(\w+)/',$post_max_size,$info);

        if (count($info) < 3) {
            $this->error_code = 13;
            return false;
        }

        $size = strtoupper($info[2]);
        
        $arr = array("K" => 10, "M" => 20);

        if (!isset($arr[$size])) {
            $this->error_code = 13;
            return false;
        } 

        return $info[1] << $arr[$info[2]];
    }

    /**
     * 设置合法的mime格式
     * @param array $type [description]
     */
    public function setValidType(array $type)
    {
        $this->valid_type = [];

        foreach ($type as $v) {

            if (empty(self::$mime_arr[$v])) {
                $this->error_code = 11;
                return false;
            }

            $this->valid_type[$v] = self::$mime_arr[$v];
        }

        return true;
    }

    /**
     * 保存
     * @param  [type] $tmp_path [description]
     * @return [type]           [description]
     */
    protected function save($tmp_path)
    {

        $file_name = $this->getFileName();
        $path = $this->dir.$file_name;

        if(!move_uploaded_file($tmp_path,$path)) {
            $this->error_code = 10;
            return false;
        }

        return true;
    }
    
    /**
     * 获取文件名称
     * @return [type] [description]
     */
    protected function getFileName()
    {
        return $this->file_name = $this->prefix_name.mt_rand(1,10).uniqid().($this->ext);
    }

    /**
     * 设置前缀
     * @param [type] $prefix_name [description]
     */
    public function setPrefixName($prefix_name)
    {
        $this->prefix_name = $prefix_name;
    }

    /**
     * 设置文件后缀和mime对照关系
     * @param array $mime [description]
     */
    public function setMiMeType(array $mime) 
    {
        self::$mime_arr = $mime;
    }

    public static function __callStatic($name,$args)
    {
        if ($name == 'upload') {
            $file = new self();
            return call_user_func_array([$file,'upload'],$args);
        }
    }

    public function __call($name,$args)
    {
        if ($name == 'upload') {
            return call_user_func_array([$this,'upload'],$args);
        }
    }

    /**
     * 添加文件夹
     */
    protected function addDir()
    {
        if (!file_exists($this->dir)) {
            return mkdir($this->dir,$this->mode,true);
        }

        return true;
    }

    /**
     * 设置文件夹路径
     * @param [string] $dir [路径]
     */
    public function setDir(string $dir)
    {
        $this->dir = rtrim($dir,'/\/').'/';
    }
}
