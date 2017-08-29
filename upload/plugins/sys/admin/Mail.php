<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-08-01
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Mail extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
		$this->lang->load('admin_mail');
        $this->Csadmin->Admin_Login();
	}

	public function index(){
        $this->load->view('mail_setting.html');
	}

	public function add(){
        $this->load->view('mail_add.html');
	}

	public function save(){
	    $CS_Smtpmode = intval($this->input->post('CS_Smtpmode', TRUE));
	    $CS_Smtphost = $this->input->post('CS_Smtphost', TRUE);
	    $CS_Smtpport = intval($this->input->post('CS_Smtpport', TRUE));
	    $CS_Smtpuser = $this->input->post('CS_Smtpuser', TRUE);
	    $CS_Smtppass = $this->input->post('CS_Smtppass', TRUE);
	    $CS_Smtpmail = $this->input->post('CS_Smtpmail', TRUE);
	    $CS_Smtpname = $this->input->post('CS_Smtpname', TRUE);
        if($CS_Smtpport==0)   $CS_Smtpport=25;
		if($CS_Smtppass==substr(CS_Smtppass,0,3).'******'){
            $CS_Smtppass=CS_Smtppass;
		}

        //判断主要数据不能为空
	    if ($CS_Smtpmode==1 && (empty($CS_Smtphost)||empty($CS_Smtpuser)||empty($CS_Smtppass)||empty($CS_Smtpmail))){
		    getjson(L('plub_01'));
	    }

        $strs="<?php"."\r\n";
        $strs.="define('CS_Smtpmode',".$CS_Smtpmode.");      //SMTP开关     \r\n";
        $strs.="define('CS_Smtphost','".$CS_Smtphost."');      //SMTP服务器      \r\n";
        $strs.="define('CS_Smtpport','".$CS_Smtpport."');      //SMTP端口    \r\n";
        $strs.="define('CS_Smtpuser','".$CS_Smtpuser."');      //SMTP帐号     \r\n";
        $strs.="define('CS_Smtppass','".$CS_Smtppass."');      //SMTP密码     \r\n";
        $strs.="define('CS_Smtpmail','".$CS_Smtpmail."');      //发送EMAIL     \r\n";
        $strs.="define('CS_Smtpname','".$CS_Smtpname."');      //发送者名称";

        //写文件
        if (!write_file(CSCMS.'sys/Cs_Mail.php', $strs)){
            getjson(L('plub_02'));
        }else{
            $info['url'] = site_url('mail').'?v='.rand(1000,9999);
            getjson($info,0);
        }
	}

	public function add_save(){
	    $sid = intval($this->input->post('sid', TRUE));
	    $email = $this->input->post('email', TRUE);
	    $email2 =  nl2br($this->input->post('email2'));
	    $zu = $this->input->post('zu', TRUE);
	    $title = $this->input->post('title', TRUE);
	    $neir = $this->input->post('neir');

	    if (empty($title)||empty($neir)){
		    getjson(L('plub_03'));
	    }

		if($sid==1){
			$arr[]=$email;
		}elseif($sid==2){
			$arr=explode("<br />",$email2);
		}else{
			$arr=array();
			if(intval($zu)>0){
				$result=$this->db->query("select email from ".CS_SqlPrefix."user where zid=".$zu."");
			}elseif($zu==0){
				$result=$this->db->query("select email from ".CS_SqlPrefix."user where zid=0");
			}else{
				$result=$this->db->query("select email from ".CS_SqlPrefix."user");
			}
			foreach ($result->result() as $row) {
				if(!empty($row->email)){
					$arr[]=$row->email;
				}
			}
		}
	    if(empty($arr)){
	    	getjson(L('plub_04'));
	    }
        $this->load->model('Csemail');
        $str = '';
		foreach ($arr as $email){
			$res=$this->Csemail->send($email,$title,$neir);
			if($res){
				$str .= "<font color=red>".$email."----->".L('plub_05')."</font><br/>";
			}else{
				$str .= "<font color=red>".$email."----->".L('plub_06')."</font><br/>";
			}
        }
        $info['msg'] = L('plub_07');
        $info['mailres'] = $str;
        $info['sid'] = $sid;
        $info['sign'] = 'mail';
        getjson($info,0);
	}
}

