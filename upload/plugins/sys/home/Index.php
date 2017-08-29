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
	    $this->lang->load('home');
	} 
	public function index($skin=''){
		//模板
		$tpl='index.html';
		//当前会员
		$uid = get_home_uid();
	    $row = $this->Csdb->get_row_arr('user','*',$uid);
		if(!$row) msg_url(L('home_01'),is_ssl().Web_Url.Web_Path);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title=$row['nichen'].L('index_01');
		$ids['uid']=$row['id'];
		$ids['uida']=$row['id'];
		$pc_wap = defined('MOBILE') ? 'mobile' : 'pc';
		if(!empty($skin) && file_exists(FCPATH.'tpl/'.$pc_wap.'/home/'.$skin.'/config.php')){
             $arr=require(FCPATH.'tpl/'.$pc_wap.'/home/'.$skin.'/config.php');
			 $row['skins']=$arr['path'];
		}
		//增加人气地址
		$hitslink = hitslink('hits/ids/'.$uid,'home');
        $this->Cstpl->home_list($row,'index',1,$tpl,$title,$ids,'','',false,'user','','',$hitslink);
	}
}
