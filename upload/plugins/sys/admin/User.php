<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-19
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class User extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
		$this->lang->load('admin_user');
	}

    //会员列表
	public function index(){
        $kstime = $this->input->get_post('kstime',true);
        $jstime = $this->input->get_post('jstime',true);
        $sid  = intval($this->input->get_post('sid'));
        $zid  = intval($this->input->get_post('zid'));
        $zd   = $this->input->get_post('zd',true);
        $key  = $this->input->get_post('key',true);
	    $page = intval($this->input->get('page'));
        if($page==0) $page=1;
		$kstimes=empty($kstime)?0:strtotime($kstime)-86400;
		$jstimes=empty($jstime)?0:strtotime($jstime)+86400;
		if($kstimes>$jstimes) $kstimes=strtotime($kstime);

        $data['page'] = $page;
        $data['sort'] = $sort;
        $data['desc'] = $desc;
        $data['sid'] = $sid;
        $data['zid'] = $zid;
        $data['zd'] = $zd;
        $data['key'] = $key;
        $data['kstime'] = $kstime;
        $data['jstime'] = $jstime;

        $sql_string = "SELECT id,name,logo,email,vip,zid,sid,tid,rzid,regip,addtime,lognum,nichen,rmb,cion FROM ".CS_SqlPrefix."user where yid=0";
		if($zid>0){
			 $sql_string.= " and zid=".$zid."";
		}
		if($sid>0){
             $sql_string.= " and sid=".($sid-1)."";
		}
		if(!empty($key)){
			 $sql_string.= " and ".$zd."='".$key."'";
		}
		if($kstimes>0){
             $sql_string.= " and addtime>".$kstimes."";
		}
		if($jstimes>0){
             $sql_string.= " and addtime<".$jstimes."";
		}
        $sql_string.= " order by id desc";
        $count_sql = str_replace('id,name,logo,email,vip,zid,sid,tid,rzid,regip,addtime,lognum,nichen,rmb,cion','count(*) as count',$sql_string);
        $query = $this->db->query($count_sql)->result_array();
        $total = $query[0]['count'];

        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        $totalPages = ($totalPages>0)?$totalPages:1;
        $page = ($page>$totalPages)?$totalPages:$page;
        $data['nums'] = $total;
        if($total<$per_page){
              $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);
        //echo $this->db->last_query();exit;

        $base_url = site_url('user')."?zid=".$zid."&zd=".$zd."&kstime=".$kstime."&jstime=".$jstime."&key=".$key."&sid=".$sid."&sort=".$sort."&desc=".$desc."&page=";
        $data['user'] = $query->result();
        $data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('user.html',$data);
	}

    //待审核会员列表
	public function verify(){
	    $page = intval($this->input->get('page'));
        if($page==0) $page=1;
        $data['page'] = $page;

        $sql_string = "SELECT id,name,email,regip,addtime FROM ".CS_SqlPrefix."user where yid>0  order by id desc";
        $query = $this->db->query($sql_string); 
        $total = $query->num_rows();
        $base_url = site_url('user/verify');
        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        $data['nums'] = $total;
        if($total<$per_page){
              $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);

        $base_url = site_url('user/verify')."?page=";
        $data['user'] = $query->result();
        $data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('user_verify.html',$data);
	}
	public function logo(){
		$id = (int)$this->input->get_post('id');
		if($id==0) exit(L('plub_01'));//参数错误
		$sql = "SELECT id,logo FROM ".CS_SqlPrefix."user where id=".$id;
		$user = $this->db->query($sql)->row();
		if($user){
			$data['logo'] = piclink('logo',$user->logo);
			$data['del'] = site_url('user/logo_del').'?id='.$id;
			$data['id'] = $id;
			$this->load->view('user_logo.html',$data);
		}else{
			exit(L('plub_02'));//暂无头像
		}
	}
	public function logo_del(){
		$id = (int)$this->input->get_post('id');
		if($id==0) getjson(L('plub_01'));//参数错误
		$upd = $this->db->update('user',array('logo'=>''),array('id'=>$id));
		if($upd){
			getjson('',0);
		}else{
			getjson(L('plub_03'));//数据异常，请稍后重试
		}
	}

    //会员组列表
	public function zu(){
	    $page = intval($this->input->get('page'));
        if($page==0) $page=1;
        $data['page'] = $page;

        $sql_string = "SELECT * FROM ".CS_SqlPrefix."userzu  order by xid asc";
        $query = $this->db->query($sql_string); 
        $total = $query->num_rows();
        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        $totalPages = ($totalPages>0)?$totalPages:1;
        $page = ($page>$totalPages)?$totalPages:$page;
        $data['nums'] = $total;
        if($total<$per_page){
            $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);

        $data['userzu'] = $query->result();
        $base_url = site_url('user/zu').'?page='.$page;
        $data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('user_zu.html',$data);
	}

    //会员等级列表
	public function level(){
	    $page = intval($this->input->get('page'));
        if($page==0) $page=1;
        $data['page'] = $page;

        $sql_string = "SELECT * FROM ".CS_SqlPrefix."userlevel  order by stars asc";
        $query = $this->db->query($sql_string); 
        $total = $query->num_rows();
        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        $totalPages = ($totalPages>1)?$totalPages:1;
        $page = ($page>$totalPages)?$totalPages:$page;
        $data['nums'] = $total;
        if($total<$per_page){
        	$per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);

        $data['userlevel'] = $query->result();
        $base_url = site_url('user/level').'?page=';
        $data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('user_level.html',$data);
	}

    //会员审核操作
	public function verify_save(){
        $id = intval($this->input->get('id'));
        $op = $this->input->get('op',true);
        $row=$this->db->query("SELECT name,email FROM ".CS_SqlPrefix."user where id=".$id."")->row();
		if($op=='ok'){ //通过
			$msg1 = L('plub_04');//已通过
			$edit['yid']=0;
            $this->Csdb->get_update('user',$id,$edit);
            $tempname[0] = $row->name;
			$emailneir=L('plub_05',$tempname);//尊敬的会员'..'，您好~！<br>您在'.Web_Name.'注册的账号已经通过审核，<br>感谢您对本站的支持~!
		}else{  //拒绝
			$msg1 = L('plub_06');//已拒绝
            $this->Csdb->get_del('user',$id);
            $tempname[0] = $row->name;
			$emailneir = L('plub_07',$tempname);//尊敬的会员'.$row->name.',您好~！<br>您在'.Web_Name.'注册的账号未能通过审核，<br>如有疑问请联系站长QQ：'.Admin_QQ.'<br>感谢您对本站的支持~!
		}
	    $this->load->model('Csemail');
		$title = L('plub_08');//账号审核邮件
		$res=$this->Csemail->send($row->email,$title,$emailneir);  //发送通知邮件
		$msg=($res)?$msg1.L('plub_09'):$msg1.L('plub_10');
		$info['msg'] = $msg;
		$info['url'] = site_url('user/verify').'?v='.rand(1000,9999);
		getjson($info,0);
	}

    //会员配置
	public function setting(){
        $this->load->view('user_setting.html');
	}

    //保存配置
	public function setting_save(){
	    $User_Mode = intval($this->input->post('User_Mode', TRUE));
	    $User_No_info = $this->input->post('User_No_info', TRUE, TRUE);
	    $User_Ym = $this->input->post('User_Ym', TRUE, TRUE);
	    $User_Code_Mode = intval($this->input->post('User_Code_Mode', TRUE));
	    $User_Logo = intval($this->input->post('User_Logo', TRUE));
	    $User_Tel = intval($this->input->post('User_Tel', TRUE));
	    $User_BookFun = intval($this->input->post('User_BookFun', TRUE));
	    $User_YkDown = intval($this->input->post('User_YkDown', TRUE));
	    $User_Uc_Mode = intval($this->input->post('User_Uc_Mode', TRUE));
	    $User_Uc_Fun = intval($this->input->post('User_Uc_Fun', TRUE));
	    $User_Downtime = intval($this->input->post('User_Downtime', TRUE));
	    $User_DownFun = intval($this->input->post('User_DownFun', TRUE));
	    $User_Downcion = intval($this->input->post('User_Downcion', TRUE));
	    $User_Reg = intval($this->input->post('User_Reg'));
	    $User_RegZw = intval($this->input->post('User_RegZw'));
	    $User_Regxy = $this->input->post('User_Regxy');
	    $User_Reg_Name = $this->input->post('User_Reg_Name', TRUE,true);
	    $User_RegMsgFun = intval($this->input->post('User_RegMsgFun', TRUE));
	    $User_RegIP = intval($this->input->post('User_RegIP', TRUE));
	    $User_RegFun = intval($this->input->post('User_RegFun', TRUE));
	    $User_RegEmailFun = intval($this->input->post('User_RegEmailFun', TRUE));
	    $User_RegEmailContent = $this->input->post('User_RegEmailContent');
	    $User_RegMsgContent = $this->input->post('User_RegMsgContent');
	    $User_PassContent = $this->input->post('User_PassContent');
	    $User_Dtts = intval($this->input->post('User_Dtts'));
	    $User_Fkts = intval($this->input->post('User_Fkts'));
	    $User_Hyts = intval($this->input->post('User_Hyts'));
	    $User_Fsts = intval($this->input->post('User_Fsts'));
	    $User_Ssts = intval($this->input->post('User_Ssts'));
	    $User_RmbToCion = intval($this->input->post('User_RmbToCion'));
	    $User_Cion_Reg = intval($this->input->post('User_Cion_Reg'));
	    $User_Cion_Log = intval($this->input->post('User_Cion_Log'));
	    $User_Cion_Qd = intval($this->input->post('User_Cion_Qd'));
	    $User_Cion_Logo = intval($this->input->post('User_Cion_Logo'));
	    $User_Cion_Add = intval($this->input->post('User_Cion_Add'));
	    $User_Cion_Zx = intval($this->input->post('User_Cion_Zx'));
	    $User_Cion_Del = intval($this->input->post('User_Cion_Del'));
	    $User_Jinyan_Reg = intval($this->input->post('User_Jinyan_Reg'));
	    $User_Jinyan_Log = intval($this->input->post('User_Jinyan_Log'));
	    $User_Jinyan_Qd = intval($this->input->post('User_Jinyan_Qd'));
	    $User_Jinyan_Logo = intval($this->input->post('User_Jinyan_Logo'));
	    $User_Jinyan_Add = intval($this->input->post('User_Jinyan_Add'));
	    $User_Jinyan_Zx = intval($this->input->post('User_Jinyan_Zx'));
	    $User_Jinyan_Del = intval($this->input->post('User_Jinyan_Del'));
	    $User_Jinyan_Share = intval($this->input->post('User_Jinyan_Share'));
	    $User_Cion_Share = intval($this->input->post('User_Cion_Share'));
	    $User_Nums_Share = intval($this->input->post('User_Nums_Share'));
	    $User_Nums_Add = intval($this->input->post('User_Nums_Add'));
	    $User_Skins = $this->input->post('User_Skins',true,true);

        if($User_RmbToCion==0)     $User_RmbToCion=1;

        //HTML转码
        $User_Regxy= str_encode($User_Regxy); 
        $User_RegEmailContent= str_encode($User_RegEmailContent); 
        $User_RegMsgContent= str_encode($User_RegMsgContent); 
        $User_PassContent= str_encode($User_PassContent); 

		//判断开启二级域名
		global $_CS_Domain;
		if(!empty($User_Ym)){
            $_CS_Domain['user']=$User_Ym;
            arr_file_edit($_CS_Domain);
		}else{
	       	if(arr_key_value($_CS_Domain,'user')){
                unset($_CS_Domain['user']);
                arr_file_edit($_CS_Domain);
		   	}
		}

        //开启UC整合
        if($User_Uc_Mode==1){
            include CSCMS.'sys/Cs_Ucenter.php';
            $UC_DBHOST=$this->input->post('UC_DBHOST',true);
            $UC_DBUSER=$this->input->post('UC_DBUSER',true);
            $UC_DBPW=$this->input->post('UC_DBPW',true);
            $UC_DBNAME=$this->input->post('UC_DBNAME',true);
            $UC_DBTABLEPRE=$this->input->post('UC_DBTABLEPRE',true);
            $UC_KEY=$this->input->post('UC_KEY',true);
            $UC_API=$this->input->post('UC_API',true);
            $UC_APPID=intval($this->input->post('UC_APPID'));
            if(substr(UC_DBPW,0,1)."********".substr(UC_DBPW,-1)==$UC_DBPW){
                $UC_DBPW=UC_DBPW;
            }
            $UC_DBTABLEPRE="`".$UC_DBNAME."`.".$UC_DBTABLEPRE."";

            if(empty($UC_DBHOST) || empty($UC_DBUSER) || empty($UC_DBPW) || empty($UC_DBNAME) || empty($UC_KEY) || empty($UC_API) || empty($UC_APPID)) getjson(L('plub_11'));//抱歉，UC配置不完整！
            $strsuc="<?php"."\r\n";
            $strsuc.="define('UC_CONNECT', 'mysql');\r\n";
            $strsuc.="define('UC_DBHOST', '".$UC_DBHOST."');\r\n";
            $strsuc.="define('UC_DBUSER', '".$UC_DBUSER."');\r\n";
            $strsuc.="define('UC_DBPW', '".$UC_DBPW."');\r\n";
            $strsuc.="define('UC_DBNAME', '".$UC_DBNAME."');\r\n";
            $strsuc.="define('UC_DBCHARSET', 'utf8');\r\n";
            $strsuc.="define('UC_DBTABLEPRE', '".$UC_DBTABLEPRE."');\r\n";
            $strsuc.="define('UC_KEY', '".$UC_KEY."');\r\n";
            $strsuc.="define('UC_API', '".$UC_API."');\r\n";
            $strsuc.="define('UC_CHARSET', 'utf-8');\r\n";
            $strsuc.="define('UC_IP', '');\r\n";
            $strsuc.="define('UC_APPID', ".$UC_APPID.");";
            if (!write_file(CSCMS.'sys/Cs_Ucenter.php', $strsuc)){
                getjson(L('plub_12'));//操作失败，./cscms/sys/Cs_Ucenter.php文件没有写入权限！
			}
		}

        $strs="<?php"."\r\n";
        $strs.="define('User_Mode',".$User_Mode.");      //会员开关  \r\n";
        $strs.="define('User_No_info','".$User_No_info."'); //会员关闭提示\r\n";
        $strs.="define('User_Ym','".$User_Ym."');      //会员板块绑定域名 \r\n";
        $strs.="define('User_Code_Mode',".$User_Code_Mode."); //会员验证码开关  \r\n";
        $strs.="define('User_Logo',".$User_Logo.");      //强制头像开关  \r\n";
        $strs.="define('User_Tel',".$User_Tel.");      //手机强制验证\r\n";
        $strs.="define('User_BookFun',".$User_BookFun.");      //网站留言开关  \r\n";
        $strs.="define('User_YkDown',".$User_YkDown.");      //游客下载开关  \r\n";
        $strs.="define('User_Uc_Mode',".$User_Uc_Mode.");      //UC整合开关 \r\n";
        $strs.="define('User_Uc_Fun',".$User_Uc_Fun.");        //UC整合会员是否需要激活 \r\n";
        $strs.="define('User_Downtime',".$User_Downtime.");    //重复扣币间隔小时  \r\n";
        $strs.="define('User_DownFun',".$User_DownFun.");      //分成比列开关  \r\n";
        $strs.="define('User_Downcion',".$User_Downcion.");     //默认分成比列数量  \r\n";
        $strs.="define('User_Reg',".$User_Reg.");      //会员注册开关  \r\n";
        $strs.="define('User_RegZw',".$User_RegZw.");      //用户名中文开关  \r\n";
        $strs.="define('User_Regxy','".$User_Regxy."');      //会员注册协议  \r\n";
        $strs.="define('User_Reg_Name','".$User_Reg_Name."');  //禁用用户名/昵称 \r\n";
        $strs.="define('User_RegMsgFun',".$User_RegMsgFun.");      //发送欢迎信息\r\n";
        $strs.="define('User_RegIP',".$User_RegIP.");      //同一IP注册限制小时  \r\n";
        $strs.="define('User_RegFun',".$User_RegFun.");      //新用户注册人工审核,1需要审核  \r\n";
        $strs.="define('User_RegEmailFun',".$User_RegEmailFun.");      //新用户邮件激活,1需要激活  \r\n"; 
        $strs.="define('User_RegEmailContent','".$User_RegEmailContent."'); //注册激活邮件内容\r\n";
        $strs.="define('User_RegMsgContent','".$User_RegMsgContent."'); //欢迎邮件内容不够\r\n";
        $strs.="define('User_PassContent','".$User_PassContent."');  //密码找回邮件内容\r\n";
        $strs.="define('User_Dtts',".$User_Dtts.");      //动态保留数，0为全部保留\r\n";   
        $strs.="define('User_Fkts',".$User_Fkts.");      //访客保留数，0为全部保留 \r\n";  
        $strs.="define('User_Hyts',".$User_Hyts.");      //好友保留数，0为全部保留  \r\n"; 
        $strs.="define('User_Fsts',".$User_Fsts.");      //粉丝保留数，0为全部保留   \r\n";
        $strs.="define('User_Ssts',".$User_Ssts.");      //说说保留数，0为全部保留   \r\n";
        $strs.="define('User_RmbToCion',".$User_RmbToCion."); //默认金币比例  \r\n";
        $strs.="define('User_Cion_Reg',".$User_Cion_Reg.");      //注册赠送金币  \r\n";
        $strs.="define('User_Cion_Log',".$User_Cion_Log.");      //登入赠送金币  \r\n";
        $strs.="define('User_Cion_Qd',".$User_Cion_Qd.");      //签到赠送金币\r\n";
        $strs.="define('User_Cion_Logo',".$User_Cion_Logo.");      //上传头像赠送金币\r\n";
        $strs.="define('User_Cion_Add',".$User_Cion_Add.");      //发表数据赠送金币\r\n";
        $strs.="define('User_Cion_Zx',".$User_Cion_Zx.");      //在线1小时赠送金币\r\n";
        $strs.="define('User_Cion_Del',".$User_Cion_Del.");      //数据删除扣除金币\r\n";
        $strs.="define('User_Jinyan_Reg',".$User_Jinyan_Reg.");      //注册赠送经验\r\n";
        $strs.="define('User_Jinyan_Log',".$User_Jinyan_Log.");      //登入赠送经验\r\n";
        $strs.="define('User_Jinyan_Qd',".$User_Jinyan_Qd.");      //签到赠送经验\r\n";
        $strs.="define('User_Jinyan_Logo',".$User_Jinyan_Logo.");      //上传头像赠送经验\r\n";
        $strs.="define('User_Jinyan_Add',".$User_Jinyan_Add.");      //发表数据赠送经验\r\n";
        $strs.="define('User_Jinyan_Zx',".$User_Jinyan_Zx.");      //在线1小时赠送经验\r\n";
        $strs.="define('User_Jinyan_Del',".$User_Jinyan_Del.");      //数据删除扣除经验\r\n";
        $strs.="define('User_Cion_Share',".$User_Cion_Share.");      //每次分享奖励金币\r\n";
        $strs.="define('User_Jinyan_Share',".$User_Jinyan_Share.");      //每次分享奖励经验\r\n";
        $strs.="define('User_Nums_Share',".$User_Nums_Share.");      //每天分享奖励次数\r\n";
        $strs.="define('User_Nums_Add',".$User_Nums_Add.");      //每天发表数据奖励次数\r\n";
        $strs.="define('User_Skins','".$User_Skins."');      //会员默认模板路径";

        //写文件
        if (!write_file(CSCMS.'sys/Cs_User.php', $strs)){
            getjson(L('plub_13'));//文件没有修改权限
        }else{
            $info['url'] = site_url('user/setting').'?v='.rand(1000,9999);
            $info['msg'] = L('plub_14');//恭喜你，修改成功
            getjson($info,0);
        }
	}
	public function init($ac){
		$id = intval($this->input->get_post('id'));
		$sign = intval($this->input->get_post('sign'));
		if($id==0 || $ac==''){
			getjson(L('plub_01'));//参数错误
		}
		if($ac=='zt'){
			$edit[$ac] = $sign;
 		}else{
 			if($sign == 1){
				$edit[$ac] = 0;
			}else{
				$edit[$ac] = 1;
			}
 		}
		$this->Csdb->get_update('user',$id,$edit);
		getjson('',0);
	}

    //登录日志
	public function log(){
        $id=intval($this->input->get_post('id'));
        $sort = $this->input->get_post('sort',true);
        $desc = $this->input->get_post('desc',true);
		if(empty($sort)) $sort="id";
		if(empty($desc)) $desc="desc";

	    //删除三个月以前的登录日志记录
		$times=time()-86400*90;
		$this->db->query("delete from ".CS_SqlPrefix."user_log where logintime<".$times."");
        
        $this->load->library('ip');
	        $page = intval($this->input->get('page'));
        if($page==0) $page=1;

        if($id>0){
             $sql_string = "SELECT * FROM ".CS_SqlPrefix."user_log where uid=".$id." order by ".$sort." ".$desc;
		}else{
             $sql_string = "SELECT * FROM ".CS_SqlPrefix."user_log order by ".$sort." ".$desc;
		}
        $query = $this->db->query($sql_string); 
        $total = $query->num_rows();

        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        $totalPages = ($totalPages>0)?$totalPages:1;
        $page = ($page>$totalPages)?$totalPages:$page;
        $data['nums'] = $total;
        if($total<$per_page){
              $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);

        $data['log'] = $query->result();
        $base_url = site_url('user/log')."?id=".$id."&sort=".$sort."&desc=".$desc."&page=";
        $data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类

        $this->load->view('user_log.html',$data);
	}

    //会员新增、修改
	public function edit(){
        $id   = intval($this->input->get('id'));
		if($id == 0){
            $data['id'] = 0;
            $data['zid'] = 0;
            $data['tid'] = 0;
            $data['sid'] = 0;
            $data['rzid'] = 0;
            $data['yid'] = 0;
            $data['uid'] = '';
            $data['name'] = '';
            $data['pass'] = '';
            $data['logo'] = '';
            $data['qq'] = '';
            $data['tel'] = '';
            $data['email'] = '';
            $data['nichen'] = '';
            $data['sex']=0;
            $data['cion']=0;
            $data['rmb']=0.00;
            $data['vip']=0;
            $data['viptime'] = time();
            $data['zutime'] = time();
            $data['qianm']='';
            $data['qdts']=0;
            $data['qdtime']=0;
            $data['level']=0;
            $data['jinyan']=0;
            $data['hits']=0;
            $data['yhits']=0;
            $data['zhits']=0;
            $data['rhits']=0;
            $data['zanhits']=0;
            $data['skins']='';
			$this->load->helper('string');
            $data['code'] = random_string('alnum', 6);
            $data['nav_name'] = L('plub_15');//添加
		}else{
            $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix."user where id=".$id."")->row(); 
		    if(!$row) exit(L('plub_16'));//记录不存在
            $data['id']=$row->id;
            $data['zid']=$row->zid;
            $data['tid']=$row->tid;
            $data['sid']=$row->sid;
            $data['rzid']=$row->rzid;
            $data['yid']=$row->yid;
            $data['uid']=$row->uid;
            $data['name']=$row->name;
            $data['pass']=$row->pass;
            $data['logo']=$row->logo;
            $data['qq']=$row->qq;
            $data['tel']=$row->tel;
            $data['email']=$row->email;
            $data['nichen']=$row->nichen;
            $data['sex']=$row->sex;
            $data['cion']=$row->cion;
            $data['rmb']=$row->rmb;
            $data['vip']=$row->vip;
            $data['viptime']=($row->viptime==0)?time():$row->viptime;
            $data['zutime']=($row->zutime==0)?time():$row->zutime;
            $data['qianm']=$row->qianm;
            $data['qdts']=$row->qdts;
            $data['code']=$row->code;
            $data['qdtime']=$row->qdtime;
            $data['level']=$row->level;
            $data['jinyan']=$row->jinyan;
            $data['hits']=$row->hits;
            $data['yhits']=$row->yhits;
            $data['zhits']=$row->zhits;
            $data['rhits']=$row->rhits;
            $data['zanhits']=$row->zanhits;
            $data['skins']=$row->skins;
            $data['nav_name'] = L('plub_17');//修改
            $data['row'] = $row;
		}

        //获取会员空间所有模板
		$this->load->helper('directory');
        $path = VIEWPATH.'pc'.FGF.'home'.FGF;
        $dir_arr = directory_map($path, 1);
        $dirs = array();
	    if ($dir_arr) {
		    foreach ($dir_arr as $t) {
			    if (is_dir($path.$t)) {
					$confiles=$path.$t.FGF.'config.php';
					if (file_exists($confiles)){
						$config=require_once($confiles);
				        $dirs[] = array(
					        'name' => $config['name'],
					        'path' => $config['path'],
				        );
					}
			    }
		    }
	    }
        $data['skin'] = $dirs;
        $this->load->view('user_edit.html',$data);
	}

    //会员保存
	public function save(){
        $id  = intval($this->input->post('id'));
        $data['zid']=intval($this->input->post('zid'));
        $data['tid']=intval($this->input->post('tid'));
        $data['sid']=intval($this->input->post('sid'));
        $data['rzid']=intval($this->input->post('rzid'));
        $data['yid']=intval($this->input->post('yid'))?0:1;
        $data['name']=$this->input->post('name',true);
        $data['code']=$this->input->post('code',true);
        $data['qq']=$this->input->post('qq',true);
        $data['tel']=$this->input->post('tel',true);
        $data['email']=$this->input->post('email',true);
        $data['nichen']=$this->input->post('nichen',true);
        $data['sex']=intval($this->input->post('sex'));
        $data['cion']=intval($this->input->post('cion'));
        $data['rmb']=$this->input->post('rmb',true);
        $data['vip']=intval($this->input->post('vip'));
        $data['viptime']=strtotime($this->input->post('viptime'));
        $data['zutime']=strtotime($this->input->post('zutime'));
        $data['qianm']=$this->input->post('qianm',true);
        $data['qdts']=intval($this->input->post('qdts'));
        $data['level']=intval($this->input->post('level'));
        $data['jinyan']=intval($this->input->post('jinyan'));
        $data['hits']=intval($this->input->post('hits'));
        $data['yhits']=intval($this->input->post('yhits'));
        $data['zhits']=intval($this->input->post('zhits'));
        $data['rhits']=intval($this->input->post('rhits'));
        $data['zanhits']=intval($this->input->post('zanhits'));
        $data['skins']=$this->input->post('skins',true);
		//修改密码
		$pass=$this->input->post('pass',true);
        if(!empty($pass)){
            $data['pass']=md5(md5($pass).$data['code']);
		}
		//删除头像
		$logo=$this->input->post('logo',true);
        if($logo=='ok'){
            $data['logo']='';
		}
		if($data['vip']==0) $data['viptime']=0;
		if($data['zid']==1) $data['zutime']=0;

		if(empty($data['name'])) getjson(L('plub_18'));//用户名不能留空
		if($id==0 && empty($data['pass'])) getjson(L('plub_19'));//创建用户时，密码不能留空
		if(empty($data['email'])) getjson(L('plub_20'));//邮箱用于找回密码，不能留空

		if($id==0){ //新增
			 $data['addtime']=time();
             $row=$this->db->query("SELECT id FROM ".CS_SqlPrefix."user where name='".$data['name']."'")->row(); 
		     if($row) getjson(L('plub_21'));//该用户已存在，请重新填写
             $this->Csdb->get_insert('user',$data);
		}else{
             $this->Csdb->get_update('user',$id,$data);
		}
		//修改UC密码
        if(!empty($pass)){
              $data['pass']=md5(md5($pass).$data['code']);
              //--------------------------- Ucenter ---------------------------
              if(User_Uc_Mode==1){
                    include CSCMS.'sys/Cs_Ucenter.php';
                    include CSPATH.'uc_client/client.php';
                    uc_user_edit($data['name'],'',$pass,'',1);
		      }
              //--------------------------- Ucenter End ---------------------------
		}
        $info['url'] = site_url('user').'?v='.rand(1000,9999);
        $info['msg'] = L('plub_22');//恭喜你，操作成功
        getjson($info,0);
	}

    //会员删除
	public function del(){
        $id = $this->input->get_post('id',true);
		if(empty($id)) getjson(L('plub_01'));//参数错误
        //--------------------------- Ucenter ---------------------------
        if(User_Uc_Mode==1){
            include CSCMS.'sys/Cs_Ucenter.php';
            include CSPATH.'uc_client/client.php';
    	    if(is_array($id)){
	     	    foreach ($id as $id) {
			        $ucid = getzd('user','uid',$id);
					if($ucid>0){
                        uc_user_delete($ucid);//删除UC会员
					}
       		    }
			}else{
		        $ucid = getzd('user','uid',$id);
				if($ucid>0){
                    uc_user_delete($ucid);//删除UC会员
				}
			}
		}
        //--------------------------- Ucenter End ---------------------------

		$this->Csdb->get_del('funco',$id,'uida'); //访客A
		$this->Csdb->get_del('funco',$id,'uidb'); //访客B
		$this->Csdb->get_del('fans',$id,'uida'); //粉丝A
		$this->Csdb->get_del('fans',$id,'uidb'); //粉丝B
		$this->Csdb->get_del('friend',$id,'uida');  //好友A
		$this->Csdb->get_del('friend',$id,'uidb');   //好友B
		$this->Csdb->get_del('gbook',$id,'uida'); //留言A
		$this->Csdb->get_del('gbook',$id,'uidb'); //留言B
		$this->Csdb->get_del('msg',$id,'uida'); //消息A
		$this->Csdb->get_del('msg',$id,'uidb'); //消息B
		$this->Csdb->get_del('pl',$id,'uid'); //评论
		$this->Csdb->get_del('blog',$id,'uid'); //说说
		$this->Csdb->get_del('dt',$id,'uid'); //动态
		$this->Csdb->get_del('user_log',$id,'uid'); //登录日志
		$this->Csdb->get_del('web_pay',$id,'uid'); //模板记录
		$this->Csdb->get_del('pay',$id,'uid'); //充值记录
		$this->Csdb->get_del('income',$id,'uid'); //收入记录
		$this->Csdb->get_del('spend',$id,'uid'); //消费记录
		$this->Csdb->get_del('share',$id,'uid'); //分享记录
		$this->Csdb->get_del('session',$id,'uid'); //session会话
        $this->Csdb->get_del('user',$id);

        $info['url'] = site_url('user').'?v='.rand(1000,9999);
        getjson($info,0);
	}

    //会员组新增、修改
	public function zu_edit(){
        $id = intval($this->input->get_post('id'));
		if($id == 0){
            $data['id'] = 0;
            $data['xid'] = 0;
            $data['name'] = '';
            $data['color'] = '';
            $data['pic'] = '';
            $data['info'] = '';
            $data['cion_y'] = 0;
            $data['cion_m'] = 0;
            $data['cion_d'] = 0;
            $data['fid'] = 0;
            $data['aid'] =0;
            $data['sid'] = 0;
            $data['vid'] = 0;
            $data['mid'] = 0;
            $data['did'] = 0;
            $data['title'] = L('plub_23');//新增会员组
		}else{
            $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix."userzu where id=".$id."")->row(); 
		    if(!$row) exit(L('plub_16'));//记录不存在~！

            $data['id']=$row->id;
            $data['xid']=$row->xid;
            $data['name']=$row->name;
            $data['color']=$row->color;
            $data['pic']=$row->pic;
            $data['info']=$row->info;
            $data['cion_y']=$row->cion_y;
            $data['cion_m']=$row->cion_m;
            $data['cion_d']=$row->cion_d;
            $data['fid']=$row->fid;
            $data['aid']=$row->aid;
            $data['sid']=$row->sid;
            $data['vid']=$row->vid;
            $data['mid']=$row->mid;
            $data['did']=$row->did;
            $data['title'] = L('plub_24');//修改会员组
		}
        $this->load->view('user_zu_edit.html',$data);
	}

    //会员组保存
	public function zu_save(){
        $id   = intval($this->input->post('id'));
        $data['name']=$this->input->post('name',true);
        $data['xid']=intval($this->input->post('xid'));
        $data['color']=$this->input->post('color',true);
        $data['pic']=$this->input->post('pic',true);
        $data['info']=$this->input->post('info',true);
        $data['cion_y']=intval($this->input->post('cion_y'));
        $data['cion_m']=intval($this->input->post('cion_m'));
        $data['cion_d']=intval($this->input->post('cion_d'));
        $data['fid']=intval($this->input->post('fid'));
        $data['aid']=intval($this->input->post('aid'));
        $data['sid']=intval($this->input->post('sid'));
        $data['vid']=intval($this->input->post('vid'));
        $data['mid']=intval($this->input->post('mid'));
        $data['did']=intval($this->input->post('did'));

		if(empty($data['name'])) getjson(L('plub_25'));//数据不完整~!
        if($id==0){
        	$this->Csdb->get_insert('userzu',$data);
		}else{
			$this->Csdb->get_update('userzu',$id,$data);
		}
        $info['url'] = site_url('user/zu').'?v='.rand(1000,9999);
        getjson($info,0);
	}

    //会员组排序
	public function zu_sort(){
        $xid = $this->input->post('xid',true);
		foreach ($xid as $k=>$v) {
			if($k>0){
				$this->db->query("update ".CS_SqlPrefix."userzu set xid=".$v." where id=".$k."");
			}
		}
		$info['url'] = site_url('user/zu').'?v='.rand(1000,9999);
		$info['msg'] = L('plub_26');//排序已生效
		getjson($info,0);
	}

    //会员组权限操作
    public function zu_init($ac){
    	$id = intval($this->input->get_post('id'));
    	$sign = intval($this->input->get_post('sign'));
    	if($id==0 || $ac==''){
    		getjson(L('plub_01'));//参数错误
    	}
    	if($sign == 1){
			$edit[$ac] = 0;
		}else{
			$edit[$ac] = 1;
		}
		$this->Csdb->get_update('userzu',$id,$edit);
		getjson('',0);
    }

    //会员组删除
	public function zu_del(){
        $id = $this->input->post('id',true);
		if(empty($id)) getjson(L('plub_27'));//请选择要删除的数据~!
		//改变当前会员组
        if(is_array($id)){
		    foreach ($id as $id) {
                $this->db->query("update ".CS_SqlPrefix."user set zid=0 where zid=".$id."");
            }
		}else{
			$this->db->query("update ".CS_SqlPrefix."user set zid=0 where zid=".$id."");
		}
        $this->Csdb->get_del('userzu',$id);
       	$info['url'] = site_url('user/zu').'?v='.rand(1000,9999);
       	$info['msg'] = L('plub_28');//恭喜你，删除成功
       	getjson($info,0);
	}

    //等级新增、修改
	public function level_edit(){
        $id = intval($this->input->get('id'));
		if($id==0){
			$row=$this->db->query("SELECT xid FROM ".CS_SqlPrefix."userlevel order by xid desc")->row();
            $data['id']=0;
            $data['xid']=($row)?$row->xid+1:1;
            $data['stars']=0;
            $data['name']='';
            $data['jinyan']=0;
            $data['title'] = L('plub_29');//新增会员等级
		}else{
            $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix."userlevel where id=".$id."")->row(); 
		    if(!$row) exit(L('plub_16'));//记录不存在
            $data['id']=$row->id;
            $data['xid']=$row->xid;
            $data['stars']=$row->stars;
            $data['name']=$row->name;
            $data['jinyan']=$row->jinyan;
            $data['title'] = L('plub_30');//修改会员等级
		}
        $this->load->view('user_level_edit.html',$data);
	}

    //等级保存
	public function level_save(){
        $id   = intval($this->input->post('id'));
        $data['name']=$this->input->post('name',true);
        $data['xid']=intval($this->input->post('xid'));
        $data['stars']=intval($this->input->post('stars'));
        $data['jinyan']=intval($this->input->post('jinyan'));

		if(empty($data['name']) || $data['stars']==0) getjson(L('plub_25'));//数据不完整

        if($id==0){
        	$this->Csdb->get_insert('userlevel',$data);
		}else{
			$this->Csdb->get_update('userlevel',$id,$data);
		}
        $info['url'] = site_url('user/level').'?v='.rand(1000,9999);
        $info['parent'] = 1;
        getjson($info,0);
	}

    //等级删除
	public function level_del(){
        $id = $this->input->post('id',true);
		if(empty($id)) getjson(L('plub_01'));//参数错误

		//改变当前等级会员的等级
        if(is_array($id)){
        	foreach ($id as $id) {
        		$this->db->query("update ".CS_SqlPrefix."user set level=0 where level=".$id."");
        	}
		}else{
			$this->db->query("update ".CS_SqlPrefix."user set level=0 where level=".$id."");
		}

        $this->Csdb->get_del('userlevel',$id);
        $info['url'] = site_url('user/level').'?v='.rand(1000,9999);
        getjson($info,0);
	}

    //会员等级排序
	public function level_sort(){
        $xid = $this->input->post('xid',true);
		foreach ($xid as $k=>$v) {
			if($k>0){
				$this->db->query("update ".CS_SqlPrefix."userlevel set xid=".$v." where id=".$k."");
			}
		}
        $info['msg'] = L('plub_26');//排序已生效
        getjson($info,0);
	}
}

