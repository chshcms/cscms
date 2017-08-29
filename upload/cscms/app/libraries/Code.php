<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-08-21
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 生成验证码
 * @author yanyujiangnan
 * 类用法
 * $this->load->library('code');
 * $this->code->getCode(); //获得验证码
 * $this->code->doimg(); //输出图片
 */
class Code {

		private $charset = 'ABCDEFGHIGKLMNOPQRSTUVWXYZ23456789'; //随机因子
  		private $code;       //验证码
  		private $codelen = 5;     //验证码长度
  		private $width = 180;     //宽度
  		private $height = 50;     //高度
  		private $img;        //图形资源句柄
  		private $font;        //指定的字体
  		private $fontsize = 22;    //指定字体大小
  		private $fontcolor;      //指定字体颜色
  
  		//构造方法初始化
  		public function __construct($params=array()) {
            if(!empty($params['width'])) $this->width=$params['width'];     //验证码的宽度
            if(!empty($params['height'])) $this->height=$params['height'];  //验证码的高
            if(!empty($params['size'])) $this->fontsize = $params['size']; //字体大小
            if(!empty($params['len'])) $this->codelen = $params['len']; //验证码长度
   			$this->font = BASEPATH.'fonts/arrusbt.ttf';
  		}
  
 		 //生成随机码
  		private function createCode() {
   			$_len = strlen($this->charset)-1;
   			for ($i=0;$i<$this->codelen;$i++) {
    				$this->code .= $this->charset[mt_rand(0,$_len)];
   			}
  		}
  
  		//生成背景
  		private function createBg() {
   			$this->img = imagecreatetruecolor($this->width, $this->height);
   			$color = imagecolorallocate($this->img, 245, 255, 255);
   			imagefilledrectangle($this->img,0,$this->height,$this->width,0,$color);
  		}
  
  		//生成文字
  		private function createFont() { 
   			$_x = $this->width / $this->codelen;
   			for ($i=0;$i<$this->codelen;$i++) {
    				$this->fontcolor = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
    				@imagettftext($this->img,$this->fontsize,mt_rand(-30,30),$_x*$i+mt_rand(1,5),$this->height / 1.4,$this->fontcolor,$this->font,$this->code[$i]);
   			}
  		}
  
  		//生成线条、雪花
  		private function createLine() {
   			for ($i=0;$i<6;$i++) {
    				$color = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
    				imageline($this->img,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$color);
   			}
   			for ($i=0;$i<100;$i++) {
    				$color = imagecolorallocate($this->img,mt_rand(200,255),mt_rand(100,255),mt_rand(200,255));
    				imagestring($this->img,mt_rand(1,5),mt_rand(0,$this->width),mt_rand(0,$this->height),'*',$color);
   			}
  		}
  
  		//输出
  		private function outPut() {
   			header('Content-type:image/png');
   			imagepng($this->img);
   			imagedestroy($this->img);
  		}
  
  		//对外生成
  		public function doimg() {
  	 		$this->outPut();
  		}
  
  		//获取验证码
  		public function getCode() {
   			$this->createBg();
   			$this->createCode();
   			$this->createLine();
   			$this->createFont();
   			return strtolower($this->code);
  		}
}
