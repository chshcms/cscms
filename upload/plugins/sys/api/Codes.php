<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-20
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Codes extends Cscms_Controller {

	function __construct(){
		    parent::__construct();
	}

	public function index()
	{
		   $params['width']  = (intval($this->input->get('w'))==0)?'':$this->input->get('w');  //宽度  默认150
		   $params['height'] = (intval($this->input->get('h'))==0)?'':$this->input->get('h');  //高度  默认50
		   $params['size']   = (intval($this->input->get('s'))==0)?'':$this->input->get('s');  //字体大小  默认20
		   $params['len']    = (intval($this->input->get('l'))==0)?'':$this->input->get('l');  //验证码长度  默认5

		   //加载库类
           $this->load->library('code',$params);
           //保存验证码
		   $this->cookie->set_cookie("codes",$this->code->getCode(),time()+1800);
		   //生成图片
           $this->code->doimg();
	}
}
