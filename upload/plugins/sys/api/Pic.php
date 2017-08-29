<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-20
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pic extends Cscms_Controller {

	function __construct(){
		    parent::__construct();
			if(empty($_SERVER['HTTP_REFERER'])) exit('error 404');
	}

	public function index()
	{
		   $size = $this->input->get('size');
		   $arr = explode('/picdata/',REQUEST_URI);
		   if(empty($arr[1])){
		        header("location:".piclink('pic',''));exit;
		   }
		   $arr[1]='attachment/'.$arr[1];
		   $arrs = explode('?',$arr[1]);
		   if(UP_Mode==1 && UP_Pan!=''){
               $files = UP_Pan.$arrs[0];
		   }else{
               $files = FCPATH.$arrs[0];
		   }
           $files = str_replace("//","/",$files);
		   if(!file_exists($files)){
		        header("location:".piclink('pic',''));exit;
		   }

           if(!empty($size)){
		       $wh = explode('*',$size);
		       $w = intval($wh[0]);
		       $h = intval($wh[1]);
			   if($w>800 || $h>800){
		           $w = 0;
		           $h = 0;
			   }
		   }else{
		       $w = 0;
		       $h = 0;
		   }
		   //没有缩放或者超过800则直接转向指定图片
		   if($w==0 && $h==0){
		   		header("location:".annexlink($arrs[0]));exit;
		   }

		   //判断缓存是否存在
		   $file_ext = strtolower(trim(substr(strrchr($arrs[0], '.'), 1)));
	  	   $cachedir = 'cache/suo_pic/'.str_replace("attachment/","",$arrs[0]).'_'.$w.'_'.$h.'.'.$file_ext;
		   $cachedir = str_replace("//","/",$cachedir);
		   if(file_exists(FCPATH.$cachedir)){
			   //会员头像2小时更新一次
			   if(!(strpos($arrs[0],'/logo/') !== FALSE && (time() - filemtime(FCPATH.$cachedir)) > 7200)){
		   		    header("location:".is_ssl().Web_Url.Web_Path.$cachedir);exit;
			   }
		   }

		   //创建缓存文件夹
		   $temp=pathinfo(FCPATH.$cachedir);
		   mkdirss($temp["dirname"]);
		   $params['file'] = $files;
		   $params['cache'] = FCPATH.$cachedir;
		   //加载库类
           $this->load->library('slpic',$params);
		   //生成图片
           $this->slpic->resize($w,$h);
	}
}
