<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-01-17
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Ajax extends Cscms_Controller {

	function __construct(){
		parent::__construct();
		$this->load->library('user_agent');
		if(!$this->agent->is_referral()) show_error(L('dance_01'),404,Web_Name.L('dance_02'));
		//关闭数据库缓存
		$this->db->cache_off();
		$this->load->model('Csuser');
	}

    //收藏
	public function dancefav($id=0)
	{
		$callback = $this->input->get('callback',true);
		$id = intval($id);   //方式
		if($id==0){
		    $error=L('dance_03');
		}elseif(!$this->Csuser->User_Login(1)){
		    $error=L('dance_04');
		}else{
		    $row=$this->Csdb->get_row('dance','cid,name,shits',$id);
		    if(!$row){
		        $error=L('dance_05');
		    }else{
				//判断是否收藏
		        $rows=$this->db->query("SELECT id FROM ".CS_SqlPrefix."dance_fav where sid=1 and did=".$id." and uid=".$_SESSION['cscms__id']."")->row();
		        if($rows){
		            $error=L('dance_06');
				}else{
					$add['did']=$id;
					$add['cid']=$row->cid;
					$add['uid']=$_SESSION['cscms__id'];
					$add['name']=$row->name;
					$add['addtime']=time();
					$this->Csdb->get_insert('dance_fav',$add);
		            //增加收藏人气
		            $updata['shits']=$row->shits+1;
		            $this->Csdb->get_update('dance',$id,$updata);
					//增加动态
					$add2['dir']='dance';
					$add2['uid']=$_SESSION['cscms__id'];
					$add2['did']=$id;
					$add2['name']=$row->name;
					$add2['link']=linkurl('play','id',$id,0,'dance');
					$add2['title']=L('dance_07');
					$add2['addtime']=time();
					$this->Csdb->get_insert('dt',$add2);
					$error='ok';
				}
			}
		}
		getjson($error,0,1,$callback);
	}


    //顶踩
	public function danceding($op,$id=0)
	{
		$callback = $this->input->get('callback',true);
		$id = intval($id);   //ID
		if($id==0) $id = intval($op);
		$ding = $this->cookie->get_cookie("danceding_id_".$id);
		if($id==0){
		    $error=L('dance_03');
		}elseif(!$this->Csuser->User_Login(1)){
		    $error=L('dance_04');
		}elseif(!empty($ding)){
		    $error=L('dance_08');
		}else{
		    $row=$this->Csdb->get_row('dance','dhits,chits',$id);
		    if(!$row){
		        $error=L('dance_05');
		    }else{
	            //记住cookie
	            $this->cookie->set_cookie("danceding_id_".$id,'ok',time()+86400);
	            //增加顶人气
				if($op=='cai'){
	                $updata['chits']=$row->chits+1;
				}else{
	                $updata['dhits']=$row->dhits+1;
				}
	            $this->Csdb->get_update('dance',$id,$updata);
				$error='ok';
			}
		}
		getjson($error,0,1,$callback);
	}

    //收藏专辑
	public function albumfav($id=0)
	{
		$callback = $this->input->get('callback',true);
		$id = intval($id);   //方式
		if($id==0){
		    $error=L('dance_03');
		}elseif(!$this->Csuser->User_Login(1)){
		    $error=L('dance_04');
		}else{
		    $row=$this->Csdb->get_row('dance_topic','cid,name,shits',$id);
		    if(!$row){
		        $error=L('dance_23');
		    }else{
				//判断是否收藏
		        $rows=$this->db->query("SELECT id FROM ".CS_SqlPrefix."dance_fav where did=".$id." and uid=".$_SESSION['cscms__id']." and sid=2")->row();
		        if($rows){
		            $error=L('dance_06');
				}else{
					$add['did']=$id;
					$add['sid']=2;
					$add['cid']=$row->cid;
					$add['uid']=$_SESSION['cscms__id'];
					$add['name']=$row->name;
					$add['addtime']=time();
					$this->Csdb->get_insert('dance_fav',$add);
		            //增加收藏人气
		            $updata['shits']=$row->shits+1;
		            $this->Csdb->get_update('dance_topic',$id,$updata);
					//增加动态
					$add2['dir']='dance';
					$add2['uid']=$_SESSION['cscms__id'];
					$add2['did']=$id;
					$add2['name']=$row->name;
					$add2['link']=linkurl('topic','show',1,1,'dance');
					$add2['title']=L('dance_24');
					$add2['addtime']=time();
					$this->Csdb->get_insert('dt',$add2);
					$error='ok';
				}
			}
		}
		getjson($error,0,1,$callback);
	}
}