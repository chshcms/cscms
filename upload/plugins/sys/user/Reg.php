<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-23
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Reg extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
		$this->load->helper('string');
		$this->load->library('user_agent');
		$this->lang->load('user');
	}

    //注册
	public function index(){
		//注册开关
		if(User_Reg==0) msg_url(L('reg_35'),Web_Path);
	    $template=$this->load->view('reg.html','',true);
		$Mark_Text=str_replace("{cscms:title}",L('reg_01')." - ".Web_Name,$template);
		$Mark_Text=str_replace("[user:regsave]",site_url('user/reg/save'),$Mark_Text);
		//判断验证码开关
		$Mark_Text=str_replace("[user:codecheck]",User_Code_Mode,$Mark_Text);
		//判断手机强制验证
		$Mark_Text=str_replace("[user:telcheck]",User_Tel,$Mark_Text);
		//token
		$Mark_Text=str_replace("[user:token]",get_token(),$Mark_Text);
        //用户名判断
		$Mark_Text=str_replace("[user:nameajaxurl]",site_url('user/reg/check').'?field=name',$Mark_Text);
        //邮件判断
		$Mark_Text=str_replace("[user:emailajaxurl]",site_url('user/reg/check').'?field=email',$Mark_Text);
        //手机判断
		$Mark_Text=str_replace("[user:telajaxurl]",site_url('user/reg/check').'?field=tel',$Mark_Text);
        $Mark_Text=$this->Csskins->template_parse($Mark_Text,true);
		echo $Mark_Text;
	}

    //注册入库
	public function save(){
		//注册开关
		if(User_Reg==0) msg_url(L('reg_35'),Web_Path);
		$userinfo = array();
		$token=$this->input->post('token', TRUE);
		if(!get_token('token',1,$token)) msg_url(L('reg_02'),'javascript:history.back();');

		//判断验证码开关
		if(User_Code_Mode==1){
			$codes=str_checkhtml($this->input->post('usercode', TRUE));
			if(empty($codes) || $this->cookie->get_cookie('codes')!=strtolower($codes)){
				msg_url(L('reg_03'),'javascript:history.back();');
			}
		}

		$userinfo['code']=random_string('alnum',6);
		$userinfo['name']=$this->input->post('username', TRUE, TRUE);
		$userinfo['pass']=$this->input->post('userpass', TRUE, TRUE);
		$userinfo['nichen']=$this->input->post('usernichen', TRUE);
		$userinfo['email']=$this->input->post('useremail', TRUE, TRUE);
		$userinfo['tel']=$this->input->post('usertel', TRUE, TRUE);
		$userinfo['regip']=getip();
		$userinfo['cion']=User_Cion_Reg;
		$userinfo['jinyan']=User_Jinyan_Reg;
		$userinfo['addtime']=time();
		$userinfo['yid']=0;
		if($userinfo['nichen']=="0") $userinfo['nichen']='';

		if(!is_username($userinfo['name'])) msg_url(L('reg_04'),'javascript:history.back();');
		if(!is_userpass($userinfo['pass'])) msg_url(L('reg_05'),'javascript:history.back();');
		if(!empty($userinfo['nichen']) && !is_username($userinfo['nichen'],1)) msg_url(L('reg_06'),'javascript:history.back();');
		if(!is_email($userinfo['email'])) msg_url(L('reg_07'),'javascript:history.back();');


        //判断保留用户名
		$ymext = explode('|',Home_Ymext);
		if(in_array($userinfo['name'], $ymext)){
        	msg_url(L('reg_08'),'javascript:history.back();');
		}

        //判断同一IP注册时间限制
		if(User_RegIP>0){
		    $row=$this->db->query("SELECT addtime FROM ".CS_SqlPrefix."user where regip='".$userinfo['regip']."' order by id desc")->row();
		    if($row && ($row->addtime+3600*User_RegIP) > time()){
            	msg_url(L('reg_09'),'javascript:history.back();');
			}
		}

        //--------------------------- Ucenter ---------------------------
        if(User_Uc_Mode==1){
			include CSCMS.'sys/Cs_Ucenter.php';
			include CSPATH.'uc_client/client.php';
			//判断UC会员是否存在
			$res = uc_user_checkname($userinfo['name']);
			if($res < 1 ){
				msg_url(L('reg_10'),'javascript:history.back();');
			}
			//判断UC邮箱是否存在
			$res = uc_user_checkemail($userinfo['email']);
			if($res < 1 ){
				msg_url(L('reg_11'),'javascript:history.back();');
			}
		}
        //--------------------------- Ucenter End ---------------------------

        //判断用户名是否注册
		$username=$this->Csdb->get_row('user','id',$userinfo['name'],'name');
		if($username){
        	msg_url(L('reg_10'),'javascript:history.back();');
		}

        //判断邮箱是否注册
		$useremail=$this->Csdb->get_row('user','id',$userinfo['email'],'email');
		if($useremail){
        	msg_url(L('reg_11'),'javascript:history.back();');
		}

		//下面选填字段
		$userinfo['qq']=$this->input->post('userqq', TRUE);
		$userinfo['sex']=intval($this->input->post('usersex', TRUE));
		$userinfo['city']=$this->input->post('usercity', TRUE);
		$userinfo['skins']='';
		$userinfo['qianm']='';

	    if(!empty($userinfo['tel'])){
			if(!is_tel($userinfo['tel'])) {
				msg_url(L('reg_12'),'javascript:history.back();');
			}
			//判断手机号码是否注册
			$usertel=$this->Csdb->get_row('user','id',$userinfo['tel'],'tel');
			if($usertel){
				msg_url(L('reg_13'),'javascript:history.back();');
			}
	    }

        //判断手机强制验证
        if(User_Tel==1){
			if(empty($userinfo['tel'])) msg_url(L('reg_12'),'javascript:history.back();');
			$telcode=intval($this->input->post('telcode', TRUE));
			if($telcode==0 || $telcode!=$_SESSION['tel_code']){
				msg_url(L('reg_14'),'javascript:history.back();');
			}
		}

		//是否需要人工验证
        if(User_RegFun==1) {
	        $userinfo['yid']=1;
		    $title=L('reg_15');
		}

        //是否需要邮件验证
		if(User_RegEmailFun==1) {
		    $userinfo['yid']=2;
			$title=L('reg_16',array($userinfo['email']));
		}

        //--------------------------- Ucenter ---------------------------
        if(User_Uc_Mode==1){
			$uid=uc_user_register($userinfo['name'],$userinfo['pass'],$userinfo['email']);
			if($uid>0){
			     $userinfo['uid'] = $uid;
			}
		}
        //--------------------------- Ucenter End ---------------------------

		//密码加密
		$userinfo['pass']=md5(md5($userinfo['pass']).$userinfo['code']);
        $regid=$this->Csdb->get_insert('user',$userinfo);
		if(intval($regid)==0){
			msg_url(L('reg_17'),'javascript:history.back();');
		}

        //摧毁token
		get_token('token',2);

        $this->load->model('Csemail');
		if(User_RegEmailFun==1) { //发送激活邮件
			$key=md5($regid.$userinfo['name'].$userinfo['pass'].$userinfo['yid']);
			$Msgs['username'] = $userinfo['name'];
			$Msgs['url']      = is_ssl().Web_Url.userurl(site_url('user/reg/verify'))."?key=".$key."&username=".$userinfo['name'];
			$title   = Web_Name.L('reg_18');
			$content = getmsgto(User_RegEmailContent,$Msgs);
			$this->Csemail->send($userinfo['email'],$title,$content);
		}

		//判断发送欢迎信息
		if(User_RegMsgFun==1){
			$Msg['username'] = $userinfo['name'];
			$addmsg['uida'] = $regid;
			$addmsg['uidb'] = 0;
			$addmsg['name'] = L('reg_19').Web_Name;
			$addmsg['neir'] = getmsgto(User_RegMsgContent,$Msg);
			$addmsg['addtime'] = time();
			$this->Csdb->get_insert('msg',$addmsg);

			//发送欢迎邮件
			if(User_RegEmailFun==0) {
				$title   = Web_Name.L('reg_20');
				$content = $addmsg['neir'];
				$this->Csemail->send($userinfo['email'],$title,$content);
			}
		}

        if($userinfo['yid']==0){ //登录
			//每天登陆加积分
			$updata['cion']    = User_Cion_Reg+User_Cion_Log;
			$updata['zx']      = 1;
			$updata['lognum']  = 1;
			$updata['logtime'] = time();
			$updata['logip']   = getip();
			$updata['logms']   = time();
			$this->Csdb->get_update ('user',$regid,$updata);

			//登录日志
			$agent = ($this->agent->is_mobile() ? $this->agent->mobile() :                    $this->agent->platform()).'&nbsp;/&nbsp;'.$this->agent->browser().' v'.$this->agent->version();
			$add['uid']=$regid;
			$add['loginip']=getip();
			$add['logintime']=time();
			$add['useragent']=$agent;
			$this->Csdb->get_insert('user_log',$add);

			$_SESSION['cscms__id']    = $regid;
			$_SESSION['cscms__name']  = $userinfo['name'];
			$_SESSION['cscms__login'] = md5($userinfo['name'].$userinfo['pass']);

			//记住登录
			$user_login=md5($userinfo['name'].$userinfo['pass'].$userinfo['code']);
			$this->cookie->set_cookie("user_id",$regid,time()+86400);
			$this->cookie->set_cookie("user_login",$user_login,time()+86400);

			msg_url(L('reg_21'),userurl(site_url('user/space')),'ok');
		}else{
			msg_url(L('reg_21').$title.'~!',userurl(site_url('user/login')),'ok');
		}
	}

	//邮箱激活
	public function verify(){
        $key = $this->input->get_post('key', TRUE); //KEY
        $username = $this->input->get_post('username', TRUE, TRUE); //name

		if(empty($username) || empty($key)) msg_url(L('reg_22'),'javascript:window.close();');

		$row=$this->Csdb->get_row('user','id,name,pass,yid',$username,'name');
		if(!$row){
               msg_url(L('reg_23'),'javascript:window.close();');
		}
		if($key != md5($row->id.$row->name.$row->pass.$row->yid)){
               msg_url(L('reg_24'),'javascript:window.close();');
		}
		$edit['yid']=(User_RegFun==1)?1:0;
        $this->Csdb->get_update('user',$row->id,$edit);
		if(User_RegFun==1){
               msg_url(L('reg_25'),'javascript:window.close();','ok');
		}else{
               msg_url(L('reg_26'),userurl(site_url('user/login')),'ok');
		}
	}

    //判断会员账号、昵称、邮箱、手机是否存在
	public function check(){
        $field = $this->input->get_post('field', TRUE, TRUE);   //需要查询的字段
        $data = $this->input->get_post('param', TRUE, TRUE);   //需要查询的字段
		if(empty($field) || empty($data)) exit('{"status":"n","info":"'.get_bm(L('reg_27')).'"}');

        //判断保留用户名
		$ymext = explode('|',Home_Ymext);
		if($field=='name' && in_array($data, $ymext)){
            exit('{"status":"n","info":"'.get_bm(L('reg_28')).'"}');
		}

        //判断用户名格式
		if($field=='name' && !is_username($data)){
            exit('{"status":"n","info":"'.get_bm(L('reg_29')).'"}');
		}

		//判断邮箱格式
		if($field=='email' && !is_email($data)){
            exit('{"status":"n","info":"'.get_bm(L('reg_30')).'"}');
		}

		//判断手机号码格式
		if($field=='tel' && !preg_match('/^1([0-9]{9})/',$data)) {
            exit('{"status":"n","info":"'.get_bm(L('reg_31')).'"}');
		}

		//判断需要查询的字段是否存在
		if(!$this->db->field_exists($field, CS_SqlPrefix.'user')){
            exit('{"status":"n","info":"'.get_bm(L('reg_32')).'"}');
		}

        //查询数据库
		$row=$this->Csdb->get_row('user','id',$data,$field);
		if($row){
			exit('{"status":"n","info":"'.get_bm(L('reg_33')).'"}');
		}else{
        	exit('{"status":"y","info":""}');
		}
	}

	//发送手机短信验证码
	public function telinit(){
		$tel=$this->input->get_post('usertel', TRUE, TRUE);
		$this->load->library('smstel');
		echo $this->smstel->seadd($tel);
	}
}
