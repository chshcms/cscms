<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-04-27
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Index extends Cscms_Controller {
	function __construct(){
		parent::__construct();
	    $this->load->model('Cstpl');
	}

	public function index(){
		$title = "会员 - ".Web_Name;
		//当前会员
		$row = array();
		if(!empty($_SESSION['cscms__id'])){
		    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
			if(empty($row['nichen'])) $row['nichen']=$row['name'];
		}
		$this->Cstpl->user_list($row,'',1,'index.html',$title);
	}
}
