<?php
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-08-21
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');

//播放列表解析
function Vod_Playlist($Mark_Text,$Ac,$ID,$Data,$Zu=0,$Ji=0){
    preg_match_all("/{vod:".$Ac."}([\s\S]+?){\/vod:".$Ac."}/",$Mark_Text,$field_arr);
    $Data_Arr=explode("#cscms#",$Data);
    if(!empty($field_arr[0]) && !empty($field_arr)){
        for($s=0;$s<count($field_arr[0]);$s++){
            $Mark_Text2="";$data_url="";
            if(!empty($Data)){
                for($i=0;$i<count($Data_Arr);$i++){
                    $Play_Content_Temp=$field_arr[1][$s];
                    $count_urls = explode("\n",$Data_Arr[$i]);
                    $count_laiys = explode("$",$count_urls[0]);
                    $Play_Content_Temp=str_replace('['.$Ac.':num]',count($count_urls),$Play_Content_Temp);
                    $Play_Content_Temp=str_replace('['.$Ac.':name]',Laiyuan(@$count_laiys[2]),$Play_Content_Temp);
                    $Play_Content_Temp=str_replace('['.$Ac.':i]',($i+1),$Play_Content_Temp);
                    $Play_Content_Temp=str_replace('['.$Ac.':ly]',@$count_laiys[2],$Play_Content_Temp);
                    preg_match_all("/{vod:url\s+order=([\s\S]+?)}([\s\S]+?){\/vod:url}/",$Play_Content_Temp,$type_arr);
                    if(isset($type_arr[2][0])){
                        $Url_Content_Temp=$type_arr[2][0];
                    }else{
                        $Url_Content_Temp='';
                    }

                    if(!empty($type_arr) && !empty($type_arr[0])){
                       $data_url=Vod_Urllist($Url_Content_Temp,$Ac,$ID,$count_urls,$i,$Ji,$type_arr[1][0]);
                    }
                    if(isset($type_arr[0][0])){
                       $Mark_Text2.=str_replace($type_arr[0][0],$data_url,$Play_Content_Temp);
                    }else{
                       $Mark_Text2.=$Play_Content_Temp;
                    }
                }
            }
            $Mark_Text=str_replace($field_arr[0][$s],$Mark_Text2,$Mark_Text);
        }
    }
    unset($field_arr);	
    return $Mark_Text;
}

//播放集数解析
function Vod_Urllist($Mark_Text,$Ac,$ID,$Data,$Zu=0,$Ji=0,$order){
    $data_url="";
    if($order=='asc'){ //倒序
        for($j=count($Data);$j>=0;$j--){
            $ListName=explode('$',$Data[$j]);
            if(!empty($ListName[0]) && !empty($ListName[1])){
                $data_url2=str_replace("[url:link]",VodPlayUrl($Ac,$ID,$Zu,$j),$Mark_Text);
                $data_url2=str_replace("[url:qurl]",$ListName[1],$data_url2);
                $data_url2=str_replace("[url:name]",$ListName[0],$data_url2);
                $data_url2=str_replace("[url:i]",($j+1),$data_url2);
                $data_url.=$data_url2;
            }
        }
    }else{  //正序
        for($j=0;$j<count($Data);$j++){
            $ListName=explode('$',$Data[$j]);
            if(!empty($ListName[0]) && !empty($ListName[1])){
                $data_url2=str_replace("[url:link]",VodPlayUrl($Ac,$ID,$Zu,$j),$Mark_Text);
                $data_url2=str_replace("[url:qurl]",$ListName[1],$data_url2);
                $data_url2=str_replace("[url:name]",$ListName[0],$data_url2);
                $data_url2=str_replace("[url:i]",($j+1),$data_url2);
                $data_url.=$data_url2;
            }
        }
    }
    return $data_url;
}

//播放地址解析
function VodPlayUrl($Ac,$ID,$Zu=0,$Ji=0,$p=0){
    $Web_Mode=config('Web_Mode','vod');
    switch($Web_Mode){
    case '1':  //动态模式
        if($p==0){
            $linkurl="index.php/vod/".$Ac."/".$ID."/".$Zu."/".$Ji;
        }else{
            $linkurl="index.php/vod/".$Ac."/{id}/{zu}/{ji}";
        }
    break;
    case '2':  //伪静态模式
        $arr=config('Rewrite_Uri','vod');
        $linkurl= ($Ac=='play')?$arr['play']['url']:$arr['down']['url'];
        $linkurl='vod/'.$linkurl;
        if($p==0){
            $linkurl=str_replace(array("{id}","{zu}","{ji}"),array($ID,$Zu,$Ji),$linkurl);
        }
    break;
    case '3':  //静态模式
       $html=config('Html_Uri','vod');
       if($Ac=='play' && $html['play']['check']==1){
            $linkurl= $html['play']['url'];
            if($p==0){
                $linkurl=str_replace(array("{id}","{zu}","{ji}"),array($ID,$Zu,$Ji),$linkurl);
    		}
       }else{
            if($p==0){
                $linkurl="index.php/vod/".$Ac."/".$ID."/".$Zu."/".$Ji;
            }else{
                $linkurl="index.php/vod/".$Ac."/{id}/{zu}/{ji}";
            }
       }
    break;
    }
    $linkurl="http://".Web_Url.Web_Path.$linkurl;
    //开启二级域名
    $Ym_Mode=config('Ym_Mode','vod'); //二级域名状态
    $Ym_Url=config('Ym_Url','vod');   //二级域名地址
    if($Ym_Mode==1){
       if($Web_Mode==2){ //伪静态
            $linkurl  = str_replace(Web_Url.Web_Path."vod/",$Ym_Url.Web_Path,$linkurl);
       }else{
    	    $linkurl  = str_replace(Web_Url.Web_Path."index.php/vod/",$Ym_Url.Web_Path."index.php/",$linkurl);
       }
    }
     return $linkurl;
}

//获取来源解析
function Laiyuan($str){
    if(empty($str))  return "未知";
    require CSCMS.'vod'.FGF.'player.php';
    $player=$player_config;
    for ($i=0; $i<count($player); $i++) {
        if($player[$i]['form']==$str){
            $str=$player[$i]['name'];
            break;
        }
    }
    return $str;
}
//采集
function caiji($spurl='',$sid=0){
    $str['error']='no';
    if(!empty($spurl) && substr($spurl,0,7)=='http://'){
       
        if(strpos($spurl,'ku6.com') !== FALSE){
            $neir=htmlall($spurl);
            if(!empty($neir)){
                $str['name']=str_substr('<title>',' 在线观看',$neir);
                $str['pic']=str_substr('cover: "','"',$neir);
                $str['url']=str_substr("snyu_page_params='vid=",'&category=',$neir);
        		$str['laiy']="ku6";
            }
        }elseif(strpos($spurl,'youku.com') !== FALSE){
            $neir=htmlall($spurl,'utf-8');
            if(!empty($neir)){
                $str['name']=str_substr('<title>','—在线播放',$neir);
                $str['pic']='';
                preg_match('/http:\/\/v.youku.com\/v_show\/id_\s*(.+).html/',$spurl,$vid);
                if(!empty($vid) && !empty($vid[1])){
                    $str['url']=$vid[1];
                }
                $str['laiy']="youku";
            }
        }elseif(strpos($spurl,'tudou.com') !== FALSE){
            $neir=htmlall($spurl,'utf-8');
            if(!empty($neir)){
                $str['name']=str_substr(",kw: '","'",$neir);
                $str['pic']=str_substr(",pic: '","'",$neir);
                $str['url']=str_substr(",icode: '","'",$neir);
        		$str['url']=trim($str['url']);
                $str['laiy']="tudou";
            }
        }elseif(strpos($spurl,'sohu.com') !== FALSE){
            $neir=htmlall($spurl);
            if(!empty($neir)){
                $str['name']=str_substr('<title>',' -',$neir);
                $str['pic']=str_substr('var cover="','";',$neir);
                $str['url']=str_substr('var vid="','";',$neir);
                $str['laiy']="sohu";
            }
        }elseif(strpos($spurl,'sina.com.cn') !== FALSE){
            $neir=htmlall($spurl,'utf-8');
            if(!empty($neir)){
                $str['name']=str_substr("title:'","',",$neir);
                $str['pic']=str_substr("pic: '","'",$neir);
                $str['url']=str_substr("vid:'","'",$neir);
                $str['laiy']="sina";
            }
        }elseif(strpos($spurl,'qq.com') !== FALSE){
            $neir=get_curl_contents($spurl,'utf-8');
            if(!empty($neir)){
                $str['name']=str_substr('title : "','"',$neir);
                $str['pic']=str_substr('pic :"','",',$neir);
                $str['url']=str_substr('vid:"','"',$neir);
                $str['laiy']="qq";
            }
        }elseif(strpos($spurl,'pps.tv') !== FALSE){
            $neir=htmlall($spurl);
            if(!empty($neir)){
                $str['name']=str_substr("<meta name='description' content=",'">',$neir);
                $str['name']=substr($str['name'],1);
                $str['pic']=str_substr('"sharepic":"','",',$neir);
                $str['pic']=str_replace("\\","",$str['pic']);
                $str['url']=str_substr('"url_key":"','",',$neir);
                $str['laiy']="pps";
            }
        }elseif(strpos($spurl,'letv.com') !== FALSE){
            $neir=htmlall($spurl,'utf-8');
            if(!empty($neir)){
                $str['name']=str_substr('title:"','",',$neir);
                $str['pic']=str_substr('videoPic:"','"',$neir);
                $str['url']=str_substr('vid:',',',$neir);
                $str['laiy']="letv";
            }
        }elseif(strpos($spurl,'56.com') !== FALSE){
            $neir=htmlall($spurl,'utf-8');
            if(!empty($neir)){
        		preg_match_all('/<img src="(.*)" alt="(.*)" class="user_cover_img" \/>/',$neir,$arr);
        		if(!empty($arr[1][0])){
                    $str['pic']=$arr[1][0];
                    $str['name']=$arr[2][0];
        		}
                $str['url']=str_substr('"id":',',',$neir);
                $str['laiy']="56";
            }
        }elseif(strpos($spurl,'yinyuetai.com') !== FALSE){
            $neir=htmlall($spurl,'utf-8');
            if(!empty($neir)){
                $str['name']=str_substr('title : "','",',$neir);
                $str['pic']=str_substr('<meta property="og:image" content="','"/>',$neir);
        		preg_match('/http:\/\/v.yinyuetai.com\/video\/\s*([0-9]+)/',$spurl,$vid);
                $str['url']=@$vid[1];
                $str['laiy']="yyt";
            }
        }
    }
    if(empty($str['url'])){
       $str['url']=$spurl;
    }
    $str['error']='ok';
    if(!empty($str['name'])) $str['name']=get_bm($str['name'],'gbk','utf-8');
    if($sid==0) $str=json_encode($str);
    return $str;
}
//获取平均分数
function getpf($pf, $phits, $sid=1){
    if($pf==0) return 0;
    $aver = $pf / $phits;
    if($sid==1){ //平均分数
        $aver = round($aver, 1);
    }else{  //百分比
        $aver = round($aver, 2) * 10;
    }
    return $aver;
}
//抓取内容
function get_curl_contents($url,$bm='gbk', $second = 30){
    if(!function_exists('curl_init')) die('php.ini未开启php_curl.dll');
    $c = curl_init();
    curl_setopt($c,CURLOPT_URL,$url);
    $UserAgent=$_SERVER['HTTP_USER_AGENT'];
    curl_setopt($c,CURLOPT_USERAGENT,$UserAgent);
    curl_setopt($c,CURLOPT_HEADER,0);
    curl_setopt($c,CURLOPT_TIMEOUT,$second);
    curl_setopt($c,CURLOPT_RETURNTRANSFER, true);
    $cnt = curl_exec($c);
    $cnt=mb_check_encoding($cnt,$bm)?iconv('gbk','utf-8//IGNORE',$cnt):$cnt; //字符编码转换
    curl_close($c);
    return $cnt;
}

//获取播放记录的实际ID，并输出地址
function get_play_url($id='',$ac='show'){
    $arr = explode('-', $id);
	if($ac=='show'){
        return linkurl('show','id',$arr[0],1,'vod');
	}else{
        return VodPlayUrl('play',$arr[0],$arr[1],$arr[2]);
	}
}
