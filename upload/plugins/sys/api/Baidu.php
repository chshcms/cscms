<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-09-20
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Baidu extends Cscms_Controller {

	function __construct(){
		  parent::__construct();
	}

    //网站地图
	public function index()
	{
          header("Content-type:text/xml;charset=utf-8"); 
		  $this->load->get_templates('common');
		  $Mark_Text=$this->load->view('baidu.html','',true);
		  $Mark_Text=$this->Csskins->template_parse($Mark_Text,false);
		  echo '<?xml version="1.0" encoding="utf-8" ?>'.$Mark_Text;
	}
}
