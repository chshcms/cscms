<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-03-06
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Blog extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	    $this->load->model('Csuser');
		$this->lang->load('user');
		$this->Csuser->User_Login();
	}

	public function index($op='',$page=1){
	    $page=intval($page); //分页
		if(empty($op)) $op='my';
		//模板
		$tpl='blog.html';
		//URL地址
	    $url='blog/index/'.$op;
        $sql = "select * from ".CS_SqlPrefix."blog where uid=".$_SESSION['cscms__id'];
        $sqlstr = ($op=='all') ? '' : $sql;
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title=L('blog_01');
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];
        $this->Cstpl->user_list($row,$url,$page,$tpl,$title,$op,$sqlstr,$ids);
	}
}
