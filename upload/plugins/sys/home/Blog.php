<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-17
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Blog extends Cscms_Controller {
	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	    $this->lang->load('home');
	}

	public function index($user='',$id=0){
        $id = (int)$id;   //ID
		//模板
		$tpl='blog.html';
		//当前会员
		$uid = get_home_uid();
	    $row=$this->Csdb->get_row_arr('user','*',$uid);
		if(!$row) msg_url(L('home_01'),is_ssl().Web_Url.Web_Path);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title = $row['nichen'].L('blog_01');
		$ids['uid']=$row['id'];
		$ids['uida']=$row['id'];
		$rowb=$this->db->query("SELECT * FROM ".CS_SqlPrefix."blog where uid=".$uid." and id=".$id."")->row_array();
		if(!$rowb){
            msg_url(L('blog_02'),'http://'.Web_Url.Web_Path);
		}
		$zdytpl['[blog:pl]'] = get_pl('blog',$id);
		$onetpl = array('blog',$rowb);
        $this->Cstpl->home_list($row,'blog',1,$tpl,$title,$ids,'','',false,'user',$zdytpl,$onetpl);
	}
}