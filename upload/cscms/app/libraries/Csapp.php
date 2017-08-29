<?php
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-09-19
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 云平台操作类
 */
class Csapp {

    function __construct ()
	{
		//.....
	}

    //组装URL
	function url($mx='plub',$data=''){
		$url = CS_YPTURL.$mx.'?time='.time().'&param='.cs_base64_encode(arraystring(array(
			'site' => Web_Url,
			'url'  => 'http://'.Web_Url.Web_Path,
			'name' => Web_Name,
			'data' => $data,
			'admin' => SELF,
			'version' => CS_Version,
			'charset' => CS_Charset,
			'uptime' => CS_Uptime,
		)));
		return $url;
    }

    //获取安装授权KEY
	function keys($data,$mx='plub'){
		$url = CS_YPTURL.$mx.'/key?param='.cs_base64_encode(arraystring(array(
			'site' => Web_Url,
			'url'  => 'http://'.Web_Url.Web_Path,
			'data' => $data,
			'admin' => SELF,
			'encry' => CS_Encryption_Key,
		)));
        return htmlall($url);
    }

    //下载模板到本地
    function down($file,$filename) {   
        if (! $file) {  
           return '-1';  
        }   
        if (ini_get('allow_url_fopen')) {
            $data=@file_get_contents($file);
        }
        if (empty($data) && function_exists('curl_init') && function_exists('curl_exec')) {
		$curl = curl_init(); //初始化curl
		curl_setopt($curl, CURLOPT_URL, $file); //设置访问的网站地址
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); //模拟用户使用的浏览器
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1);    //自动设置来路信息
		curl_setopt($curl, CURLOPT_TIMEOUT, 300);      //设置超时限制防止死循环
		curl_setopt($curl, CURLOPT_HEADER, 0);         //显示返回的header区域内容
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); //获取的信息以文件流的形式返回
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); 
		$data = curl_exec($curl);
		curl_close($curl);
        }
        if(empty($data)){
            return '-2'; 
		}elseif($data=='10001' || $data=='10002' || $data=='10003'){
            return $data; 
        }else{
            if (!@file_put_contents($filename, $data)){
                 return '-3';
            }else{
                 return false;
            }
        }
    } 
}


