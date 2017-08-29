<?php if ( ! defined('CSCMS')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-08
 */
class Fav extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	    $this->load->model('Csuser');
        $this->Csuser->User_Login();
	}

    //歌曲
	public function index($page=1){
	    $page=intval($page);
		//模板
		$tpl='fav.html';
		//URL地址
	    $url='fav/index';
        $sqlstr = "select {field} from ".CS_SqlPrefix."dance_fav where sid=1 and uid=".$_SESSION['cscms__id'];
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title='我收藏的歌曲 - 会员中心';
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];
        $this->Cstpl->user_list($row,$url,$page,$tpl,$title,'',$sqlstr,$ids);
	}

	//专辑
	public function album($page=1){
	    $page=intval($page);
		//模板
		$tpl='fav-album.html';
		//URL地址
	    $url='fav/album';
        $sqlstr = "select {field} from ".CS_SqlPrefix."dance_fav where sid=2 and uid=".$_SESSION['cscms__id'];
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title='我收藏的专辑 - 会员中心';
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];
        $this->Cstpl->user_list($row,$url,$page,$tpl,$title,'',$sqlstr,$ids);
	}

	//删除
	public function del($id=0,$sid=0){
	    $id=intval($id);
	    $sid=intval($sid);
	    $callback = $this->input->get('callback',true);
		if($sid==0) $sid=1;
        $row=$this->db->query("select uid from ".CS_SqlPrefix."dance_fav where id=".$id." and sid=".$sid."")->row();
		if($row){
			if($row->uid!=$_SESSION['cscms__id']){
				$err=1002;
				if(empty($callback)) msg_url('没有权限操作','javascript:history.back();');
			}else{
				$this->db->query("DELETE FROM ".CS_SqlPrefix."dance_fav where id=".$id."");
				$err=1001;
				if(empty($callback)) msg_url('删除成功~!','javascript:history.back();');
			}
		}else{
			$err=1002;
			if(empty($callback)) msg_url('数据不存在','javascript:history.back();');
		}
		echo $callback."({error:".$err."})";
	}
}

