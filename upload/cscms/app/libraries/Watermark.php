<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @Cscms open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-07-26
 */
class Watermark {

  		//构造方法初始化
  		public function __construct() {

  		}

  		public function getimageinfo($src)
  		{
	  		return getimagesize($src);
  		}
  		/**
  		* 创建图片，返回资源类型
  		* @param string $src 图片路径
  		* @return resource $im 返回资源类型 
  		* **/
  		public function create($src)
  		{
	  		$info=$this->getimageinfo($src);
	  		switch ($info[2])
	  		{
		  		case 1:
		  			$im=imagecreatefromgif($src);
		  			break;
		  		case 2:
		  			$im=imagecreatefromjpeg($src);
			  		break;
		  		case 3:
			  		$im=imagecreatefrompng($src);
			  		break;
	  		}
	  		return $im;
  		}
  		/**
  		* 缩略图主函数
  		* @param string $src 图片路径
  		* @param int $w 缩略图宽度
  		* @param int $h 缩略图高度
  		* @return mixed 返回缩略图路径
  		* **/
  		public function resize($src,$temp_w='',$temp_h='')
  		{
	  		$temp=pathinfo($src);
	  		$name=$temp["basename"];//文件名
	  		$dir=$temp["dirname"];//文件所在的文件夹
	  		$extension=$temp["extension"];//文件扩展名
	  		$savepath="{$dir}/{$name}.small.jpg";//缩略图保存路径,新的文件名为*.small.jpg

	  		//获取图片的基本信息
	  		$info=$this->getimageinfo($src);
	  		$width=$info[0];//获取图片宽度
	  		$height=$info[1];//获取图片高度
          	$w=intval($width/2);
	  		$h=intval($height/2);//计算缩略图长宽比

            if($temp_w=='' && $temp_h==''){
	  		    $temp_w=$w;//计算原图缩放后的宽度
	  		    $temp_h=$h;//计算原图缩放后的高度
            }
	  		$temp_img=imagecreatetruecolor($temp_w,$temp_h);//创建画布
	  		$im=$this->create($src);
	  		imagecopyresampled($temp_img,$im,0,0,0,0,$temp_w,$temp_h,$width,$height);
	  		if($w>$h)
	  		{
		  		imagejpeg($temp_img,$savepath, 100);
	  			imagedestroy($im);
		  		return $this->addbg($savepath,$w,$h,"w");
		  		//宽度优先，在缩放之后高度不足的情况下补上背景
	  		}
	  		if($w==$h)
	  		{
		  		imagejpeg($temp_img,$savepath, 100);
		  		imagedestroy($im);
		  		return $savepath;
		  		//等比缩放
	  		}
	  		if($w<$h)
	  		{
		  		imagejpeg($temp_img,$savepath, 100);
		  		imagedestroy($im);
		  		return $this->addbg($savepath,$w,$h,"h");
		  		//高度优先，在缩放之后宽度不足的情况下补上背景
	  		}
  		}
  		/**
  		* 添加背景
  		* @param string $src 图片路径
  		* @param int $w 背景图像宽度
  		* @param int $h 背景图像高度
  		* @param String $first 决定图像最终位置的，w 宽度优先 h 高度优先 wh:等比
  		* @return 返回加上背景的图片
  		* **/
  		public function addbg($src,$w,$h,$fisrt="w")
  		{
	  		$bg=imagecreatetruecolor($w,$h);
	  		$white = imagecolorallocate($bg,255,255,255);
	  		imagefill($bg,0,0,$white);//填充背景

	  		//获取目标图片信息
	  		$info=$this->getimageinfo($src);
	  		$width=$info[0];//目标图片宽度
	  		$height=$info[1];//目标图片高度
	  		$img=$this->create($src);
	  		if($fisrt=="wh")
	  		{
		  		//等比缩放
	  			return $src;
	  		}else{
		  		if($fisrt=="w")
		  		{
			  		$x=0;
			  		$y=($h-$height)/2;//垂直居中
		  		}
		  		if($fisrt=="h")
		  		{
			  		$x=($w-$width)/2;//水平居中
			  		$y=0;
		  		}
		  		imagecopymerge($bg,$img,$x,$y,0,0,$width,$height,100);
		  		imagejpeg($bg,$src,100);
		  		imagedestroy($bg);
		  		imagedestroy($img);
		  		return $src;
	  		}

  		}

  		public function imagewatermark($filename){

          		$watertype=CS_WaterMode; //水印类型(1为文字,2为图片) 
          		$waterposition=CS_WaterLocation; //文字水印位置(1为左下角,2为右下角,3为左上角,4为右上角,5为居中);  
          		$waterpositions=CS_WaterLocations; //图片水印位置(1为左下角,2为右下角,3为左上角,4为右上角,5为居中);  
          		$waterstring=CS_WaterFont; //水印字符串  
          		$waterstringw=130; //水印字符串的高度
          		$waterstringh=20; //水印字符串的高度
          		$waterstringpadding = 1; //水印字符串的居上右下左
          		$waterimg=FCPATH.CS_WaterLogo; //水印图片  
          		$imgquality = CS_WaterLogotm; //图片质量0-100，值最大图片质量愈好，图片的大小也越大 *推荐90-100 太小图片会出现模糊现象

          		$image_size = getimagesize($filename);   //上传图片的大小
	  		    $upimgw = $image_size[0];  //上传图片的宽度
	  		    $upimgh = $image_size[1];  //上传图片的高度

	  		    $waterimg_size =  getimagesize($waterimg); //水印图片的大小
	  		    $waterimgw = $waterimg_size[0];  //水印图片的宽度
	  		    $waterimgh = $waterimg_size[1];  //水印图片的高度
	  		    $wpinfo=pathinfo($waterimg);    
         		$wptype=$wpinfo['extension'];     //水印图片的后缀

          		$destination=$filename;

            	if(CS_WaterFontColor!="") { 
              			$R = hexdec(substr(CS_WaterFontColor,1,2)); 
              			$G = hexdec(substr(CS_WaterFontColor,3,2)); 
              			$B = hexdec(substr(CS_WaterFontColor,5)); 
				} else { 
              			$R = hexdec(substr('#0000CC',1,2)); 
              			$G = hexdec(substr('#0000CC',3,2)); 
              			$B = hexdec(substr('#0000CC',5)); 
				}

          		$iinfo=getimagesize($destination,$iinfo);  
          		$nimage=imagecreatetruecolor($image_size[0],$image_size[1]);  
          		$white=imagecolorallocate($nimage, $R, $G, $B);  
          		$black=imagecolorallocate($nimage,0,0,0);  
          		$red=imagecolorallocate($nimage,255,0,0);  
          		imagefill($nimage,0,0,$white);  
          		switch ($iinfo[2])  
         		 {  
             		 case 1:  
             		      $simage =imagecreatefromgif($destination);  
             		      break;  
             		 case 2:  
             		      $simage =imagecreatefromjpeg($destination);  
              		      break;  
             		 case 3:  
             		      $simage =imagecreatefrompng($destination);  
             		      break;  
             		 case 6:  
             		      $simage =imagecreatefromwbmp($destination);  
              		      break;  
              		default:  
             		      $simage =imagecreatefromgif($destination);  
             		      break;  
          		}  
          		imagecopy($nimage,$simage,0,0,0,0,$image_size[0],$image_size[1]); 
			    switch($watertype)  
			    {  
					case 1:   //加水印字符串  
				  		switch ($waterposition) {  
					  		case 1:   //水印位置：左下
				  		  		$waterstart_x = $waterstringpadding ;
				  		  		$waterstart_y = $upimgh-$waterstringh;
				  		  		break;
					  		case 2:   //水印位置：右下
				  		  		$waterstart_x = $upimgw-$waterstringw-$waterstringpadding;
				  		  		$waterstart_y = $upimgh-$waterstringh-$waterstringpadding ;
				  		  		break;
					  		case 3:   //水印位置：左上
				  		  		$waterstart_x = $waterstringpadding;
				  		  		$waterstart_y = $waterstringpadding;
				  		  		break;
					  		case 4:   //水印位置：右上
				  		  		$waterstart_x = $upimgw-$waterstringw-$waterstringpadding;
				  		  		$waterstart_y = $waterstringpadding;
				  		  		break;
					  		case 5:   //水印位置：中间
				  		  		$waterstart_x = ($upimgw-$waterstringw)/2;
				  		  		$waterstart_y = ($upimgh-$waterstringh)/2;
				  		  		break;
						}
              		    imagestring($nimage,CS_WaterFontSize,$waterstart_x,$waterstart_y,$waterstring,$white);  
              		    break;  
              		case 2:   //加水印图片
					    $simage1 ="";
					    switch($wptype)  
					    {  
						    case 'png':   //水印图片格式png  
						    $simage1 =imagecreatefrompng($waterimg); break;
						    case 'gif':   //水印图片格式gif  
						    $simage1 =imagecreatefromgif($waterimg); break;
					    }

			  		    switch ($waterpositions) {  
				  			case 1:   //水印位置：左下
			  		  			$waterstart_x = 0;
			  		  			$waterstart_y = $upimgh-$waterimgh;
			  		  			break;
				  			case 2:   //水印位置：右下
			  		  			$waterstart_x = $upimgw-$waterimgw;
			  		  			$waterstart_y = $upimgh-$waterimgh;
			  		  			break;
				  			case 3:   //水印位置：左上
			  		  			$waterstart_x = 0;
			  		  			$waterstart_y = 0;
			  		  			break;
				  			case 4:   //水印位置：右上
			  		  			$waterstart_x = $upimgw-$waterimgw;
			  		  			$waterstart_y = 0;
			  		  			break;
				  			case 5:   //水印位置：中间
			  		  			$waterstart_x = ($upimgw-$waterimgw)/2;
			  		  			$waterstart_y = ($upimgh-$waterimgh)/2;
			  		  			break;
			  			}
			
			  			imagecopy($nimage,$simage1,$waterstart_x,$waterstart_y,0,0,$waterimgw,$waterimgh); 
						imagedestroy($simage1);  
              			break;  
          		}
          		switch ($iinfo[2])  
          		{  
             		  	case 1:  
             		  		 //imagegif($nimage, $destination);  
              		  		imagejpeg($nimage, $destination,$imgquality);  
              		  		break;  
              		        case 2:  
              		  		imagejpeg($nimage, $destination,$imgquality);  
              		  		break;  
              		        case 3:  
              		  		imagepng($nimage, $destination,$imgquality);  
              		  		break;  
              		        case 6:  
              		  		imagewbmp($nimage, $destination,$imgquality);    
              		  		break;  
          		}  
          		//覆盖原上传文件  
          		imagedestroy($nimage);  
          		imagedestroy($simage);   
  		}
}

