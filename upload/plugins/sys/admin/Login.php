<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2017 chshcms.com. All rights reserved.
 * @Author:zhwdeveloper
 * @Dtime:2016-12-20
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Login extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
		$this->load->library('user_agent');
        $this->load->library('card');
        $this->lang->load('admin_login');
	    $this->load->model('Csadmin');
	}
	//登录页面
	public function index(){
        $this->load->view('login.html');
	}
	//登录信息处理
	public function log(){
		$name = $this->input->post('adminname',TRUE);
		$pass = $this->input->post('adminpass',TRUE);
		$code = $this->input->post('admincode',TRUE);

		if (empty($name)||empty($pass)||empty($code)){
			$info = L('log_0');
			getjson($info);
		}

		if($code!=Admin_Code){
			$info = L('log_1');
			getjson($info);
		}

		$log = $this->Csdb->get_row('admin','*',array('adminname'=>$name));
		if($log){
			if($log->adminpass != md5(md5($pass).$log->admincode)){
				$info = L('log_2');
				getjson($info);
			}elseif(CS_Safe_Card==1 && !empty($log->card)){  //电子口令验证
                //获取口令卡坐标
				$zb_arr = $this->card->authe_rand($name);
				$zb = implode(',', $zb_arr);
                $_SESSION['card_zb'   ] = $zb;
                $_SESSION['card_code' ] = $log->card;
                $_SESSION['admin_name'] = $log->adminname;
                $error = 3;
                $info['url'] = site_url('login/card');
                getjson($info,$error);
            }else{
			    $ip = getip();
                $updata['lognums'] = $log->lognums+1;
                $updata['logip'  ] = $ip;
                $updata['logtime'] = time();
                $this->Csdb->get_update('admin',$log->id,$updata);

				$agent = ($this->agent->is_mobile() ? $this->agent->mobile() : $this->agent->platform()).'&nbsp;/&nbsp;'.$this->agent->browser().' v'.$this->agent->version();
				$add['uid']=$log->id;
				$add['loginip']=$ip;
				$add['logintime']=time();
				$add['useragent']=$agent;
				$this->Csdb->get_insert('admin_log',$add);


                $_SESSION['admin_name']  = $log->adminname;
                $_SESSION['admin_id']    = $log->id;
                $_SESSION['admin_pass']  = md5($log->adminpass);
                $_SESSION['admin_logtime']  = date('Y-m-d H:i:s',$log->logtime);
                $_SESSION['admin_logip']  = $log->logip;

                //记住登录24小时
                $this->cookie->set_cookie("admin_id",$log->id,time()+86400);
                $this->cookie->set_cookie("admin_login",md5($log->adminname.$log->adminpass),time()+86400);

                $error = 0;
                $info['msg'] = L('card_3');
                $info['url'] = site_url('index');
                getjson($info,$error);
			}
		}else{
			$info = L('log_3');
			getjson($info);
		}
	}

	public function logout(){
        unset($_SESSION['admin_name'],$_SESSION['admin_id'],$_SESSION['admin_pass']);
        unset($_SESSION['admin_logtime'],$_SESSION['admin_logip']);

        //清除记住登录
	    $this->cookie->set_cookie("admin_id");
        $this->cookie->set_cookie("admin_login");

		if(empty($_SERVER['HTTP_REFERER']) || site_url('opt/head')==$_SERVER['HTTP_REFERER']){
		      exit("<script>window.location='".site_url('login')."';</script>");
		}else{
		      exit("<script>window.location='".site_url('login')."?backurl=".urlencode($_SERVER['HTTP_REFERER'])."';</script>");
		}
	}

	//口令卡展示页面
	public function card(){
		$data['zb'] = $_SESSION['card_zb'];
		$this->load->view('login_card.html',$data);
	}
	//口令卡验证
	public function log_card(){
		$card = $this->input->post('admincard', TRUE);
		if (empty($card)){
			getjson(L('card_0'));
		}
		//验证口令
		$zb = explode(',',$_SESSION['card_zb']);
		$res = $this->card->verification($_SESSION['admin_name'],$zb,$card);
		if($res != L('card_1')){
			getjson($res);
		}
		$log = $this->Csdb->get_row('admin','*',$_SESSION['card_code'],'card');
		if($log){
			$ip=getip();
			$updata['lognums']  = $log->lognums+1;
			$updata['logip']   = $ip;
			$updata['logtime'] = time();
			$this->Csdb->get_update('admin',$log->id,$updata);

			$agent = ($this->agent->is_mobile() ? $this->agent->mobile() : $this->agent->platform()).'&nbsp;/&nbsp;'.$this->agent->browser().' v'.$this->agent->version();
			$add['uid']=$log->id;
			$add['loginip']=$ip;
			$add['logintime']=time();
			$add['useragent']=$agent;
			$this->Csdb->get_insert('admin_log',$add);

			$_SESSION['card_zb'] ='';
			$_SESSION['card_code']  = '';
			$_SESSION['admin_name']  = $log->adminname;
			$_SESSION['admin_id']    = $log->id;
			$_SESSION['admin_pass']  = md5($log->adminpass);
			$_SESSION['admin_logtime']  = date('Y-m-d H:i:s',$log->logtime);
			$_SESSION['admin_logip']  = $log->logip;

			//记住登录24小时
			$this->cookie->set_cookie("admin_id",$log->id,time()+86400);
			$this->cookie->set_cookie("admin_login",md5($log->adminname.$log->adminpass),time()+86400);

			if(empty($url)){
				$info['url'] = site_url('index');
			}else{
				$info['url'] = $url;
			}
			$info['msg'] = L('card_3');
			getjson($info,0);
		}else{
			$info['msg'] = L('card_2');
			$info['url'] = site_url('login');
			getjson($info);
		}
	}
}

