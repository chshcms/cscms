<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-26
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Pass extends Cscms_Controller {

	function __construct(){
		parent::__construct();
		$this->load->helper('string');
		$this->lang->load('user');
	}

    //密码找回
	public function index(){
        $key = $this->input->get_post('key', TRUE, TRUE); //KEY
        $username = $this->input->get_post('username', TRUE, TRUE); //name

		if(!empty($username) && !empty($key)){

		         $row=$this->Csdb->get_row('user','id,name,pass,email',$username,'name');
		         if(!$row){
                        msg_url(L('pass_01'),'javascript:window.close();');
		         }
		         if($key != md5($row->id.$row->name.$row->pass.$row->email.substr(time(),0,-6))){
                        msg_url(L('pass_02'),'javascript:window.close();');
		         }
		         $Mark_Text=$this->load->view('pass-edit.html','',true);
		         $Mark_Text=str_replace("[user:passsave]",site_url('user/pass/edit')."?key=".$key."&username=".$username,$Mark_Text);

		}else{
		         $Mark_Text=$this->load->view('pass.html','',true);
		         $Mark_Text=str_replace("[user:passsave]",site_url('user/pass/save'),$Mark_Text);
		}

		$Mark_Text=str_replace("{cscms:title}",L('pass_03')." - ".Web_Name,$Mark_Text);
		//判断验证码开关
		$Mark_Text=str_replace("[user:codecheck]",User_Code_Mode,$Mark_Text);
		//token
		$Mark_Text=str_replace("[user:token]",get_token(),$Mark_Text);

        $Mark_Text=$this->Csskins->template_parse($Mark_Text,true);
		echo $Mark_Text;
	}

    //找回验证
	public function save(){
		$token=$this->input->post('token', TRUE);
		if(!get_token('token',1,$token)) msg_url(L('pass_04'),'javascript:history.back();');

		$username = $this->input->get_post('username', TRUE, TRUE);   //username
		$useremail = $this->input->get_post('useremail', TRUE, TRUE);   //useremail

		//判断验证码开关
		if(User_Code_Mode==1){
		     $codes=$this->input->post('usercode', TRUE);
			 if(empty($codes) || $this->cookie->get_cookie('codes')!=strtolower($codes)){
				    msg_url(L('pass_05'),'javascript:history.back();');
			 }
		}

		if(empty($username)){
			msg_url(L('pass_06'),'javascript:history.back();');  //用户名为空
		}elseif(empty($useremail)){
			msg_url(L('pass_07'),'javascript:history.back();');  //用户邮箱为空
		}else{

		    //可以用会员名、邮箱来进行登入
		    $sqlu="SELECT code,email,pass,id,name FROM ".CS_SqlPrefix."user where name='".$username."' and email='".$useremail."'";
		    $row=$this->db->query($sqlu)->row();
		    if(!$row){
		             msg_url(L('pass_08'),'javascript:history.back();');  //账号或者邮箱不正确
		    }else{

				$key=md5($row->id.$row->name.$row->pass.$row->email.substr(time(),0,-6));
				$Msgs['username'] = $row->name;
				$Msgs['url']      = is_ssl().Web_Url.userurl(site_url('user/pass'))."?key=".$key."&username=".$username;
				$title   = Web_Name.L('pass_09');
				$content = getmsgto(User_PassContent,$Msgs);
				$this->load->model('Csemail');
				$this->Csemail->send($row->email,$title,$content);
				get_token('token',2);
				msg_url(L('pass_10'),'javascript:history.back();');
			}
		}
	}

	//邮箱验证
	public function verify(){
		$key = $this->input->get_post('key', TRUE, TRUE); //KEY
		$username = $this->input->get_post('username', TRUE, TRUE); //name

		if(empty($username) || empty($key)) msg_url(L('pass_11'),'javascript:window.close();');

		$row=$this->Csdb->get_row('user','id,name,pass,email',$username,'name');
		if(!$row){
		    msg_url(L('pass_12'),'javascript:window.close();');
		}
		if($key != md5($row->id.$row->name.$row->pass.$row->email.substr(time(),0,-6))){
		    msg_url(L('pass_13'),'javascript:window.close();');
		}
	}

    //找回密码修改
	public function edit(){
		$token=$this->input->post('token', TRUE);
		if(!get_token('token',1,$token)) msg_url(L('pass_04'),'javascript:history.back();');

		$key = $this->input->get_post('key', TRUE, TRUE); //KEY
		$username = $this->input->get_post('username', TRUE, TRUE); //name

		$userpass = $this->input->get_post('userpass', TRUE, TRUE); 
		$userpass2 = $this->input->get_post('userpass2', TRUE, TRUE); 

		if(empty($username) || empty($key)) msg_url(L('pass_11'),'javascript:window.close();');

		$row=$this->Csdb->get_row('user','id,name,code,pass,email',$username,'name');
		if(!$row){
		       msg_url(L('pass_12'),'javascript:window.close();');
		}
		if($key != md5($row->id.$row->name.$row->pass.$row->email.substr(time(),0,-6))){
		       msg_url(L('pass_14'),'javascript:window.close();');
		}

		if(empty($userpass)) msg_url(L('pass_15'),'javascript:history.back();');
		if($userpass!=$userpass2) msg_url(L('pass_16'),'javascript:history.back();');

		$edit['pass']=md5(md5($userpass).$row->code);
		$this->Csdb->get_update ('user',$row->id,$edit);
		get_token('token',2);

		msg_url(L('pass_17'),userurl(site_url('user/login')));
	}
}
