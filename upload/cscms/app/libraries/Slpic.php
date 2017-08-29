<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @Cscms open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-07-26
 */

class Slpic {
    //原图片文件，包含路径和文件名
    private $orpic; 
    //原图的临时图像
    private $tempic;
    //缩略图
    private $thpic;
    //原宽度
    private $width; 
    //原高度
    private $height;
    //新宽度
    private $newswidth; 
    //新高度
    private $newsheight;
    //缩略后的宽度
    private $thwidth;
    //缩略后的高度
    private $thheight;
	//缩略后的目录地址
    private $cache;
     
    public function __construct($params=array()){
		$file = $params['file'];
		$this->cache = $params['cache'];
        $this->orpic = $file;
        $infos = getimagesize($file);
        $this->width = $infos[0];
        $this->height = $infos[1];
        $this->type = $infos[2];
    }

    //根据用户所指定最大宽高来计算缩略图尺寸
    function cal_size(){
        //缩略图最大宽度与最大高度比
        $thcrown = $this->newswidth/$this->newsheight;    
        //原图宽高比
        $crown = $this->width/$this->height;    
        $this->thwidth = $this->newswidth;
        $this->thheight = $this->newswidth/$crown;
		if($this->thheight<$this->newsheight){
             $this->thheight=$this->newsheight;
		}
    }
     
	//获取图片内容
    function init(){
        switch($this->type){
            case 1:     //GIF
                $this->tempic = imagecreatefromgif($this->orpic);
                break;
            case 2:     //JPG
                $this->tempic = imagecreatefromjpeg($this->orpic);
                break;
            case 3:     //PNG
                $this->tempic = imagecreatefrompng($this->orpic);
                break;
        }
    }
 
    //缩小图片
    function resize($maxwidth, $maxheight){
        $this->newswidth = ($maxwidth==0)?$this->width:$maxwidth;
        $this->newsheight = ($maxheight==0)?$this->height:$maxheight;
        //初始化图像
        $this->init();
        //计算出缩略图尺寸
        $this->cal_size();

		//原图不缩放
		if($maxwidth==0 && $maxheight==0){
               $this->topic($this->tempic);
			   exit;
		}

        //等比缩小
        $this->thpic = imagecreatetruecolor($this->thwidth, $this->thheight);
        imagecopyresampled($this->thpic, $this->tempic, 0, 0, 0 ,0, $this->thwidth, $this->thheight, $this->width, $this->height);
		//宽度优先，在缩放之后高度不足的情况下补上背景
	  	if($this->thwidth>$this->thheight){
		  		$this->addBg($this->thpic,$this->thwidth,$this->thheight,"wh");
	  	}
		//等比缩放
	  	if($this->thwidth==$this->thheight){
		  		$this->addBg($this->thpic,$this->thwidth,$this->thheight,"wh");
	  	}
		//高度优先，在缩放之后宽度不足的情况下补上背景
	  	if($this->thwidth<$this->thheight){
		  		$this->addBg($this->thpic,$this->thwidth,$this->thheight,"h");
	  	}
    }
     
  	//补填图片背景
  	public function addBg($temp_img,$w,$h,$fisrt="wh"){
	  	$bg=imagecreatetruecolor($this->newswidth,$this->newsheight);
	  	$white = imagecolorallocate($bg,255,255,255);
	    imagefill($bg,0,0,$white);//填充背景
	    if($fisrt=="w"){
			$x=0;
			$y=($h-$this->newsheight)/2;//垂直居中
		}
		if($fisrt=="h"){
		    $x=($w-$this->newswidth)/2;//水平居中
			$y=0;
		}
		if($fisrt=="wh"){
            $x=0;
			$y=0;
		}
		imagecopymerge($bg,$temp_img,$x,$y,0,0,$this->newswidth,$this->newsheight,100);
        switch($this->type){
            case 1:     //GIF
                imagegif($bg);
                break;
            case 2:     //JPG
                imagejpeg($bg);
                break;
            case 3:     //PNG
                imagepng($bg);
                break;
            default:
                echo '暂不支持该图片格式';
        }
		$this->topic($bg);
		//imagedestroy($bg);
	}

  	//输出图片
  	public function topic($temp_img){
        if($this->type==1){
		      header("Content-type: image/gif"); 
		}elseif($this->type==2){
		      header("Content-type: image/jpeg"); 
		}else{
		      header("Content-type: image/png"); 
		}
        switch($this->type){
            case 1:     //GIF
                imagegif($temp_img,$this->cache,90);
                break;
            case 2:     //JPG
                imagejpeg($temp_img,$this->cache,90);
                break;
            case 3:     //PNG
                imagepng($temp_img,$this->cache,9);
                break;
            default:
                echo '暂不支持该图片格式';
        }
		@imagedestroy($temp_img);
		@imagedestroy($this->tempic);
		@imagedestroy($this->thpic);
	}
}
