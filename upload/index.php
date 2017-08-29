<?php
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Kai Jie
 * @Dtime:2017-03-10
 */
//默认时区
date_default_timezone_set("Asia/Shanghai");
//应用环境，TRUE 打开报错，FALSE关闭报错
define('ENVIRONMENT',false);
//路径分隔符
define('FGF', DIRECTORY_SEPARATOR);
//核心路径配置
$cs_folder = 'cscms/config';
//环境报错设置
if(ENVIRONMENT == TRUE){
	error_reporting(-1);
	ini_set('display_errors', 1);
}else{
	ini_set('display_errors', 0);
	if (version_compare(PHP_VERSION, '5.3', '>=')){
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
	}else{
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
	}
}
//路径常量设置
if(!defined('SELF')){
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
}
if(!defined('FCPATH')){
	define('FCPATH', dirname(__FILE__).FGF);
}
//CSCMS路径检测
if(is_dir($cs_folder)){
	if (($_temp = realpath($cs_folder)) !== FALSE){
		$cs_folder = $_temp.FGF;
	}else{
		$cs_folder = strtr(rtrim($cs_folder, '/\\'),'/\\',FGF.FGF).FGF;
	}
}else{
	header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
	echo 'The kernel configuration directory is incorrect.';exit;
}
define('CSCMS', $cs_folder);
define('CSPATH', FCPATH.'cscms'.FGF);
define('CSCMSPATH', FCPATH.'packs'.FGF);
//当前运行URI
define('REQUEST_URI', str_replace(array(SELF,'//'),array('','/'),$_SERVER['REQUEST_URI']));
require_once CSCMS.'sys/Cs_Cscms.php';