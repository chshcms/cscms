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
	}

	public function index(){
        //判断运行模式
        if(Web_Mode==3 && file_exists(FCPATH.Html_Index) && !defined('MOBILE')){
            header("Location: ".Web_Path.Html_Index);exit;
        }
        $this->Cstpl->home();
	}

    //分享
	public function share($uid=0){
		//关闭数据库缓存
		$this->db->cache_off();
		$this->load->library('user_agent');
		$uid=intval($uid);
		if($uid==0){
		   header("Location: ".Web_Path);exit;
		}
		//判断每天上限次数
		$addid=1;
		if(User_Nums_Share>0){
		    $times=strtotime(date("Y-m-d 0:0:0"));
		    $nums=$this->db->query("select id from ".CS_SqlPrefix."share where uid=".$uid." and addtime>".$times."")->num_rows();
		    if($nums>User_Nums_Share){
		        $addid=0;
		    }
		}
		//增加金币和经验
		if($addid==1){
		    $edit='';
		    if(User_Cion_Share>0){
		         $edit.=",cion=cion+".User_Cion_Share."";
		    }
		    if(User_Jinyan_Share>0){
		         $edit.=",jinyan=jinyan+".User_Jinyan_Share."";
		    }
		    if(!empty($edit)){
			   $edit=substr($edit,1);
		       $this->db->query("update ".CS_SqlPrefix."user set ".$edit." where id=".$uid."");
		    }
		}
		//写入分享记录
		$agent = ($this->agent->is_mobile() ? $this->agent->mobile() :                    $this->agent->platform()).'&nbsp;/&nbsp;'.$this->agent->browser().' v'.$this->agent->version();
		$add['uid']=$uid;
		$add['cion']=($addid==1)?User_Cion_Share:0;
		$add['jinyan']=($addid==1)?User_Jinyan_Share:0;
		$add['ip']=getip();
		$add['agent']=$agent;
		$add['addtime']=time();
		$this->Csdb->get_insert('share',$add);
		//分享访问的地址
		$shareurl=is_ssl().Web_Url.Web_Path;
		header("Location: ".$shareurl);exit;
	}
}

