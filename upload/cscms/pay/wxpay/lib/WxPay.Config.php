<?php
/**
* 	配置账号信息
*/

class WxPayConfig
{
	const APPID = CS_Wxpay_ID;
	const MCHID = CS_Wxpay_Mchid;
	const KEY = CS_Wxpay_Key;
	const APPSECRET = '';

	const SSLCERT_PATH = "../cert/apiclient_cert.pem";
	const SSLKEY_PATH = "../cert/apiclient_key.pem";
	
	const CURL_PROXY_HOST = "0.0.0.0";//"10.152.18.220";
	const CURL_PROXY_PORT = 0;//8080;

	const REPORT_LEVENL = 1;
}
