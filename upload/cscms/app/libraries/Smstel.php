<?php
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-08-21
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 手机短信类
 */
class Smstel {

    function __construct ()
	{
		   $this->appid   = CS_Sms_ID;  //商户ID
		   $this->appkey  = CS_Sms_Key;  //商户KEY
           $this->curl    = 'http://sms.chshcms.com/index.php/api/';
	}

    //发送
	function add($tel,$neir){
		   $get='index?uid='.$this->appid;
		   $get.='&key='.$this->appkey;
		   $get.='&tel='.trim($tel);
		   $get.='&charset=utf8';
		   $get.='&neir='.urlencode($neir.'【'.CS_Sms_Name.'】');
           $url=$this->curl.$get;
		   $msg=htmlall($url);
		   $msg=$this->error($msg);
		   $msg=get_bm($msg);
		   return $msg;
    }

    //发送注册验证码
	function seadd($tel){

		   $tel_time=$_SESSION['tel_time'];
           if($tel_time && $tel_time+60>time()){
		       return 'addok'; //发送时间没有过60秒
		   }
		   $code=random_string('nozero',4);
		   $_SESSION['tel_code']=$code;
		   $_SESSION['tel_time']=time();		   

		   $neir='欢迎注册'.Web_Name.'，您的验证码是'.$code.'，请尽快完成验证。(如非本人操作，可不予理会)';
		   $get='index?uid='.$this->appid;
		   $get.='&key='.$this->appkey;
		   $get.='&tel='.trim($tel);
		   $get.='&charset=utf8';
		   $get.='&neir='.urlencode($neir.'【'.CS_Sms_Name.'】');
           $url=$this->curl.$get;
		   $msg=htmlall($url);
		   $msg=get_bm($msg);
		   $msg=$this->error($msg);
		   return $msg;
    }

    //查询余额
	function balance(){

		   $get='rmb?uid='.$this->appid;
		   $get.='&key='.$this->appkey;
           $url=$this->curl.$get;
		   $rmb=htmlall($url);
		   return get_bm($rmb);
    }

    //查询记录
	function lists($len=12,$p=1){

		   $get='lists?uid='.$this->appid;
		   $get.='&key='.$this->appkey;
		   $get.='&len='.$len;
		   $get.='&p='.$p;
           $url=$this->curl.$get;
		   $str=htmlall($url);
		   return $str;
    }

    //错误提示
    function error($msg){
		    if(empty($msg)){
                 return L('curl_err');
			}
            return $msg;
	}
}


