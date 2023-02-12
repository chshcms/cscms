<?php
if (!defined('FCPATH')) exit('No direct script access allowed');

/**
 * 微信在线支付类
 */
class Wxpays {

    public function __construct ()
	{
		//应用APPID
		$this->app_id = CS_Wxpay_ID;
		//商户ID
		$this->mch_id = CS_Wxpay_Mchid;
		//商户密钥
		$this->mch_key = CS_Wxpay_Key;
		//异步地址
		$this->notify_url = get_link("pay/wxpay/notify_url");
	}

    //扫码下单
	public function get_ma($dingdan,$total_fee,$body){

		$arr['appid'] = $this->app_id;
		$arr['mch_id'] = $this->mch_id;
		$arr['nonce_str'] = $this->getNonceStr();
		$arr['body'] = $body;
		$arr['out_trade_no'] = $dingdan;
		$arr['total_fee'] = $total_fee*100;
		$arr['spbill_create_ip'] = $this->get_ip();
		$arr['notify_url'] = $this->notify_url;
		$arr['trade_type'] = 'NATIVE';
		$arr['sign'] = $this->getsign($arr);

		$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
		$post_xml = $this->arrtoxml($arr);
		$xml = $this->geturl($url,$post_xml);
		$arr2 = $this->xmltoarr($xml);
		if($arr2['return_code'] != 'SUCCESS' || $arr2['result_code'] != 'SUCCESS'){
			if(empty($arr2['return_msg'])) $arr2['return_msg'] = $arr2['err_code_des'];
			exit($arr2['return_msg']);
		}else{
			return $arr2['code_url'];
		}
    }

    //验证签名
	public function is_sign(){
		$xml = file_get_contents("php://input");
		//@file_put_contents('./1.txt',$xml);
		$arr = $this->xmltoarr($xml);
		$this->rmb = $arr['total_fee']/100;
		if($arr['return_code'] == 'SUCCESS' && $arr['result_code'] == 'SUCCESS') {
			$sign = $arr['sign'];
			$md5 =  $this->getsign($arr);
			if($sign == $md5){
				return $arr['out_trade_no'];
			}
		}
		return false;
	}

	//生成签名，$arr为请求数组，$key为私钥
	public function getsign($arr){
		if(isset($arr['sign'])) unset($arr['sign']);
        ksort($arr);
		$arr['key'] = $this->mch_key;
        $requestString = $this->arrtouri($arr);
        $newSign = md5($requestString);
        return strtoupper($newSign);
    }

	//数组转URI
	function arrtouri($param){
		$str = '';
		foreach($param as $key => $value) {
			$str .= $key .'=' . $value . '&';
		}
		$str = substr($str,0,-1);
		return $str;
	}

	//获取IP
	public function get_ip(){    
		$ip = '';    
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){        
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];    
		}elseif(isset($_SERVER['HTTP_CLIENT_IP'])){        
			$ip = $_SERVER['HTTP_CLIENT_IP'];    
		}else{        
			$ip = $_SERVER['REMOTE_ADDR'];    
		}
		$ip_arr = explode(',', $ip);
		return $ip_arr[0];
	}

	//数组转XML
	public function arrtoxml($arr){
		$xml = '<xml>';
		foreach($arr as $k=>$v){
			$xml .= '<'.$k.'>'.$v.'</'.$k.'>';
		}
		$xml .= '</xml>';
        return $xml;
    }

	//XML转数组
	public function xmltoarr($xml){ 
		//禁止引用外部xml实体 
		libxml_disable_entity_loader(true); 
		$xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA); 
		$val = json_decode(json_encode($xmlstring),true); 
		return $val; 
	}

	//产生随机字符串，不长于32位
	public function getNonceStr($length = 32) 
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		} 
		return $str;
	}

	//获取远程内容
	function geturl($url,$post=''){
		// fopen模式
		if(function_exists('curl_init')){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			if(!empty($post)){
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			}
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			$data = curl_exec($ch);
			curl_close($ch);
		}
		return $data;
	}
}