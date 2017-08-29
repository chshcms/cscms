<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-17
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Ajax extends Cscms_Controller {

	function __construct(){
		parent::__construct();
		$this->lang->load('home');
		//关闭数据库缓存
		$this->db->cache_off();
		$this->load->model('Csuser');
	}

    //赞会员
	public function zan($uid=0){
		$callback = $this->input->get('callback',true);
		$uid = (int)$uid;
		$error="";
		if($uid==0){
		    $error=L('ajax_03');
		}elseif(!$this->Csuser->User_Login(1)){
		    $error=L('ajax_04');
		}elseif($uid==$_SESSION['cscms__id']){
		    $error=L('ajax_05');
		}else{
		    $row=$this->Csdb->get_row('user','id,zanhits',$uid);
		    if(!$row){
		        $error=L('ajax_06');
		    }else{
			    //判断今天是否赞过
				$zantime = $this->cookie->get_cookie('home_zan_'.$uid);
				if(!empty($zantime) && (int)$zantime>time()){
		             $error=L('ajax_07');
				}else{
				     //增加赞人气
		             $updata['zanhits']=$row->zanhits+1;
		             $this->Csdb->get_update('user',$uid,$updata);
					 $this->cookie->set_cookie('home_zan_'.$uid,strtotime(date('Y-m-d 0:0:0',time()+86400)),time()+86400);
					 //通知
			         $addm['uida']=$uid;
			         $addm['uidb']=$_SESSION['cscms__id'];
			         $addm['name']=L('ajax_13');
			         $addm['neir']=vsprintf(L('ajax_12'),array($_SESSION['cscms__name']));
			         $addm['addtime']=time();
			         $this->Csdb->get_insert('msg',$addm);
					 $error='ok';
				}
		    }
		}
		echo $callback."({error:".json_encode($error)."})";
	}

    //检测是否关注
	public function fansinit(){
		$callback = $this->input->get('callback',true);
		$uid = $this->input->get_post('uid',true);
		$error="";
		$gz=array();
		if(empty($uid)){
		   $error=L('ajax_03');
		}elseif(!$this->Csuser->User_Login(1)){
		   $error=L('ajax_04');
		}else{
		   $all=explode(',',$uid);
		   for($j=0;$j<count($all);$j++){	
			   $uid=(int)$all[$j];
		       if($uid>0){
			        //判断是否关注
				    $row=$this->db->query("SELECT id FROM ".CS_SqlPrefix."friend where uidb=".$uid." and uida=".$_SESSION['cscms__id']."")->row();
				    if($row){ //已关注
		                //是否相互关注
						$row2=$this->db->query("SELECT id FROM ".CS_SqlPrefix."friend where uida=".$uid." and uidb=".$_SESSION['cscms__id']."")->row();
						if($row2){
		                    $gz[$uid]=2;
						}else{
		                    $gz[$uid]=1;
						}
				    }
			   }
		   }
		}
		echo $callback."({error:".json_encode($error).",data:".json_encode($gz)."})";
	}

    //关注
	public function fans($uid=0,$sid=0){
		$callback = $this->input->get('callback',true);
		$uid = (int)$uid;   //ID	
		$sid = intval($sid);   //SID
		if($uid==0){
		    $error=L('ajax_03');
		}elseif(!$this->Csuser->User_Login(1)){
		    $error=L('ajax_04');
		}elseif($uid==$_SESSION['cscms__id']){
		    $error=L('ajax_08');
		}else{
		    $rowu=$this->Csdb->get_row('user','id',$uid);
		    if(!$rowu){
		        $error=L('ajax_06');
		    }else{
			    //判断是否关注
				$row=$this->db->query("SELECT id FROM ".CS_SqlPrefix."friend where uidb=".$uid." and uida=".$_SESSION['cscms__id']."")->row();
				if($row){ //已关注则解除
					if($sid==0){
		                 $this->Csdb->get_del('friend',$row->id);
					}
					$error='del';
				}else{ 
					//新增关注
		            $add['uidb']=$uid;
		            $add['uida']=$_SESSION['cscms__id'];
		            $add['addtime']=time();
					$this->Csdb->get_insert('friend',$add);

		            //同一会员24小时内只发送一次消息
		            if($this->cookie->get_cookie('fans_add_'.$uid)!='ok'){
			            $addm['uida']=$uid;
			            $addm['uidb']=$_SESSION['cscms__id'];
			            $addm['name']=L('ajax_13');
			            $addm['neir']=vsprintf(L('ajax_11'),array($_SESSION['cscms__name']));
			            $addm['addtime']=time();
			            $this->Csdb->get_insert('msg',$addm);
		                $this->cookie->set_cookie("fans_add_".$uid,"ok",time()+86400);
					}

					//判断粉丝是否存在
					$rows=$this->db->query("SELECT id FROM ".CS_SqlPrefix."fans where uida=".$uid." and uidb=".$_SESSION['cscms__id']."")->row();
					if(!$rows){
					    //增加粉丝
		                $add2['uida']=$uid;
		                $add2['uidb']=$_SESSION['cscms__id'];
		                $add2['addtime']=time();
					    $this->Csdb->get_insert('fans',$add2);
					}
					$error='ok';
				}
		    }
		}
		echo $callback."({error:".json_encode(get_bm($error,'gbk','utf-8'))."})";
	}

    //检测歌曲是否收藏
	public function favinit()
	{
		$callback = $this->input->get('callback',true);
		$did = $this->input->get_post('did',true);
		$error="";
		$fav=array();
		if(empty($did)){
			$error=L('ajax_03');
		}elseif(!$this->Csuser->User_Login(1)){
			$error=L('ajax_04');
		}else{
			$all=explode(',',$did);
			for($j=0;$j<count($all);$j++){	
			    $did=(int)$all[$j];
			    if($did>0){
			        //判断是否关注
				    $row=$this->db->query("SELECT id FROM ".CS_SqlPrefix."dance_fav where sid=1 and did=".$did." and uid=".$_SESSION['cscms__id']."")->row();
				    if($row){ //已收藏
			             $fav[$did]=1;
				    }
			    }
			}
		}
		echo $callback."({error:".json_encode($error).",data:".json_encode($fav)."})";
	}

    //收藏歌曲
	public function fav($did=0){
		$callback = $this->input->get('callback',true);
		$did = (int)$did;   //ID	
		if($did==0){
		    $error=L('ajax_03');
		}elseif(!$this->Csuser->User_Login(1)){
		    $error=L('ajax_04');
		}else{
		    $rowd=$this->Csdb->get_row('dance','id,name,cid,shits',$did);
		    if(!$rowd){
		        $error=L('ajax_09');
		    }else{
			    //判断是否收藏
				$row=$this->db->query("SELECT id FROM ".CS_SqlPrefix."dance_fav where did=".$did." and uid=".$_SESSION['cscms__id']." and sid=1")->row();
				if($row){ //已收藏则解除
					$this->Csdb->get_del('dance_fav',$row->id);
					$error='del';
				}else{ 
					//新增
		            $add['did']=$did;
		            $add['cid']=$rowd->cid;
		            $add['name']=$rowd->name;
		            $add['did']=$did;
		            $add['uid']=$_SESSION['cscms__id'];
		            $add['addtime']=time();
					$this->Csdb->get_insert('dance_fav',$add);

		            //增加收藏人气
		            $updata['shits']=$rowd->shits+1;
		            $this->Csdb->get_update('dance',$did,$updata);

					//判断动态是否存在
					$rows=$this->db->query("SELECT id FROM ".CS_SqlPrefix."dt where did=".$did." and uid=".$_SESSION['cscms__id']." and dir='dance' and link='".linkurl('play','id',$did,0,'dance')."'")->row();
					if(!$rows){
					    //增加动态
					    $add2['dir']='dance';
					    $add2['uid']=$_SESSION['cscms__id'];
					    $add2['did']=$did;
					    $add2['name']=$rowd->name;
					    $add2['link']=linkurl('play','id',$did,0,'dance');
					    $add2['title']=L('ajax_10');
					    $add2['addtime']=time();
					    $this->Csdb->get_insert('dt',$add2);
					}
					$error='ok';
				}
		    }
		}
		echo $callback."({error:".json_encode($error)."})";
	}
}
