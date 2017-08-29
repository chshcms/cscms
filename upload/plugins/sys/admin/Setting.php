<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-08-01
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Setting extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->lang->load('admin_setting');
        $this->Csadmin->Admin_Login();
	}

	public function index(){
        $data['mc'] = require CSCMS.'sys'.FGF.'CS_Memcached.php';
        $this->load->view('setting.html',$data);
	}
	public function ftp(){
	    $this->load->library('csup');
		$data['up']=$this->csup->init();
        $this->load->view('ftp_setting.html',$data);
	}

	public function tb(){
        $this->load->view('tb_setting.html');
	}

	public function denglu(){
        $this->load->view('denglu_setting.html');
	}

	public function save(){
	    $Web_Name = $this->input->post('Web_Name', TRUE, TRUE);
	    $Web_Url = $this->input->post('Web_Url', TRUE, TRUE);
	    $Web_Path = $this->input->post('Web_Path', TRUE, TRUE);
	    $Admin_Code = $this->input->post('Admin_Code', TRUE, TRUE);
	    $Web_Off = intval($this->input->post('Web_Off', TRUE));
	    $Web_Onneir = $this->input->post('Web_Onneir', TRUE, TRUE);
	    $Web_Mode = intval($this->input->post('Web_Mode', TRUE));
	    $Web_Icp = $this->input->post('Web_Icp', TRUE, TRUE);
	    $Admin_QQ = $this->input->post('Admin_QQ', TRUE, TRUE);
	    $Admin_Tel = $this->input->post('Admin_Tel', TRUE, TRUE);
	    $Admin_Mail = $this->input->post('Admin_Mail', TRUE, TRUE);
	    $Web_Key = $this->input->post('Web_Key', TRUE, TRUE);
	    $Web_Count = $_POST['Web_Count'];
	    $Web_Title = $this->input->post('Web_Title', TRUE);
	    $Web_Keywords = $this->input->post('Web_Keywords', TRUE);
	    $Web_Description = $this->input->post('Web_Description', TRUE);
	    $Web_Notice = $this->input->post('Web_Notice', TRUE, TRUE);
	    $Pl_Modes = intval($this->input->post('Pl_Modes', TRUE));
	    $Pl_Youke = intval($this->input->post('Pl_Youke', TRUE));
	    $Pl_Num = intval($this->input->post('Pl_Num', TRUE));
	    $Pl_Yy_Name = $this->input->post('Pl_Yy_Name', TRUE);
	    $Pl_Ds_Name = $this->input->post('Pl_Ds_Name', TRUE);
	    $Pl_Cy_Id = $this->input->post('Pl_Cy_Id', TRUE, TRUE);
	    $Pl_Str = $this->input->post('Pl_Str', TRUE, TRUE);
	    $Cache_Is = intval($this->input->post('Cache_Is', TRUE));
	    $Cache_Time = intval($this->input->post('Cache_Time', TRUE));
        $Cache_Mx = $this->input->post('Cache_Mx', TRUE);
	    $CS_Play_w = intval($this->input->post('CS_Play_w'));
	    $CS_Play_h = intval($this->input->post('CS_Play_h'));
	    $CS_Play_sw = intval($this->input->post('CS_Play_sw'));
	    $CS_Play_sh = intval($this->input->post('CS_Play_sh'));
	    $CS_Play_AdloadTime = intval($this->input->post('CS_Play_AdloadTime'));
	    $Html_Index = $this->input->post('Html_Index', TRUE, TRUE);
	    $Html_StopTime = intval($this->input->post('Html_StopTime', TRUE));
	    $Html_PageNum = intval($this->input->post('Html_PageNum', TRUE));
        $Html_Wap_Dir = $this->input->post('Html_Wap_Dir', TRUE);
	    $CS_Language = $this->input->post('CS_Language', TRUE, TRUE);

	    $CS_Cache_Time = intval($this->input->post('CS_Cache_Time', TRUE));
	    $CS_Cache_Dir = $this->input->post('CS_Cache_Dir', TRUE, TRUE);
	    $CS_Cache_On = $this->input->post('CS_Cache_On', TRUE, TRUE);

	    $Mobile_Is = intval($this->input->post('Mobile_Is', TRUE));
	    $Mobile_Url = $this->input->post('Mobile_Url', TRUE, TRUE);
	    $Mobile_Win = intval($this->input->post('Mobile_Win', TRUE));

        $Is_Ssl = intval($this->input->post('Is_Ssl', TRUE));

        $Mobile_Skins_Dir = $this->input->post('Mobile_Skins_Dir', TRUE, TRUE);
        $Mobile_User_Dir = $this->input->post('Mobile_User_Dir', TRUE, TRUE);
        $Mobile_Home_Dir = $this->input->post('Mobile_Home_Dir', TRUE, TRUE);
        $Pc_Skins_Dir = $this->input->post('Pc_Skins_Dir', TRUE, TRUE);
        $Pc_User_Dir = $this->input->post('Pc_User_Dir', TRUE, TRUE);
        $Pc_Home_Dir = $this->input->post('Pc_Home_Dir', TRUE, TRUE);

        if($CS_Cache_Time==0)     $CS_Cache_Time=600;
        if(empty($CS_Cache_Dir))  $CS_Cache_Dir="sql";
        if($CS_Cache_On==0){
        	$CS_Cache_On="FALSE";
        }else{
        	$CS_Cache_On='TRUE';
        }

        if($Html_StopTime==0)      $Html_StopTime=1;
        if($Html_PageNum==0)       $Html_PageNum=20;
        if($Pl_Num==0)             $Pl_Num=10;
        if($Cache_Time==0)         $Cache_Time=600;
        if($CS_Play_w==0)          $CS_Play_w=445;
        if($CS_Play_h==0)          $CS_Play_h=64;
        if($CS_Play_sw==0)         $CS_Play_sw=600;
        if($CS_Play_sh==0)         $CS_Play_sh=450;

        //判断手机生成目录
        $wapdir = array('attachment','cache','cscms','packs','plugins','tpl');
        if(in_array($Html_Wap_Dir,$wapdir)){
            getjson(L('plub_set_00'),1);
        }

        //HTML转码
        $Web_Onneir= str_encode($Web_Onneir); 
        $Web_Title= str_encode($Web_Title); 
        $Web_Keywords= str_encode($Web_Keywords); 
        $Web_Description= str_encode($Web_Description); 
        $Web_Notice=str_encode($Web_Notice); 
	    $Web_Count= str_encode($Web_Count); 

        //判断主要数据不能为空
	    if (empty($Web_Name)||empty($Web_Url)||empty($Web_Path)||empty($Admin_Code)){
            //站点名称、域名、路径、认证码不能为空
		    getjson(L('plub_set_01'),1);  
	    }
		//判断生成首页文件格式
		$file_ext = strtolower(trim(substr(strrchr($Html_Index, '.'), 1)));
		if($file_ext!='html' && $file_ext!='htm' && $file_ext!='shtm' && $file_ext!='shtml'){
		    getjson(L('plub_set_02'),1);  //静态文件格式不正确
		}

        //判断数据库缓存目录
        if($CS_Cache_Dir!=CS_Cache_Dir){
	        if(file_exists(FCPATH.'cache/'.CS_Cache_Dir)){
		        if(!rename(FCPATH.'cache/'.CS_Cache_Dir,FCPATH.'cache/'.$CS_Cache_Dir)){
                    getjson(L('plub_set_03'),1);
                }
			}else{
		        @mkdir(FCPATH.'cache/'.$CS_Cache_Dir);
			}
		}
        //修改数据库缓存配置
        $this->load->helper('file');
        $db_cof=read_file(CSCMS."sys/Cs_DB.php");
        $db_cof=preg_replace("/'CS_Cache_On',(.*?)\)/","'CS_Cache_On',".$CS_Cache_On.")",$db_cof);
        $db_cof=preg_replace("/'CS_Cache_Dir','(.*?)'/","'CS_Cache_Dir','".$CS_Cache_Dir."'",$db_cof);
        $db_cof=preg_replace("/'CS_Cache_Time',(.*?)\)/","'CS_Cache_Time',".$CS_Cache_Time.")",$db_cof);
        if(!write_file(CSCMS."sys/Cs_DB.php", $db_cof)){
            getjson(L('plub_set_04'));
		}

        $strs="<?php"."\r\n";
        $strs.="define('Web_Name','".$Web_Name."'); //站点名称  \r\n";
        $strs.="define('Web_Url','".$Web_Url."'); //站点域名  \r\n";
        $strs.="define('Web_Path','".$Web_Path."'); //站点路径  \r\n";
        $strs.="define('Admin_Code','".$Admin_Code."');  //后台验证码  \r\n";
        $strs.="define('Web_Off',".$Web_Off.");  //网站开关  \r\n";
        $strs.="define('Web_Onneir','".$Web_Onneir."');  //网站关闭内容  \r\n";
        $strs.="define('Web_Mode',".$Web_Mode.");  //网站运行模式  \r\n";
        $strs.="define('Html_Index','".$Html_Index."');  //主页静态URL  \r\n";
        $strs.="define('Html_StopTime',".$Html_StopTime.");  //生成间隔秒数  \r\n";
        $strs.="define('Html_PageNum',".$Html_PageNum.");  //每页生成数量  \r\n";
        $strs.="define('Html_Wap_Dir','".$Html_Wap_Dir."');  //手机版本目录  \r\n";

        $strs.="define('Web_Icp','".$Web_Icp."');  //网站ICP  \r\n";
        $strs.="define('Admin_QQ','".$Admin_QQ."');  //站长QQ  \r\n";
        $strs.="define('Admin_Tel','".$Admin_Tel."');  //站长电话  \r\n";
        $strs.="define('Admin_Mail','".$Admin_Mail."');  //站长EMAIL  \r\n";
        $strs.="define('Web_Key','".$Web_Key."');  //热门搜索  \r\n";
        $strs.="define('Web_Count','".$Web_Count."');  //统计代码  \r\n";
        $strs.="define('Web_Title','".$Web_Title."'); //SEO-标题  \r\n";
        $strs.="define('Web_Keywords','".$Web_Keywords."'); //SEO-Keywords  \r\n";
        $strs.="define('Web_Description','".$Web_Description."'); //SEO-description  \r\n";
        $strs.="define('Web_Notice','".$Web_Notice."');  //网站公告  \r\n";

        $strs.="define('Pl_Modes',".$Pl_Modes.");  //评论方式  \r\n";
        $strs.="define('Pl_Youke',".$Pl_Youke.");  //游客是否可以评论  \r\n";
        $strs.="define('Pl_Num',".$Pl_Num.");  //评论每页条数  \r\n";
        $strs.="define('Pl_Yy_Name','".$Pl_Yy_Name."');  //友言 ID  \r\n";
        $strs.="define('Pl_Ds_Name','".$Pl_Ds_Name."');  //多说账号  \r\n";
		$strs.="define('Pl_Cy_Id','".$Pl_Cy_Id."');  //畅言APP_Id  \r\n";
        $strs.="define('Pl_Str','".$Pl_Str."');  //评论过滤字符  \r\n";

        $strs.="define('Cache_Is',".$Cache_Is.");  //缓存开关  \r\n";
        $strs.="define('Cache_Mx','".$Cache_Mx."');  //缓存适配器  \r\n";
        $strs.="define('Cache_Time',".$Cache_Time.");  //缓存时间  \r\n";

        $strs.="define('CS_Play_w',".$CS_Play_w.");    \r\n";
        $strs.="define('CS_Play_h',".$CS_Play_h.");    \r\n";
        $strs.="define('CS_Play_sw',".$CS_Play_sw.");    \r\n";
        $strs.="define('CS_Play_sh',".$CS_Play_sh.");    \r\n";
        $strs.="define('CS_Play_AdloadTime',".$CS_Play_AdloadTime."); //视频播放前广告时间    \r\n";
        $strs.="define('CS_Language','".$CS_Language."'); //网站语言,english英文，zh_cn中文 \r\n";

        $strs.="define('Mobile_Is',".$Mobile_Is.");    //手机门户是否开启    \r\n";
        $strs.="define('Mobile_Url','".$Mobile_Url."');  //手机门户域名    \r\n";
        $strs.="define('Mobile_Win',".$Mobile_Win.");   //电脑是否可以访问手机页面  \r\n";
        $strs.="define('Mobile_Skins_Dir','".$Mobile_Skins_Dir."');   //手机默认模版  \r\n";
        $strs.="define('Mobile_User_Dir','".$Mobile_User_Dir."');   //手机会员模版  \r\n";
        $strs.="define('Mobile_Home_Dir','".$Mobile_Home_Dir."');   //手机空间模版  \r\n";
        $strs.="define('Pc_Skins_Dir','".$Pc_Skins_Dir."');   //PC系统默认模版  \r\n";
        $strs.="define('Pc_User_Dir','".$Pc_User_Dir."');   //PC会员默认模版  \r\n";
        $strs.="define('Pc_Home_Dir','".$Pc_Home_Dir."');   //PC空间默认模版  \r\n";
        $strs.="define('Is_Ssl',".$Is_Ssl.");   //是否开启SSL  ";

        if($Cache_Mx == 'memcached'){
            $mc['hostname'] = $this->input->post('mc_hostname',true);
            $mc['port'] = (int)$this->input->post('mc_port',true);
            $mc['weight'] = (int)$this->input->post('mc_weight',true);
            arr_file_edit($mc,CSCMS.'sys'.FGF.'CS_Memcached.php');
        }

        //写文件
        if (!write_file(CSCMS.'sys/Cs_Config.php', $strs)){
            getjson(L('plub_set_05'),1);
        }else{
        	$info['url'] = site_url('setting').'?v='.rand(1000,1999);
            $info['msg'] = L('plub_set_06');
        	getjson($info,0);
        }
	}

	public function ftp_save(){
	    $UP_Mode = intval($this->input->post('UP_Mode', TRUE));
	    $UP_Size = intval($this->input->post('UP_Size', TRUE));
	    $UP_Type = $this->input->post('UP_Type', TRUE, TRUE);

	    $UP_Url = $this->input->post('UP_Url', TRUE, TRUE);
	    $UP_Pan = str_replace("\\","/",$this->input->post('UP_Pan', TRUE, TRUE));

	    $FTP_Url = $this->input->post('FTP_Url', TRUE, TRUE);
	    $FTP_Port = intval($this->input->post('FTP_Port', TRUE));
	    $FTP_Server = $this->input->post('FTP_Server', TRUE, TRUE);
	    $FTP_Dir = $this->input->post('FTP_Dir', TRUE, TRUE);
	    $FTP_Name = $this->input->post('FTP_Name', TRUE, TRUE);
	    $FTP_Pass = $this->input->post('FTP_Pass', TRUE);
	    $FTP_Ive = intval($this->input->post('FTP_Ive', TRUE));
	    $FTP_Ive = $FTP_Ive?'TRUE':'FALSE';
        if($UP_Mode==0)   $UP_Mode=1;
        if($UP_Size==0)   $UP_Size=1024;
        if($FTP_Port==0)  $FTP_Port=21;
		if($FTP_Pass==substr(FTP_Pass,0,3).'******'){
            $FTP_Pass=FTP_Pass;
		}

        //判断主要数据不能为空
	    if ($UP_Mode==2 && (empty($FTP_Url)||empty($FTP_Server)||empty($FTP_Name)||empty($FTP_Pass))){
		    getjson(L('plub_ftp_00'));
	    }
	    if (!empty($UP_Pan) && empty($UP_Url)){
		    getjson(L('plub_ftp_01'));
	    }
		if(empty($UP_Pan) || $UP_Url==is_ssl().Web_Url.Web_Path){
            $UP_Url = '';
		}

        $strs="<?php"."\r\n";
        $strs.="define('UP_Mode',".$UP_Mode.");      //会员上传附件方式  1站内，2FTP，3七牛，4阿里云，5又拍云... \r\n";
        $strs.="define('UP_Size',".$UP_Size.");      //上传支持的最大KB \r\n";
        $strs.="define('UP_Type','".$UP_Type."');  //上传支持的格式 \r\n";
        $strs.="define('UP_Url','".$UP_Url."');  //本地访问地址 \r\n";
        $strs.="define('UP_Pan','".$UP_Pan."');  //本地存储路径 \r\n";

        $strs.="define('FTP_Url','".$FTP_Url."');      //远程FTP连接地址     \r\n";
        $strs.="define('FTP_Server','".$FTP_Server."');      //远程FTP服务器IP    \r\n";
        $strs.="define('FTP_Dir','".$FTP_Dir."');      //远程FTP目录    \r\n";
        $strs.="define('FTP_Port','".$FTP_Port."');      //远程FTP端口    \r\n";
        $strs.="define('FTP_Name','".$FTP_Name."');      //远程FTP帐号    \r\n";
        $strs.="define('FTP_Pass','".$FTP_Pass."');      //远程FTP密码    \r\n";
        $strs.="define('FTP_Ive',".$FTP_Ive.");      //是否使用被动模式   ";

		if($UP_Mode>2){ //其他上传修改配置
            $this->load->library('csup');
			$this->csup->edit($UP_Mode);
		}

        //写文件
        if (!write_file(CSCMS.'sys/Cs_Ftp.php', $strs)){
            getjson(L('plub_ftp_02'));
        }else{
        	$info['url'] = site_url('setting/ftp').'?v='.rand(1000,9999);
            getjson($info,0);
        }
	}

	public function tb_save(){
	    $CS_WaterMark = intval($this->input->post('CS_WaterMark', TRUE));
	    $CS_WaterMode = intval($this->input->post('CS_WaterMode', TRUE));
	    $CS_WaterFontSize = intval($this->input->post('CS_WaterFontSize', TRUE));
	    $CS_WaterLocation = intval($this->input->post('CS_WaterLocation', TRUE));
	    $CS_WaterFont = $this->input->post('CS_WaterFont', TRUE, TRUE);
	    $CS_WaterFontColor = $this->input->post('CS_WaterFontColor', TRUE, TRUE);

	    $CS_WaterLogo = $this->input->post('CS_WaterLogo', TRUE, TRUE);
	    $CS_WaterLogotm = intval($this->input->post('CS_WaterLogotm', TRUE));
	    $CS_WaterLocations = $this->input->post('CS_WaterLocations', TRUE, TRUE);


        if($CS_WaterLogotm>100)   $CS_WaterLogotm=100;
        if($CS_WaterFontSize==0)  $CS_WaterFontSize=12;
        if($CS_WaterLogotm==0)    $CS_WaterLogotm=90;
        if($CS_WaterLocation==0)  $CS_WaterLocation=2;

        $strs="<?php"."\r\n";
        $strs.="define('CS_WaterMark',".$CS_WaterMark.");  //水印开关  \r\n";
        $strs.="define('CS_WaterMode',".$CS_WaterMode.");  //水印类型  \r\n";
        $strs.="define('CS_WaterFontSize',".$CS_WaterFontSize.");  //水印字体大小  \r\n";
        $strs.="define('CS_WaterLocation',".$CS_WaterLocation.");  //水印位置  \r\n";
        $strs.="define('CS_WaterFont','".$CS_WaterFont."');  //水印文字  \r\n";
        $strs.="define('CS_WaterFontColor','".$CS_WaterFontColor."');  //水印颜色  \r\n";
        $strs.="define('CS_WaterLogo','".$CS_WaterLogo."');  //水印图片路径  \r\n";
        $strs.="define('CS_WaterLogotm','".$CS_WaterLogotm."');  //水印质量  \r\n";
        $strs.="define('CS_WaterLocations','".$CS_WaterLocations."');  //LOGO图片坐标位置 \r\n";

        //写文件
        if (!write_file(CSCMS.'sys/Cs_Water.php', $strs)){
            getjson(L('plub_tb_00'));
        }else{
        	$info['url'] = site_url('setting/tb').'?v='.rand(1000,9999);
            $info['msg'] = L('plub_tb_01');
        	getjson($info,0);
        }
	}

	public function denglu_save(){
	    $CS_Appmode = intval($this->input->post('CS_Appmode', TRUE));
	    $CS_Appid = $this->input->post('CS_Appid', TRUE, TRUE);
	    $CS_Appkey = $this->input->post('CS_Appkey', TRUE, TRUE);
	    $CS_Qqmode = intval($this->input->post('CS_Qqmode', TRUE));
	    $CS_Qqid = $this->input->post('CS_Qqid', TRUE, TRUE);
	    $CS_Qqkey = $this->input->post('CS_Qqkey', TRUE, TRUE);
	    $CS_Wbmode = intval($this->input->post('CS_Wbmode', TRUE));
	    $CS_Wbid = $this->input->post('CS_Wbid', TRUE, TRUE);
	    $CS_Wbkey = $this->input->post('CS_Wbkey', TRUE, TRUE);
	    $CS_Bdmode = intval($this->input->post('CS_Bdmode', TRUE));
	    $CS_Bdid = $this->input->post('CS_Bdid', TRUE, TRUE);
	    $CS_Bdkey = $this->input->post('CS_Bdkey', TRUE, TRUE);
	    $CS_Rrmode = intval($this->input->post('CS_Rrmode', TRUE));
	    $CS_Rrid = $this->input->post('CS_Rrid', TRUE, TRUE);
	    $CS_Rrkey = $this->input->post('CS_Rrkey', TRUE, TRUE);
	    $CS_Kxmode = intval($this->input->post('CS_Kxmode', TRUE));
	    $CS_Kxid = $this->input->post('CS_Kxid', TRUE, TRUE);
	    $CS_Kxkey = $this->input->post('CS_Kxkey', TRUE, TRUE);
	    $CS_Dbmode = intval($this->input->post('CS_Dbmode', TRUE));
	    $CS_Dbid = $this->input->post('CS_Dbid', TRUE, TRUE);
	    $CS_Dbkey = $this->input->post('CS_Dbkey', TRUE, TRUE);

        $strs="<?php"."\r\n";
        $strs.="define('CS_Appmode',".$CS_Appmode.");  //第三方登录方式，0为独立，1为官方 \r\n";
        $strs.="define('CS_Appid','".$CS_Appid."');  //官方Appid \r\n";
        $strs.="define('CS_Appkey','".$CS_Appkey."');  //官方Appkey \r\n\r\n";
        $strs.="define('CS_Qqmode',".$CS_Qqmode.");  //QQ登陆开关 \r\n";
        $strs.="define('CS_Qqid','".$CS_Qqid."');  //QQ登陆id \r\n";
        $strs.="define('CS_Qqkey','".$CS_Qqkey."');  //QQ登陆key \r\n\r\n";
        $strs.="define('CS_Wbmode',".$CS_Wbmode.");  //微博登陆开关  \r\n";
        $strs.="define('CS_Wbid','".$CS_Wbid."');   //微博登陆id  \r\n";
        $strs.="define('CS_Wbkey','".$CS_Wbkey."');  //微博登陆key  \r\n\r\n";
        $strs.="define('CS_Bdmode',".$CS_Bdmode.");  //百度登陆开关  \r\n";
        $strs.="define('CS_Bdid','".$CS_Bdid."');   //百度登陆id  \r\n";
        $strs.="define('CS_Bdkey','".$CS_Bdkey."');  //百度登陆key  \r\n\r\n";
        $strs.="define('CS_Rrmode',".$CS_Rrmode.");  //人人登陆开关  \r\n";
        $strs.="define('CS_Rrid','".$CS_Rrid."');   //人人登陆id  \r\n";
        $strs.="define('CS_Rrkey','".$CS_Rrkey."');  //人人登陆key  \r\n\r\n";
        $strs.="define('CS_Kxmode',".$CS_Kxmode.");  //开心登陆开关  \r\n";
        $strs.="define('CS_Kxid','".$CS_Kxid."');   //开心登陆id  \r\n";
        $strs.="define('CS_Kxkey','".$CS_Kxkey."');  //开心登陆key  \r\n\r\n";
        $strs.="define('CS_Dbmode',".$CS_Dbmode.");  //豆瓣登陆开关  \r\n";
        $strs.="define('CS_Dbid','".$CS_Dbid."');   //豆瓣登陆id  \r\n";
        $strs.="define('CS_Dbkey','".$CS_Dbkey."');  //豆瓣登陆key ";

        //写文件
        if (!write_file(CSCMS.'sys/Cs_Denglu.php', $strs)){
            getjson(L('plub_denglu_00'));
        }else{
        	$info['url'] = site_url('setting/denglu').'?v='.rand(1000,9999);
            $info['msg'] = L('plub_denglu_01');
            getjson($info,0);
        }
	}
}

