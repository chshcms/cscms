<?php
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-09-19
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 采集操作类
 */
class Caiji {

	public $web_url='';

    function __construct ()
	{
		//....
	}

    //改变默认主页连接
    function weburl($url){
           $this->web_url=$url;
	}

    //获取连接数组
	function str($url='',$code='utf-8'){
		     $neir=htmlall($url);
		     if(!empty($neir)){
                 if($code!='utf-8') $neir=get_bm($neir);
		     }
		     return $neir;
    }

    // 字符串截取函数
    function getstr($str,$start,$end,$sid=1){  
             if($start && $end){
                  $temp = explode($start, $str);  
                  if(!empty($temp[1])){
                      $content = explode($end, $temp[1], 2);  
                      if($sid==2){
                             $content[0] = preg_replace("/<a[^>]+>(.+?)<\/a>/i","$1",$content[0]);
	                         //过滤换行符
	                         $content[0] = preg_replace("/ /","",$content[0]);
	                         $content[0] = preg_replace("/&nbsp;/","",$content[0]);
	                         $content[0] = preg_replace("/　/","",$content[0]);
	                         $content[0] = preg_replace("/\r\n/","",$content[0]);
	                         $content[0] = str_replace(chr(13),"",$content[0]);
	                         $content[0] = str_replace(chr(10),"",$content[0]);
	                         $content[0] = str_replace(chr(9),"",$content[0]);
                      }
				  }else{
                      $content[0]="";
				  } 
			 }else{
                   $content[0]="";
			 }
             return $content[0];     
    }   
    //返回结果 字符串数组
    function getarr($startstr,$endstr,$content,$pid=1){
		  if(empty($startstr) || empty($endstr)){
	          return '';
		  }
	      $newars="";
	      $startstr=str_replace("'","\'",str_replace("/","\/",$startstr));
	      $endstr=str_replace("'","\'",str_replace("/","\/",$endstr));
	      $theregstr='/'.$startstr.'(.*?)'.$endstr.'/';
	      preg_match_all($theregstr,$content,$match_array);
	      $newarray=$match_array[1];
          if($newarray){
              if($pid==1){
 	               foreach ($newarray as $row) { 
                        if(substr($this->web_url,0,7)=="http://"){
                       		$http = 'http://';
                        }else{
                       		$http = 'https://';
                        }
                        if(substr($row,0,8)!="https://" && substr($row,0,7)!="http://"){
                       		$row = $this->web_url.$row;
					   		$row = str_replace($http,"",$row);
					   		$row = $http.str_replace("//","/",$row);
                        }else{
                            if(substr($row,0,7)=="http://"){
                                $http = 'http://';
                            }else{
                                $http = 'https://';
                            }
                            $row = str_replace($http,"",$row);
                            $row = $http.str_replace("//","/",$row);
                        }
        	            $newars[] = $row;
 	               }
              }else{
 	               foreach ($newarray as $rowp) { 
                       $newars.=$rowp."$$$";
 	               }
                   $newars.="]]]";
                   $newars=str_replace('$$$]]]','',$newars);
                   $newars=str_replace("$$$","\r\n",$newars);
              }
          }else{
               $newars="";
          }
	      return $newars;
    }

    //采集内容替换
    function rep($str,$reg){
          if(!empty($reg)){
	           $reg=str_replace('\\','',$reg);
	           $regArr=explode("\n",$reg);
	           for($i=0;$i<count($regArr);$i++){
		          $restr=explode('@cscms@',$regArr[$i]);
		          $ostr=$restr[0];
		          $nstr=@$restr[1];
		          $str=str_replace($ostr,$nstr,$str);
	           }
          }
	      $str=str_replace("\r","",$str);
          return $str;	
    }
}


