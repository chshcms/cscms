<?php
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-18
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 上传操作类
 */
class Csup {

    public function __construct (){
		error_reporting(0); //关闭错误
		set_time_limit(0); //超时时间
		require_once CSCMS.'sys/Cs_Qiniu.php';
	    require_once CSCMS.'sys/Cs_Oss.php';
	    require_once CSCMS.'sys/Cs_Upyun.php';
	}

    //上传方式
	public function init(){
        $str = array(
			'1'=>array(
			      'name'=>'本地空间',
			      'type'=>'web',
			      'file'=>''
		     ),
			'2'=>array(
			      'name'=>'远程FTP',
			      'type'=>'ftp',
			      'file'=>''
		     ),
			'3'=>array(
			      'name'=>'七牛网盘',
			      'type'=>'qiniu',
			      'file'=>'Cs_Qiniu.php'
		     ),
			'4'=>array(
			      'name'=>'阿里云存储',
			      'type'=>'oss',
			      'file'=>'Cs_Oss.php'
		     ),
			'5'=>array(
			      'name'=>'又拍云',
			      'type'=>'upyun',
			      'file'=>'Cs_Upyun.php'
		     )
		);
		return $str;
    }

    //判断上传方式
	public function up($file_path,$file_name=''){
		if(empty($file_path)) return false;
        $types=$this->init();
		if(UP_Mode>1){
			  $mode=$types[UP_Mode]['type']; //方法
              $res=$this->$mode($file_path,$file_name);
			  return $res;
        }else{
		      return true;
		}
    }

	//获取下载地址
	public function down($ids){
        if($ids==3){  //七牛网盘
			$linkurl = CS_Qn_Url;
			if($linkurl!=''){
                if(substr($linkurl,0,7)!='http://' && substr($linkurl,0,8)!='https://') $linkurl = "http://".$linkurl;
			}else{
                $linkurl = is_ssl().CS_Qn_Bk.".qiniudn.com/";
			}
        }elseif($ids==4){  //阿里云
              $linkurl = is_ssl().CS_Os_Bk.".oss-cn-hangzhou.aliyuncs.com/";
        }elseif($ids==5){  //又拍云
              $linkurl = CS_Upy_Url;
			  if(substr($linkurl,0,7)!='http://' && substr($linkurl,0,8)!='https://') $linkurl = "http://".$linkurl;
		}else{
              $linkurl="";
		}
        return $linkurl;
	}

	//修改配置文件
	public function edit($id){
        $str = $this->init();
		$files = $str[$id]['file'];
        if($id<3 || empty($files)) return false;
        $ci = &get_instance();
		//七牛存储
		if($id==3){ 
		    $CS_Qn_Bk = $ci->input->post('CS_Qn_Bk', TRUE);
		    $CS_Qn_Ak = $ci->input->post('CS_Qn_Ak', TRUE);
		    $CS_Qn_Sk = $ci->input->post('CS_Qn_Sk', TRUE);
		    $CS_Qn_Url = $ci->input->post('CS_Qn_Url', TRUE);
		    if (empty($CS_Qn_Bk)||empty($CS_Qn_Ak)||empty($CS_Qn_Sk)){
			    getjson('七牛空间名称、AccessKey、SecretKey都不能为空');
		    }
	        $strs="<?php"."\r\n";
	        $strs.="define('CS_Qn_Bk','".$CS_Qn_Bk."'); //空间名称 \r\n";
	        $strs.="define('CS_Qn_Ak','".$CS_Qn_Ak."'); //AK   \r\n";
	        $strs.="define('CS_Qn_Sk','".$CS_Qn_Sk."'); //SK  \r\n";
	        $strs.="define('CS_Qn_Url','".$CS_Qn_Url."'); //七牛下载地址  ";
		}

        //阿里云存储
	    if($id==4){
		    $CS_Os_Bk = $ci->input->post('CS_Os_Bk', TRUE);
		    $CS_Os_Ai = $ci->input->post('CS_Os_Ai', TRUE);
		    $CS_Os_Ak = $ci->input->post('CS_Os_Ak', TRUE);
		    if (empty($CS_Os_Bk)||empty($CS_Os_Ai)||empty($CS_Os_Ak)){
			      getjson('BUCKET、ACCESS_ID、ACCESS_KEY都不能为空');
		    }
	        $strs="<?php"."\r\n";
	        $strs.="define('CS_Os_Bk','".$CS_Os_Bk."'); //BK  \r\n";
	        $strs.="define('CS_Os_Ai','".$CS_Os_Ai."'); //AI   \r\n";
	        $strs.="define('CS_Os_Ak','".$CS_Os_Ak."'); //AK  ";
		}

        //又拍云存储
	    if($id==5){
		    $CS_Upy_Name = $ci->input->post('CS_Upy_Name', TRUE);
		    $CS_Upy_Pwd = $ci->input->post('CS_Upy_Pwd', TRUE);
		    $CS_Upy_Bucket = $ci->input->post('CS_Upy_Bucket', TRUE);
		    $CS_Upy_Url = $ci->input->post('CS_Upy_Url', TRUE);
		    if (empty($CS_Upy_Name) || empty($CS_Upy_Pwd) || empty($CS_Upy_Bucket) || empty($CS_Upy_Url)){
			    getjson('授权账号、授权密码、空间名、访问域名都不能为空');
		    }
			if($CS_Upy_Pwd==substr(CS_Upy_Pwd,0,3).'******'){
                  $CS_Upy_Pwd=CS_Upy_Pwd;
			}
	        $strs="<?php"."\r\n";
	        $strs.="define('CS_Upy_Name','".$CS_Upy_Name."'); //又拍云授权账号  \r\n";
	        $strs.="define('CS_Upy_Pwd','".$CS_Upy_Pwd."'); //又拍云授权密码   \r\n";
	        $strs.="define('CS_Upy_Bucket','".$CS_Upy_Bucket."'); //又拍云空间名 \r\n";
	        $strs.="define('CS_Upy_Url','".$CS_Upy_Url."'); //又拍云访问域名 ";
		}
		//修改配置文件
        write_file(CSCMS.'sys/'.$files, $strs);
		return true;
	}

    //判断删除方式
	public function del($file_path,$dir=''){
		if(empty($file_path)) return false;
		if(UP_Mode==2){ //FTP
            return $this->ftpdel($file_path);
		}elseif(UP_Mode==3){ //七牛
            return $this->qiniudel($file_path);
		}elseif(UP_Mode==4){ //阿里云
            return $this->ossdel($file_path);
		}elseif(UP_Mode==5){ //又拍云
            return $this->upyundel($file_path);
        }else{
			  if(substr($file_path,0,7)=='http://' || substr($file_path,0,8)=='https://'){
		          return true;
			  }
			  if(UP_Pan!=''){
			      $path = UP_Pan.$file_path;
			  }else{
				  $file_path = str_replace('attachment/'.$dir.'/', '', $file_path);
			      $path = FCPATH.'attachment/'.$dir.'/'.$file_path;
			  }
			  $path = str_replace("//","/",$path);
			  unlink($path);
		      return true;
		}
    }

    //FTP上传
	public function ftp($file_path,$file_name){
		$ci = &get_instance();
		$ci->load->library('ftp');
		if ($ci->ftp->connect(array(
				'port' => FTP_Port,
				'debug' => false,
				'passive' => FTP_Ive,
				'hostname' => FTP_Server,
				'username' => FTP_Name,
				'password' => FTP_Pass,
		))) { // 连接ftp成功
				$Dirs=FTP_Dir;
				if(substr($Dirs,-1)=='/') $Dirs=substr($Dirs,0,-1);
				$dir = $Dirs.'/'.date('Ymd').'/';
				$ci->ftp->mkdir($dir);
				if ($ci->ftp->upload($file_path, $dir.$file_name, SITE_ATTACH_MODE, 0775)) {
				     unlink($file_path);
				}else{
                     return false;
				}
				$ci->ftp->close();
		        return true;
		}
		return false;
    }

    //删除远程FTP文件
    public function ftpdel($file_path){
		$ci = &get_instance();
		$ci->load->library('ftp');
		if(substr($file_path,0,1)=='/') $file_path=substr($file_path,1);
		if ($ci->ftp->connect(array(
				'port' => FTP_Port,
				'debug' => FALSE,
				'passive' => FTP_Ive,
				'hostname' => FTP_Server,
				'username' => FTP_Name,
				'password' => FTP_Pass,
		))) { // 连接ftp成功
				$Dirs=FTP_Dir;
				if(substr($Dirs,-1)=='/') $Dirs=substr($Dirs,0,-1);
				$path = $Dirs.'/'.$file_path;
				$res=$ci->ftp->delete_file(".".$path);
				$ci->ftp->close();
                if (!$res) {  //失败
                    return FALSE;
                } else {  //成功
                    return TRUE;
                }
		}
    }

    //七牛上传
	public function qiniu($file_path,$file_name){
		require_once CSPATH.'uploads/qiniu/io.php';
		require_once CSPATH.'uploads/qiniu/rs.php';

		$key1 = date('Ymd').'/'.$file_name;
		Qiniu_SetKeys(CS_Qn_Ak, CS_Qn_Sk);
		$putPolicy = new Qiniu_RS_PutPolicy(CS_Qn_Bk);
		$upToken = $putPolicy->Token(null);
		$putExtra = new Qiniu_PutExtra();
		$putExtra->Crc32 = 1;
		list($ret, $err) = Qiniu_PutFile($upToken, $key1, $file_path, $putExtra);
		if ($err !== null) {
   		    return false;
		} else {
			unlink($file_path);
    		return true;
		}
    }

    //删除七牛文件
    public function qiniudel($file_path){
		require_once CSPATH.'uploads/qiniu/io.php';
		require_once CSPATH.'uploads/qiniu/rs.php';
		Qiniu_SetKeys(CS_Qn_Ak, CS_Qn_Sk);
        $client = new Qiniu_MacHttpClient(null);
		if(substr($file_path,0,1)=='/') $file_path=substr($file_path,1);
        $err = Qiniu_RS_Delete($client, CS_Qn_Bk, $file_path);
        if ($err !== null) {  //失败
            return FALSE;
        } else {  //成功
            return TRUE;
        }
    }

	//阿里云上传
	public function oss($file_path,$file_name){
		require_once CSPATH.'uploads/oss/sdk.class.php';
        $obj = new ALIOSS();
	    $dir=date('Ymd');
        $response  = $obj->create_object_dir(BUCKET,$dir);
		if ($response->status != '200') {
    		return false;
		}
        $object=date('Ymd').'/'.$file_name;
	    $response = $obj->upload_file_by_file(BUCKET,$object,$file_path);
		if ($response->status == 200) {
			unlink($file_path);
            return true;
		}else{
    		return false;
		}
    }

    //删除阿里云文件
    public function ossdel($file_path){
		require_once CSPATH.'uploads/oss/sdk.class.php';
		if(substr($file_path,0,1)=='/') $file_path=substr($file_path,1);
        $obj = new ALIOSS();
	    $response = $obj->delete_object(BUCKET,$file_path);
		if ($response->status == 200) {
            return true;
		}else{
    		return false;
		}
    }

	//又拍云上传
	public function upyun($file_path,$file_name){
		require_once CSPATH.'uploads/upyun/upyun.class.php';
        $upyun = new UpYun(CS_Upy_Bucket, CS_Upy_Name, CS_Upy_Pwd);
        $object='/'.date('Ymd').'/'.$file_name;
		$data = @file_get_contents($file_path);
        $response = $upyun->writeFile($object, $data, True);
		if ($response) {
			unlink($file_path);
            return true;
		}else{
    		return false;
		}
    }

    //删除又拍云文件
    public function upyundel($file_path){
		require_once CSPATH.'uploads/upyun/upyun.class.php';
        $upyun = new UpYun(CS_Upy_Bucket, CS_Upy_Name, CS_Upy_Pwd);
        $response = $upyun->delete($file_path);
		if ($response) {
            return true;
		}else{
    		return false;
		}
    }

    //阿里云表单上传获取签名
    public function osssign(){
	    $now = time();
	    $expire = 1800; //设置有效时间
	    $end = $now + $expire;
	    //生成到期时间
	    $dtStr = date("c", $end);
        $mydatetime = new DateTime($dtStr);
        $expiration = $mydatetime->format(DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos)."Z";
	    $condition = array(0=>'content-length-range', 1=>0, 2=>UP_Size*1024);
	    $conditions[] = $condition; 
	    $start = array(0=>'starts-with', 1=>CS_Os_Ak, 2=>'');
	    $conditions[] = $start; 
	    $arr = array('expiration'=>$expiration,'conditions'=>$conditions);

	    $policy = json_encode($arr);
	    $base64_policy = base64_encode($policy);
	    $string_to_sign = $base64_policy;
	    $signature = base64_encode(hash_hmac('sha1', $string_to_sign, CS_Os_Ak, true));

	    $response = array();
	    $response['host'] = is_ssl().CS_Os_Bk.'.oss-cn-hangzhou.aliyuncs.com';
	    $response['policy'] = $base64_policy;
	    $response['OSSAccessKeyId'] = CS_Os_Ai;
	    $response['signature'] = $signature;
	    $response['success_action_status'] = 200;
	    $response['callback'] = '';
	    return $response;
    }

    //七牛表单上传token
	public function qiniu_uptoken(){
		require_once CSPATH.'uploads/qiniu/io.php';
		require_once CSPATH.'uploads/qiniu/rs.php';
		Qiniu_SetKeys(CS_Qn_Ak, CS_Qn_Sk);
		$putPolicy = new Qiniu_RS_PutPolicy(CS_Qn_Bk);
		$upToken = $putPolicy->Token(null);
   		return $upToken;
    }
}