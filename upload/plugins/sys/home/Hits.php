<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-09
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Hits extends Cscms_Controller {

	function __construct(){
		parent::__construct();
		$this->lang->load('home');
		$this->load->library('user_agent');
		if(!$this->agent->is_referral()) show_error(L('ajax_01'),404,Web_Name);
		//关闭数据库缓存
		$this->db->cache_off();
	}

    //增加播放人气
	public function ids($id=0){
       $id = intval($id);   //ID			
	   $row=$this->Csdb->get_row('user','name,rhits,zhits,yhits,hits',$id);
	   if(!$row){
		   exit();
	   }
       //增加人气
       $updata['rhits']=$row->rhits+1;
       $updata['zhits']=$row->zhits+1;
       $updata['yhits']=$row->yhits+1;
       $updata['hits']=$row->hits+1;
       $this->Csdb->get_update('user',$id,$updata);
	   //增加访客记录
	   $this->load->model('Csuser');
	   //判断是否登录
	   if($this->Csuser->User_Login(1) && $id!=$_SESSION['cscms__id']){
               $rows=$this->db->query("Select id,addtime from ".CS_SqlPrefix."funco where uida=".$id." and uidb=".$_SESSION['cscms__id']."")->row();
		       if(!$rows){
                   $add['uida']=$id;
                   $add['uidb']=$_SESSION['cscms__id'];
                   $add['addtime']=time();
                   $this->Csdb->get_insert('funco',$add);
		       }else{  //修改时间
                   $updata2['addtime']=time();
                   $this->Csdb->get_update('funco',$rows->id,$updata2);
			   }
	   }
		//清空月人气
		$str = array();
		$month=@file_get_contents(FCPATH."cache/cscms_time/home-month.txt");
		if($month!=date('m')){
			$str[]='yhits=0';
		    write_file(FCPATH."cache/cscms_time/home-month.txt",date('m'));
		}
		//清空周人气
		$week=@file_get_contents(FCPATH."cache/cscms_time/home-week.txt");
		if($week!=date('W',time())){
			$str[]='zhits=0';
		    write_file(FCPATH."cache/cscms_time/home-week.txt",date('W',time()));
		}
		//清空日人气
		$day=@file_get_contents(FCPATH."cache/cscms_time/home-day.txt");
		if($day!=date('d')){
			$str[]='rhits=0';
		    write_file(FCPATH."cache/cscms_time/home-day.txt",date('d'));
		}
		if(!empty($str)){
		    $this->db->query("update ".CS_SqlPrefix."user set ".implode(',',$str));
		}
	}

    //动态加载人气
	public function dt($op='',$id=0){
       $id = intval($id);   //ID
	   $dos = array('hits', 'yhits', 'zhits', 'rhits', 'zanhits');
       $op= (!empty($op) && in_array($op, $dos))?$op:'hits';
	   $row=$this->Csdb->get_row('user',$op,$id);
	   if(!$row){
		        echo "document.write('0');";
	   }else{
				echo "document.write('".$row->$op."');";
	   }
	}
}
