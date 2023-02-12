<?php
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-04-27
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');
class Cstpl extends CI_Model{

	//构造方法初始化
	public function __construct() {
    	parent:: __construct ();
	}

    //网站主页
    public function home($return = FALSE, $fidetpl=array()) {
        $cache_id = "index_".(int)defined('MOBILE');
        if(!$this->Cscache->get($cache_id)){
			$Mark_Text = $this->load->view('index.html',array(),true);
			$Mark_Text = $this->Csskins->template_parse($Mark_Text,true,$return);
			//替换自定义标签
			if(!empty($fidetpl) && is_array($fidetpl)){
				foreach ($fidetpl as $key => $value) {
					$Mark_Text = str_replace($key, $value, $Mark_Text);
				}
			}
			if ($return == FALSE){
				$Mark_Text = $this->Csskins->labelif($Mark_Text);
				echo $Mark_Text;
			}else{
				return $Mark_Text;
			}
		    //写入缓存
			$this->Cscache->save();
		}
	}

    //板块主页
    public function plub_index($plub, $tpl='index.html', $return = FALSE, $t='', $k='', $d='', $fidetpl=array()) {
	    $plub = PLUBPATH == 'sys' ? $plub : PLUBPATH;
        $cache_id = $plub."_index_".(int)defined('MOBILE');
        if($return || !$this->Cscache->get($cache_id)){
        	//获取模版
			$template = $this->load->view($tpl,array(),true);
			//获取板块全局SEO
			$Seo = config('Seo');
			//SEO标题
			if(!empty($t)){
			    $seo_title=$t;
			}elseif(!empty($Seo['title'])){
			    $seo_title=$Seo['title'];
			}else{
			    $seo_title=str_decode(Web_Title);
			}
			//SEO关键词
			if(!empty($k)){
			    $seo_keywords=$k;
			}elseif(!empty($Seo['keywords'])){
			    $seo_keywords=$Seo['keywords'];
			}else{
			    $seo_keywords=str_decode(Web_Keywords);
			}
			//SEO描述
			if(!empty($d)){
			    $seo_description=$d;
			}elseif(!empty($Seo['description'])){
			    $seo_description=$Seo['description'];
			}else{
			    $seo_description=str_decode(Web_Description);
			}

			$Mark_Text=$this->Csskins->topandend($template);
			$Mark_Text=str_replace("{cscms:title}",$seo_title,$Mark_Text);
			$Mark_Text=str_replace("{cscms:keywords}",$seo_keywords,$Mark_Text);
			$Mark_Text=str_replace("{cscms:description}",$seo_description,$Mark_Text);
			$Mark_Text=$this->Csskins->template_parse($template,true,$return);
			//替换自定义标签
			if(!empty($fidetpl) && is_array($fidetpl)){
				foreach ($fidetpl as $key => $value) {
					$Mark_Text = str_replace($key, $value, $Mark_Text);
				}
			}
			if ($return == FALSE){
				$Mark_Text = $this->Csskins->labelif($Mark_Text);
				echo $Mark_Text;
			}else{
			  return $Mark_Text;
			}
			//写入缓存
		    $this->Cscache->save();
		}
	}

	//板块分类列表循环，row分类数据数组、id 当前数据ID、page 当前分页ID、tpl 当前分类模板、field标签前缀、t SEO标题、k SEO关键词、d SEO简介
    public function plub_list($row, $id, $fid='id', $page=1, $ids=array(), $return = FALSE, $tpl='list.html', $type='lists', $field='', $t='', $k='', $d='', $fidetpl=array(), $sql='', $hitsurl='', $onetpl=array()){
	    if(empty($ids)) $ids = $id;
	    if(empty($row)) $row = array();
        $plub = PLUBPATH == 'sys' ? 'index' : PLUBPATH;
        //页面缓存标示
        $cache_id = $plub."_".$type."_".$id."_".$fid."_".$tpl."_".$page."_".(int)defined('MOBILE');
        if($return || !$this->Cscache->get($cache_id)){ //缓存是否存在
			$data=$data_content=$aliasname='';
			//装载模板
			if(empty($tpl)) $tpl='list.html';
			$template=$this->load->view($tpl,$data,true);

			//获取板块全局SEO
			$Seo=config('Seo');

			//SEO标题
			if(!empty($row['title'])){
			    $seo_title=$row['title'];
			}elseif(!empty($t)){
			    $seo_title=$t;
			}elseif(!empty($Seo['title'])){
			    $seo_title=$Seo['title'];
			}else{
			    $seo_title=str_decode(Web_Title);
			}
			//SEO关键词
			if(!empty($row['keywords'])){
			    $seo_keywords=$row['keywords'];
			}elseif(!empty($k)){
			    $seo_keywords=$k;
			}elseif(!empty($Seo['keywords'])){
			    $seo_keywords=$Seo['keywords'];
			}else{
			    $seo_keywords=str_decode(Web_Keywords);
			}
			//SEO描述
			if(!empty($row['description'])){
			    $seo_description=$row['description'];
			}elseif(!empty($d)){
			    $seo_description=$d;
			}elseif(!empty($Seo['description'])){
			    $seo_description=$Seo['description'];
			}else{
			    $seo_description=str_decode(Web_Description);
			}

			$Mark_Text=$this->Csskins->topandend($template);
			$Mark_Text=str_replace("{cscms:title}",$seo_title,$Mark_Text);
			$Mark_Text=str_replace("{cscms:keywords}",$seo_keywords,$Mark_Text);
			$Mark_Text=str_replace("{cscms:description}",$seo_description,$Mark_Text);
			$Mark_Text=str_replace("{cscms:fid}",$fid,$Mark_Text); //当前使用的fid
			$Mark_Text=str_replace("{cscms:ids}",$id,$Mark_Text); //当前使用的id

			//预先除了分页
			$pagenum=getpagenum($Mark_Text);
			preg_match_all('/{cscms:([\S]+)\s+(.*?pagesize=\"([\S]+)\".*?)}([\s\S]+?){\/cscms:\1}/',$Mark_Text,$page_arr);//判断是否有分页标识
			if(!empty($page_arr) && !empty($page_arr[2])){

				$fields=$page_arr[1][0]; //前缀名
				//组装SQL数据
				$sort=$this->get_sort($fid);
				$sqlstr=$this->Csskins->cscms_sql($page_arr[1][0],$page_arr[2][0],$page_arr[0][0],$page_arr[3][0],$sort,$ids,0,$sql);
				//总数量
				$nums = $this->Csdb->get_allnums($sqlstr);
				//翻页标签是否存在
				$plist = strpos($Mark_Text, '{cscms:pagelist}') !== false;
				$Arr = spanpage($sqlstr,$nums,$page_arr[3][0],$pagenum,$type,$fid,$id,$page,$plist);
				//判断页数大于2/3则倒序显示
				if($Arr[6]>10 && $page > $Arr[6]*2/3){
					$Arr[0] = trim(current(explode(' limit ', strtolower($Arr[0]))));
					if(substr($Arr[0],-4) == 'desc'){
						$Arr[0] = substr($Arr[0],0,-4).' asc';
					}elseif(substr($Arr[0],-3) == 'asc'){
						$Arr[0] = substr($Arr[0],0,-3).' desc';
					}
					$spage = ($Arr[6]-$page)*$Arr[7];
					$Arr[0] .= ' limit '.$spage.','.$Arr[7];
				}
				if($nums>0){
				    $sorti=1;
				    $result_array=$this->db->query($Arr[0])->result_array();
				    foreach ($result_array as $row2) {
						$datatmp='';
				        $datatmp=$this->Csskins->cscms_skins($fields,$page_arr[0][0],$page_arr[4][0],$row2,$sorti);
				        $sorti++;
				        $data_content.=$datatmp;
				    }
				}
				$Mark_Text=page_mark($Mark_Text,$Arr);	//分页解析
				$Mark_Text=str_replace($page_arr[0][0],$data_content,$Mark_Text);
			}
			unset($page_arr);
			$Mark_Text=$this->Csskins->cscms_common($Mark_Text);
			$Mark_Text=$this->Csskins->csskins($Mark_Text,$ids);
			$Mark_Text=$this->Csskins->cscms_skins($field,$Mark_Text,$Mark_Text,$row);//解析当前数据标签
			$Mark_Text=$this->Csskins->template_parse($Mark_Text,true,$return);
			//替换自定义标签
			if(!empty($fidetpl) && is_array($fidetpl)){
				foreach ($fidetpl as $key => $value) {
					$Mark_Text = str_replace($key, $value, $Mark_Text);
				}
			}
			//解析当前数据
			if(!empty($onetpl) && is_array($onetpl)){
				$Mark_Text=$this->Csskins->cscms_skins($onetpl[0],$Mark_Text,$Mark_Text,$onetpl[1]);
			}
			//加入人气统计
			if(!empty($hitsurl)){
				$Mark_Text = hits_js($Mark_Text,$hitsurl);
			}
			if ($return == FALSE){
				$Mark_Text = $this->Csskins->labelif($Mark_Text);
				echo $Mark_Text;
			}else{
				return $Mark_Text;
			}
			//写入缓存
		    $this->Cscache->save();
		}
	}

	//会员列表循环，row会员数据数组、id 当前数据ID、page 当前分页ID、tpl 当前分类模板、field标签前缀、t SEO标题
	public function user_list($row, $url='', $page=1, $tpl='space.html', $t='会员中心', $fid='', $sql='', $ids='', $return = FALSE, $field='user', $fidetpl=array(),$onetpl=array()){
		$dir = PLUBPATH != 'sys' ? PLUBPATH : '';
		$data=$data_content=$aliasname='';
		//装载模板
		$template=$this->load->view($tpl,$data,true);
		//SEO标题
		$seo_title=(!empty($t)) ? $t : str_decode(Web_Title);
		//SEO关键词
		$seo_keywords=str_decode(Web_Keywords);
		//SEO描述
		$seo_description=str_decode(Web_Description);
		$Mark_Text=$this->Csskins->topandend($template);
		$Mark_Text=str_replace("{cscms:title}",$seo_title,$Mark_Text);
		$Mark_Text=str_replace("{cscms:keywords}",$seo_keywords,$Mark_Text);
		$Mark_Text=str_replace("{cscms:description}",$seo_description,$Mark_Text);
		$Mark_Text=str_replace("{cscms:fid}",$fid,$Mark_Text); //当前使用的fid
		//预先除了分页
		$pagenum=getpagenum($Mark_Text);
		preg_match_all('/{cscms:([\S]+)\s+(.*?pagesize=\"([\S]+)\".*?)}([\s\S]+?){\/cscms:\1}/',$Mark_Text,$page_arr);
		if(!empty($page_arr) && !empty($page_arr[2])){

			$fields=$page_arr[1][0]; //前缀名
			//组装SQL数据
			$sqlstr=$this->Csskins->cscms_sql($page_arr[1][0],$page_arr[2][0],$page_arr[0][0],$page_arr[3][0],'id',$ids,0,$sql);
			//总数量
			$nums = $this->Csdb->get_allnums($sqlstr);
			$Arr=userpage($sqlstr,$nums,$page_arr[3][0],$pagenum,$url,$page,$dir);
			//判断页数大于2/3则倒序显示
			if($Arr[6]>10 && $page > $Arr[6]*2/3){
				$Arr[0] = trim(current(explode(' limit ', strtolower($Arr[0]))));
				if(substr($Arr[0],-4) == 'desc'){
					$Arr[0] = substr($Arr[0],0,-4).' asc';
				}elseif(substr($Arr[0],-3) == 'asc'){
					$Arr[0] = substr($Arr[0],0,-3).' desc';
				}
				$spage = ($Arr[6]-$page)*$Arr[7];
				$Arr[0] .= ' limit '.$spage.','.$Arr[7];
			}
			if($nums>0){
			    $sorti=1;
			    $result_array=$this->db->query($Arr[0])->result_array();
			    foreach ($result_array as $row2) {
					$datatmp='';
			        $datatmp=$this->Csskins->cscms_skins($fields,$page_arr[0][0],$page_arr[4][0],$row2,$sorti);
			        $sorti++;
			        $data_content.=$datatmp;
			    }
			}
			$Mark_Text=page_mark($Mark_Text,$Arr);	//分页解析
			$Mark_Text=str_replace($page_arr[0][0],$data_content,$Mark_Text);
		}
		unset($page_arr);
		$Mark_Text=$this->Csskins->cscms_common($Mark_Text);
		$Mark_Text=$this->Csskins->csskins($Mark_Text,$ids);
		$Mark_Text=$this->Csskins->cscms_skins($field,$Mark_Text,$Mark_Text,$row);
		//解析当前数据标签
		$Mark_Text=$this->Csskins->template_parse($Mark_Text,true,$return,$row);
		//会员版块导航
		$uid = isset($_SESSION['cscms__id']) ? $_SESSION['cscms__id'] : 0 ;
		$Mark_Text=$this->Csskins->cscmsumenu($Mark_Text,$uid);
		//替换自定义标签
		if(!empty($fidetpl) && is_array($fidetpl)){
			foreach ($fidetpl as $key => $value) {
				$Mark_Text = str_replace($key, $value, $Mark_Text);
			}
		}
		//解析当前数据
		if(!empty($onetpl) && is_array($onetpl)){
			$Mark_Text=$this->Csskins->cscms_skins($onetpl[0],$Mark_Text,$Mark_Text,$onetpl[1]);
		}
		if ($return == FALSE){
			$Mark_Text = $this->Csskins->labelif($Mark_Text);
		    echo $Mark_Text;
		}else{
		    return $Mark_Text;
		}
	}

	//会员主页，row会员数据数组、id 当前数据ID、page 当前分页ID、tpl 当前分类模板、field标签前缀、t SEO标题
    public function home_list($row, $op='', $page=1, $tpl='index.html', $t='', $ids='', $fid='', $sql='', $return = FALSE, $field='user', $fidetpl=array(), $onetpl=array(),$hitsurl=''){
		//页面缓存标示
        $cache_id =$tpl."_".$row['id']."_".$op."_".$fid."_".$page."_".(int)defined('MOBILE'); 
        if($return || !$this->Cscache->get($cache_id)){ //缓存是否存在
			$dir = PLUBPATH != 'sys' ? PLUBPATH : '';
			$data=$data_content=$aliasname='';
			if(empty($row['skins'])){
				$row['skins'] = defined('MOBILE') ? Mobile_Home_Dir : Pc_Home_Dir;
			}else{
				if(defined('MOBILE')){
					$skinfile = 'mobile'.FGF.'home'.FGF.str_replace('/', FGF, $row['skins']).'sys'.FGF.'index.html';
				}else{
					$skinfile = 'pc'.FGF.'home'.FGF.str_replace('/', FGF, $row['skins']).'sys'.FGF.'index.html';
				}
				if(!file_exists(VIEWPATH.$skinfile)){
					$row['skins'] = defined('MOBILE') ? Mobile_Home_Dir : Pc_Home_Dir;
				}
			}
			if(!defined('HOMESKINS')) define('HOMESKINS',$row['skins']);
			if(!defined('HOMEPATH')) define('HOMEPATH',true);
			$this->load->get_templates('',$row['skins']);
			$template=$this->load->view($tpl,$data,true);
			//SEO标题
			$seo_title=(!empty($t)) ? $t : $row['nichen'].'的个人主页';
			//SEO关键词
			$seo_keywords=str_decode(Web_Keywords);
			//SEO描述
			$seo_description=str_decode(Web_Description);
			$Mark_Text=$this->Csskins->topandend($template,$row['skins']);
			$Mark_Text=str_replace("{cscms:title}",$seo_title,$Mark_Text);
			$Mark_Text=str_replace("{cscms:keywords}",$seo_keywords,$Mark_Text);
			$Mark_Text=str_replace("{cscms:description}",$seo_description,$Mark_Text);
			$Mark_Text=str_replace("{cscms:fid}",$fid,$Mark_Text); //当前使用的fid
			//预先除了分页
			$pagenum=getpagenum($Mark_Text);
			preg_match_all('/{cscms:([\S]+)\s+(.*?pagesize=\"([\S]+)\".*?)}([\s\S]+?){\/cscms:\1}/',$Mark_Text,$page_arr);
			if(!empty($page_arr) && !empty($page_arr[2])){
				$fields=$page_arr[1][0]; //前缀名
				//组装SQL数据
				$sqlstr=$this->Csskins->cscms_sql($page_arr[1][0],$page_arr[2][0],$page_arr[0][0],$page_arr[3][0],'id',$ids,0,$sql);
				//总数量
				$nums = $this->Csdb->get_allnums($sqlstr);
				$Arr=homepage($sqlstr,$nums,$page_arr[3][0],$pagenum,$op,$row['id'],$row['name'],$fid,$page);
				//判断页数大于2/3则倒序显示
				if($Arr[6]>10 && $page > $Arr[6]*2/3){
					$Arr[0] = trim(current(explode(' limit ', strtolower($Arr[0]))));
					if(substr($Arr[0],-4) == 'desc'){
						$Arr[0] = substr($Arr[0],0,-4).' asc';
					}elseif(substr($Arr[0],-3) == 'asc'){
						$Arr[0] = substr($Arr[0],0,-3).' desc';
					}
					$spage = ($Arr[6]-$page)*$Arr[7];
					$Arr[0] .= ' limit '.$spage.','.$Arr[7];
				}
				if($nums>0){
				    $sorti=1;
				    $result_array=$this->db->query($Arr[0])->result_array();
				    foreach ($result_array as $row2) {
						$datatmp='';
				        $datatmp=$this->Csskins->cscms_skins($fields,$page_arr[0][0],$page_arr[4][0],$row2,$sorti);
				        $sorti++;
				        $data_content.=$datatmp;
				    }
				}
				$Mark_Text=page_mark($Mark_Text,$Arr);	//分页解析
				$Mark_Text=str_replace($page_arr[0][0],$data_content,$Mark_Text);
			}
			unset($page_arr);
			$Mark_Text=$this->Csskins->cscms_common($Mark_Text,$row['skins']);
			$Mark_Text=$this->Csskins->csskins($Mark_Text,$ids);
			$Mark_Text=$this->Csskins->cscms_skins('user',$Mark_Text,$Mark_Text,$row);
			$Mark_Text=$this->Csskins->template_parse($Mark_Text,true,$return);
			//替换自定义标签
			if(!empty($fidetpl) && is_array($fidetpl)){
				foreach ($fidetpl as $key => $value) {
					$Mark_Text = str_replace($key, $value, $Mark_Text);
				}
			}
			//解析当前数据
			if(!empty($onetpl) && is_array($onetpl)){
				$Mark_Text=$this->Csskins->cscms_skins($onetpl[0],$Mark_Text,$Mark_Text,$onetpl[1]);
			}
			//加入人气统计
			if(!empty($hitsurl)){
				$Mark_Text = hits_js($Mark_Text,$hitsurl);
			}
			if ($return == FALSE){
				$Mark_Text = $this->Csskins->labelif($Mark_Text);
			    echo $Mark_Text;
			}else{
			    return $Mark_Text;
			}
			//写入缓存
		    $this->Cscache->save();
		}
	}

	//板块搜索列表循环，dir搜索的数据库名称、so 搜索数组、page 当前分页ID、tpl 模板、t SEO标题、k 关键词、d 简介
    public function plub_search($dir, $so, $page=1, $return = FALSE, $tpl='search.html', $t='', $k='', $d='',$fidetpl=array()){
	    $so = get_bm($so);
	    //获取自定义搜索字段
	    $field = require CSCMS.'sys/Cs_Field.php';
	    $zdarr = array('name');
	    if(isset($field[$dir])){
	    	foreach ($field[$dir] as $k => $v) {
	    		if(isset($v['search']) && $v['search']==1){
	    			$var = $this->input->get_post($k,true,true);
	    			if(!empty($var)){
	    				$so[$k] = $var;
	    			}
	    			$zdarr[] = $k;
	    		}
	    	}
	    }
	    $mjk = md5(json_encode($so));
	    $cache_id = 'so_'.$dir.'_'.$mjk.'_'.$tpl.'_'.$page.'_'.(int)defined('MOBILE');
        if($return || !$this->Cscache->get($cache_id)){ //缓存是否存在
	        $data=$data_content=$aliasname=$zm='';
	        //装载模板
	        $template=$this->load->view($tpl,$data,true);
		    //SEO标题
		    if(!empty($t)){
	       		$seo_title=$t;
		    }else{
				$seo_title="搜索 - ".Web_Name;
		    }
		    //SEO关键词
		    if(!empty($k)){
	       		$seo_keywords=$k;
			}else{
				$seo_keywords=str_decode(Web_Keywords);
			}
		    //SEO描述
		    if(!empty($d)){
	       		$seo_description=$d;
			}else{
				$seo_description=str_decode(Web_Description);
			}

	        $Mark_Text=$this->Csskins->topandend($template);
	        $Mark_Text=str_replace("{cscms:title}",$seo_title,$Mark_Text);
	        $Mark_Text=str_replace("{cscms:keywords}",$seo_keywords,$Mark_Text);
	        $Mark_Text=str_replace("{cscms:description}",$seo_description,$Mark_Text);

	        //预先除了分页
	        $pagenum=getpagenum($Mark_Text);
			preg_match_all('/{cscms:([\S]+)\s+(.*?pagesize=\"([\S]+)\".*?)}([\s\S]+?){\/cscms:\1}/',$Mark_Text,$page_arr);
	        if(!empty($page_arr) && !empty($page_arr[2])){

				$field=$page_arr[1][0]; //前缀名
				//组装SQL数据
				$sqlstr="select {field} from `".CS_SqlPrefix.$dir."`";
				$sql=$fid=$key="";
				$wk = array();
				foreach ($so as $k=>$v) {
					if ($this->db->field_exists($k, $dir) && !empty($v)){ //判断条件字段是否存在
						if(!preg_match("/^\d*$/",$v)){
							if(is_array($v)){
								$v2 = $v[0];
								$vs = implode(',',$v[$v2]);
								$v = $v2;
								$wk[] = $k." in(".$vs.") ";
							}else{
								$wk[] = $k." like '%".$v."%' ";
							}
						}else{
							$wk[] = $k."=".$v." ";
						}
						$Mark_Text=str_replace("{cscms:".$k."}",$v,$Mark_Text);
						$Mark_Text=str_replace("{cscms:key}",$v,$Mark_Text);
						$fid.="&".$k."=".urlencode($v);
					}
					if($k=='key' && !empty($v)){
						//自定义搜索字段
						$or = array();
						foreach ($zdarr as $ks) {
							if($this->db->field_exists($ks, $dir)){
								$or[] = $ks." like '%".$v."%'";
							}
						}
						if(!empty($or)){
							$wk[] = "(".implode(' or ',$or).") ";
							$Mark_Text=str_replace("{cscms:key}",$v,$Mark_Text);
							$fid.="&key=".urlencode($v);
						}
					}
					if($k=='zm'){
						$zd=(empty($v['zd']))?'name':$v['zd'];
						$zm=(empty($v['zm']))?'':$v['zm'];
						$zimu_arr=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
						$zimu_arr1=array(-20319,-20283,-19775,-19218,-18710,-18526,-18239,-17922,-1,-17417,-16474,-16212,-15640,-15165,-14922,-14914,-14630,-14149,-14090,-13318,-1,-1,-12838,-12556,-11847,-11055);
						$zimu_arr2=array(-20284,-19776,-19219,-18711  ,-18527,-18240,-17923,-17418,-1,-16475,-16213,-15641,-15166,-14923,-14915,-14631,-14150,-14091,-13319,-12839,-1,-1,-12557,-11848,-11056,-2050);
						if(!empty($zm) && !in_array(strtoupper($zm),$zimu_arr) && $this->db->field_exists($zd, $dir)){
						    $wk[] = "substring( ".$zd.", 1, 1 ) NOT REGEXP '^[a-zA-Z]' and substring( ".$zd.", 1, 1 ) REGEXP '^[u4e00-u9fa5]'";
						}elseif(in_array(strtoupper($zm),$zimu_arr) && $this->db->field_exists($zd, $dir)){
							$posarr=array_keys($zimu_arr,strtoupper($zm));
							$pos=$posarr[0];
							$wk[] = "(((ord( substring(convert(".$zd." USING gbk), 1, 1 ) ) -65536>=".($zimu_arr1[$pos])." and  ord( substring(convert(".$zd." USING gbk), 1, 1 ) ) -65536<=".($zimu_arr2[$pos]).")) or UPPER(substring(convert(".$zd." USING gbk), 1, 1 ))='".$zimu_arr[$pos]."')";
						}
						$Mark_Text=str_replace("{cscms:zm}",$zm,$Mark_Text);
						if(!empty($zm)) $fid.="&zm=".$zm;
					}
				}
				//组装条件
				if(!empty($wk)){
				    $fid=substr($fid,1);
				    $sql=implode(' and ',$wk);
				    $sqlstr.=" where ".$sql;
				}
				$sqlstr=$this->Csskins->cscms_sql($page_arr[1][0],$page_arr[2][0],$page_arr[0][0],$page_arr[3][0],'',0,0,$sqlstr);
				//总数量
				$nums = $this->Csdb->get_allnums($sqlstr);
				//翻页标签是否存在
				$plist = strpos($Mark_Text, '{cscms:pagelist}') !== false;
				$Arr=spanpage($sqlstr,$nums,$page_arr[3][0],$pagenum,'search',$fid,urlencode($key),$page,$plist);
				//判断页数大于2/3则倒序显示
				if($Arr[6]>10 && $page > $Arr[6]*2/3){
					$Arr[0] = trim(current(explode(' limit ', strtolower($Arr[0]))));
					if(substr($Arr[0],-4) == 'desc'){
						$Arr[0] = substr($Arr[0],0,-4).' asc';
					}elseif(substr($Arr[0],-3) == 'asc'){
						$Arr[0] = substr($Arr[0],0,-3).' desc';
					}
					$spage = ($Arr[6]-$page)*$Arr[7];
					$Arr[0] .= ' limit '.$spage.','.$Arr[7];
				}
				if($nums>0){
				    $sorti=1;
				    $result_array=$this->db->query($Arr[0])->result_array();
				    foreach ($result_array as $row2) {
				        $datatmp=$this->Csskins->cscms_skins($field,$page_arr[0][0],$page_arr[4][0],$row2,$sorti);
				        $sorti++;
				        $data_content.=$datatmp;
				    }
				}
				$Mark_Text=page_mark($Mark_Text,$Arr);	//分页解析
				$Mark_Text=str_replace($page_arr[0][0],$data_content,$Mark_Text);
			}
	        unset($page_arr);
			$Mark_Text=str_replace("{cscms:cid}",'',$Mark_Text);
			$Mark_Text=str_replace("{cscms:zhuyan}",'',$Mark_Text);
			$Mark_Text=str_replace("{cscms:daoyan}",'',$Mark_Text);
			$Mark_Text=str_replace("{cscms:yuyan}",'',$Mark_Text);
			$Mark_Text=str_replace("{cscms:diqu}",'',$Mark_Text);
			$Mark_Text=str_replace("{cscms:year}",'',$Mark_Text);
			$Mark_Text=str_replace("{cscms:tags}",'',$Mark_Text);
			$Mark_Text=str_replace("{cscms:name}",'',$Mark_Text);
			$Mark_Text=str_replace("{cscms:type}",'',$Mark_Text);
			$Mark_Text=str_replace("{cscms:key}",'',$Mark_Text);
			$Mark_Text=str_replace("{cscms:zm}",'',$Mark_Text);
	        $Mark_Text=$this->Csskins->cscms_common($Mark_Text);
			$Mark_Text=$this->Csskins->csskins($Mark_Text,$so);
	        $Mark_Text=$this->Csskins->template_parse($Mark_Text,true,$return);
			//替换自定义标签
			if(!empty($fidetpl) && is_array($fidetpl)){
				foreach ($fidetpl as $key => $value) {
					$Mark_Text = str_replace($key, $value, $Mark_Text);
				}
			}
			if ($return == FALSE){
				$Mark_Text = $this->Csskins->labelif($Mark_Text);
				echo $Mark_Text;
			}else{
	            return $Mark_Text;
			}
			//写入缓存
		    $this->Cscache->save();
		}
	}

    //板块内容
    public function plub_show($field, $row, $ids, $return = FALSE, $tpl='show.html', $t='', $k='', $d='', $cacheid='',$fidetpl=array(),$hitsurl = '',$page=0,$pagecount=0) {
		$plub = PLUBPATH == 'sys' ? 'index' : PLUBPATH;
		if(empty($row)) $row=array('id'=>0);
		$id = $row['id'];
		if(empty($cacheid)){
			$cacheid = $plub."_show_".$tpl."_".$id."_".(int)defined('MOBILE');
		}else{
			$cacheid .= '_'.(int)defined('MOBILE');
		}
		if($return || !$this->Cscache->get($cacheid)){
			$template=$this->load->view($tpl,array(),true);
			//获取板块全局SEO
			$Seo=config('Seo');
			//SEO标题
			if(!empty($row['title'])){
				$seo_title=$row['title'];
			}elseif(!empty($t)){
			    $seo_title=$t;
			}elseif(!empty($Seo['title'])){
			    $seo_title=$Seo['title'];
			}else{
			    $seo_title=str_decode(Web_Title);
			}
			//SEO关键词
			if(!empty($row['keywords'])){
			    $seo_keywords=$row['keywords'];
			}elseif(!empty($k)){
			    $seo_keywords=$k;
			}elseif(!empty($Seo['keywords'])){
			    $seo_keywords=$Seo['keywords'];
			}else{
			    $seo_keywords=str_decode(Web_Keywords);
			}
			//SEO描述
			if(!empty($row['description'])){
				$seo_description=$row['description'];
			}elseif(!empty($d)){
				$seo_description=$d;
			}elseif(!empty($Seo['description'])){
				$seo_description=$Seo['description'];
			}else{
				$seo_description=str_decode(Web_Description);
			}
			$Mark_Text=$this->Csskins->topandend($template);
			$Mark_Text=str_replace("{cscms:title}",$seo_title,$Mark_Text);
			$Mark_Text=str_replace("{cscms:keywords}",$seo_keywords,$Mark_Text);
			$Mark_Text=str_replace("{cscms:description}",$seo_description,$Mark_Text);
			$Mark_Text=str_replace("{cscms:id}",$id,$Mark_Text); //当前使用的id
			$Mark_Text=$this->Csskins->cscms_common($Mark_Text);
			$Mark_Text=$this->Csskins->csskins($Mark_Text,$ids);
			//解析当前数据标签
			$Mark_Text=$this->Csskins->cscms_skins($field,$Mark_Text,$Mark_Text,$row);
			//解析视频播放地址
			if(PLUBPATH == 'vod' && isset($row['purl'])){
				$Mark_Text = Vod_Playlist($Mark_Text,'play',$row['id'],$row['purl']);
	        	$Mark_Text = Vod_Playlist($Mark_Text,'down',$row['id'],$row['durl']);
			}
			//内容分页标签解析
			if($page>0){
				$pagenum = getpagenum($Mark_Text);
				$plist = preg_match('/{cscms:pagelist}/i', $Mark_Text);
				$Arr = spanpage('',$pagecount,1,$pagenum,'show','id',$id,$page,$plist);
				$Mark_Text = page_mark($Mark_Text,$Arr);	//分页解析
			}
			$Mark_Text=$this->Csskins->template_parse($Mark_Text,true,$return);
			//替换自定义标签
			if((!empty($fidetpl) && is_array($fidetpl))){
				foreach ($fidetpl as $key => $value) {
					$Mark_Text = str_replace($key, $value, $Mark_Text);
				}
			}
			//加入人气统计
			if(!empty($hitsurl)){
				$Mark_Text = hits_js($Mark_Text,$hitsurl);
			}
			if ($return == FALSE){
				$Mark_Text = $this->Csskins->labelif($Mark_Text);
				echo $Mark_Text;
			}else{
				return $Mark_Text;
			}
			//写入缓存
		    $this->Cscache->save();
		}
	}

	//网站留言
    public function gbook(){
        $cache_id ="cscms_gbook_".(int)defined('MOBILE');
        if(!$this->Cscache->get($cache_id)){
            $template=$this->load->view('gbook.html','',true);
		    $gbook="<div id='cscms_gbook'><img src='".Web_Path."packs/images/load.gif'>&nbsp;&nbsp;加载留言内容,请稍等......</div>\r\n<script type='text/javascript'>cscms.getlGbook(1,0,0);</script>";
            $Mark_Text=str_replace("{cscms:gbook}",$gbook,$template);
            $Mark_Text=$this->Csskins->template_parse($Mark_Text,true);
		    echo $Mark_Text;
			$this->Cscache->save(); //写入缓存
		}
	}

	//留言列表
    public function gbook_list($page=1){
	    if(User_BookFun==0){ //网站关闭留言
		    return "<div id='cscms_gbook'>网站已经关闭了在线留言~!</div>";
		}
	    $data_content='';
        //装载模板
        $Mark_Text=$this->load->view('gbook_ajax.html','',true);
        //预先除了分页
        $pagenum=getpagenum($Mark_Text);
		preg_match_all('/{cscms:([\S]+)\s+(.*?pagesize=\"([\S]+)\".*?)}([\s\S]+?){\/cscms:\1}/',$Mark_Text,$page_arr);
        if(!empty($page_arr) && !empty($page_arr[2])){

			$field=$page_arr[1][0]; //前缀名
			//组装SQL数据
			$sqlstr=$this->Csskins->cscms_sql($page_arr[1][0],$page_arr[2][0],$page_arr[0][0],$page_arr[3][0],'id',0);
			//总数量
			$nums = $this->Csdb->get_allnums($sqlstr);
			$Arr=spanajaxpage($sqlstr,$nums,$page_arr[3][0],$pagenum,'cscms.getlGbook',$page);
			//判断页数大于2/3则倒序显示
			if($Arr[6]>10 && $page > $Arr[6]*2/3){
				$Arr[0] = trim(current(explode(' limit ', strtolower($Arr[0]))));
				if(substr($Arr[0],-4) == 'desc'){
					$Arr[0] = substr($Arr[0],0,-4).' asc';
				}elseif(substr($Arr[0],-3) == 'asc'){
					$Arr[0] = substr($Arr[0],0,-3).' desc';
				}
				$spage = ($Arr[6]-$page)*$Arr[7];
				$Arr[0] .= ' limit '.$spage.','.$Arr[7];
			}
			if($nums>0){
			    $sorti=1;
			    $result_array=$this->db->query($Arr[0])->result_array();
			    foreach ($result_array as $row2) {
			        $datatmp=$this->Csskins->cscms_skins($field,$page_arr[0][0],$page_arr[4][0],$row2,$sorti);
			        $sorti++;
			        $data_content.=$datatmp;
			    }
			}
			$Mark_Text=page_mark($Mark_Text,$Arr);	//分页解析
			$Mark_Text=str_replace($page_arr[0][0],$data_content,$Mark_Text);
		}
        unset($page_arr);
		$Mark_Text=str_replace("[gbook:token]",get_token('gbook_token'),$Mark_Text);
        $Mark_Text=$this->Csskins->template_parse($Mark_Text,false);
		return $Mark_Text;
	}

	//评论列表
    public function pl($dir,$did,$cid=0,$page=1){
        if(Pl_Modes==1){ //友言
		    return '<div id="uyan_frame"></div><script type="text/javascript" src="http://v2.uyan.cc/code/uyan.js?uid='.Pl_Yy_Name.'"></script>';
	    }
        if(Pl_Modes==2){ //多说
		    return "<div class='ds-thread' data-thread-key='".$dir."-".$did."'></div><script type='text/javascript'>var duoshuoQuery={short_name:\"".Pl_Ds_Name."\"};(function(){var ds=document.createElement('script');ds.type='text/javascript';ds.async=true;ds.src=(document.location.protocol=='https:'?'https:':'http:')+'//static.duoshuo.com/embed.js';ds.charset='UTF-8';(document.getElementsByTagName('head')[0]||document.getElementsByTagName('body')[0]).appendChild(ds)})();</script>";
	    }
        if(Pl_Modes==3){ //畅言
		    return "<div id='SOHUCS' sid='".$dir."-".$did."'></div><script>(function(){var appid='".Pl_Cy_Id."',conf='prod_28f42ecbb9691ec71b9dcc68742151c2';var doc=document,s=doc.createElement('script'),h=doc.getElementsByTagName('head')[0]||doc.head||doc.documentElement;s.type='text/javascript';s.charset='utf-8';s.src='http://assets.changyan.sohu.com/upload/changyan.js?conf='+conf+'&appid='+appid;h.insertBefore(s,h.firstChild)})()</script>";
	    }
	    if(Pl_Modes==4){ //网站关闭评论
		    return "<div id='cscms_pl' style='text-align:center;'><b>网站已经关闭了评论~!</b></div>";
		}
	    if($did==0){ //参数错误
		    return "<div id='cscms_pl' style='text-align:center;'><b>参数错误，数据ID为空~!</b></div>";
		}
	    $data_content='';
        //装载模板
	    if($dir=='blog'){
            $skins=getzd('user','skins',getzd('blog','uid',$did));
		    if(!defined('HOMEPATH')) define('HOMEPATH', true);
		    $this->load->get_templates('',$skins);
	    }else{
		    //评论标示
		    define('OPT_DIR', $dir);
		    $this->load->get_templates();
		}
        $Mark_Text=$this->load->view('pl.html','',true);
        //预先除了分页
        $pagenum=getpagenum($Mark_Text);
		preg_match_all('/{cscms:([\S]+)\s+(.*?pagesize=\"([\S]+)\".*?)}([\s\S]+?){\/cscms:\1}/',$Mark_Text,$page_arr);
        if(!empty($page_arr) && !empty($page_arr[2])){

			$field=$page_arr[1][0]; //前缀名
			//组装SQL数据
			$arr['did']=$did;
			$sql="SELECT {field} FROM ".CS_SqlPrefix."pl where dir='".$dir."'";
			$sqlstr=$this->Csskins->cscms_sql($page_arr[1][0],$page_arr[2][0],$page_arr[0][0],$page_arr[3][0],'id',$arr,$cid,$sql);
			//总数量
			$nums = $this->Csdb->get_allnums($sqlstr);
			$Arr=spanajaxpage($sqlstr,$nums,$page_arr[3][0],$pagenum,'cscms.pl',$page);
			//判断页数大于2/3则倒序显示
			if($Arr[6]>10 && $page > $Arr[6]*2/3){
				$Arr[0] = trim(current(explode(' limit ', strtolower($Arr[0]))));
				if(substr($Arr[0],-4) == 'desc'){
					$Arr[0] = substr($Arr[0],0,-4).' asc';
				}elseif(substr($Arr[0],-3) == 'asc'){
					$Arr[0] = substr($Arr[0],0,-3).' desc';
				}
				$spage = ($Arr[6]-$page)*$Arr[7];
				$Arr[0] .= ' limit '.$spage.','.$Arr[7];
			}
			if($nums>0){
			    $sorti=1;
			    $result_array=$this->db->query($Arr[0])->result_array();
			    foreach ($result_array as $row2) {
			        $datatmp=$this->Csskins->cscms_skins($field,$page_arr[0][0],$page_arr[4][0],$row2,$sorti);
			        $sorti++;
			        $data_content.=$datatmp;
			    }
			}
			$Mark_Text=page_mark($Mark_Text,$Arr);	//分页解析
			$Mark_Text=str_replace($page_arr[0][0],$data_content,$Mark_Text);
		}
        unset($page_arr);
		$Mark_Text=str_replace("[pl:token]",get_token('pl_token'),$Mark_Text);
		//表情
        $plfaces="";
        for($i=1;$i<=56;$i++){
        	$plfaces.="<img style='cursor:pointer;' src=\"".Web_Path."packs/images/faces/e".$i.".gif\" onclick=\"$('#cscms_pl_content').val($('#cscms_pl_content').val()+'[em:".$i."]');$('#cscms_faces').hide();\" />";
        }
		$Mark_Text=str_replace("[pl:faces]",$plfaces,$Mark_Text);
		//判断登录
		$login='ok';
		if(Pl_Youke==0){
		    if(!$this->Csuser->User_Login(1)){
		        $login='no';
		    }
		}
		$Mark_Text=str_replace("[pl:login]",$login,$Mark_Text);
        $Mark_Text=$this->Csskins->template_parse($Mark_Text,false);
		return $Mark_Text;
	}

    //自定义OPT
    public function opt($op,$return = FALSE ,$fidetpl = array()) {
        $cache_id ="opt_".$op."_".(int)defined('MOBILE');
        if($return || !$this->Cscache->get($cache_id)){
            $tpl="opt-".$op.".html";
            $template = $this->load->view($tpl,array(),true);
            $Mark_Text = $this->Csskins->template_parse($template,true,$return);
			//替换自定义标签
			if(!empty($fidetpl) && is_array($fidetpl)){
				foreach ($fidetpl as $key => $value) {
					$Mark_Text = str_replace($key, $value, $Mark_Text);
				}
			}
			if ($return == FALSE){
				$Mark_Text = $this->Csskins->labelif($Mark_Text);
				echo $Mark_Text;
			}else{
				return $Mark_Text;
			}
			//写入缓存
			$this->Cscache->save();
		}
	}

    //自定义页面
    public function page($row,$return=FALSE,$fidetpl = array()) {
        $cache_id ="page_".$row['name']."_".defined('MOBILE');
        if(!$this->Cscache->get($cache_id)){
			$Mark_Text=str_decode($row['html']);
			$Mark_Text=$this->Csskins->template_parse($Mark_Text,true);
			//替换自定义标签
			if(!empty($fidetpl) && is_array($fidetpl)){
				foreach ($fidetpl as $key => $value) {
					$Mark_Text = str_replace($key, $value, $Mark_Text);
				}
			}
			if ($return == FALSE){
				$Mark_Text = $this->Csskins->labelif($Mark_Text);
				echo $Mark_Text;
			}else{
				return $Mark_Text;
			}
			//写入缓存
			$this->Cscache->save();
		}
	}

    //兼容3.5版本URL地址
    function get_sort($fid){
		$fid = current(explode('/',$fid));
		$sort = empty($fid) ? 'id' : $fid;
		$dos = array('id', 'hits', 'yhits', 'zhits', 'rhits', 'dhits', 'chits', 'shits', 'xhits', 'news', 'reco', 'play', 'down', 'fav', 'yue', 'zhou', 'ri', 'ding', 'cai');
		if(!empty($fid) && (in_array($fid, $dos))){
		    $old = array('yue', 'zhou', 'ri', 'ding', 'cai', 'fav', 'down', 'news', 'play');
		    $new = array('yhits', 'zhits', 'rhits', 'dhits', 'chits', 'shits', 'xhits', 'addtime', 'playtime');
		    $sort = str_replace($old,$new,$fid);
		}
		return $sort;
	}
}
