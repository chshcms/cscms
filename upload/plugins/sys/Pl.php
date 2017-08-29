<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-30
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pl extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    //关闭数据库缓存
        $this->db->cache_off();
	    $this->load->model('Cstpl');
	    $this->load->model('Csuser');
	}

    //评论列表
	public function index($dir='blog',$did=0,$cid=0,$page=1){
	    $callback = $this->input->get('callback',true);
        $Mark_Text = $this->Cstpl->pl($dir,intval($did),intval($cid),intval($page));
        getjson(array('str'=>$Mark_Text),0,1,$callback);
	}

    //新增评论
	public function add()
	{
	    $callback = $this->input->get('callback',true);
		$token=$this->input->get_post('token', TRUE);
		$add['dir']=$this->input->get_post('dir', TRUE);
		$add['content']=$this->input->get_post('neir', TRUE);
		$add['content']=facehtml(filter($add['content']));
		//转化回复
		$hf=0;
        preg_match_all('/'.L('pl_00').'@(.*)@:/i',$add['content'],$bs);
        if(!empty($bs[0][0]) && !empty($bs[1][0])){
			$uid=getzd('user','id',$bs[1][0],'name');
			$nichen=getzd('user','nichen',$bs[1][0],'name');
			$ulink=userlink('index',$uid,$bs[1][0]);
			if(empty($nichen)) $nichen=$bs[1][0];
			$b=L('pl_00').'<a target="_blank" href="'.$ulink.'">@'.$nichen.'@</a>:';
			$add['content']=str_replace($bs[0][0],$b,$add['content']);
			$hf=1;
		}
        unset($bs);
		$add['did']=intval($this->input->get_post('did'));
		if(Pl_Modes==3){
            $error=L('pl_01');
		}elseif($add['did']==0){
            $error=L('pl_02');
		}elseif(!get_token('pl_token',1,$token)){
            $error=L('pl_03');
		}elseif(isset($_SESSION['pladdtime']) && time()<$_SESSION['pladdtime']+30){
            $error=L('pl_04');
		}elseif(empty($add['content'])){
            $error=L('pl_05');
		}elseif(Pl_Youke==0 && empty($_SESSION['cscms__id'])){//关闭游客评论
            $error=L('pl_06');
		}else{

            $add['uid']=isset($_SESSION['cscms__id'])?intval($_SESSION['cscms__id']):0;
            $add['user']=isset($_SESSION['cscms__name'])?$_SESSION['cscms__name']:L('pl_07');
		    $add['cid']=intval($this->input->get_post('cid'));
		    $add['fid']=intval($this->input->get_post('fid'));
		    $add['ip']=getip();
		    $add['addtime']=time();

            $ids=$this->Csdb->get_insert('pl',$add);
		    if(intval($ids)==0){
                $error=L('pl_08'); //失败
			}else{
                //摧毁token
		        get_token('pl_token',2);
                $_SESSION['pladdtime']=time();
                $error='ok';

				//发送通知
				if($add['dir']!='singer'){
					$dirname=getzd('plugins','name',$add['dir'],'dir');
				    if($add['dir']=='dance'){
                         $link=linkurl('play','id',$add['did'],1,'dance');
					}elseif($add['dir']!='blog'){
                         $link=linkurl('show','id',$add['did'],1,$add['dir']);
					}
					if($add['dir']=='pic'){
						 $dataname=getzd('pic_type','name',$add['did']);
						 $pluid=getzd('pic_type','uid',$add['did']);
					}elseif($add['dir']=='blog'){
						 $pluid=getzd('blog','uid',$add['did']);
						 $dataname=getzd('blog','neir',$add['did']);
						 $dirname=L('pl_09');
						 $username=getzd('user','name',$pluid);
                         $link=userlink('blog',$pluid,$username,$add['did']);
					}else{
						 $dataname=getzd($add['dir'],'name',$add['did']);
						 $pluid=getzd($add['dir'],'uid',$add['did']);
					}
					if($hf==0){
			             $pltitle=L('pl_10',array($dirname));
			             $plneir=L('pl_12',array($_SESSION['cscms__name'],$dirname,$link,$dataname));
					}else{
						 $pltitle=L('pl_11');
			             $plneir=L('pl_13',array($_SESSION['cscms__name'],$link,$dataname));
						 $pluid=$uid;
				    }
					if($pluid>0){
			            $addm['uida']=$pluid;
			            $addm['uidb']=$_SESSION['cscms__id'];
			            $addm['name']=$pltitle;
			            $addm['neir']=$plneir;
			            $addm['addtime']=time();
        	            $this->Csdb->get_insert('msg',$addm);
					}
				}
			}
		}
		getjson(array('msg'=>$error),0,1,$callback);
	}

    //删除评论
	public function del()
	{
	    $callback = $this->input->get('callback',true);
		$id = intval($this->input->get_post('id'));
		$token=$this->input->get_post('token', TRUE);
		if($id==0){
            $error=L('pl_14');
		}elseif(!get_token('pl_token',1,$token)){
            $error=L('pl_15');
		}elseif(empty($_SESSION['cscms__id'])){ //未登陆
            $error=L('pl_16');
		}else{
            $row=$this->Csdb->get_row('pl','uid',$id);
            if(!$row){
                 $error=L('pl_02');
			}elseif($row->uid!=$_SESSION['cscms__id']){
                 $error=L('pl_17');
			}else{
                 //权限通过删除
				 $this->Csdb->get_del('pl',$id,'fid');
				 $this->Csdb->get_del('pl',$id);
                 $error='ok';
                 //摧毁token
	             get_token('pl_token',2);
			}
		}
		getjson(array('msg'=>$error),0,1,$callback);
	}
}
