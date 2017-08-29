<?php
/**
 * @Cscms open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-07-26
 */
//获取地址
function get_link($uri){
	$url = is_ssl().Web_Url.site_url($uri);
	//伪静态
	if(Web_Mode==2){
		$url = str_replace('index.php/','',$url);
	}
	return $url;
}
//获取会员UID
function get_home_uid(){
	//判断会员系统开关
	if(User_Mode==0) msg_url(User_No_info,is_ssl().Web_Url.Web_Path);
    if(Home_Ym==1){
         $arr = explode('.', $_SERVER['HTTP_HOST']);
         $uid = $arr[0];
    }else{
         $uid = cscms_uri();
    }
	if(Home_Fs==1) $uid = getzd('user','id',$uid,'name');
	$uid = (int)$uid;
	if($uid==0) msg_url(L('home_02'),is_ssl().Web_Url.Web_Path);
	return $uid;
}
//分页标签解析
function page_mark($Mark_Text,$Arr){
	$Mark_Text=preg_replace('/\{cscms:pagenum(.*?)\}/','{cscms:pagenum}',$Mark_Text);
	$Mark_Text=str_replace('{cscms:pagedata}',$Arr[10],$Mark_Text);
	$Mark_Text=str_replace('{cscms:pagedown}',$Arr[4],$Mark_Text);
	$Mark_Text=str_replace('{cscms:pagenow}',$Arr[5],$Mark_Text);
	$Mark_Text=str_replace('{cscms:pagecout}',$Arr[6],$Mark_Text);
	$Mark_Text=str_replace('{cscms:pagelist}',$Arr[9],$Mark_Text);
	$Mark_Text=str_replace('{cscms:pagesize}',$Arr[7],$Mark_Text);
	$Mark_Text=str_replace('{cscms:pagefirst}',$Arr[1],$Mark_Text);
	$Mark_Text=str_replace('{cscms:pageup}',$Arr[3],$Mark_Text);
	$Mark_Text=str_replace('{cscms:pagenum}',$Arr[8],$Mark_Text);
	$Mark_Text=str_replace('{cscms:pagelast}',$Arr[2],$Mark_Text);	
	return $Mark_Text;
}
//获取分页数目	
function getpagenum($Mark_Text){
	preg_match('/\{cscms:(pagenum)\s*([a-zA-Z=]*)\s*([\d]*)\}/',$Mark_Text,$pagearr);
	if(!empty($pagearr)){
		if(trim($pagearr[3])!=""){
			$pagenum=$pagearr[3];
		}else{
			$pagenum=10;
		}	
	}else{
		$pagenum=10;
	}
	unset($pagearr);
		return $pagenum;
}

//版块连接地址解析
function cscmslink($dir){
    //获取版块配置参数
	$Ym_Mode=config('Ym_Mode',$dir); //二级域名状态
	$Ym_Url=config('Ym_Url',$dir);   //二级域名地址
	$Web_Mode=config('Web_Mode',$dir);   //站点运行方式
	$Html=config('Html_Uri',$dir); //静态规则
    $weburl = (defined('MOBILE_YM')) ? Mobile_Url.Web_Path : Web_Url.Web_Path;
	if($Web_Mode==2){ //伪静态模式
	    $Rewrite=config('Rewrite_Uri',$dir); //伪静态规则
        $linkurl=$Rewrite['index']['url'];
	}elseif($Web_Mode==3 && $Html['index']['check']==1){ //静态模式
        $linkurl=$Html['index']['url'];
        //手机版
        if(defined('MOBILE')){
        	if(Mobile_Url=='' || $Ym_Mode==1){
        		$linkurl = Html_Wap_Dir.'/'.$linkurl;
        	}else{
        		$weburl = Mobile_Url.Web_Path;
        	}
        }
	}else{
        $linkurl=($Ym_Mode==1) ? '' : 'index.php/'.$dir;
	}
	//开启二级域名
	if($Ym_Mode==1 && !defined('MOBILE_YM')){
        $weburl  = $Ym_Url.Web_Path;
        global $_CS_Rewrite;
		$linkurl = str_replace($_CS_Rewrite[$dir],'',$linkurl);
	}
    return is_ssl().$weburl.$linkurl;
}

//版块人气地址解析
function hitslink($path,$dir){
    //获取版块配置参数
	if($dir!='home'){
	    $Ym_Mode=config('Ym_Mode',$dir); //二级域名状态
	    $Ym_Url=config('Ym_Url',$dir);   //二级域名地址
	}else{
	    $Ym_Mode = 2;
	}
	//开启二级域名
	if($Ym_Mode==1){
          $linkurl = $Ym_Url.Web_Path."index.php/".$path;
	}else{
          $linkurl = Web_Url.Web_Path."index.php/".$dir."/".$path;
	}
    return is_ssl().$linkurl;
}

//连接地址解析
function linkurl($fid,$sort='id',$id=1,$pid=1,$dir='',$sname='null'){
    $linkurl='';
	//获取版块配置参数
	if(empty($dir) && PLUBPATH != 'sys') $dir = PLUBPATH;
	$parr = config('',$dir);
	$Ym_Mode = $parr['Ym_Mode']; //二级域名状态
	$Ym_Url = $parr['Ym_Url'];   //二级域名地址
	$Web_Mode = $parr['Web_Mode'];   //站点运行方式
	$Rewrite = $parr['Rewrite_Uri']; //伪静态规则
	$Html = $parr['Html_Uri'];  //生成静态规则
	if($fid=='gbook'){ //网站留言
	    if(Web_Mode==2){ //伪静态模式
	        $linkurl = Web_Url.Web_Path."gbook/index/".$pid;
	    }else{
	        $linkurl = Web_Url.Web_Path."index.php/gbook/index/".$pid;
	    }
	    return is_ssl().$linkurl;
	}elseif($fid=='search'){
		$sort=strpos($sort,'=')?$sort:$sort."=";
		$sort=strpos($sort,'?')?$sort:"?".$sort;
		if($Web_Mode==2){ //伪静态模式
		    $linkurl=Web_Url.Web_Path.$dir."/search".$sort.$id;
		}else{
		    $linkurl=Web_Url.Web_Path."index.php/".$dir."/search".$sort.$id;
		}
		//开启二级域名
		if($Ym_Mode==1 && !defined('MOBILE_YM')){
			if($Web_Mode==2){
		        $linkurl  = str_replace(Web_Url.Web_Path.$dir,$Ym_Url.Web_Path,$linkurl);
			}else{
		        $linkurl  = str_replace(Web_Url.Web_Path."index.php/".$dir,$Ym_Url.Web_Path."index.php",$linkurl);
			}
		}
		if($pid>1){
		    $linkurl.="&page=".$pid;
		}
		if(defined('MOBILE_YM')){
		    $linkurl  = str_replace(Web_Url,Mobile_Url,$linkurl);
		}
		return is_ssl().str_replace("//","/",$linkurl);
	}
	switch($Web_Mode){
		case '1':  //动态模式
			if($pid>1){
				if($id>0){
					$linkurl=Web_Url.Web_Path."index.php/".$dir."/".$fid."/".$sort."/".$id."/".$pid;
				}else{
					$linkurl=Web_Url.Web_Path."index.php/".$dir."/".$fid."/".$sort."/".$pid;
				}
			}else{
			    if($id>0){
			        $linkurl=Web_Url.Web_Path."index.php/".$dir."/".$fid."/".$sort."/".$id;
			    }else{
			        $linkurl=Web_Url.Web_Path."index.php/".$dir."/".$fid."/".$sort."/".$pid;
			    }
			}
		break;
		case '2':  //伪静态模式
			global $_CS_Rewrite;
			//兼容专题
			if($fid=='topic' && ($sort=='lists' || $sort=='show')){
				$linkurl=$Rewrite[$fid.'/'.$sort]['url'];
			}else{
				if(isset($Rewrite[$fid]['url'])){
					$linkurl = $Rewrite[$fid]['url'];
				}else{
					$linkurl = $fid;
					if(!empty($sort)) $linkurl .= '/'.$sort;
					if(!empty($id)) $linkurl .= '/'.$id;
					if(!empty($pid)) $linkurl .= '/'.$pid;
				}
			}
			$sname='null';
			//兼容歌曲下载、歌词下载、收费文章内容
			if(($dir=='dance' && $fid=='down' && ($sort=='load' || $sort=='lrc')) || ($dir=='news' && $sort=='pay')) {
				if(isset($_CS_Rewrite[$dir])) $dir = $_CS_Rewrite[$dir];
				$linkurl=Web_Url.Web_Path."index.php/".$dir."/".$fid."/".$sort."/".$id;
				if($pid>1) $linkurl.="/".$pid;
			}else{
				if(isset($_CS_Rewrite[$dir])) $dir = $_CS_Rewrite[$dir];
				$linkurl=Web_Url.Web_Path.$dir."/".str_replace_dir($linkurl,$id,$sname,$sort,$pid);
			}

		break;
		case '3':  //静态模式

			//兼容专题
			if($fid=='topic' && ($sort=='lists' || $sort=='show')){
				$check=$Html[$fid.'/'.$sort]['check'];
				$linkurl=$Html[$fid.'/'.$sort]['url'];
			}else{
				if(isset($Html[$fid]['url'])){
					if($fid=='topic') $fid='topic/lists';
					$linkurl=$Html[$fid]['url'];
			  		$check=$Html[$fid]['check'];
				}else{
					$check=0;
				}
			  
			}
			if($check==1){
				$linkurl = str_replace_dir($linkurl,$id,$sname,$sort,$pid,$dir);
				//手机版
				if(defined('MOBILE')){
					if((!defined('MOBILE_YM') && Mobile_Url=='') || $Ym_Mode==1){
						$linkurl = Html_Wap_Dir.'/'.$linkurl;
					}
					if(Mobile_Url!=''){
						$linkurl = Mobile_Url.Web_Path.$linkurl;
					}else{
						$linkurl = Web_Url.Web_Path.$linkurl;
					}
				}else{
					$linkurl = Web_Url.Web_Path.$linkurl;
				}
			}else{
				if($pid>1){
				    if($id>0){
				    	$linkurl=Web_Url.Web_Path."index.php/".$dir."/".$fid."/".$sort."/".$id."/".$pid;
				    }else{
				        $linkurl=Web_Url.Web_Path."index.php/".$dir."/".$fid."/".$sort."/".$pid;
				    }
				}else{
				    if($id>0){
				    	$linkurl=Web_Url.Web_Path."index.php/".$dir."/".$fid."/".$sort."/".$id;
				    }else{
				    	$linkurl=Web_Url.Web_Path."index.php/".$dir."/".$fid."/".$sort."/".$pid;
				    }
				}
			}
			//兼容歌曲下载、歌词下载、收费文章内容
			if(($dir=='dance' && $fid=='down' && ($sort=='load' || $sort=='lrc')) || ($dir=='news' && $sort=='pay')){
				$linkurl=Web_Url.Web_Path."index.php/".$dir."/".$fid."/".$sort."/".$id;
				if($pid>1) $linkurl.="/".$pid;
			}
		break;
	}
	//开启二级域名
	if($Ym_Mode==1 && !defined('MOBILE_YM')){
	    if($Web_Mode==2){
	    	$linkurl  = str_replace(Web_Url.Web_Path.$dir,$Ym_Url.Web_Path,$linkurl);
	    }else{
	        $linkurl  = str_replace(Web_Url.Web_Path."index.php/".$dir,$Ym_Url.Web_Path."index.php",$linkurl);
			$linkurl  = str_replace(Web_Url.Web_Path,$Ym_Url.Web_Path,$linkurl);
	    }
	}
	if(defined('MOBILE_YM')){
        $linkurl  = str_replace(Web_Url,Mobile_Url,$linkurl);
	}
	return is_ssl().str_replace("//","/",$linkurl);
}

//会员空间相关连接
function userlink($Classid,$Uid=0,$Name='',$ID='',$Pages=0){
    $ID=(string)$ID;
	if(Home_Fs==2) $Name=$Uid;  //启用会员ID做主页地址
   	if(Home_Ym==1){ 
      	if($Classid=='index'){
            $userlink=$Name.".".Home_YmUrl;
      	}elseif($ID!=''){
            $userlink=$Name.".".Home_YmUrl."/index.php/".$Classid."/".$ID;
			if($Pages>0) $userlink.="/".$Pages;
      	}elseif($Pages>0){
            $userlink=$Name.".".Home_YmUrl."/index.php/".$Classid."/".$Pages;
      	}else{
            $userlink=$Name.".".Home_YmUrl."/index.php/".$Classid;
      	}
	}else{
      	if($Classid=='index'){
            $userlink=Web_Url.Web_Path.'index.php/'.$Name.'/home';
      	}elseif($ID!=''){
            $userlink=Web_Url.Web_Path.'index.php/'.$Name.'/home/'.$Classid.'/'.$ID;
			if($Pages>0) $userlink.="/".$Pages;
      	}elseif($Pages>0){
            $userlink=Web_Url.Web_Path.'index.php/'.$Name.'/home/'.$Classid.'/'.$Pages;
		}else{
            $userlink=Web_Url.Web_Path.'index.php/'.$Name.'/home/'.$Classid;
      	}
	    if(defined('MOBILE_YM')){
            $userlink  = str_replace(Web_Url,Mobile_Url,$userlink);
	    }
	}
    //伪静态
    if(Home_Mode==1) $userlink = str_replace("/index.php/","/",$userlink);
	return is_ssl().$userlink;
}

//会员中心连接
function spacelink($url='',$dir=''){
	$url = str_replace(",","/",$url);
	$uarr = explode('/',$url);
	$filename = $uarr[0] == 'user' ? ucwords($uarr[1]).'.php' : ucwords($uarr[0]).'.php';
	if($dir=='sys') $dir='';
	$plub = $dir;
	if($dir=='' && PLUBPATH!='sys'){
	    $plub = PLUBPATH;
	}
	if($plub!='' && file_exists(FCPATH.'plugins'.FGF.$plub.FGF.'user'.FGF.$filename)){
		$url=Web_Url.Web_Path.'index.php/'.$plub.'/user/'.$url;
	}else{
		$url=Web_Url.Web_Path.'index.php/user/'.$url;
	}
	//伪静态
	if(Web_Mode==2) $url=str_replace("/index.php/","/",$url);
	$url = str_replace("/user/user/","/user/",$url);
	$url = userurl($url,$plub);
	return is_ssl().$url;
}

//会员连接转换
function userurl($url,$plub=''){
	if(defined('ADMINSELF'))  $url=str_replace(ADMINSELF,"index.php",$url);
	//伪静态
	if(Web_Mode==2) $url=str_replace("index.php/user","user",$url);
	if(User_Ym!='' && !defined('MOBILE_YM')){
		if($plub==''){ //不是版块链接
			//伪静态
			if(Web_Mode==2){
			    $url=str_replace(Web_Url.Web_Path.'user',User_Ym,$url);
			}else{
			    $url=str_replace(Web_Url.Web_Path.'index.php/user',User_Ym.Web_Path.'index.php',$url);
			}
		}else{ //版块会员链接
			//伪静态
			if(Web_Mode==2){
				$url=str_replace(Web_Url.Web_Path.$plub.'/user',User_Ym.Web_Path.$plub,$url);
			}else{
				$url=str_replace(Web_Url.Web_Path.'index.php/'.$plub.'/user',User_Ym.Web_Path.'index.php/'.$plub,$url);
			}
		}
	}
	if(defined('MOBILE_YM')){
	     $url  = str_replace(Web_Url,Mobile_Url,$url);
	}
	return $url;
}

//后台生成地址转换
function adminhtml($url,$dir=''){
	$dir = !empty($dir) ? $dir : (PLUBPATH != 'sys' ? PLUBPATH : '');
	$Ym_Mode = config('Ym_Mode',$dir); //二级域名状态
	$Ym_Url = config('Ym_Url',$dir);   //二级域名地址
	if($Ym_Mode == 1){
	   $url = str_replace(is_ssl().$Ym_Url,"",$url);
	}
	$url = str_replace(is_ssl().Web_Url,"",$url);
	$file_ext = strtolower(trim(substr(strrchr($url, '.'), 1)));
	if(empty($file_ext)){
		if(substr($url,-1)!='/'){
			$url.='.html';
		}else{
			$url.='index.html';
		}
	}
	if(Mobile_Url!=''){
		$url=str_replace(is_ssl().Mobile_Url,Html_Wap_Dir,$url);
	}
	if(Web_Path!='/'){
		$url=str_replace(Web_Path,"/",$url);
	}
	$url=str_replace(is_ssl(),'',$url);
	return $url;
}

//获取图片信息
function piclink($Table='pic',$Url,$dx='') {
	if(UP_Mode==2){  //FTP远程附件
	      $linkurl=FTP_Url;
	}elseif(UP_Mode>2){  //其他网盘
	      $ci = &get_instance();
		  $ci->load->library('csup');
	      $linkurl=$ci->csup->down(UP_Mode);
	}else{
	      $linkurl='';
	      /*if(UP_Pan!=''){
	          $linkurl=UP_Url;
		  }else{
	          $linkurl=is_ssl().Web_Url.Web_Path;
		  }*/
	}
	if(substr($Url,0,7)=="http://" || substr($Url,0,8)=="https://"){
	 	  $picurl=$Url;
	}elseif(empty($Url)){
	      if($Table=='bgpic' || $Table=='toppic'){
	  		     return '';
	      }elseif($Table=='logo'){
	             if($dx==1){
	  		          $picurl=is_ssl().Web_Url.Web_Path."attachment/nv_nopic.jpg";
	             }else{
	  		          $picurl=is_ssl().Web_Url.Web_Path."attachment/nan_nopic.jpg";
	             }
	      }else{
	 		     $picurl=is_ssl().Web_Url.Web_Path."attachment/nopic.gif";
	      }
		  return $picurl;
	}else{
	      if(substr($Url,0,1)!='/') $Url='/'.$Url;
	      if(UP_Mode==1){
	      		$picurl=$linkurl."attachment/".$Table.$Url;
		  }else{
	      		$picurl=$linkurl.$Url;
		  }
	 	  if($dx!=''){
		        $picurl.=".small.jpg";
	 	  }
	}
	if(substr($picurl,0,7)=="http://" || substr($picurl,0,8)=="https://"){
		 if(is_ssl() == 'https://'){
			//QQ头像之内的
			$picurl = str_replace('http://','https://',$picurl);
		 }
	     return $picurl;
	}else{
		 return is_ssl().Web_Url.Web_Path.'index.php/picdata/'.str_replace("attachment/","",$picurl);
	}
}
//获取附件连接地址
function annexlink($url) {
	if(substr($url,0,7)=='http://' || substr($url,0,8)=='https://' || substr($url,0,7)=='rtsp://' || substr($url,0,7)=='rtmp://' || substr($url,0,6)=='mms://'){  //外部附件
	      return $url;
	}elseif(UP_Mode==2){  //FTP远程附件
	      $linkurl=FTP_Url;
	}elseif(UP_Mode>2){  //其他网盘
	      $ci = &get_instance();
		  $ci->load->library('csup');
	      $linkurl=$ci->csup->down(UP_Mode);
	}else{
	      if(UP_Pan!=''){
	          $linkurl=UP_Url;
		  }else{
			  if(substr($url,0,1)=='/') $url=substr($url,1);
	          $linkurl=is_ssl().Web_Url.Web_Path;
		  }
	}
	 return $linkurl.$url;
}
//版块分页解析
function spanpage($sqlstr,$nums,$pagesize,$pagenum,$fid,$sort='id',$id=1,$pages=1,$plist=0){
    $znums=$nums;
	if($nums==0) $nums=1;
    $pagejs = ceil($nums/$pagesize);//总页数
    if($pages==0) $pages=1;
    if($pages > $pagejs) $pages = $pagejs;
    $sqlstr.=" LIMIT ".$pagesize*($pages-1).",".$pagesize;
	$str="";
	$first=linkurl($fid,$sort,$id,1);
   	if($pages==1){
     	$pageup = linkurl($fid,$sort,$id,1);
    }else{
     	$pageup = linkurl($fid,$sort,$id,($pages-1));
    }

   	if($pagejs>$pages){
     	$pagenext = linkurl($fid,$sort,$id,($pages+1));
    }else{
     	$pagenext = linkurl($fid,$sort,$id,$pagejs);
    }
	$last=linkurl($fid,$sort,$id,$pagejs);
	$pagelist="<select  onchange=javascript:window.location=this.options[this.selectedIndex].value;>\r\n<option value='0'>跳转</option>\r\n";
	if($plist){
		for($k=1;$k<=$pagejs;$k++) $pagelist.="<option value='".linkurl($fid,$sort,$id,$k)."'>第".$k."页</option>\r\n";
	}
	$pagelist.="</select>";	
	if($pagejs<=$pagenum){
		for($i=1;$i<=$pagejs;$i++){
            if($i==$pages){
			    $str.="<a href='".linkurl($fid,$sort,$id,$i)."' class='on'>".$i."</a>";
            }else{
			    $str.="<a href='".linkurl($fid,$sort,$id,$i)."'>".$i."</a>";
            }
 		}
	}else{
		if($pages>=$pagenum){
			for($i=$pages-intval($pagenum/2);$i<=$pages+(intval($pagenum/2));$i++){
				if($i<=$pagejs){
					if($i==$pages){
					    $str.="<a href='".linkurl($fid,$sort,$id,$i)."' class='on'>".$i."</a>";
					}else{
					    $str.="<a href='".linkurl($fid,$sort,$id,$i)."'>".$i."</a>";
					}
		    	}
			}
			if($i<=$pagejs){ 
		        $str.="<a href='".linkurl($fid,$sort,$id,$pagejs)."'>".$pagejs."</a>";
			}
		}else{
			for($i=1;$i<=$pagenum;$i++){
                if($i==$pages){
			        $str.="<a href='".linkurl($fid,$sort,$id,$i)."' class='on'>".$i."</a>";
                }else{
			        $str.="<a href='".linkurl($fid,$sort,$id,$i)."'>".$i."</a>";
                }
	        } 
	        if($i<=$pagejs){ 
		     	$str.="<a href='".linkurl($fid,$sort,$id,$pagejs)."'>".$pagejs."</a>";
			}
	 	}
	}
 	$arr=array($sqlstr,$first,$last,$pageup,$pagenext,$pages,$pagejs,$pagesize,$str,$pagelist,$znums);
 	return $arr;
}
//AJAX分页解析
function spanajaxpage($sqlstr,$nums,$pagesize,$pagenum,$op,$pages=1,$id=0,$fid=''){
    $znums=$nums;
	if($nums==0){$nums=1;}
    $pagejs=ceil($nums/$pagesize);//总页数
    if($pages==0) $pages=1;
    if($pages>$pagejs){
        $pages=$pagejs;
    }
    $sqlstr.=" LIMIT ".$pagesize*($pages-1).",".$pagesize;
	$str="";
	$first="javascript:".$op."('1','".$id."','".$fid."');";
   	if($pages==1){
     		$pageup="javascript:".$op."('1','".$id."','".$fid."');";
    }else{
     		$pageup="javascript:".$op."('".($pages-1)."','".$id."','".$fid."');";
    }
   	 if($pagejs>$pages){
     		$pagenext="javascript:".$op."('".($pages+1)."','".$id."','".$fid."');";
    }else{
     		$pagenext="javascript:".$op."('".$pagejs."','".$id."','".$fid."');";
    }
	$last="javascript:".$op."('".$pagejs."','".$id."','".$fid."');";
	$pagelist="<select  onchange=javascript:window.location=this.options[this.selectedIndex].value;>\r\n<option value='0'>跳转</option>\r\n";
	for($k=1;$k<=$pagejs;$k++){
		$pagelist.="<option value='".$op."('".$k."','".$id."','".$fid."');'>第".$k."页</option>\r\n";
	}
	$pagelist.="</select>";	
	if($pagejs<=$pagenum){
			for($i=1;$i<=$pagejs;$i++){
               if($i==$pages){
				        $str.="<a href=\"javascript:".$op."('".$i."','".$id."','".$fid."');\" class='on'>".$i."</a>";
               }else{
				        $str.="<a href=\"javascript:".$op."('".$i."','".$id."','".$fid."');\">".$i."</a>";
               }
	 		}
	}else{
			if($pages>=$pagenum){
				for($i=$pages-intval($pagenum/2);$i<=$pages+(intval($pagenum/2));$i++){
					if($i<=$pagejs){
                      if($i==$pages){
				                $str.="<a href=\"javascript:".$op."('".$i."','".$id."','".$fid."');\" class='on'>".$i."</a>";
                      }else{
				                $str.="<a href=\"javascript:".$op."('".$i."','".$id."','".$fid."');\">".$i."</a>";
                      }
			    }
				}
				if($i<=$pagejs){ 
    		        $str.="<a href=\"javascript:".$op."('".$pagejs."','".$id."','".$fid."');\">".$i."</a>";
				}
			}else{
				for($i=1;$i<=$pagenum;$i++){
                      if($i==$pages){
				                $str.="<a href=\"javascript:".$op."('".$i."','".$id."','".$fid."');\" class='on'>".$i."</a>";
                      }else{
				                $str.="<a href=\"javascript:".$op."('".$i."','".$id."','".$fid."');\">".$i."</a>";
                      }
		        } 
		        if($i<=$pagejs){ 
  		     	  $str.="<a href=\"javascript:".$op."('".$pagejs."','".$id."','".$fid."');\">".$i."</a>";
			}
		 	}
	}
 	$arr=array($sqlstr,$first,$last,$pageup,$pagenext,$pages,$pagejs,$pagesize,$str,$pagelist,$znums);
 	return $arr;
}
//会员中心分页解析
function userpage($sqlstr,$nums,$pagesize,$pagenum,$url,$pages=1,$dir=''){
    if(substr($url,-1)!='/' && strpos($url, '?') === false) $url.='/';
    $znums=$nums;
	if($nums==0){$nums=1;}
    $pagejs=ceil($nums/$pagesize);//总页数
    if($pages==0) $pages=1;
    if($pages>$pagejs){
        $pages=$pagejs;
    }
    $sqlstr.=" LIMIT ".$pagesize*($pages-1).",".$pagesize;
	$str="";
	$first=spacelink($url.'1',$dir);
   	if($pages==1){
     		$pageup=spacelink($url.'1',$dir);
    }else{
     		$pageup=spacelink($url.($pages-1),$dir);
    }
   	if($pagejs>$pages){
     		$pagenext=spacelink($url.($pages+1),$dir);
    }else{
     		$pagenext=spacelink($url.$pagejs,$dir);
    }
	$last=spacelink($url.$pagejs,$dir);
	$pagelist="<select  onchange=javascript:window.location=this.options[this.selectedIndex].value;>\r\n<option value='0'>跳转</option>\r\n";
	for($k=1;$k<=$pagejs;$k++){
		$pagelist.="<option value='".spacelink($url.$k,$dir)."'>第".$k."页</option>\r\n";
	}
	$pagelist.="</select>";	
	if($pagejs<=$pagenum){
			for($i=1;$i<=$pagejs;$i++){
               if($i==$pages){
				        $str.="<a href='".spacelink($url.$i,$dir)."' class='on'>".$i."</a>";
               }else{
				        $str.="<a href='".spacelink($url.$i,$dir)."'>".$i."</a>";
               }
	 		}
	}else{
			if($pages>=$pagenum){
				for($i=$pages-intval($pagenum/2);$i<=$pages+(intval($pagenum/2));$i++){
					if($i<=$pagejs){
                      if($i==$pages){
				                $str.="<a href='".spacelink($url.$i,$dir)."' class='on'>".$i."</a>";
                      }else{
				                $str.="<a href='".spacelink($url.$i,$dir)."'>".$i."</a>";
                      }
			    }
				}
				if($i<=$pagejs){ 
    		        $str.="<a href='".spacelink($url.$pagejs,$dir)."'>".$pagejs."</a>";
				}
			}else{
				for($i=1;$i<=$pagenum;$i++){
                      if($i==$pages){
				                $str.="<a href='".spacelink($url.$i,$dir)."' class='on'>".$i."</a>";
                      }else{
				                $str.="<a href='".spacelink($url.$i,$dir)."'>".$i."</a>";
                      }
		        } 
		        if($i<=$pagejs){ 
  		     	$str.="<a href='".spacelink($url.$pagejs,$dir)."'>".$pagejs."</a>";
			}
		 	}
	}
 	$arr=array($sqlstr,$first,$last,$pageup,$pagenext,$pages,$pagejs,$pagesize,$str,$pagelist,$znums);
 	return $arr;
}
//会员主页分页解析
function homepage($sqlstr,$nums,$pagesize,$pagenum,$op,$uid,$user,$id=0,$pages=1){
    $znums=$nums;
	if($nums==0){$nums=1;}
    $pagejs=ceil($nums/$pagesize);//总页数
    if($pages==0) $pages=1;
    if($pages>$pagejs){
        $pages=$pagejs;
    }
    $sqlstr.=" LIMIT ".$pagesize*($pages-1).",".$pagesize;
	$str="";
	$first=userlink($op,$uid,$user,$id,1);
   	if($pages==1){
     		$pageup=userlink($op,$uid,$user,$id,1);
    }else{
     		$pageup=userlink($op,$uid,$user,$id,($pages-1));
    }
   	if($pagejs>$pages){
     		$pagenext=userlink($op,$uid,$user,$id,($pages+1));
    }else{
     		$pagenext=userlink($op,$uid,$user,$id,$pagejs);
    }
	$last=userlink($op,$uid,$user,$id,$pagejs);
	$pagelist="<select  onchange=javascript:window.location=this.options[this.selectedIndex].value;>\r\n<option value='0'>跳转</option>\r\n";
	for($k=1;$k<=$pagejs;$k++){
		$pagelist.="<option value='".userlink($op,$uid,$user,$id,$k)."'>第".$k."页</option>\r\n";
	}
	$pagelist.="</select>";	
	if($pagejs<=$pagenum){
			for($i=1;$i<=$pagejs;$i++){
               if($i==$pages){
				        $str.="<a href='".userlink($op,$uid,$user,$id,$i)."' class='on'>".$i."</a>";
               }else{
				        $str.="<a href='".userlink($op,$uid,$user,$id,$i)."'>".$i."</a>";
               }
	 		}
	}else{
			if($pages>=$pagenum){
				for($i=$pages-intval($pagenum/2);$i<=$pages+(intval($pagenum/2));$i++){
					if($i<=$pagejs){
                      if($i==$pages){
				                $str.="<a href='".userlink($op,$uid,$user,$id,$i)."' class='on'>".$i."</a>";
                      }else{
				                $str.="<a href='".userlink($op,$uid,$user,$id,$i)."'>".$i."</a>";
                      }
			    }
				}
				if($i<=$pagejs){ 
    		        $str.="<a href='".userlink($op,$uid,$user,$id,$pagejs)."'>".$pagejs."</a>";
				}
			}else{
				for($i=1;$i<=$pagenum;$i++){
                      if($i==$pages){
				                $str.="<a href='".userlink($op,$uid,$user,$id,$i)."' class='on'>".$i."</a>";
                      }else{
				                $str.="<a href='".userlink($op,$uid,$user,$id,$i)."'>".$i."</a>";
                      }
		        } 
		        if($i<=$pagejs){ 
  		     	$str.="<a href='".userlink($op,$uid,$user,$id,$pagejs)."'>".$pagejs."</a>";
			}
		 	}
	}
 	$arr=array($sqlstr,$first,$last,$pageup,$pagenext,$pages,$pagejs,$pagesize,$str,$pagelist,$znums);
 	return $arr;
}
//后台分页
function admin_page($url,$page,$pages){
	$phtml = '<div class="layui-box layui-laypage layui-laypage-default" id="layui-laypage-0">';
	if($page > 1){
		$phtml .= '<a href="'.$url.($page-1).'" class="layui-laypage-prev" data-page="'.($page-1).'">上一页</a>';
	}
	if($pages<6 || $page<4){
		if($pages < 2){
			return '';
		}
		if($pages<6){
			$len = $pages;
		}else{
			$len = 5;
		}
		for($i=1;$i<$len+1;$i++){
			$phtml .= page_curr($url,$page,$i);
		}
		if($pages>5){
			$phtml .= '<span>…</span><a href="'.$url.$pages.'" class="layui-laypage-last" title="尾页" data-page="'.$pages.'">末页</a>';
		}
	}else{//pages>$nums
		if($pages<$page+2){
			$phtml .= '<a href="'.$url.'1" class="laypage_first" data-page="1" title="首页">首页</a><span>…</span>';
			for($i=$pages-4;$i<$pages+1;$i++){
				$phtml .= page_curr($url,$page,$i);
			}
		}else{
			$phtml .='<a href="'.$url.'1" class="laypage_first" data-page="1" title="首页">首页</a><span>…</span>';
			for($i=$page-2;$i<$page+3;$i++){
				$phtml .= page_curr($url,$page,$i);
			}
			$phtml .= '<span>…</span><a href="'.$url.$pages.'" class="layui-laypage-last" title="尾页" data-page="'.$pages.'">末页</a>';
		}
	}
	if($page < $pages){
		$phtml .= '<a href="'.$url.($page+1).'" class="layui-laypage-next" data-page="'.($page+1).'">下一页</a>';
	}
	$phtml .= '<span class="layui-laypage-total phide">到第 <input id="goto_page" type="number" min="1" onkeyup="this.value=this.value.replace(/\D/, \'\')" value="'.$page.'" class="layui-laypage-skip"> 页 <button type="button" onclick="cscms.goto_page(\''.$url.'\')" class="layui-laypage-btn">确定</button></span></div>';
	return $phtml;
}
function page_curr($url,$page,$i){
	$phtml = '';
	if($page==$i){
		$phtml .= '<span class="layui-laypage-curr"><em class="layui-laypage-em"></em><em>'.$page.'</em></span>';
	}else{
		$phtml .= '<a href="'.$url.$i.'" data-page="'.$i.'">'.$i.'</a>';
	}
	return $phtml;
}
function page_data($nums,$page,$pages){
	if($pages<2){
		return '';
	}else{
		return '共'.$nums.'条记录'.$pages.'页,当前显示第'.$page.'页';
	}
}
