<?php
/**
 * @Cscms v4.0 open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-07-27
 */
header('Content-Type: text/html; charset=utf-8');
header("Cache-Control: max-age=3");
//判断是否安装
if(!defined('IS_INSTALL') && !file_exists(FCPATH.'packs/install/install.lock')){
	$uri = parse_url('http://cscms'.REQUEST_URI);
	$webpath = str_replace(SELF,'',$uri['path']);
	header("location:".$webpath."install.php");exit;
}
//装载全局配置文件
require_once 'Cs_Version.php';
require_once 'Cs_DB.php';
require_once 'Cs_Config.php';
require_once 'Cs_User.php';
require_once 'Cs_Home.php';
require_once 'Cs_Pay.php';
require_once 'Cs_Mail.php';
require_once 'Cs_Water.php';
require_once 'Cs_Denglu.php';
require_once 'Cs_Ftp.php';
require_once 'Cs_Sms.php';
//判断网站运行状态
if(!defined('IS_ADMIN') && Web_Off==0){
    die(Web_Onneir);
}
//判断会员系统开关
if(!defined('IS_ADMIN') && User_Mode==0 && strpos(REQUEST_URI,'user') !== FALSE){
	die(User_No_info);
}
//手机客户端访问标示
if(preg_match("/(iPhone|iPad|iPod|Android)/i", strtoupper($_SERVER['HTTP_USER_AGENT']))){
    if(Mobile_Is==1 && !defined('IS_ADMIN')){
	    define('MOBILE', true);	
	}
}
//手机二级域名
if(Mobile_Is==1 && $_SERVER['HTTP_HOST']==Mobile_Url){
    define('MOBILE_YM', true);	
}
//判断手机访问域名
if(defined('MOBILE_YM') && !defined('MOBILE')){
	define('MOBILE', true);	
}
//判断会员主页泛域名
if(Home_Ym==1){
    $HOME_ONE = current(explode(".",$_SERVER['HTTP_HOST']));
	$HOME_YM = str_replace($HOME_ONE.'.', "",$_SERVER['HTTP_HOST']);
	$HOME_EXT = explode('|',Home_Ymext);
	if($HOME_ONE != 'www' && !in_array($HOME_ONE, $HOME_EXT) && $HOME_YM==Home_YmUrl){
		define('HOMEPATH', true);
	}
}
//判断版块绑定域名
$_ERYM=FALSE;
if(file_exists(CSCMS.'sys/Cs_Domain.php')){
    $_CS_Domain = require(CSCMS.'sys/Cs_Domain.php');
    if (is_array($_CS_Domain)) {
		foreach ($_CS_Domain as $key => $host) {
		    if($_SERVER['HTTP_HOST']==$host){
				if($key=='user'){ //会员中心二级域名
					define('USERPATH', true);
					//判断版块会员二级域名
					$dir = cscms_uri();
					if(empty($dir)) $dir = 'sys';
					if(file_exists(FCPATH.'plugins/'.$dir)){
		                define('PLUBPATH', $dir);
					}
				}else{ //板块二级域名
					if (!defined('PLUBPATH')) {
		                define('PLUBPATH', $key);
					}
				}
				$_ERYM=TRUE;
				break;
		    }
		}
    }
}
//板块未开启二级域名获得版块实际目录
if(!$_ERYM){
	//判断会员主页泛域名不正确，则301跳转
	if(!defined('IS_INSTALL') && !defined('HOMEPATH') && !defined('MOBILE_YM')){
		if($_SERVER['HTTP_HOST'] != Web_Url){
			$Web_Link="http://".str_replace('//','/',Web_Url.Web_Path.cscms_cur_url());
			header("location:".$Web_Link);exit;
		}
	}
	$SELF = cscms_uri();
	//板块
	if(!empty($SELF) && is_dir(FCPATH.'plugins/'.$SELF) && file_exists(CSCMS.$SELF.'/site.php')){
		define('PLUBPATH', $SELF);
	}
	//会员中心
	if(!defined('IS_ADMIN') && (!defined('PLUBPATH') && $SELF=='user') || (defined('PLUBPATH') && cscms_uri(1)=='user')){
		define('USERPATH', true);
	}
	//会员主页
	if((!defined('PLUBPATH') && cscms_uri(1)=='home') || (defined('PLUBPATH') && $SELF=='home')){
	  define('HOMEPATH', true);
		//会员主页板块
		if(!defined('PLUBPATH')){
			$SELF2 = cscms_uri(2);
			if(cscms_uri(1)=='home' && !empty($SELF2) && is_dir(FCPATH.'plugins/'.$SELF2) && file_exists(CSCMS.$SELF2.'/site.php')){
		  		define('PLUBPATH', $SELF2);
			}
		}
	}
}
//默认板块名称配置
$_CS_Rewrite = require(CSCMS.'sys/Cs_Rewrite.php');
if(!defined('PLUBPATH')){
	//判断伪静态板块名
	if (is_array($_CS_Rewrite)) {
		$dir = cscms_uri(1);
		foreach ($_CS_Rewrite as $key => $val) {
			if($SELF == $val){
				define('PLUBPATH', $key);
				break;
			}
		}
	}
	if(!defined('PLUBPATH')) define('PLUBPATH', 'sys');
}
//加载板块配置
$PLUBARR = array();
if(defined('PLUBPATH') && file_exists(CSCMS.PLUBPATH.FGF.'site.php')){
	$PLUBARR[PLUBPATH] = require CSCMS.PLUBPATH.FGF.'site.php';
	$PLUBARR[PLUBPATH]['menu'] = require CSCMS.PLUBPATH.FGF.'menu.php';
}
//判断手机客户端访问
if(defined('MOBILE')){
    if(!defined('IS_ADMIN') && Mobile_Is==1){
		if(Web_Mode<3 && Home_Ym==0 && Mobile_Url!='' && $_SERVER['HTTP_HOST']!=Mobile_Url){
			$Web_Link="http://".str_replace('//','/',Mobile_Url.Web_Path.cscms_cur_url());
		    header("location:".$Web_Link);exit;
		}
    }
}
if(!defined('MOBILE') || (defined('MOBILE') && Mobile_Win==0)){
	if((Mobile_Is==0 || Mobile_Win==0) && $_SERVER['HTTP_HOST']==Mobile_Url){
		$Web_Link="http://".str_replace('//','/',Web_Url.Web_Path.cscms_cur_url());
		header("location:".$Web_Link);exit;
	}
}
//核心路径配置
$sys_folder = 'cscms/system';
$app_folder = 'cscms/app';
$tpl_folder = 'tpl';
if(($_temp = realpath($sys_folder)) !== FALSE){
	$sys_folder = $_temp.FGF;
}else{
	$sys_folder = strtr(rtrim($sys_folder, '/\\'),'/\\',FGF.FGF).FGF;
}
if(!is_dir($sys_folder)){
	header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
	echo 'The frame path is not configured properly.';exit;
}
if(is_dir($app_folder)){
	if (($_temp = realpath($app_folder)) !== FALSE){
		$app_folder = $_temp;
	}else{
		$app_folder = strtr(rtrim($app_folder, '/\\'),'/\\',FGF.FGF);
	}
}else{
	header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
	echo 'The controller path is not configured properly.';exit;
}
if(is_dir($tpl_folder)){
	if (($_temp = realpath($tpl_folder)) !== FALSE){
		$tpl_folder = $_temp;
	}else{
		$tpl_folder = strtr(rtrim($tpl_folder, '/\\'),'/\\',FGF.FGF);
		$tpl_folder.= FGF;
	}
}else{
	header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
	echo 'The template path is not configured properly.';exit;
}
define('BASEPATH', $sys_folder);
define('SYSDIR', basename(BASEPATH));
define('APPPATH', $app_folder.FGF);
define('VIEWPATH', $tpl_folder.FGF);
require_once BASEPATH.'core/CodeIgniter.php';


//获取当前目录路径参数
function cscms_cur_url() { 
    if(!empty($_SERVER["REQUEST_URI"])){ 
        $scrtName = $_SERVER["REQUEST_URI"]; 
        $nowurl = $scrtName; 
    } else { 
        $scrtName = $_SERVER["PHP_SELF"]; 
        if(empty($_SERVER["QUERY_STRING"])) { 
            $nowurl = $scrtName; 
        } else { 
            $nowurl = $scrtName."?".$_SERVER["QUERY_STRING"]; 
        } 
    } 
	$nowurl=str_replace("//", "/", $nowurl);
    return $nowurl; 
}
//获取当前URI参数
function cscms_uri($n=0){
	$REQUEST_URI = substr(REQUEST_URI,0,1)=='/' ? substr(REQUEST_URI,1) : REQUEST_URI;
	if(!empty($REQUEST_URI)){
		$arr = explode('/', $REQUEST_URI);
		if(Web_Path != '/'){
			unset($arr[0]);
			$arr = array_merge($arr);
		}
		if(!empty($arr[$n])){
    		return str_replace("/", "", $arr[$n]);
		}
	}
    return '';
}