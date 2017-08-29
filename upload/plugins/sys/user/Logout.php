<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-23
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Logout extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
		$this->lang->load('user');
	}

    //退出登录
	public function index(){
		//删除在线状态
		$updata['zx']=0;
		if(isset($_SESSION['cscms__id'])){
			$this->Csdb->get_update('user',$_SESSION['cscms__id'],$updata);
			$this->Csdb->get_del('session',$_SESSION['cscms__id'],'uid');
		}

		unset($_SESSION['cscms__id'],$_SESSION['cscms__name'],$_SESSION['cscms__login']);

		//清除记住登录
		$this->cookie->set_cookie("user_id");
		$this->cookie->set_cookie("user_login");

		//--------------------------- Ucenter ---------------------------
		$log=(User_Uc_Mode==1)?uc_user_synlogout:'';
		//--------------------------- Ucenter ---------------------------

		msg_url(L('logout_01').$log,userurl(site_url('user/login')),'ok');  //退出登录成功
	}
}
