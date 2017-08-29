<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-03-10
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Open extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
		$this->load->helper('string');
		$this->load->library('denglu');
		$this->load->library('user_agent');
		$this->lang->load('user');
	}

    //登录
	public function login($ac=''){
		$log_state=md5(uniqid(rand(), TRUE));
		$dos = array('qq', 'weibo', 'baidu', 'renren', 'kaixin', 'douban', 'weixin');
		$ac = (!empty($ac) && in_array($ac, $dos))?$ac:'qq';
		//保存到cookie
        $this->cookie->set_cookie("log_ac",$ac,time()+1800);
        $this->cookie->set_cookie("log_fhurl",$_SERVER['HTTP_REFERER'],time()+1800); //来源路径
		$this->cookie->set_cookie("log_state",$log_state,time()+1800); //安全state
		//连接
        $this->denglu->login($ac,$log_state);
	}

    //返回
	public function callback(){
	    $ac=$this->cookie->get_cookie('log_ac'); //方式
	    $log_state=$this->cookie->get_cookie('log_state'); //安全验证
	    $log_fhurl=$this->cookie->get_cookie('log_fhurl'); //返回地址
        $uid=$this->denglu->callback($ac,$log_state);
		if(empty($log_fhurl)) $log_fhurl=spacelink('space');

		//判断是否登录，已经登录则直接绑定
        if(!empty($_SESSION['denglu__id'])){
	         $this->load->model('Csuser');
             if($this->Csuser->User_Login(1)){ 
                  $updata['uid']   = $_SESSION['cscms__id'];
                  $this->Csdb->get_update ('useroauth',$_SESSION['denglu__id'],$updata);
                  msg_url(L('open_01'),$log_fhurl,'ok');  //登录成功
			 }
		}

		if($uid>0){ //已经存在则登录

             $sqlu="SELECT code,email,pass,sid,yid,id,name,lognum,cion,vip,logtime,viptime,zid,zutime FROM ".CS_SqlPrefix."user where id=".$uid."";
	         $row=$this->db->query($sqlu)->row();
	         if($row){
                   if($row->sid==1){
						   msg_url(L('open_02'),'javascript:history.back();');
                   }elseif($row->yid==1){
						   msg_url(L('open_03'),'javascript:history.back();');
                   }elseif($row->yid==2){
		                   $key=md5($row->id.$row->name.$row->pass.$row->yid);
                           $Msgs['username'] = $row->name;
                           $Msgs['url']      = is_ssl().Web_Url.userurl(site_url('user/reg/verify'))."?key=".$key."&username=".$row->name;
                           $title   = Web_Name.L('open_04');
                           $content = getmsgto(User_RegEmailContent,$Msgs);
						   $this->load->model('Csemail');
                           $this->Csemail->send($row->email,$title,$content);
						   msg_url(L('open_05'),'javascript:history.back();');
                   }else{

                       //每天登陆加积分
                       if(User_Cion_Log>0 && date("Y-m-d",$row->logtime)!=date('Y-m-d')){
                           $updata['cion']  = $row->cion+User_Cion_Log;
                       }

                       //判断VIP
                       IF($row->vip>0 && $row->viptime<time()){
                            $updata['vip']  = 0;
                            $updata['viptime']  = 0;
                       }
                       //判断会员组
                       IF($row->zid>1 && $row->zutime<time()){
                            $updata['zid']  = 1;
                            $updata['zutime']  = 0;
                       }

                       $updata['zx']      = 1;
                       $updata['lognum']  = $row->lognum+1;
                       $updata['logtime'] = time();
                       $updata['logip']   = getip();
                       $updata['logms']   = time();
                       $this->Csdb->get_update ('user',$row->id,$updata);

                       //登录日志
					   $agent = ($this->agent->is_mobile() ? $this->agent->mobile() :                    $this->agent->platform()).'&nbsp;/&nbsp;'.$this->agent->browser().' v'.$this->agent->version();
					   $add['uid']=$row->id;
					   $add['loginip']=getip();
					   $add['logintime']=time();
					   $add['useragent']=$agent;
					   $this->Csdb->get_insert('user_log',$add);

                       $_SESSION['cscms__id']    = $row->id;
                       $_SESSION['cscms__name']  = $row->name;
                       $_SESSION['cscms__login'] = md5($row->name.$row->pass);

                       //记住登录
                       $this->cookie->set_cookie("user_id",$row->id,time()+86400*30);
	                   $this->cookie->set_cookie("user_login",md5($row->name.$row->pass.$row->code),time()+86400*30);

                       unset($_SESSION['denglu__id'],$_SESSION['denglu__name'],$_SESSION['denglu__logo']);

                       msg_url(L('open_06'),$log_fhurl,'ok');  //登录成功
                   }
			 }else{
                   msg_url(L('open_07'),spacelink('login'),'ok');
			 }

		}else{  //不存在则绑定

	         $template=$this->load->view('open.html','',true);
		     $Mark_Text=str_replace("{cscms:title}",L('open_08')." - ".Web_Name,$template);
		     $Mark_Text=str_replace("[user:regsave]",spacelink('open/reg_save'),$Mark_Text);
		     $Mark_Text=str_replace("[user:loginsave]",spacelink('open/log_save'),$Mark_Text);
		     //判断验证码开关
		     $Mark_Text=str_replace("[user:codecheck]",User_Code_Mode,$Mark_Text);
		     //判断手机强制验证
		     $Mark_Text=str_replace("[user:telcheck]",User_Tel,$Mark_Text);
		     //token
		     $Mark_Text=str_replace("[user:token]",get_token('open_token'),$Mark_Text);
			 //昵称
		     $Mark_Text=str_replace("[user:nichen]",$_SESSION['denglu__name'],$Mark_Text);
			 //头像
		     $Mark_Text=str_replace("[user:logo]",$_SESSION['denglu__logo'],$Mark_Text);
             //用户名判断
		     $Mark_Text=str_replace("[user:nameajaxurl]",spacelink('reg/check').'?field=name',$Mark_Text);
             //邮件判断
		     $Mark_Text=str_replace("[user:emailajaxurl]",spacelink('reg/check').'?field=email',$Mark_Text);
             //手机判断
		     $Mark_Text=str_replace("[user:telajaxurl]",spacelink('reg/check').'?field=tel',$Mark_Text);
             $Mark_Text=$this->Csskins->template_parse($Mark_Text,true);
		     echo $Mark_Text;
		}
	}

    //绑定登录验证
	public function log_save(){
	    if(!isset($_SESSION['denglu__id'])) msg_url(L('open_09'),spacelink('login'));
		$token=$this->input->post('token', TRUE);
		if(!get_token('open_token',1,$token)) msg_url(L('open_16'),'javascript:history.back();');

        $username = $this->input->post('username', TRUE, TRUE);   //username or useremail
        $userpass = $this->input->post('userpass', TRUE, TRUE);   //userpass
	    $cookietime = intval($this->input->post('cookie')); //cookie保存时间
		if($cookietime==0) $cookietime=1;

        if(empty($username)){
			msg_url(L('open_10'),'javascript:history.back();');  //用户名为空
        }elseif(empty($userpass)){
			msg_url(L('open_11'),'javascript:history.back();');  //密码为空
		}else{

            //可以用会员名、邮箱来进行登入
            $sqlu="SELECT code,email,pass,sid,yid,id,name,lognum,cion,vip,logtime,viptime FROM ".CS_SqlPrefix."user where name='".$username."' or email='".$username."'";
	        $row=$this->db->query($sqlu)->row();
	        if(!$row){

                 //--------------------------- Ucenter ---------------------------
                 if(User_Uc_Mode==1){
                            include CSCMS.'sys/Cs_Ucenter.php';
                            include CSPATH.'uc_client/client.php';
                            $uid = uc_user_login($username,$userpass);
                            if(intval($uid[0]) > 0 ) {  //UC存在则新增会员

                                    $this->load->helper('string');
                                    $user['name']=$username;
                                    $user['code']=random_string('alnum', 6);
                                    $user['pass']=md5(md5($userpass).$user['code']);
                                    $user['email']=$uid[3];
                                    $user['uid']=$uid[0];
                                    $user['regip'] = getip();
                                    $user['qianm'] = '';
	                                if(User_Cion_Reg>0){
                                        $user['cion'] = User_Cion_Reg;
                                    }
									if(User_Uc_Fun==1){
                                         $user['yid']  = 2;
									}
                                    $user['zx']  = 1;
                                    $user['lognum']  = 1;
                                    $user['logtime'] = time();
                                    $user['logip'] = getip();
                                    $user['logms'] = time();
                                    $user['addtime'] = time();
                                    $res=$this->Csdb->get_insert('user',$user);
                                    if(intval($res) > 0 ) {

										 //修改第三方登录UID
										 $this->db->query("update ".CS_SqlPrefix."useroauth set uid=".$res." where id=".$_SESSION['denglu__id']."");
					                     //摧毁登录SESSION
					                     unset($_SESSION['denglu__id'],$_SESSION['denglu__name'],$_SESSION['denglu__logo']);

									     if(User_Uc_Fun==0){  //不需要激活

                                               //登录日志
    					                       $agent = ($this->agent->is_mobile() ? $this->agent->mobile() :                    $this->agent->platform()).'&nbsp;/&nbsp;'.$this->agent->browser().' v'.$this->agent->version();
						                       $add['uid']=$res;
						                       $add['loginip']=getip();
						                       $add['logintime']=time();
						                       $add['useragent']=$agent;
						                       $this->Csdb->get_insert('user_log',$add);

                                               $_SESSION['cscms__id']    = $res;
                                               $_SESSION['cscms__name']  = $username;
                                               $_SESSION['cscms__login'] = md5($username.$user['pass']);

                                               //记住登录
                                               $this->cookie->set_cookie("user_id",$res,time()+86400*$cookietime);
		                                       $this->cookie->set_cookie("user_login",md5($username.$user['pass'].$user['code']),time()+86400*$cookietime);

                                               msg_url(L('open_06'),userurl(site_url('user/space')),'ok');  //登录成功

										 }else{

			                                   $key=md5($res.$username.$user['pass'].'2');
                                               $Msgs['username'] = $username;
                                               $Msgs['url']      =         is_ssl().Web_Url.userurl(site_url('user/reg/verify'))."?key=".$key."&username=".$username;
                                               $title   = Web_Name.L('open_12');
                                               $content = getmsgto(User_RegEmailContent,$Msgs);
											   $this->load->model('Csemail');
                                               $this->Csemail->send($user['email'],$title,$content);

                                               msg_url(L('open_13',array($user['email'])),'javascript:history.back();','ok');  //需要激活
										 }
                                    }
							}
				}else{

                     msg_url('账号不存在','javascript:history.back();');  //账号不存在
				}
            }else{
				if($row->pass!=md5(md5(trim($userpass)).$row->code)){
					msg_url(L('open_14'),'javascript:history.back();');
				}elseif($row->sid==1){
					msg_url(L('open_15'),'javascript:history.back();');
				}elseif($row->yid==1){
					msg_url(L('open_03'),'javascript:history.back();');
				}elseif($row->yid==2){
					$key=md5($row->id.$username.$row->pass.$row->yid);
					$Msgs['username'] = $username;
					$Msgs['url']      = is_ssl().Web_Url.userurl(site_url('user/reg/verify'))."?key=".$key."&username=".$username;
					$title   = Web_Name.L('open_04');
					$content = getmsgto(User_RegEmailContent,$Msgs);
					$this->load->model('Csemail');
					$this->Csemail->send($row->email,$title,$content);
					msg_url(L('open_05'),'javascript:history.back();');
				}else{

				   //修改第三方登录UID
				   $this->db->query("update ".CS_SqlPrefix."useroauth set uid=".$row->id." where id=".$_SESSION['denglu__id']."");
				   //摧毁登录SESSION
				   unset($_SESSION['denglu__id'],$_SESSION['denglu__name'],$_SESSION['denglu__logo']);


				   //每天登陆加积分
				   if(User_Cion_Log>0 && date("Y-m-d",$row->logtime)!=date('Y-m-d')){
				       $updata['cion']  = $row->cion+User_Cion_Log;
				   }

				   //判断VIP
				   IF($row->vip>0 && $viptime<time()){
				        $updata['vip']  = 0;
				        $updata['viptime']  = 0;
				   }

				   $updata['zx']      = 1;
				   $updata['lognum']  = $row->lognum+1;
				   $updata['logtime'] = time();
				   $updata['logip']   = getip();
				   $updata['logms']   = time();
				   $this->Csdb->get_update ('user',$row->id,$updata);

				   //登录日志
				   $agent = ($this->agent->is_mobile() ? $this->agent->mobile() :                    $this->agent->platform()).'&nbsp;/&nbsp;'.$this->agent->browser().' v'.$this->agent->version();
				   $add['uid']=$row->id;
				   $add['loginip']=getip();
				   $add['logintime']=time();
				   $add['useragent']=$agent;
				   $this->Csdb->get_insert('user_log',$add);

				   $_SESSION['cscms__id']    = $row->id;
				   $_SESSION['cscms__name']  = $row->name;
				   $_SESSION['cscms__login'] = md5($row->name.$row->pass);

				   //记住登录
				   $this->cookie->set_cookie("user_id",$row->id,time()+86400*$cookietime);
				   $this->cookie->set_cookie("user_login",md5($row->name.$row->pass.$row->code),time()+86400*$cookietime);

				   msg_url(L('open_06'),userurl(site_url('user/space')),'ok');  //登录成功
				}
			}
		}
	}

    //新注册绑定
	public function reg_save(){
		//注册开关
		if(User_Reg==0) msg_url(L('reg_35'),Web_Path);
		if(!isset($_SESSION['denglu__id'])) msg_url(L('open_09'),spacelink('login'));
		$token=$this->input->post('token', TRUE);
		if(!get_token('open_token',1,$token)) msg_url(L('open_16'),'javascript:history.back();');

		$userpass2=$this->input->post('repassword', TRUE, TRUE);

		$userinfo['code']=random_string('alnum',6);
		$userinfo['name']=$this->input->post('username', TRUE, TRUE);
		$userinfo['pass']=$this->input->post('userpass', TRUE, TRUE);
		$userinfo['nichen']=$this->input->post('usernichen', TRUE, TRUE);
		if(empty($userinfo['nichen'])) $userinfo['nichen']=$_SESSION['denglu__name'];
		$userinfo['logo']=$_SESSION['denglu__logo'];
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
		if($userinfo['pass']!=$userpass2) msg_url(L('reg_34'),'javascript:history.back();');
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
		$userinfo['skins']= '';
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
			include CSCMS.'sys/Cs_Ucenter.php';
			include CSPATH.'uc_client/client.php';
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

		//修改第三方登录UID
		$this->db->query("update ".CS_SqlPrefix."useroauth set uid=".$regid." where id=".$_SESSION['denglu__id']."");

		//摧毁token
		get_token('open_token',2);
		unset($_SESSION['denglu__id']);

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
}
