<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-03-30
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Share extends Cscms_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Cstpl');
		$this->load->model('Csuser');
		$this->Csuser->User_Login();
		$this->lang->load('user');
	}

    //宣传地址
	public function index(){
		//模板
		$tpl='share.html';
		//URL地址
		$url='share/index';
		//当前会员
		$row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title=L('share_01');
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];

		$zdy['[user:sharelink]'] = is_ssl().Web_Url.site_url('share/'.$_SESSION['cscms__id']);
		$zdy['[user:sharecion]'] = User_Cion_Share;
		$zdy['[user:sharejinyan]'] = User_Jinyan_Share;
		$zdy['[user:sharenums]'] = User_Nums_Share;

		$this->Cstpl->user_list($row,$url,1,$tpl,$title,'','',$ids,false,'user',$zdy);
	}

    //宣传记录
	public function lists($page=1){
	    $page=intval($page); //分页
		//模板
		$tpl='share-list.html';
		//URL地址
	    $url='share/lists';
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title=L('share_02');
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];
        $this->Cstpl->user_list($row,$url,$page,$tpl,$title,'','',$ids);
	}
}
