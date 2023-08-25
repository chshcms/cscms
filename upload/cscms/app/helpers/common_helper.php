<?php
/**
 * @Cscms open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-07-26
 */
/**
 * 全局通用函数
 */
//字符串截取函数
function str_substr($start, $end, $str, $sid=1){
    $temp = explode($start, $str, 2);
    $content = explode($end, $temp[1], 2);
    $str = $content[0];
	if($sid==2){
		$str = preg_replace("/<a[^>]+>(.+?)<\/a>/i","$1",$str);
		//过滤换行符
		$str = preg_replace("/ /","",$str);
		$str = preg_replace("/&nbsp;/","",$str);
		$str = preg_replace("/　/","",$str);
		$str = preg_replace("/\r\n/","",$str);
		$str = str_replace(chr(13),"",$str);
		$str = str_replace(chr(10),"",$str);
		$str = str_replace(chr(9),"",$str);
    }
    return $str;
}
//XML实体字符转换输出
function xml_string($str){
	$ci = &get_instance();
	$ci->load->helper('xml');
    return xml_convert($str);
}
//检测PHP设置参数
function show($varName){
	switch($result = get_cfg_var($varName)){
		case 0:return '<font color="red">×</font>';break;
		case 1:return '<font color=#0076AE>√</font>';break;
		default: return $result;break;
	}
}
//检测Mysql
function mydb(){
	$ci = &get_instance();
	if (!isset($ci->db)){
        $ci->load->database();
	}
    $getmysqlver=$ci->db->version();
    if(empty($getmysqlver)){
        return L('jiance_no');
    }
    $MysqlALL=explode(".",$getmysqlver);
    if($MysqlALL[0] < 5){
	    return $getmysqlver.'&nbsp;&nbsp;<i class="fa fa-close" style="color: red;"></i>';
    }else{
	    return $getmysqlver.'&nbsp;&nbsp;<i class="fa fa-check colorl"></i>';
    }

}
//检测PHP环境
function isfun($funName = ''){
    if (!$funName || trim($funName) == '' || preg_match('~[^a-z0-9\_]+~i', $funName, $tmp)) return L('error');
	return (false !== function_exists($funName)) ? '<font color=#0076AE>√</font>' : '<font color="red">×</font>';
}
//获取版块配置参数
function config($found='',$dir=''){
    $dir = empty($dir) ? PLUBPATH : $dir;
	if($dir=='sys'){
		if(!empty($found)){
			$res = null;
			if(defined($found)){
				$res = constant($found);
			}
			if(!$res){
				if($found=='Seo'){
					$res = array();
					$res['title'] = Web_Title;
					$res['keywords'] = Web_Keywords;
					$res['description'] = Web_Description;
				}
			}
			return $res;
		}else{
			return null;
		}
	}
    global $PLUBARR;
    if(!isset($PLUBARR[$dir])){
    	$site = require CSCMS.$dir.FGF.'site.php';
    	//写进全局变量
    	$GLOBALS['PLUBARR'][$dir] = $site;
    }else{
    	$site = $PLUBARR[$dir];
    }
	if(empty($found)) return $site;
	if(isset($site[$found])){
		return $site[$found];
	}else{
		return null;
	}
}
//加载语言
function L($key = '', $arr=array()){
	if($key == ''){
        return '';
	}else{
        $ci	= &get_instance();
		if(!empty($arr)){
            return vsprintf($ci->lang->line($key),$arr);
		}else{
            return $ci->lang->line($key);
		}
	}
}
//获取版块风格目录
function Skins_Dir($skins=null){
	if(defined('MOBILE') && Mobile_Is==1){ //手机
		if(defined('USERPATH')){ //会员
        	$TempImg = Web_Path.'tpl/mobile/user/'.Mobile_User_Dir.'/';
		}elseif(defined('HOMEPATH')){ //空间
			if($skins){
        		$TempImg = Web_Path.'tpl/mobile/home/'.$skins.'/';
			}else{
        		$TempImg = Web_Path.'tpl/mobile/home/'.Mobile_Home_Dir.'/';
			}
		}else{
        	$TempImg = Web_Path.'tpl/mobile/skins/'.Mobile_Skins_Dir.'/';
		}
	}else{
		if(defined('USERPATH')){ //会员
        	$TempImg = Web_Path.'tpl/pc/user/'.Pc_User_Dir.'/';
		}elseif(defined('HOMEPATH')){ //空间
			if($skins){
        		$TempImg = Web_Path.'tpl/pc/home/'.$skins.'/';
			}else{
        		$TempImg = Web_Path.'tpl/pc/home/'.Pc_Home_Dir.'/';
			}
		}else{
        	$TempImg = Web_Path.'tpl/pc/skins/'.Pc_Skins_Dir.'/';
		}
    }
    $TempImg = str_replace('//','/',$TempImg);
    $end = strrpos(substr($TempImg,0,strlen($TempImg)-1),'/')+1;
    return substr(substr($TempImg,0,strlen($TempImg)-1),0,$end);
}
//获取任意字段信息
function getzd($table,$ziduan,$id,$cha='id'){
	global $CSZDY;
	if(!isset($CSZDY[$table][$ziduan][$id])){
		$zdy = null;
		$ci = &get_instance();
		if (!isset($ci->db)){
	        $ci->load->database();
		}
	    if($table && $ziduan && $id){
		    $ci->db->where($cha,$id);
		   	$ci->db->select($ziduan);
		   	$row=$ci->db->get($table)->row();
			if($row){
				$zdy = $row->$ziduan;
			}else{
				return null;	
			}
		}
		$GLOBALS['CSZDY'][$table][$ziduan][$id] = $zdy;
	}else{
		$zdy = $CSZDY[$table][$ziduan][$id];
	}
	return $zdy;
}
//判断是否关注
function getgz($uid=0){
	$ci = &get_instance();
	if (!isset($ci->db)){
        $ci->load->database();
	}
	if((int)$uid==0){
		return 0;
	}
	$guanzu=0;
    if(isset($_SESSION['cscms__id'])){
        $count_sql ="SELECT count(*) as count FROM ".CS_SqlPrefix."friend where uidb=".$uid." and uida=".$_SESSION['cscms__id']."";
	    $query = $ci->db->query($count_sql)->result_array();
	    $guanzu = $query[0]['count'];
	}
	return $guanzu;
}
//获取会员等级
function getlevel($jinyans=0,$type=0){
	$ci = &get_instance();
	if (!isset($ci->db)){
       $ci->load->database();
	}
	$level = 1;
	$name = '初级';
	$xid = 0;
	$jinyan = 0;
	$stars = 1;
	$row=$ci->db->query("SELECT * FROM ".CS_SqlPrefix."userlevel where jinyan<".intval($jinyans+1)." order by xid desc")->row();
	if($row){
		$level = $row->xid; //等级ID
		$name = $row->name; //等级名称
		$jinyan = $row->jinyan; //等级经验
		$xid = $row->id;   //当前等级ID
		$stars = $row->stars; //星星数量
	}
	if($type==0){
		return $level;	 //会员等级
	}
	if($type==1){
		return get_stars($stars,$level,$name);	//显示星星
	}
	$xjinyan=$jinyan;
	$rowx=$ci->db->query("SELECT jinyan FROM ".CS_SqlPrefix."userlevel where id>".$xid." order by xid asc")->row();
	if($rowx){
		$xjinyan=$rowx->jinyan;
	}
	if($type==2){
		return $xjinyan;	//下个级别需要经验
	}
	if($type==3){
		return ($xjinyan-$jinyans);	//下个级别剩余经验
	}
	if($type==4){
		return number_format($jinyans/$xjinyan*100,2);	//下个级别百分比
	}
	if($type==5){
		return $name;	//等级名称
	}
}
//替换静态生成标签
function str_replace_dir($url,$id,$sname,$sort,$page,$dir='',$fid='lists'){
	if(empty($sort)) $sort='id';
	if(strpos($url,'{sname}') !== FALSE && !empty($dir)){
	     $ci = &get_instance();
		 $ci->load->library('pinyin');
         if(empty($sname) || $sname=='null'){
			 if($fid=='topic/show' || ($fid=='topic' && $sort=='show')){
			     $table=$dir.'_topic';
				 $field='name';
			 }else{
			     $table=($fid=='lists') ? $dir.'_list' : $dir;
				 $field=($fid=='lists') ? 'bname' : 'name';
			 }
		     if($ci->db->table_exists(CS_SqlPrefix.$table) && $ci->db->field_exists($field, CS_SqlPrefix.$table)){
                 $sname=getzd($table,$field,$id);
			 }
		 }
		 $sname=$ci->pinyin->result($sname);
	}
	$old = array('{id}','{sname}','{pinyinid}','{sort}','{md5id}','{page}','{zu}','{ji}');
	$new = array($id,$sname,topinyin($id),$sort,substr(md5($id),0,16),$page,0,0);
	return str_replace($old,$new,$url);
}
//获取主域名
function host_ym($k=0) {
    $host=$_SERVER['HTTP_HOST'];
    preg_match('/[\w][\w-]*\.(?:com\.cn|gov\.cn|cn\.com|org\.cn|net\.cn)(\/|$)/isU', $host, $domain);
    $ym = rtrim(@$domain[0], '/');
	$ips = explode(':',$host);
    if(empty($ym) && $host!='localhost' && !preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/",$ips[0])){
           $ymarr=explode('.',$host);
           $nums=count($ymarr);
           $ym=$ymarr[$nums-2].'.'.$ymarr[$nums-1];
	}
	if(empty($ym)){
		$ym = $ips[0];
	}else{
   	    $ym = $k==0 ? '.'.$ym : $ym;
	}
    return $ym;
}
//获取后缀名称归类
function get_extpic($ext) {
    $ext=strtolower($ext);
    if($ext=='php' || $ext=='asp' || $ext=='css' || $ext=='js' || $ext=='jsp' || $ext=='tpl') {
        $pic = 'php';
    }elseif($ext=='jpg' || $ext=='png' || $ext=='gif' || $ext=='bmp' || $ext=='jpge') {
        $pic = 'pic';
    }elseif($ext=='html' || $ext=='htm' || $ext=='shtm' || $ext=='shtml') {
        $pic = 'html';
    }elseif($ext=='mp3' || $ext=='wma' || $ext=='m4a') {
        $pic = 'mp3';
    }elseif($ext=='mp4' || $ext=='wmv' || $ext=='flv' || $ext=='wav' || $ext=='avi') {
        $pic = 'mp4';
    }elseif($ext=='rar' || $ext=='zip' || $ext=='7z') {
        $pic = 'rar';
	}else{
        $pic = 'no';
    }
	return $pic;
}
//截取字符串的函数
function sub_str($str, $length, $start=0, $suffix="...", $charset="utf-8"){
	$str=str_checkhtml($str);
	if(($length+2) >= strlen($str)){
		return $str;
	}
	if(function_exists("mb_substr")){
		return mb_substr($str, $start, $length, $charset).$suffix;
	}elseif(function_exists('iconv_substr')){
		return iconv_substr($str,$start,$length,$charset).$suffix;
	}
	$re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
	$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
	$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
	$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
	preg_match_all($re[$charset], $str, $match);
	$slice = join("",array_slice($match[0], $start, $length));
	return $slice.$suffix;
}
//写文件
function write_file($path, $data, $mode = FOPEN_WRITE_CREATE_DESTRUCTIVE){
	$dir = dirname($path);
	if(!is_dir($dir)){
		mkdirss($dir);
	}
	if ( ! $fp = @fopen($path, $mode)){
		return FALSE;
	}
	flock($fp, LOCK_EX);
	fwrite($fp, $data);
	flock($fp, LOCK_UN);
	fclose($fp);
	return TRUE;
}
//递归创建文件夹
function mkdirss($dir) {
    if (!$dir) {
        return FALSE;
    }
    if (!is_dir($dir)) {
        mkdirss(dirname($dir));
        if (!file_exists($dir)) {
            mkdir($dir, 0777);
        }
    }
    return true;
}
//时间格式转换
function datetime($TimeTime){
	$limit=time()-$TimeTime;
	if ($limit <5) {$show_t = L('time_01');}
	if ($limit >= 5 and $limit <60) {$show_t = $limit.L('time_02');}
	if ($limit >= 60 and $limit <3600) {$show_t = sprintf("%01.0f",$limit/60).L('time_03');}
	if ($limit >= 3600 and $limit <86400) {$show_t = sprintf("%01.0f",$limit/3600).L('time_04');}
	if ($limit >= 86400 and $limit <2592000) {$show_t = sprintf("%01.0f",$limit/86400).L('time_05');}
	if ($limit >= 2592000 and $limit <31104000) {$show_t = sprintf("%01.0f",$limit/2592000).L('time_06');}
	if ($limit >= 31104000) {$show_t = L('time_07');}
	return $show_t;
}
//Base64加密
function cs_base64_encode($string) {
    $data = base64_encode($string);
    $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
    return $data;
}
//Base64解密
function cs_base64_decode($string) {
    $data = str_replace(array('-', '_'), array('+', '/'), $string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data.= substr('====', $mod4);
    }
    return base64_decode($data);
}
//数组实列化
function arraystring($data) {
    return $data ? addslashes(serialize($data)) : '';
}
//实列化数组
function unarraystring($data) {
	if(empty($data)) return '';
    return unserialize(stripslashes($data));
}
//HTML转字符
function str_encode($str){
	if(is_array($str)) {
		foreach($str as $k => $v) {
			$str[$k] = str_encode($v); 
		}
	}else{
		if(is_string($str)){
			$str=str_replace("<","&lt;",$str);
			$str=str_replace(">","&gt;",$str);
			$str=str_replace("\"","&quot;",$str);
			$str=str_replace("'",'&#039;',$str);
			$str=str_replace('{','&#123;',$str);
			$str=str_replace('}','&#125;',$str);
			$str=str_replace("$","&#36;",$str);
			$str=str_replace("(","&#40;",$str);
			$str=str_replace(")","&#41;",$str);
			if(!defined('IS_ADMIN')){
				$str=str_replace("cscmsphp",'',$str);
			}
		}
	}
	return $str;
}
//字符转HTML
function str_decode($str){
	if(is_array($str)) {
		foreach($str as $k => $v) {
			$str[$k] = str_decode($v); 
		}
	}else{
		if(is_string($str)){
			$str=str_replace("&lt;","<",$str);
			$str=str_replace("&gt;",">",$str);
			$str=str_replace("&quot;","\"",$str);
			$str=str_replace("&#039;","'",$str);
			$str=str_replace('&#123;','{',$str);
			$str=str_replace('&#125;','}',$str);
			$str=str_replace("&#36;","$",$str);
			$str=str_replace("&#40;","(",$str);
			$str=str_replace("&#41;",")",$str);
		}
	}
	return $str;
}
//SQL过滤
function safe_replace($string){
	if(is_array($string)) {
		foreach($string as $k => $v) {
			$string[$k] = safe_replace($v); 
		}
	}else{
		if(is_string($string)){
		    $string = str_replace('%20','',$string);
		    $string = str_replace('%27','',$string);
		    $string = str_replace('%2527','',$string);
			$string = str_replace("'",'&#039;',$string);
		    $string = str_replace('"','&quot;',$string);
		    $string = str_replace(';','',$string);
		    $string = str_replace('*','',$string);
		    $string = str_replace('<','&lt;',$string);
		    $string = str_replace('>','&gt;',$string);
		    $string = str_replace('\\','',$string);
		    $string = str_replace('%','\%',$string);
		    $string = str_replace('{','%7b',$string);
		    $string = str_replace('}','%7d',$string);
		}else{
		    if(preg_match('/^\d{1,9}$/',$string)) $string = (int)$string;
		}
	}
	return $string;
}
//屏蔽所有html
function str_checkhtml($str,$sql=0) {
	if(is_array($str)) {
		foreach($str as $k => $v) {
			$str[$k] = str_checkhtml($v); 
		}
	}else{
		$str = preg_replace("/\s+/"," ", $str);
		$str = preg_replace("/&nbsp;/","",$str);
		$str = preg_replace("/\r\n/","",$str);
		$str = preg_replace("/\n/","",$str);
		$str = str_replace(chr(13),"",$str);
		$str = str_replace(chr(10),"",$str);
		$str = str_replace(chr(9),"",$str);
		$str = strip_tags($str);
		$str = str_encode($str);
	}
	if($sql==1){
		$str = safe_replace($str);
	}
	return $str;
}
//xss过滤函数
function remove_xss($val) { 
	$val = str_replace('cscmsphp','',$val);
	$ci = &get_instance();
	$vaule = $ci->security->xss_clean($val);
	return $vaule; 
}
//检查密码长度是否符合规定
function is_userpass($password) {
	$strlen = strlen($password);
	if($strlen >= 6 && $strlen <= 20) return true;
	return false;
}
//检测输入中是否含有错误字符
function is_badword($string) {
	$badwords = array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n","#");
	foreach($badwords as $value){
		if(strpos($string, $value) !== FALSE) {
			return TRUE;
		}
	}
	return FALSE;
}
//判断用户名格式是否正确
function is_username($username,$s=0) {
	$strlen = strlen($username);
    if($s==0 && User_RegZw==0 && preg_match("/[\x7f-\xff]/", $username)) {
		return false;
	} elseif (is_badword($username) || !preg_match("/^[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/", $username)){
		return false;
	} elseif ( 20 < $strlen || $strlen < 2 ) {
		return false;
	}
	return true;
}
//判断email格式是否正确
function is_email($email) {
	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}
//判断手机号码格式是否正确
function is_tel($tel) {
	return preg_match("/^1[3|4|5|7|8][0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$/", $tel);
}
//判断QQ号码格式是否正确
function is_qq($qq) {
    return preg_match('/^[1-9][0-9]{4,12}$/', $qq);
}
//编码转换
function get_bm($string,$s1='gbk',$s2='utf-8') {
    if(is_array($string)) {
        foreach($string as $k => $v) { 
            $string[$k] = get_bm($v,$s1,$s2); 
        } 
	}else{
         if(!(strtolower($s2)=='utf-8' && is_utf8($string))){
	     	if(function_exists("mb_convert_encoding")){
				$string = mb_convert_encoding($string, $s2, $s1);
            }else{
                $string = iconv($s1, $s2, $string);
            }
	    } 
		if(preg_match('/^\d{1,9}$/',$string)) $string = (int)$string;
	}
    return $string;
}
//urlencode解码
function rurlencode($string) {
	$key=rawurldecode($string);
	if(!is_utf8($key)){
		$key = get_bm($key,'gbk', 'utf-8');
	}
	return $key;
}
//判断字符是否是UTF-8
function is_utf8($text) { 
	$e = mb_detect_encoding($text, array('UTF-8', 'GBK'));
	if($e=='UTF-8'){
		return true;
	} else { 
	    return false;
	}
}
//escape编码
function escape($string, $in_encoding = 'UTF-8',$out_encoding = 'UCS-2') { 
    $return = ''; 
    if (function_exists('mb_get_info')) { 
        for($x = 0; $x < mb_strlen ( $string, $in_encoding ); $x ++) { 
            $str = mb_substr ( $string, $x, 1, $in_encoding ); 
            if (strlen ( $str ) > 1) { // 多字节字符 
                $return .= '%u' . strtoupper ( bin2hex ( mb_convert_encoding ( $str, $out_encoding, $in_encoding ) ) ); 
            } else { 
                $return .= '%' . strtoupper ( bin2hex ( $str ) ); 
            } 
        } 
    } 
    return $return; 
}
//escape编码解析
function unescape($str) { 
    $ret = ''; 
    $len = strlen($str); 
    for ($i = 0; $i < $len; $i ++) { 
        if ($str[$i] == '%' && $str[$i + 1] == 'u') { 
            $val = hexdec(substr($str, $i + 2, 4)); 
            if ($val < 0x7f) 
                $ret .= chr($val); 
            else  
                if ($val < 0x800) 
                    $ret .= chr(0xc0 | ($val >> 6)) . 
                     chr(0x80 | ($val & 0x3f)); 
                else 
                    $ret .= chr(0xe0 | ($val >> 12)) . 
                     chr(0x80 | (($val >> 6) & 0x3f)) . 
                     chr(0x80 | ($val & 0x3f)); 
            $i += 5; 
        } else  
            if ($str[$i] == '%') { 
                $ret .= urldecode(substr($str, $i, 3)); 
                $i += 2; 
            } else 
                $ret .= $str[$i]; 
    } 
    return $ret; 
}
//字符加密、解密
function sys_auth($string,$operation,$key=''){
	$key=($key=='')?CS_Encryption_Key:$key;
	$key=md5($key);
	$key_length=strlen($key);
	$string=$operation=='D'?base64_decode(str_replace('-','/',str_replace('_','+',$string))):substr(md5($string.$key),0,8).$string;
	$string_length=strlen($string);
	$rndkey=$box=array();
	$result='';
	for($i=0;$i<=255;$i++){
       $rndkey[$i]=ord($key[$i%$key_length]);
       $box[$i]=$i;
	}
	for($j=$i=0;$i<256;$i++){
       $j=($j+$box[$i]+$rndkey[$i])%256;
       $tmp=$box[$i];
       $box[$i]=$box[$j];
       $box[$j]=$tmp;
	}
	for($a=$j=$i=0;$i<$string_length;$i++){
       $a=($a+1)%256;
       $j=($j+$box[$a])%256;
       $tmp=$box[$a];
       $box[$a]=$box[$j];
       $box[$j]=$tmp;
       $result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
	}
	if($operation=='D'){
	   if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8)){
	        return substr($result,8);
	   }else{
	        return'';
	   }
	}else{
	   return str_replace('+','_',str_replace('=','',base64_encode($result)));
	}
}
//随机颜色
function random_color() {
    $str = '#';
    for ($i = 0; $i < 6; $i++) {
        $randNum = rand(0, 15);
        switch ($randNum) {
            case 10: $randNum = 'A'; break;
            case 11: $randNum = 'B'; break;
            case 12: $randNum = 'C'; break;
            case 13: $randNum = 'D'; break;
            case 14: $randNum = 'E'; break;
            case 15: $randNum = 'F'; break;
        }
        $str.= $randNum;
    }
    return $str;
}
//表情转换
function facehtml($str)  {  
	if (empty($str)) return false;  
	for($i=1;$i<=56;$i++){
	     $str = str_replace( '[em:'.$i.']', "<img align='absmiddle' src=".Web_Path."packs/images/faces/e".$i.".gif border=0>", $str); 
	}
	return $str;
}
//大小转换
function formatsize($size, $dec=2){
	$a = array("B", "KB", "MB", "GB", "TB", "PB");
	$pos = 0;
	while ($size >= 1024) {
		$size /= 1024;
		$pos++;
	}
	return round($size,$dec)." ".$a[$pos];
}
//判断权限
function getqx($value,$perm,$sid=0){
	if(empty($perm)){
	    return "no";
	}else{
		$permarr=explode(',',$perm);
		for($i=0;$i<count($permarr);$i++){
		    if($sid>0){
		        if(strpos($value,$permarr[$i]) !== FALSE){
					return "ok";
		        }
		    }else{
		        if($permarr[$i]==$value){
					return "ok";
		        }
		    }
		}
	}
	return "no";
}
//获取IP
function getip(){ 
	$ci = &get_instance();
	$ip = $ci->input->ip_address();
	if(preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/",$ip)){
	    return $ip; 
	}else{
	    return null;
	}
} 
//邮件消息标签替换
function getmsgto($str,$all){ 
	foreach ($all as $key => $value) {
	    if($key=='username'){
	        $str = str_replace("{cscms:user}", $value, $str);
	    }
	    if($key=='url'){
	        $str = str_replace("{cscms:url}", $value, $str); 
	    }
	}
	$str = str_replace("{cscms:webname}", Web_Name, $str); 
	$str = str_replace("{cscms:time}", date('Y-m-d H:i:s'), $str); 
	return str_decode($str); 
} 
//通过key查找数组的value
function arr_key_value($arr,$key){
	if(is_array($arr)){
		foreach ($arr as $keys => $value) {
		    if($key==$keys){
		        return $value;
		    }
		}
	}
	return null;
}
//写入新数组到文件
function arr_file_edit($arr,$file=''){
	if($file=='') $file=CSCMS.'sys/Cs_Domain.php';
	if(is_array($arr)){
	    $con = var_export($arr,true);
	} else{
	    $con = $arr;
	}
	$strs="<?php if (!defined('FCPATH')) exit('No direct script access allowed');".PHP_EOL;
	$strs.="return $con;";
	$strs.="?>";
	return write_file($file, $strs);
}
//加入全局核心JS
function cs_addjs($Mark_Text){
	if(strpos($Mark_Text,'</title>') !== FALSE && strpos($Mark_Text,'<html mip>') === FALSE){
	    $view = explode('</title>',$Mark_Text); 
	    $Mark_Text=$view[0]."</title>\r\n<script type='text/javascript'>var cscms_path='".is_ssl().Web_Url.Web_Path."';</script>\r\n<link rel='stylesheet' href='".Web_Path."packs/layui/css/layui.css?v=2.0'>\r\n<script type='text/javascript' src='".Web_Path."packs/layui/layui.js?v=2.0'></script>\r\n<script type='text/javascript' src='".Web_Path."packs/js/jquery.min.js'></script>\r\n<script type='text/javascript' src='".Web_Path."packs/js/cscms.js?v=2.1'></script>";
	    if(count($view)>1) $Mark_Text.=$view[1];
	}
	return $Mark_Text;
}
//给模板加入增加人气JS
function hits_js($Mark_Text,$jslink){
	if(strpos($Mark_Text,'<html mip>') !== FALSE) return $Mark_Text;
	$js="<script type='text/javascript'>cscms.inc_js('".$jslink."');</script>";
	if(strpos($Mark_Text,'</body>') !== FALSE){
		$view = explode('</body>',$Mark_Text); 
		$Mark_Text=$view[0].$js."\r\n</body>";
		if(count($view)>1) $Mark_Text.=$view[1];
	}else{
	   	$Mark_Text.=$js;
	}
	return $Mark_Text;
}
//数字转拼音
function topinyin($str) {
	$str=str_replace("0","ling",$str);
	$str=str_replace("1","yi",$str);
	$str=str_replace("2","er",$str);
	$str=str_replace("3","san",$str);
	$str=str_replace("4","si",$str);
	$str=str_replace("5","wu",$str);
	$str=str_replace("6","liu",$str);
	$str=str_replace("7","qi",$str);
	$str=str_replace("8","ba",$str);
	$str=str_replace("9","jiu",$str);
	return $str;	
}
//获取远程内容
function htmlall($url,$codes='utf-8'){
	if(empty($url)) return null;
	 // curl模式
	if (function_exists('curl_init') && function_exists('curl_exec')){
		$curl = curl_init(); //初始化curl
		curl_setopt($curl, CURLOPT_URL, $url); //设置访问的网站地址
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); //模拟用户使用的浏览器
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1);    //自动设置来路信息
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);      //设置超时限制防止死循环
		curl_setopt($curl, CURLOPT_HEADER, 0);         //显示返回的header区域内容
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); //获取的信息以文件流的形式返回
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); 
		$data = curl_exec($curl);
		curl_close($curl);
	}else{
	    $data = @file_get_contents($url);
	}
	if(strtolower($codes)=='gbk'){
	    $data = get_bm($data);
	}
	$data=str_replace('</textarea>','&lt;/textarea&gt;',$data);
	return $data;
}
// HTML转JS  
function htmltojs($str){
    $re='';
    $str=str_replace('\\','\\\\',$str);
    $str=str_replace("'","\'",$str);
    $str=str_replace('"','\"',$str);
    $str=str_replace('\t','',$str);
    $str= explode("\r\n",$str);
    for($i=0;$i<count($str);$i++){
        $re.="document.writeln(\"".$str[$i]."\");\r\n";
    }
    return $re;
}
//删除目录和文件
function deldir($dir,$sid='ok') {
	//先删除目录下的文件：
	if(!is_dir($dir)){
	  return true;
	}
	$dh=opendir($dir);
	while ($file=readdir($dh)) {
		if($file!="." && $file!="..") {
			$fullpath=$dir."/".$file;
		 	if(!is_dir($fullpath)) {
		     	@unlink($fullpath);
		 	} else {
		     	deldir($fullpath);
		 	}
		}
	}
	closedir($dh);
	//删除当前文件夹：
	if($sid=='ok'){
		if(@rmdir($dir)) {
		    return true;
		}else{
		    return false;
		}
	}else{
		return true;
	}
}
//获取当前目录总大小
function getdirsize($dir){ 
    $handle = opendir($dir);
    $sizeResult=0;
    while (false!==($FolderOrFile = readdir($handle))){ 
        if($FolderOrFile != "." && $FolderOrFile != ".."){ 
            if(is_dir("$dir/$FolderOrFile")){ 
                $sizeResult += getDirSize("$dir/$FolderOrFile"); 
            }else{ 
                $sizeResult += filesize("$dir/$FolderOrFile"); 
            }
        }    
    }
    closedir($handle);
    return $sizeResult;
}
//星星数量转图标
function get_stars($num, $level=0, $name='', $starthreshold = 4) {
    $str = '';
    $alt = ($level>0)?'title="当前等级: Lv.' . $level . '('.$name.')"':'';
    for ($i = 3; $i > 0; $i--) {
        $numlevel = intval($num / pow($starthreshold, ($i - 1)));
        $num = ($num % pow($starthreshold, ($i - 1)));
        for ($j = 0; $j < $numlevel; $j++) {
            $str.= '<img align="absmiddle" src="'.Web_Path.'packs/images/stars'.$i.'.gif" ' . $alt . ' />';
        }
    }
    return $str;
}
//评论、留言屏蔽关键字
function filter($str){
	$KeyArr=explode(',',Pl_Str);
	for($i=0;$i<count($KeyArr);$i++){
	    $str=str_replace($KeyArr[$i],"**",$str);
	}
	return $str;
}
//中文分词
function gettag($title,$content=''){
	return '';
	$content = sub_str($content,200);
	$url = 'http://api.pullword.com/get.php?source='.urlencode($title.$content).'&param1=1&param2=0';
	$text = trim(file_get_contents($url));
	$text = str_replace("\r", "",$text);
	$arr = array_filter(explode("\n", $text));
	$len = count($arr) >10 ? 10 : count($arr);
	$tags = array();
	for($i=0;$i<$len;$i++){
		if(!empty($arr[$i]) && $arr[$i]!='error'){
			$tags[] = $arr[$i];
		}
	}
	if(!empty($tags)){
		return get_bm(implode(',',$tags));
	}else{
		return '';
	}
}
//评论调用
function get_pl($dir, $did=0, $cid=0){
	$cscms_pl="<div id='cscms_pl'><div class='cscms_txt'><img src='".Web_Path."packs/images/load.gif'>&nbsp;&nbsp;加载评论内容,请稍等......</div></div>\r\n<script type='text/javascript'>var dir='".$dir."';var did=".$did.";var cid=".$cid.";cscms.pl(1,0,0);</script>";
	return $cscms_pl;
}
//留言调用
function get_gbook($uid=0){
	$cscms_gbook="<div id='cscms_gbook'><div class='cscms_txt'><img src='".Web_Path."packs/images/load.gif'>&nbsp;&nbsp;加载留言内容,请稍等......</div></div>\r\n<script type='text/javascript'>var uid=".$uid.";cscms.home_gbook(1);</script>";
	return $cscms_gbook;
}
//Token令牌
function get_token($name='token',$s=0,$time=3600){
	$ci = &get_instance();
	if($s==0){ //写入
		$ci->load->helper('string');
		$token=random_string('alnum',10);
		$ci->cookie->set_cookie($name,$token,time()+$time);
		return $token;
	}elseif($s==2){ //删除
		$ci->cookie->set_cookie($name);
		return true;
	}else{ //判断
		$token=$ci->cookie->get_cookie($name);
		if(empty($token) || $token!=$time){
		    return false;
		}else{
		    return true;
		}
	}
}
//获取多个分类ID  如 cid=1,2,3,4,5,6
function getChild($CID,$type='',$zd='fid'){
	if(empty($type)) $type = PLUBPATH.'_list';
	$ci = &get_instance();
	if (!isset($ci->db)){
		$ci->load->database();
	}
	if(!empty($CID)){
	    $ClassArr = explode(',',$CID);
	    for($i=0;$i<count($ClassArr);$i++){
			$sql="select id from ".CS_SqlPrefix.$type." where ".$zd."='$ClassArr[$i]'";
			$result=$ci->db->query($sql)->result();
			if(!empty($result)){
                foreach ($result as $row) {
                	$ClassArr[]=$row->id;
                }
	        }
	  		$CID = implode(',',$ClassArr);
		}
	}
	return $CID;
}
//获取数据总数
function getcount($table='',$day=0){
	if(empty($type)) $type = PLUBPATH;
	$ci = &get_instance();
	if (!isset($ci->db)){
		$ci->load->database();
	}
	$sql="select count(*) as count from ".CS_SqlPrefix.$table;
	$where = array();
	//查询时间
	if($day>0){
		$day--;
		$time = strtotime(date("Y-m-d 0:0:0")) - $day*86400;
		$where[] = 'addtime>'.$time;
	}
	if(!empty($where)) $sql.=' where '.implode(' and ',$where);
	$row = $ci->db->query($sql)->row_array();
	$nums = !$row ? 0 : $row['count'];
	return $nums;
}

//标签解析
function tagslink($Search_Key,$fid='tags'){
	$dir = PLUBPATH == 'sys' ? 'dance' : PLUBPATH;
	$Search_link = is_ssl().Web_Url.Web_Path.'index.php/'.$dir.'/'; //板块地址
    $Search_List=$Search_Key1="";
    $Search_Key=trim($Search_Key);
    $Str=" @,@，@|@/@_";
    $StrArr=explode('@',$Str);
    for($i=0;$i<=5;$i++){
        if(stristr($Search_Key,$StrArr[$i])){
            $Search_Key1 = explode($StrArr[$i],$Search_Key);
        }
	}
    if(is_array($Search_Key1)){
        for($j=0;$j<count($Search_Key1);$j++){
            $Search_List.="<a target=\"tags\" href=\"".$Search_link."search?".$fid."=".urlencode($Search_Key1[$j])."\">".$Search_Key1[$j]."</a> ";
		}
	}else{
        $Search_List="<a target=\"tags\" href=\"".$Search_link."search?".$fid."=".urlencode($Search_Key)."\">".$Search_Key."</a> ";
	}
	//开启二级域名
	$Ym_Mode = config('Ym_Mode',$dir); //二级域名状态
	$Ym_Url = config('Ym_Url',$dir);   //二级域名地址
	$Web_Mode = config('Web_Mode',$dir);   //版块运行模式
	if($Ym_Mode==1){
		if($Web_Mode==2){
	        $Search_List  = str_replace(Web_Url.Web_Path."index.php/".PLUBPATH."/",$Ym_Url.Web_Path,$Search_List);
		}else{
	        $Search_List  = str_replace(Web_Url.Web_Path."index.php/".PLUBPATH."/",$Ym_Url.Web_Path."index.php/",$Search_List);
		}
	}
	if($Web_Mode==2){
        $Search_List  = str_replace("index.php/","",$Search_List);
	}
    return $Search_List;
}
//判断当前是否为ssl运行模式
function is_ssl(){   
	if(Is_Ssl === 1){  //后台设置
		return 'https://';  
	}else{  
		if(!is_https()){
			return 'http://';
		}else{
			return 'https://';
		}  
	}
} 
//JSON输出
function getjson($info,$error=1,$sign=0,$callback=''){
	$msg = $info;
	$data['error'] = $error;
	$data['info'] = $info;
	//兼容前台
	$data['msg'] = $msg;
	if($sign==1 && is_array($msg)){
		$data = array_merge($msg);
	}
	$json  = json_encode($data);
	if(!empty($callback)){
		echo $callback."(".$json.")";
	}else{
		echo $json;
	}
	exit;
}
//获取自定义字段信息
function opt_field($dir,$opt,$table){
	if (is_file(CSCMS.'sys/Cs_Field.php')) {
		$field = require(CSCMS.'sys/Cs_Field.php');
		if(isset($field[$dir])){
			$str = '';$fid = 4;$tid=1;$gctime=1;$upid=5;
			foreach ($field[$dir] as $key => $value) {
				if(defined('IS_ADMIN')){
					$display1 = 'inline';
					$display2 = '';
					$ifelse = $value['table']==$table && $value['status']==1;
				}else{
					$display1 = 'block';
					$display2 = 'display:none';
					$ifelse = $value['table']==$table && $value['status']==1 && $value['qiantai']==1;
				}
				if($ifelse){
					switch ($value['leix']) {
						case 'number':
							$str .= '<div class="layui-form-item"><label class="layui-form-label">'.$value['note'].'</label><div class="layui-input-'.$display1.'"><input type="text" name="'.$value['zd'].'" value="'.$opt[$value['zd']].'" class="layui-input"></div><div style="'.$display2.'" class="layui-form-mid layui-word-aux">'.$value['notice'].'</div></div>';
							break;
						case 'text':
							if($value['attr']==0){
								$pass = ($value['pass']==1)?'password':'text';
								$str .= '<div class="layui-form-item"><label class="layui-form-label">'.$value['note'].'</label><div class="layui-input-'.$display1.'"><input type="'.$pass.'" name="'.$value['zd'].'" value="'.$opt[$value['zd']].'" class="layui-input"></div><div style="'.$display2.'" class="layui-form-mid layui-word-aux">'.$value['notice'].'</div></div>';
							}elseif ($value['attr']==1) {
								$str .= '<div class="layui-form-item layui-form-text"><label class="layui-form-label">'.$value['note'].'</label><div class="layui-input-block"><textarea name="'.$value['zd'].'" style="height: 100px" class="layui-textarea">'.$opt[$value['zd']].'</textarea></div></div>';
							}else{
								$str .= '<div class="layui-form-item layui-form-text">
	                            <label class="layui-form-label">'.$value['note'].'</label>
	                            <div class="layui-input-block"><textarea name="'.$value['zd'].'" lay-verify="content" id="gc'.$gctime.'" placeholder="'.$value['notice'].'" style="display: none;" class="layui-textarea">'.$opt[$value['zd']].'</textarea>
	                            </div>
	                        </div>';
								$str .= '<script>var gc'.$gctime.'_name=\''.$value['zd'].'\';layui.use([\'layedit\',\'form\'], function(){var layedit = layui.layedit;var form = layui.form;layedit.set({uploadImage: {elem:\'Filedata\',url: \''.Web_Path.SELF.'/upload/up_save_json?dir='.$dir.'\'}});gc'.$gctime.' = layedit.build(\'gc'.$gctime.'\',{height: 160 });form.verify({content: function(value){layedit.sync(gc'.$gctime.');}});});gctime++;</script>';
								$gctime++;
							}
							break;
						case 'image':
							$str .= '<div class="layui-form-item"><label class="layui-form-label">'.$value['note'].'</label><div class="layui-input-inline"><input type="text" id="pic'.$fid.'" name="'.$value['zd'].'" value="'.$opt[$value['zd']].'" class="layui-input"></div>';
	                        if(preg_match("/(iPhone|iPad|iPod|linux|Android)/i", strtoupper($_SERVER['HTTP_USER_AGENT']))){
	                            $str .= '<button type="button" class="layui-btn layui-btn-primary" id="image'.$fid.'"><i class="layui-icon colorb">&#xe608;</i>上传图片</button>';
	                            $str .= '<script>cscms.mode("cscms.getUpload(\''.Web_Path.SELF.'/upload/up_save_json?dir=dance&fid='.$fid.'\',\'pic'.$fid.'\',\'image'.$fid.'\',\''.$value['accept'].'\')");</script>';
	                        }else{
	                            $str .= '<div class="layui-input-inline" style="width: auto;"><a href="javascript:cscms.get_open(\''.site_url('upload/up').'?dir='.$dir.'&fid='.$fid.'\',\'上传图片\',\'500px\',\'360px\')" class="layui-btn layui-btn-primary"><i class="layui-icon" style="color:green">&#xe608;</i>上传图片</a></div>';
	                        }
							$str .= '</div>';
							$fid++;
							break;
						case 'select':
							$option = '';
							if(!empty($value['option'])){
								$op = explode('|', $value['option']);
								foreach ($op as $k => $v) {
									$temp_op = explode(':', $v);
									if($value['attr']==0){
										$cls=($opt[$value['zd']]==$temp_op[1])?'checked':'';
										$option .= '<input type="radio" name="'.$value['zd'].'" value="'.$temp_op[1].'" title="'.$temp_op[0].'" '.$cls.'>';
									}elseif($value['attr']==1){
										$zdarrs = explode('|cscms|', $opt[$value['zd']]);
										$cls = in_array($temp_op[1],$zdarrs) ? 'checked' : '';
										$option .= '<input type="checkbox" lay-skin="primary" name="'.$value['zd'].'['.$k.']" value="'.$temp_op[1].'" title="'.$temp_op[0].'" '.$cls.'>';
									}else{
										$cls=($opt[$value['zd']]==$temp_op[1])?'selected':''; 
										$option .= '<option type="checkbox" value="'.$temp_op[1].'" '.$cls.'>'.$temp_op[0].'</option>';
									}
								}
								$str .= '<div class="layui-form-item"><label class="layui-form-label">'.$value['note'].'</label><div class="layui-input-'.$display1.'">';
								if($value['attr']==2) $str .= '<select name="'.$value['zd'].'">';
								$str .= $option;
								if($value['attr']==2) $str .= '</select>';
								$str .= '</div></div>';
							}
							break;
						case 'datetime':
							if($value['attr']=='date'){
								$str .= '<div class="layui-form-item"><label class="layui-form-label">'.$value['note'].'</label><div class="layui-input-'.$display1.' "><input type="text" name="'.$value['zd'].'" value="'.$opt[$value['zd']].'" class="layui-input" id="laytime_'.$value['zd'].'"></div></div>';
								$str .= '<script>cscms.mode("cscms.getTime(\'laytime_'.$value['zd'].'\',\'date\')");</script>';
							}else{
								$str .= '<div class="layui-form-item"><label class="layui-form-label">'.$value['note'].'</label><div class="layui-input-'.$display1.' "><input type="text" id="laytime_'.$value['zd'].'" name="'.$value['zd'].'" value="'.$opt[$value['zd']].'" class="layui-input" ></div></div>';
								$str .= '<script>cscms.mode("cscms.getTime(\'laytime_'.$value['zd'].'\',\'datetime\')");</script>';
							}
							break;
						case 'upload':
							$str .= '<div class="layui-form-item">
                            <label class="layui-form-label">'.$value['note'].'</label>
                            <div class="layui-input-inline">
                                <input type="text" name="'.$value['zd'].'" id="upf'.$upid.'" value="'.$opt[$value['zd']].'" class="layui-input">
                            </div>';
                            $actemp = explode('|', $value['accept']);
                    		$acarr = array();
                    		foreach ($actemp as $k => $v) {
                    			$acarr[$k] = '*.'.$v;
                    		}
                    		$accept = implode(';',$acarr);
                            if(!preg_match("/(iPhone|iPad|iPod|linux|Android)/i", strtoupper($_SERVER['HTTP_USER_AGENT']))){
                            		
                                    $str .= '<div class="layui-input-inline" style="width: auto;"><a href="javascript:cscms.get_open(\''.site_url('upload/up').'?fhid=upf&fid='.$upid.'&nums='.$value['attr'].'&type='.$accept.'\',\'上传文件\',\'500px\',\'360px\')" class="layui-btn layui-btn-primary"><i class="layui-icon" style="color:green">&#xe608;</i>上传文件</a></div>';
                                }else{
                                    $str .= '<button type="button" class="layui-btn layui-btn-primary" id="lupf'.$upid.'"><i class="layui-icon colorb">&#xe608;</i>上传文件</button>';
                                    $str .= '<script>
									cscms.mode("cscms.getUpload(\''.Web_Path.SELF.'/upload/up_save_json?fhid=upf&fid='.$upid.'&dir=&type='.$accept.'\',\'upf'.$upid.'\',\'lupf'.$upid.'\',\''.$value['accept'].'\',2)");
                                    </script>';
                                }
                            $str .= '</div>';
							break;
						default:
							# code...
							break;
					}
				}
			}
			$gcstr = '<script>';
			for ($i=1; $i < $gctime; $i++) { 
				$gcstr .= 'var gc'.$i.';';
			}
			$gcstr .= '</script>';
			$data['str'] = $gcstr.$str;
			$data['gctime'] = $gcstr;
			return $data;
		}
	}
}
//写入自定义字段到文件
function save_field($dir,$table,$sign=0){
	$arr = array();
	if (is_file(CSCMS.'sys/Cs_Field.php')) {
		$field = require(CSCMS.'sys/Cs_Field.php');
		if(isset($field[$dir])){
			foreach ($field[$dir] as $key => $value) {
				if($value['table']==$table && $value['status']==1){
					if($sign==0 || ($sign==1 && $value['qiantai']==1)){
						if($value['leix']=='text' && $value['attr']==2){
							if(isset($_POST[$value['zd']])){
								$arr[$value['zd']] = remove_xss($_POST[$value['zd']]);
							}
						}else{
							if(isset($_POST[$value['zd']])){
								$arr[$value['zd']] = str_encode($_POST[$value['zd']]);
							}
						}
						if($value['required']==1 && isset($arr[$value['zd']]) && empty($arr[$value['zd']])){
							if($sign==1){
								msg_url($value['note'].'不能为空~!','javascript:history.back();');
							}else{
								getjson($value['note'].'不能为空~!');
							}
						}
						if(!empty($value['regexp']) && !empty($arr[$value['zd']])){
							if(!preg_match($value['regexp'], $arr[$value['zd']])){
								if($sign==1){
									msg_url($value['note'].'：'.$value['wrong'],'javascript:history.back();');
								}else{
									getjson($value['note'].'：'.$value['wrong']);
								}
							}
						}
						//判断复选数组转字符
						if(is_array($arr[$value['zd']])){
							$arr[$value['zd']] = implode('|cscms|',$arr[$value['zd']]);
						}
					}
				}
			}
		}
	}
	return $arr;
}
//后台提示信息
function admin_msg($msg, $gourl, $zt='ok', $color='',$left=''){
	$sign = $zt=='ok' ? 1 : 2;
	admin_info(array('msg'=>$msg,'url'=>$gourl),$sign);
}
//前台页面返回信息
function msg_url($title,$url,$time=3000) {
	if(intval($time)==0) $time=3000;
	//手机访问
	if(preg_match("/(iPhone|iPad|iPod|Android)/i", strtoupper($_SERVER['HTTP_USER_AGENT']))){
	    include FCPATH.'tpl'.FGF.'errors'.FGF.'html'.FGF.'mbmsg.php';
	    exit();
	}
	include FCPATH.'tpl'.FGF.'errors'.FGF.'html'.FGF.'pcmsg.php';
    exit();
}
//前台错误返回信息
function msg_txt($msg) {
	$msg = get_bm($msg);
	//手机访问
	if(preg_match("/(iPhone|iPad|iPod|Android)/i", strtoupper($_SERVER['HTTP_USER_AGENT']))){
        exit("<!DOCTYPE html><html><head><meta name='viewport' content='width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no'></head><body><center><br><br>".$msg."</center></body></html>");
	}
	//PC访问
	echo '<!DOCTYPE html><html><head><meta http-equiv="Content-Type"content="text/html; charset=utf-8"/><meta name="generator" content="cscms 4.x" /><link rel="stylesheet" type="text/css" href="'.Web_Path.'packs/images/msg.css"/><title>Cscms Error</title></head><body class="msg_txt"><div id="container"><h1>Cscms Error</h1><div class="box"><span class="code"><b class="f14">'.L('cscms_msg_04').'</b><font><p><b>'.$msg.'</b></p></font></span></div><div class="box"><span class="code"><b class="f14">'.L('cscms_msg_05').'</b><font><p>'.L('cscms_msg_06').'</p><p><a href="http://bbs.chshcms.com/search.html?key='.urlencode($msg).'" target="help">'.L('cscms_msg_07').'</a></p><p><a href="javascript:history.back(-1);">'.L('cscms_msg_08').'</a></p><p><a href="'.is_ssl().Web_Url.Web_Path.'">返回主页 ('.Web_Url.')</a></p></font></span></div></div></body></html>';
	exit();
}
//单独页面提示信息
function admin_info($info,$sign=0){
	$sign = intval($sign);
	$res = '<link rel="stylesheet" href="'.Web_Path.'packs/layui/css/layui.css?v=2.0"><script src="'.Web_Path.'packs/js/jquery.min.js"></script><script src="'.Web_Path.'packs/layui/layui.js?v=2.0"></script><script src="'.Web_Path.'packs/admin/js/cscms.js?v=2.0"></script><script>';
	$res .= 'layui.use([\'layer\'], function(){
		var layer = layui.layer;';
	if($sign==0){
		$res .= 'layer.msg(\''.$info.'\',{icon:2,time:0});';

	}
	if($sign==1){
		if(is_array($info)){
			$res .= 'layer.msg(\''.$info['msg'].'\',{icon:1,time:0});';
			$res .= 'setTimeout(function(){location.href=\''.$info['url'].'\'},2000);';
		}else{
			$res .= 'layer.msg(\''.$info.'\',{icon:1,time:0});';
		}
		
	}
	if($sign==2){
		$res .= 'layer.msg(\''.$info['msg'].'\',{icon:2,time:0});';
		$res .= 'setTimeout(function(){location.href=\''.$info['url'].'\'},2000);';
	}
	if($sign==3){
		$res .= 'layer.msg(\''.$info['msg'].'\',{icon:2,time:0});';
		$res .= 'setTimeout(function(){location.href=\''.$info['url'].'\'},5000);';
	}
	$res .= '});</script>';
	exit($res);
}