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
		if(!$this->agent->is_referral()) show_error('您访问的页面不存在~!',404,Web_Name.'提醒您');
		//关闭数据库缓存
		$this->db->cache_off();
		$this->load->model('Csuser');
	}

    //收藏
	public function vodfav($id=0){
		$callback = $this->input->get('callback',true);
		$id = intval($id);   //方式
		if($id==0){
		    $error='ID为空';
		}elseif(!$this->Csuser->User_Login(1)){
		    $error='您还没有登录';
		}else{
		    $row=$this->Csdb->get_row('vod','cid,name,shits',$id);
		    if(!$row){
		        $error='数据不存在';
		    }else{
				//判断是否收藏
		        $rows=$this->db->query("SELECT id FROM ".CS_SqlPrefix."vod_fav where did=".$id." and uid=".$_SESSION['cscms__id']." and sid=0")->row();
		        if($rows){
		            $error='您已经收藏了该视频';
				}else{
					$add['did']=$id;
					$add['cid']=$row->cid;
					$add['uid']=$_SESSION['cscms__id'];
					$add['sid']=0;
					$add['name']=$row->name;
					$add['addtime']=time();
					$this->Csdb->get_insert('vod_fav',$add);
		            //增加收藏人气
		            $updata['shits']=$row->shits+1;
		            $this->Csdb->get_update('vod',$id,$updata);

				    //增加下载动态
				    $dt['dir']='vod';
				    $dt['uid']=$_SESSION['cscms__id'];
				    $dt['did']=$id;
				    $dt['name']=$row->name;
				    $dt['link']=linkurl('show','id',$id,0,'vod');
				    $dt['title']='收藏了视频';
				    $dt['addtime']=time();
				    $this->Csdb->get_insert('dt',$dt);

					$error='ok';
				}
			}
		}
		getjson($error,0,1,$callback);
	}

    //顶
	public function vodding($id,$op=''){
		$callback = $this->input->get('callback',true);
		$id = intval($id);   //ID
		$ding = $this->cookie->get_cookie("vodding_id_".$id);
		if($id==0){
		    $error='ID为空';
		}elseif(!$this->Csuser->User_Login(1)){
		    $error='您还没有登录';
		}elseif(!empty($ding)){
		    $error='您今天已经赞过了';
		}else{
		    $row=$this->Csdb->get_row('vod','dhits,chits',$id);
		    if(!$row){
		        $error='数据不存在';
		    }else{
		            //记住cookie
		            $this->cookie->set_cookie("vodding_id_".$id,'ok',time()+86400);
		            //增加顶人气
					if($op=='cai'){
		                $updata['chits']=$row->chits+1;
					}else{
		                $updata['dhits']=$row->dhits+1;
					}
		            $this->Csdb->get_update('vod',$id,$updata);
					$error='ok';
			}
		}
		getjson($error,0,1,$callback);
	}

    //获取评分
	public function vodpfen($id=0){
		$this->load->helper('vod');
		$callback = $this->input->get('callback',true);
		$id = intval($id);   //ID
		$row=$this->Csdb->get_row('vod','phits,pfen',$id);
		if(!$row){
		    $fen=0;
		}else{
		    $fen=getpf($row->pfen,$row->phits);
		}
		getjson(array('fen'=>$fen),0,1,$callback);
	}

    //视频评分
	public function vodpfenadd($id=0,$fen=0){
		$callback = $this->input->get('callback',true);
		$id = intval($id);   //ID
		$fen = intval($fen);   //分
		$pfen = $this->cookie->get_cookie("vodpfen_id_".$id);
		if($id==0 || $fen==0){
		    $error='参数错误';
		}elseif(!$this->Csuser->User_Login(1)){
		    $error='您还没有登录';
		}elseif(!empty($pfen)){
		    $error='您已经评过分了';
		}else{
		    $row=$this->Csdb->get_row('vod','phits,pfen',$id);
		    if(!$row){
		        $error='数据不存在';
		    }else{
		            //记住cookie
		            $this->cookie->set_cookie("vodpfen_id_".$id,'ok',time()+86400*30);
		            //增加评分、人气
		            $updata['phits']=$row->phits+1;
		            $updata['pfen']=$row->pfen+$fen;
		            $this->Csdb->get_update('vod',$id,$updata);
					$error='ok';
			}
		}
		getjson($error,0,1,$callback);
	}
}
