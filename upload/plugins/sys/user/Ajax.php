<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-03-05
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ajax extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
		$this->lang->load('user');
        $this->load->library('user_agent');
        if(!$this->agent->is_referral()) show_error(L('ajax_01'),404,Web_Name.L('ajax_02'));
        //关闭数据库缓存
        $this->db->cache_off();
	    $this->load->model('Csuser');
	}

	//签到
	public function qiandao(){
	    $callback = $this->input->get('callback',true);
		$err=1002;
		$str=L('ajax_03');
		if($this->Csuser->User_Login(1)){
			$row=$this->db->query("select qdts,qdtime from ".CS_SqlPrefix."user where id=".$_SESSION['cscms__id']."")->row();
			if(date('Y-m-d',$row->qdtime)==date('Y-m-d')){ //今天已经签到
		        $err='1001';
				$str=L('ajax_04');
			}elseif(date("Y-m-d",$row->qdtime)!=date("Y-m-d",strtotime("-1 day"))){ //未连续签到
                $this->db->query("update ".CS_SqlPrefix."user set qdts=1,qdtime='".time()."',cion=cion+".User_Cion_Qd.",jinyan=jinyan+".User_Jinyan_Qd." where id=".$_SESSION['cscms__id']."");
			}else{
                 
                $this->db->query("update ".CS_SqlPrefix."user set qdts=qdts+1,qdtime='".time()."',cion=cion+".User_Cion_Qd.",jinyan=jinyan+".User_Jinyan_Qd." where id=".$_SESSION['cscms__id']."");
			}
		}else{
		    $err=1000;
		}
		getjson(array('error'=>$err,'str'=>$str),0,1,$callback);
	}

    //未读消息
	public function msg(){
	    $callback = $this->input->get('callback',true);
		$nums=0;
		if($this->Csuser->User_Login(1)){
              $nums=$this->db->query("select id from ".CS_SqlPrefix."msg where uida=".$_SESSION['cscms__id']." and did=0")->num_rows();
		}
		getjson(array('nums'=>$nums),0,1,$callback);
	}

    //消息全部标记为已读
	public function msg_du(){
	    $callback = $this->input->get('callback',true);
		if($this->Csuser->User_Login(1)){
             $this->db->query("update ".CS_SqlPrefix."msg set did=1 where uida=".$_SESSION['cscms__id']."");
			 $error=1001;
		}else{
             $error=1000;
		}
		getjson(array('error'=>$error),0,1,$callback);
	}

    //删除消息
	public function msg_del(){
	    $callback = $this->input->get('callback',true);
        $id = intval($this->input->get_post('id'));   //ID
		if(!$this->Csuser->User_Login(1)){ //未登陆
             $err=1000;
		}elseif($id==0){
	         $err=1001;//ID为空
		}else{
             $row=$this->db->query("select uida from ".CS_SqlPrefix."msg where id=".$id."")->row();
			 if($row){
                 if($row->uida!=$_SESSION['cscms__id']){
	                  $err=1002;//没有权限
				 }else{
                      $this->db->query("DELETE FROM ".CS_SqlPrefix."msg where id=".$id."");
	                  $err=1003;//删除完成
				 }
			 }else{
	             $err=1002;//数据不存在
			 }
		}
		getjson(array('error'=>$err),0,1,$callback);
	}

    //会员动态
	public function feed(){
	    $callback = $this->input->get('callback',true);
        $dir = $this->input->get_post('dir', TRUE, TRUE);   //板块
	    $cid = intval($this->input->get_post('cid')); //CID方式，1全站，2好友，3自己
		if($cid>1 && !$this->Csuser->User_Login(1)){ //未登陆
			getjson(array('str'=>L('ajax_05')),0,1,$callback);
		}
        //加载模板
	    $Mark_Text=$this->load->view('feed-ajax.html','',true);
		preg_match_all('/{cscms:([\S]+)\s+(.*?loop=\"([\S]+)\".*?)}([\s\S]+?){\/cscms:\1}/',$Mark_Text,$page_arr);
	    if(!empty($page_arr) && !empty($page_arr[2])){
		      $field=$page_arr[1][0]; //前缀名

		      $sqlstr="select {field} from `".CS_SqlPrefix."dt` where 1=1";
              if($cid==3){ //自己
                    $sqlstr.=" and uid=".$_SESSION['cscms__id']."";
		      }
              if($cid==2){ //好友
                    $user='';
                    $result=$this->db->query("select uidb from ".CS_SqlPrefix."friend where uida=".$_SESSION['cscms__id']." order by addtime desc");
                    foreach ($result->result() as $rowf) {
                         $user.=",".$rowf->uidb."";
                    }
		            if($user==''){
		            	getjson(array('str'=>'<div class="feed_items">'.L('ajax_06').'</div>'),0,1,$callback);
		            }
                    $user=substr($user,1);
                    $sqlstr.=" and uid in (".$user.")";
			  }
			  if(!empty($dir) && $dir!='all'){
                    $sqlstr.=" and dir='".$dir."'";
			  }
			  $sqls=$this->Csskins->cscms_sql($page_arr[1][0],$page_arr[2][0],$page_arr[0][0],$page_arr[3][0],'',0,0,$sqlstr);
			  $query=$this->db->query($sqls);
			  $nums=$query->num_rows(); //总数量
			  $data_content='';
		      if($nums==0){
			           $data_content.="<div class='feed_items'>".L('ajax_06')."</div>";
		      }else{
				       $sorti=1;
					   $result_array=$query->result_array();
				       foreach ($result_array as $row2) {
					        $datatmp=$this->Csskins->cscms_skins($field,$page_arr[0][0],$page_arr[4][0],$row2,$sorti);
					        $sorti++;
					        $data_content.=$datatmp;
				       }
			  }
		      $Mark_Text=str_replace($page_arr[0][0],$data_content,$Mark_Text);
		}
        $Mark_Text=$this->Csskins->template_parse($Mark_Text,true);
        getjson(array('str'=>$Mark_Text),0,1,$callback);
	}

    //发表说说
	public function blog(){
	    $callback = $this->input->get('callback',true);
        $neir = $this->input->get_post('neir', TRUE);   //内容
		if(!$this->Csuser->User_Login(1)){ //未登陆
             $err=1000;
		}elseif(empty($neir)){
	         $err=1001;//内容为空
		}elseif(strlen($neir)>280){
	         $err=1002;//内容过长
		}elseif(isset($_SESSION['blogtime']) && time()<$_SESSION['blogtime']+20){
	         $err=1003;//时间太短
        }else{
	         $add['uid'] = $_SESSION['cscms__id'];
	         $add['neir'] = facehtml($neir);	
	         $add['addtime'] = time();
			 $ids=$this->Csdb->get_insert('blog',$add);
			 if($ids){

				  //发表动态
	              $dt['dir'] = 'blog';
	              $dt['uid'] = $_SESSION['cscms__id'];
	              $dt['did'] = $ids;
	              $dt['title'] = L('ajax_07');
	              $dt['name'] = facehtml($neir);
	              $dt['link'] = userlink('blog',$_SESSION['cscms__id'],$_SESSION['cscms__name'],$ids);
	              $dt['addtime'] = time();
                  $this->Csdb->get_insert('dt',$dt);

				  $_SESSION['blogtime']=time();
				  $err=1004;
			 }else{
                  $err=1005;
			 }
		}
		getjson(array('error'=>$err),0,1,$callback);
	}

    //删除说说
	public function blog_del(){
	    $callback = $this->input->get('callback',true);
        $id = intval($this->input->get_post('id'));   //ID
		if(!$this->Csuser->User_Login(1)){ //未登陆
             $err=1000;
		}elseif($id==0){
	         $err=1001;//ID为空
		}else{
             $row=$this->db->query("select uid from ".CS_SqlPrefix."blog where id=".$id."")->row();
			 if($row){
                 if($row->uid!=$_SESSION['cscms__id']){
	                  $err=1002;//没有权限
				 }else{
                      $this->db->query("DELETE FROM ".CS_SqlPrefix."blog where id=".$id."");
					  //删除动态
                      $this->db->query("DELETE FROM ".CS_SqlPrefix."dt where did=".$id." and dir='blog'");
	                  $err=1003;//删除完成
				 }
			 }else{
	             $err=1002;//数据不存在
			 }
		}
		getjson(array('error'=>$err),0,1,$callback);
	}

	//回复留言
	public function gbook_hf(){
	    $callback = $this->input->get('callback',true);
        $fid = intval($this->input->get_post('fid'));   //回复ID
        $uida = intval($this->input->get_post('uida'));   //接收会员ID
        $neir = $this->input->get_post('neir', TRUE, TRUE);   //内容

		if($fid==0 || $uida==0){  //参数错误
             $err=1001;
		}elseif(!$this->Csuser->User_Login(1)){ //未登陆
             $err=1000;
		}elseif(empty($neir)){
	         $err=1002;//内容为空
        }else{
			 //判断留言是否存在
			 $row=$this->db->query("select id from ".CS_SqlPrefix."gbook where id=".$fid."")->row();
			 if(!$row){
	             $err=1003;//留言被删除
			 }else{
			     $rowu=$this->db->query("select id from ".CS_SqlPrefix."user where id=".$uida."")->row();
				 if(!$rowu){
	                 $err=1004;//接收会员不存在
				 }else{
	                 $add['uida'] = $uida;
	                 $add['fid']  = $fid;
	                 $add['uidb'] = $_SESSION['cscms__id'];
	                 $add['neir'] = facehtml($neir);	
	                 $add['addtime'] = time();
			         $ids=$this->Csdb->get_insert('gbook',$add);
			         if($ids){
				         //发送消息提醒
	                     $msg['uida'] = $uida;
	                     $msg['uidb'] = 0;
	                     $msg['name'] = $_SESSION['cscms__name'].L('ajax_08');
	                     $msg['neir'] = $_SESSION['cscms__name'].L('ajax_09').$neir;
	                     $msg['addtime'] = time();
                         $this->Csdb->get_insert('msg',$msg);
				         $err=1005;
					 }
				 }
			 }
		}
		getjson(array('error'=>$err),0,1,$callback);
	}

    //删除留言
	public function gbook_del(){
	    $callback = $this->input->get('callback',true);
        $id = intval($this->input->get_post('id'));   //ID
		if(!$this->Csuser->User_Login(1)){ //未登陆
             $err=1000;
		}elseif($id==0){
	         $err=1001;//ID为空
		}else{
             $row=$this->db->query("select uida,uidb from ".CS_SqlPrefix."gbook where id=".$id."")->row();
			 if($row){
                 if($row->uida!=$_SESSION['cscms__id'] && $row->uidb!=$_SESSION['cscms__id']){
	                  $err=1002;//没有权限
				 }else{
                      $this->db->query("DELETE FROM ".CS_SqlPrefix."gbook where id=".$id."");
					  //删除回复
                      $this->db->query("DELETE FROM ".CS_SqlPrefix."gbook where fid=".$id."");
	                  $err=1003;//删除完成
				 }
			 }else{
	             $err=1002;//数据不存在
			 }
		}
		getjson(array('error'=>$err),0,1,$callback);
	}

    //删除粉丝
	public function fans_del(){
	    $callback = $this->input->get('callback',true);
        $id = intval($this->input->get_post('id'));   //ID
		if(!$this->Csuser->User_Login(1)){ //未登陆
             $err=1000;
		}elseif($id==0){
	         $err=1001;//ID为空
		}else{
             $row=$this->db->query("select uida from ".CS_SqlPrefix."fans where id=".$id."")->row();
			 if($row){
                 if($row->uida!=$_SESSION['cscms__id']){
	                  $err=1002;//没有权限
				 }else{
                      $this->db->query("DELETE FROM ".CS_SqlPrefix."fans where id=".$id."");
	                  $err=1003;//删除完成
				 }
			 }else{
	             $err=1002;//数据不存在
			 }
		}
		getjson(array('error'=>$err),0,1,$callback);
	}

    //解除好友
	public function friend_del(){
	    $callback = $this->input->get('callback',true);
        $id = intval($this->input->get_post('id'));   //ID
		if(!$this->Csuser->User_Login(1)){ //未登陆
             $err=1000;
		}elseif($id==0){
	         $err=1001;//ID为空
		}else{
             $row=$this->db->query("select uida from ".CS_SqlPrefix."friend where id=".$id."")->row();
			 if($row){
                 if($row->uida!=$_SESSION['cscms__id']){
	                  $err=1002;//没有权限
				 }else{
                      $this->db->query("DELETE FROM ".CS_SqlPrefix."friend where id=".$id."");
	                  $err=1003;//删除完成
				 }
			 }else{
	             $err=1002;//数据不存在
			 }
		}
		getjson(array('error'=>$err),0,1,$callback);
	}
	
    //解除第三方登录
	public function open_del(){
	    $callback = $this->input->get('callback',true);
        $cid = intval($this->input->get_post('cid'));   //ID
		if(!$this->Csuser->User_Login(1)){ //未登陆
             $err=1000;
		}else{
             $this->db->query("DELETE FROM ".CS_SqlPrefix."useroauth where cid=".$cid." and uid=".$_SESSION['cscms__id']."");
             $err=1001;
		}
		getjson(array('error'=>$err),0,1,$callback);
	}
	
    //会员主页模板
	public function web(){
	    $callback = $this->input->get('callback',true);
        $dir = $this->input->get_post('dir',true);   //ID
		$confiles = FCPATH.'tpl'.FGF.'pc'.FGF.'home'.FGF.$dir.FGF.'config.php';
		if(!$this->Csuser->User_Login(1)){ //未登陆
             $err=1000;
		}elseif(empty($dir) || !file_exists($confiles)){
             $err=1001;
		}else{
			 $con=require_once($confiles);
             $row=$this->db->query("select zid,level,cion from ".CS_SqlPrefix."user where id=".$_SESSION['cscms__id']."")->row();
			 if($row->zid < $con['vip']){
                  $err=1002;
			 }elseif($row->level < $con['level']){
                  $err=1003;
			 }else{
			     
			     if($row->cion < $con['cion']){
                     $err=1004;
				 }else{
					 if($con['cion']>0){
                        //判断是否使用过
                        $rowp=$this->db->query("select id from ".CS_SqlPrefix."web_pay where uid=".$_SESSION['cscms__id']." and mid=".intval($con['mid'])."")->row();
						if(!$rowp){
                            //减去金币
					        $edit['cion']=$row->cion - $con['cion'];

							//增加使用记录
							$add['mid']=intval($con['mid']);
							$add['uid']=$_SESSION['cscms__id'];
							$add['cion']=$con['cion'];
							$add['name']=$con['name'];
                            $add['addtime']=time();
				            $this->Csdb->get_insert('web_pay',$add);

					        //写入消费记录
					        $add2['title']=L('ajax_10').'《'.$con['name'].'》';
					        $add2['uid']=$_SESSION['cscms__id'];
					        $add2['nums']=$con['cion'];
					        $add2['ip']=getip();
					        $add2['dir']='user';
                            $add2['addtime']=time();
				            $this->Csdb->get_insert('spend',$add2);
						}
					 }
					 $edit['skins']=$con['path'];
		             $this->Csdb->get_update('user',$_SESSION['cscms__id'],$edit);
					 $err=1005;
				 }
			 }

		}
		getjson(array('error'=>$err),0,1,$callback);
	}
}