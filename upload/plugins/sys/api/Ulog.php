<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-04-27
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Ulog extends Cscms_Controller {

	function __construct(){
		parent::__construct();
		$this->load->library('user_agent');
		//关闭数据库缓存
		$this->db->cache_off();
	}

    //会员登录状态
	public function log($ac='',$user='')
	{
		$this->load->model('Csuser');
		$callback=$this->input->get('callback',true);	
		//判断会员是否关闭
		if(User_Mode==0){
			getjson(array('str'=>''),0,1,$callback);
		}
		$ucid='logout';
		$login=$this->Csuser->User_Login(1);
		$template=(!$login)?'ulogin.html':'uinfo.html';
		$skins = '';
		if($ac=='user'){
		    if(!defined('USERPATH')) define('USERPATH', true);
		}elseif($ac=='home'){
			if(!defined('HOMEPATH')) define('HOMEPATH', true);
			$skins = (Home_Fs==1)?getzd('user','skins',$user,'name'):getzd('user','skins',$user);
		}else{
			define('OPT_DIR',$ac);
		}
		$this->load->get_templates('',$skins);
		$Mark_Text=$this->load->view($template,'',true);
		$Mark_Text=str_replace("{cscms:logadd}","cscms.loginAdd();",$Mark_Text);
		$Mark_Text=str_replace("{cscms:logout}","cscms.logOut();",$Mark_Text);
		if(defined('HOMEPATH')){
		   $Mark_Text=$this->Csskins->cscms_common($Mark_Text,$skins);
		}
		if($login){
		    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
			if(empty($row['nichen'])) $row['nichen']=$row['name'];
			$Mark_Text=$this->Csskins->cscms_skins('user',$Mark_Text,$Mark_Text,$row);
			$ucid=$row['uid'];
		}
		$Mark_Text=$this->Csskins->template_parse($Mark_Text,false);
		//同步UC，解决高速浏览器不兼容
		if(User_Uc_Mode==1){
		   $Mark_Text.="<iframe marginwidth=\"0\" marginheight=\"0\" src=\"".site_url('api/ulog/uclog')."?uid=".$ucid."\" frameborder=\"0\" width=\"1\" scrolling=\"no\" height=\"1\" leftmargin=\"0\" topmargin=\"0\"></iframe>";
		}
		getjson(array('str'=>$Mark_Text),0,1,$callback);
	}

	//UC同步登录
	public function uclog()
	{
		  if(User_Mode==0) die(User_No_info);
		  if(User_Uc_Mode==0) die('Uc Close');
          $uid = $this->input->get_post('uid',TRUE);
	      include CSCMS.'sys/Cs_Ucenter.php';
          include CSPATH.'uc_client/client.php';
		  if($uid=='logout'){
			   echo uc_user_synlogout();
	      }elseif((int)$uid > 0) {
			   echo uc_user_synlogin($uid);
		  }
	}

    //提交登录
	public function login()
	{
          //当sessions使用文件存储时，每天清理一次sessions文件夹
          if(CS_Session_Is==1){
	          $day=@file_get_contents(FCPATH."cache/sessions/day.txt");
	          if($day!=date('Y-m-d')){
                   $dh=opendir(FCPATH."cache/sessions/");
                   while ($file=readdir($dh)) {
                      if($file!="." && $file!="..") {
                         $fullpath=FCPATH."cache/sessions/".$file;
                         @unlink($fullpath);
                      }
                   }
                   closedir($dh);
	               @file_put_contents(FCPATH."cache/sessions/day.txt",date('Y-m-d'));
	          }
		  }
		  if(User_Mode==0) die(User_No_info);
          $username = $this->input->get('username', TRUE, TRUE);   //username or useremail
          $userpass = $this->input->get('userpass', TRUE, TRUE);   //userpass
		  $callback = $this->input->get('callback',true);
		  $cookietime = intval($this->input->get('cookie')); //cookie保存时间
	      if($cookietime==0) $cookietime=1;

            if(empty($username)){
				$error=L('p_06');  //用户名为空
            }elseif(empty($userpass)){
				$error=L('p_07');  //密码为空
			}else{

                //可以用会员名、邮箱来进行登入
                $sqlu="SELECT code,email,pass,sid,yid,id,name,lognum,cion,vip,logtime,viptime,zid,zutime FROM ".CS_SqlPrefix."user where name='".$username."' or email='".$username."'";
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

                                                   $error='ok'; //登入成功

											 }else{

				                                   $key=md5($res.$username.$user['pass'].$user['yid']);
                                                   $Msgs['username'] = $username;
                                                   $Msgs['url']      = is_ssl().Web_Url.userurl(site_url('user/reg/verify'))."?key=".$key."&username=".$username;
                                                   $title   = Web_Name.L('p_08');
                                                   $content = getmsgto(User_RegEmailContent,$Msgs);
												   $this->load->model('Csemail');
                                                   $this->Csemail->send($user['email'],$title,$content);
                                                   $error=L('p_09');  //需要激活
											 }
                                        }
								}else{
                                    $error=L('p_10');  //帐号不存在
								}
					}else{

                         $error=L('p_10');  //帐号不存在
					}
                }else{
                           if($row->pass!=md5(md5(trim($userpass)).$row->code)){
                                   $error=L('p_11'); //密码错误
                           }elseif($row->sid==1){
                                   $error=L('p_12'); //帐号被锁定
                           }elseif($row->yid==1){
                                   $error=L('p_13'); //站长未审核
                           }elseif($row->yid==2){
				                   $key=md5($row->id.$username.$row->pass.$row->yid);
                                   $Msgs['username'] = $username;
                                   $Msgs['url']      = is_ssl().Web_Url.userurl(site_url('user/reg/verify'))."?key=".$key."&username=".$username;
                                   $title   = Web_Name.L('p_08');
                                   $content = getmsgto(User_RegEmailContent,$Msgs);
								   $this->load->model('Csemail');
                                   $this->Csemail->send($row->email,$title,$content);
                                   $error=L('p_09'); //邮件未激活
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
	                           $this->cookie->set_cookie("user_id",$row->id,time()+86400*$cookietime);
			                   $this->cookie->set_cookie("user_login",md5($row->name.$row->pass.$row->code),time()+86400*$cookietime);

                               $error='ok'; //登入成功
                           }
				}
			}
			getjson($error,0,1,$callback);
	}

    //退出登录
	public function logout()
	{
		    $callback = $this->input->get('callback',true);
            //删除在线状态
            $updata['zx']=0;
			if(isset($_SESSION['cscms__id'])){
                $this->Csdb->get_update('user',$_SESSION['cscms__id'],$updata);
				$this->Csdb->get_del('session',$_SESSION['cscms__id'],'uid');
			}

            unset($_SESSION['cscms__id'],$_SESSION['cscms__name'],$_SESSION['cscms__login']);

            //清除记住登录
	        $this->cookie->set_cookie("user_id");
			$this->cookie->set_cookie("user_login");
			getjson('ok',0,1,$callback);
	}
}
