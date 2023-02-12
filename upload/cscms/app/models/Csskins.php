<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-08-21
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 模板解析
 */
class Csskins extends CI_Model{

    public function __construct(){
	    parent:: __construct ();
    }

	//解析模板
	public function template_parse($str,$ts=TRUE,$if=true,$row=array()) {
		if(empty($str)) msg_txt(L('skins_null'));
		//解析头部、底部、左右分栏
		$str = $this->topandend($str);
		//会员登录框
		$str=str_replace('{cscms:logkuang}',$this->logkuang(),$str);
		//自定义标签
		$str=$this->cscmsopt($str);
		//解析全局标签
		$str=$this->cscms_common($str);
		//数据循环
		$str=$this->csskins($str);
		//数据统计标签
		$str=$this->cscount($str);
		//自定义字段
		$field = isset($row['cscms_field']) ? $row['cscms_field'] : $row;
		$str=$this->field($str,$field);

		//PHP代码解析
		preg_match_all('/{cscmsphp}([\s\S]+?){\/cscmsphp}/',$str,$php_arr);
		if(!empty($php_arr[0])){
			for($i=0;$i<count($php_arr[0]);$i++){
			    $str=$this->cscms_php($php_arr[0][$i],$php_arr[1][$i],$str);
			}
		}
		unset($php_arr);

		//数据统计解析
		preg_match_all('/{cscmscount\s+param\=\"(.*?)\"}/',$str,$count_arr);
		if(!empty($count_arr[0])){
			for($i=0;$i<count($count_arr[0]);$i++){
				$link='<script src="'.is_ssl().Web_Url.Web_Path.'index.php/api/count?param='.$count_arr[1][$i].'"></script>';
				$str=str_replace($count_arr[0][$i],$link,$str);
			}
		}
		unset($count_arr);

		//会员主页模板
		preg_match_all('/{cscmsweb}([\s\S]+?){\/cscmsweb}/',$str,$web_arr);
		if(!empty($web_arr[0])){
			for($i=0;$i<count($web_arr[0]);$i++){
				$str=$this->cscms_web($web_arr[0][$i],$web_arr[1][$i],$str);
			}
		}
		unset($web_arr);

		//解析IF判断标签
		if($if) $str=$this->labelif($str);
		//加入全局JS
		if($ts) $str=cs_addjs($str);

		//后台操作
		if (defined('IS_ADMIN')){
			$str=str_replace(SELF,"index.php",$str);
		}

		//板块伪静态
		if(PLUBPATH != 'sys'){
			$Web_Mode=config('Web_Mode');
			if($Web_Mode==2){
				$str=str_replace("index.php/","",$str);
			}
		}

		//手机二级域名电脑版
		if(defined('MOBILE') && Mobile_Url!=''){
			if(defined('MOBILE_YM')){
				$str  = str_replace(Web_Url,Mobile_Url,$str);
			}
			//纯静态替换样式路径
			if(config('Web_Mode')==3 && config('Ym_Mode')==0){
				$str  = str_replace(Web_Path.'packs/',is_ssl().Web_Url.Web_Path.'packs/',$str);
				$str  = str_replace(Web_Path.'tpl/',is_ssl().Web_Url.Web_Path.'tpl/',$str);
			}
		}
		return $str;
	}

	//自定义统计标签
	public function cscount($strs){
		preg_match_all('/{cscmstj\s+param\=\"([a-zA-Z0-9\_\-\|\=]+)\"}/',$strs,$Arrtj);
		if(!empty($Arrtj[1])){
			for($i=0;$i<count($Arrtj[1]);$i++){
				$count=0;
				$str=explode('|', $Arrtj[1][$i]);
				$table=$str[0];
				if(!empty($table) && $this->db->table_exists(CS_SqlPrefix.$table)){
					$sql="";
					for($j=1;$j<count($str);$j++){	
						$v=explode('=', $str[$j]);
						if($v[0]=='times'){
							$k=explode('-', $v[1]);
							$fidel=$k[0];
							if($this->db->field_exists($fidel, $table)){
								$day=intval($k[1]);
								$times=strtotime(date('Y-m-d 23:59:59',strtotime("-".$day." day")));
								$sql.="and ".$fidel.">".$times." ";
							}
						}else{
							if(!empty($v[1]) || (int)$v[1]==0){
								if($this->db->field_exists($v[0], $table)){
									$sql.="and ".$v[0]."='".$v[1]."' ";
								}
							}else{
								$sql.="and ".$str[$j]." ";
							}
						}
					}
					if(substr($sql,0,3)=='and') $sql=substr($sql,3);
					if(!empty($sql)) $sql=" where".$sql;
					$sql="select count(*) as count from ".CS_SqlPrefix.$table." ".$sql;
					$query = $this->db->query($sql)->result_array();
					$count = (int)$query[0]['count'];
				}
				$strs=str_replace($Arrtj[0][$i],$count,$strs);
			}
		}
		unset($Arrtj);
		return $strs;
	}

	//解析CSCMS大标签
	public function csskins($str,$autoarr=array()){
		$str=$this->cscmsopt($str);
		preg_match_all('/{cscms:([\S]+)\s+(.*?)}([\s\S]+?){\/cscms:\1}/',$str,$str_arr);
		if(!empty($str_arr[0])){
			for($i=0;$i<count($str_arr[0]);$i++){
			    $str=$this->cscms_sql_to($str_arr[1][$i],$str_arr[2][$i],$str_arr[0][$i],$str_arr[3][$i],$str,$autoarr);
			}
		}
		unset($str_arr);
		//二级分类
		preg_match_all('/{cscmstype:([\S]+)\s+(.*?)}([\s\S]+?){\/cscmstype:\1}/',$str,$str_arr2);
		if(!empty($str_arr2[0])){
			for($i=0;$i<count($str_arr2[0]);$i++){
				$str=$this->cscms_sql_to($str_arr2[1][$i],$str_arr2[2][$i],$str_arr2[0][$i],$str_arr2[3][$i],$str,$autoarr);
			}
		}
		unset($str_arr2);
		return $str;
	}

	//解析自定义字段
	public function field($str,$row=array()){
		if(is_object($row)){
			$row2 = array();
			foreach ($row as $key => $value) $row2[$key] = $value;
			$row = $row2;
		}
		preg_match_all('/{cscms:opt_field_([a-zA-Z0-9\_\-]+)}/',$str,$arr);
		//不存在自定义字段则直接返回
		if(empty($arr[0])) return $str;
		//手机
		$Mobile_Is = config('Mobile_Is',PLUBPATH);
		if(defined('MOBILE') && $Mobile_Is==1){
			$dir = 'mobile'.FGF.'user'.FGF.str_replace('/', FGF, Mobile_User_Dir).PLUBPATH.FGF;
	    }else{
	    	$dir = 'pc'.FGF.'user'.FGF.str_replace('/', FGF, Pc_User_Dir).PLUBPATH.FGF;
	    }
		$path = VIEWPATH.$dir.'field.html';
		//模版不存在
		if(!file_exists($path)){
			$str = str_replace($arr[0][0], '', $str);
		}else{
	    	$table = $arr[1][0];//数据表
	    	$dir = PLUBPATH == 'sys' ? 'user' : PLUBPATH;//板块
			$str2 = file_get_contents($path);
			$upkey = '';
			if(isset($_SESSION['cscms__id'])){
				$upkey = sys_auth(addslashes(serialize(array('id'=>$_SESSION['cscms__id'],'login'=>$_SESSION['cscms__login']))),'E');
			}
			$str2  = str_replace("{cscms:upkey}",$upkey,$str2);
			preg_match_all('/{cscmsfield:([a-zA-Z0-9\_\-]+)}([\s\S]+?){\/cscmsfield:\1}/',$str2,$str_arr);
			if(!empty($str_arr)){
				if (is_file(CSCMS.'sys/Cs_Field.php')) {
	            	$field = require(CSCMS.'sys/Cs_Field.php');
		        }else{
		            $field = array();
		        }
				for($i=0;$i<count($str_arr[0]);$i++){
					$var = $str_arr[1][$i];
					$str1 = $str_arr[2][$i];
					$newstr = '';
			        if(isset($field[$dir])){
	                	$index = 0;//列表编号i
		                foreach ($field[$dir] as $key => $value){
		                	//替换修改值
		                	if(isset($row[$value['zd']])){
		                		$value['default'] = $row[$value['zd']];
		                	}
		                    if($value['table']==$table && $value['status']==1 && $value['qiantai']==1){
		                    	$index++;
		                        preg_match_all('/\['.$var.':([A-Za-z0-9-_]+)\]/',$str1,$arr2);
		                        $temp = $str1;
		                        foreach ($arr2[1] as $k => $v) {
		                        	if($v=='i'){
		                        		$temp = str_replace($arr2[0][$k], $index,$temp);
		                        	}elseif($v=='dir'){
		                        		$temp = str_replace($arr2[0][$k], $dir,$temp);
		                        	}elseif($v=='option1'||$v=='option2'||$v=='option3'){
		                        	}elseif($v=='accept'){
		                        		$actemp = explode('|', $value['accept']);
			                    		$acarr = array();
			                    		foreach ($actemp as $kk => $vv) {
			                    			$acarr[$kk] = '*.'.$vv;
			                    		}
			                    		$accept = implode(';',$acarr);
			                    		$temp = str_replace($arr2[0][$k],$accept, $temp);
		                        	}else{
		                        		$temp = str_replace($arr2[0][$k],$value[$v], $temp);
		                        	}
		                        }
		                        if($value['leix']=='select'){//选项循环解析
									preg_match_all('/{cscmsfield2}([\s\S]+?){\/cscmsfield2}/',$temp,$sel);
									$tempsel = explode('|', $value['option']);
									for($j=0;$j<count($sel[0]);$j++) {
										$temp2 = '';
										foreach ($tempsel as $ks => $vs) {
											$temp3 = $sel[1][$j];
											$kv = explode(':', $vs);
											$zdarrs = explode('|cscms|', $row[$value['zd']]);
											if((int)$value['attr'] < 2){
												$check = in_array($kv[1],$zdarrs) ? ' checked' : '2';
											}else{
												$check = in_array($kv[1],$zdarrs) ? ' selected="selected"' : '';
											}
											$temp3 = str_replace('[dadd:option1]', $kv[1],$temp3);
											$temp3 = str_replace('[dadd:option2]', $kv[0],$temp3);
											$temp3 = str_replace('[dadd:option3]', $check,$temp3);
											$temp2 .= $temp3;
										}
										$temp = str_replace($sel[0][$j], $temp2, $temp);
									}
		                        }
		                        $newstr .= $temp;
		                    }
		                } 
		            }
					$newstr = str_replace("\n","",$newstr);
		            $str2 = str_replace($str_arr[0][$i], $newstr, $str2);
				}
			}
			unset($str_arr);
			$str = str_replace($arr[0][0],$str2,$str);
		}
		unset($arr);
		return $str;
	}

    //自定义OPT模板
	public function cscmsopt($str,$uid=0){
		//字母标签解析
		preg_match_all('/{cscmszm:([0-9]+)}([\s\S]+?){\/cscmszm}/',$str,$Mark_ZM);  //歌曲字母搜索标签解析
		if(!empty($Mark_ZM)){
			$strzm=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','其他');
			for($k=0;$k<count($Mark_ZM[0]);$k++){
			    $zmstr='';
				$zmlen=($Mark_ZM[1][$k]>27)?27:$Mark_ZM[1][$k];
			    for($i=0;$i<$zmlen;$i++){
			         $zmstr.=str_replace('[zm:name]',$strzm[$i],$Mark_ZM[2][$k]);
			    }
			    $str=str_replace($Mark_ZM[0][$k],$zmstr,$str);
			}
		}	
		unset($Mark_ZM);
		//自定义标签解析
		preg_match_all('/{cscmsself:([A-Za-z0-9-_]+)}/',$str,$Arrself);
		if(!empty($Arrself[1])){
			for($i=0;$i<count($Arrself[1]);$i++){
				$sql="SELECT selflable FROM ".CS_SqlPrefix."label where name='".$Arrself[1][$i]."'";
				$row=$this->db->query($sql)->row(); 
				if($row){
				    $str=str_replace($Arrself[0][$i],str_decode($row->selflable),$str);
				}
			}
		}
		unset($Arrself);
		//自定义JS标签解析
		preg_match_all('/{cscmsjs:([A-Za-z0-9-_]+)}/',$str,$ArrJs);   
		if(!empty($ArrJs[1])){
			for($j=0;$j<count($ArrJs[1]);$j++){
				$sql="SELECT js FROM ".CS_SqlPrefix."ads where name='".$ArrJs[1][$j]."'";
				$rows=$this->db->query($sql)->row(); 
				if($rows){
				    $str=str_replace($ArrJs[0][$j],"<script src='".is_ssl().Web_Url.Web_Path."attachment/js/".$rows->js.".js'></script>",$str);
				}
			}
		}
		unset($ArrJs);
		//自定义页面标签解析
		preg_match_all('/{cscmspage:([A-Za-z0-9-_]+)}/',$str,$ArrPage);   
		if(!empty($ArrPage[1])){
			for($j=0;$j<count($ArrPage[1]);$j++){
			    $sql="SELECT url FROM ".CS_SqlPrefix."page where name='".$ArrPage[1][$j]."'";
		        $rows=$this->db->query($sql)->row(); 
		        if($rows){
		                if(Web_Mode==2){  //伪静态
		                    $rows->url=str_replace('index.php/','',$rows->url);
		                }
		                $str=str_replace($ArrPage[0][$j],is_ssl().Web_Url.$rows->url,$str);
				}
			}
		}
		unset($ArrPage);
		//自定义opt模板
		preg_match_all('/{cscms:opt-([A-Za-z0-9-_]+)}/',$str,$ArrBd);   //榜单标签解析
		if(!empty($ArrBd[1])){
			for($i=0;$i<count($ArrBd[1]);$i++){
				$dir='';
				if(strpos($ArrBd[1][$i],'-') !== FALSE){
					$arr=explode('-',$ArrBd[1][$i]);
					if(!empty($arr[0]) && is_dir(FCPATH.'plugins'.FGF.$arr[0])){
						$dir=$arr[0];
					}
				}
				if($dir=='' && PLUBPATH!='sys'){
					$dir = PLUBPATH;
				}
				$opt = str_replace($dir.'-','',$ArrBd[1][$i]);
				if($dir=='' || $dir=='sys' || $dir=='index'){
					$str=str_replace($ArrBd[0][$i],get_link('opt/'.$ArrBd[1][$i]),$str);
				}else{
					$opt=str_replace($dir.'-','',$ArrBd[1][$i]);
					$optlink=is_ssl().Web_Url.Web_Path.'index.php/'.$dir.'/opt/'.$opt;
					//二级域名
					$Ym_Mode=config('Ym_Mode',$dir);
					$Ym_Url=config('Ym_Url',$dir);
					$Web_Mode=config('Web_Mode',$dir); //运行模式
					if($Ym_Mode==1 && $Web_Mode<3 && !defined('MOBILE_YM')){
						$optlink=str_replace(Web_Url.Web_Path.'index.php/'.$dir.'/',$Ym_Url.Web_Path.'index.php/',$optlink);
					}
					if($Web_Mode==2){
					   $optlink=str_replace('index.php/','',$optlink);
					}elseif($Web_Mode==3){
					   //手机版生成
					   if(defined('MOBILE')){
						 $optlink=str_replace(Web_Path.'index.php',Web_Path.Html_Wap_Dir.'/index.php',$optlink);
					   }
					   $optlink=str_replace('index.php/'.$dir.'/','',$optlink.'_'.$dir.'.html');
					}
					$str=str_replace($ArrBd[0][$i],$optlink,$str);
				}
			}
		}
		unset($ArrBd);
		return $str;
	}

    //解析会员版块导航
	public function cscmsumenu($str,$uid=0){
		preg_match_all('/{cscmsmenu:([A-Za-z0-9]+)}([\s\S]+?){\/cscmsmenu}/',$str,$Mark_M);   //榜单标签解析
		if(!empty($Mark_M) && !empty($Mark_M[0][0])){
			$us=$this->db->query("select level,zid from ".CS_SqlPrefix."user where id=".$uid."")->row_array();
			if(!$us){
			    $us['zid']=0;
			    $us['level']=0;
			}
			$sqlstr="select dir from ".CS_SqlPrefix."plugins order by id asc";
			$result=$this->db->query($sqlstr);
			$menu_s='';
			$k = 0;
			foreach ($result->result() as $row) {
				$k++;
				$mstr='';
				if(file_exists(CSCMS.$row->dir.FGF.'menu.php') && file_exists(CSCMS.$row->dir.FGF.'site.php')){
					preg_match_all('/{cscmsmenu:auto}([\s\S]+?){\/cscmsmenu:auto}/',$str,$Mark_A);   //榜单标签解析
					if(!empty($Mark_A)){
						global $PLUBARR;
						if(!isset($PLUBARR[$row->dir]['menu'])){
					    	$site_arr = require CSCMS.$row->dir.FGF.'site.php';
					    	$menu_arr = require CSCMS.$row->dir.FGF.'menu.php';
					    	//写进全局变量
					    	$GLOBALS['PLUBARR'][$row->dir] = $site_arr;
					    	$GLOBALS['PLUBARR'][$row->dir]['menu'] = $menu_arr;
					    }else{
					    	$site_arr = $PLUBARR[$row->dir];
					    	$menu_arr = $site_arr['menu'];
					    }
						$menu = $menu_arr['user'];
						if(!empty($menu[0]['menu']) && getqx($us['zid'],$site_arr['User_Qx'])!='ok' && getqx($us['level'],$site_arr['User_Dj_Qx'])!='ok'){
							$count=count($menu[0]['menu']);
							$mstr='';
							for($j=0;$j<$count;$j++){
								$mstr.=str_replace(array('[menu:i]','[menu:dir]','[menu:name]','[menu:link]'),array(($j+1),$row->dir,$menu[0]['menu'][$j]['name'],spacelink($menu[0]['menu'][$j]['link'],$row->dir)),$Mark_A[1][0]);
							}
							$mstr=str_replace($Mark_A[0][0],$mstr,$Mark_M[2][0]);
						}
					}
					unset($Mark_A);
				}
				$menu_s.=$mstr;
				if(!empty($menu[0]['name'])){
					$menu_s=str_replace('[menu:name]',$menu[0]['name'],$menu_s);
				}
				$menu_s=str_replace('[menu:k]',$k,$menu_s);
			}
			$str=str_replace($Mark_M[0][0],$menu_s,$str);
		}	
		unset($Mark_M);
		return $str;
	}

	//解析头部、底部、左右分栏
	public function topandend($str,$skins=''){
		$str = preg_replace ( "/\{cscms:head\}/", $this->topandbottom('head',$skins), $str );
		$str = preg_replace ( "/\{cscms:left\}/", $this->topandbottom('left',$skins), $str );
		$str = preg_replace ( "/\{cscms:right\}/", $this->topandbottom('right',$skins), $str );
		$str = preg_replace ( "/\{cscms:bottom\}/", $this->topandbottom('bottom',$skins), $str );
		//获取非版块模板
		$str = preg_replace ( "/\{cscms:indexhead\}/", $this->topandbottom2('indexhead',$skins), $str );
		$str = preg_replace ( "/\{cscms:indexleft\}/", $this->topandbottom2('indexleft',$skins), $str );
		$str = preg_replace ( "/\{cscms:indexright\}/", $this->topandbottom2('indexright',$skins), $str );
		$str = preg_replace ( "/\{cscms:indexbottom\}/", $this->topandbottom2('indexbottom',$skins), $str );
		return $str;
	}

	//解析头部底部模板
	public function topandbottom($type,$skins=''){
        $str='';
		if(defined('MOBILE') && Mobile_Is==1){
		 	if(defined('USERPATH')){
			    $DIRS=VIEWPATH.'mobile'.FGF.'user'.FGF.Mobile_User_Dir;
		 	}elseif(defined('HOMEPATH')){
		 		if(!defined('HOMESKINS')){
			    	$DIRS=VIEWPATH.'mobile'.FGF.'home'.FGF.Mobile_Home_Dir;
		 		}else{
			    	$DIRS=VIEWPATH.'mobile'.FGF.'home'.FGF.HOMESKINS;
		 		}
		 	}else{
			    $DIRS=VIEWPATH.'mobile'.FGF.'skins'.FGF.Mobile_Skins_Dir;
		 	}
		}else{ //PC
		 	if(defined('USERPATH')){
			    $DIRS=VIEWPATH.'pc'.FGF.'user'.FGF.Pc_User_Dir;
		 	}elseif(defined('HOMEPATH')){
		 		if(!defined('HOMESKINS')){
			    	$DIRS=VIEWPATH.'pc'.FGF.'home'.FGF.Pc_Home_Dir;
		 		}else{
			    	$DIRS=VIEWPATH.'pc'.FGF.'home'.FGF.HOMESKINS;
		 		}
		 	}else{
			    $DIRS=VIEWPATH.'pc'.FGF.'skins'.FGF.Pc_Skins_Dir;
		 	}
		}
		$DIRS=str_replace("/", FGF, $DIRS);
        switch($type){
		    case 'head'  : $files = $DIRS.PLUBPATH.FGF."head.html";break;
		    case 'left'  : $files = $DIRS.PLUBPATH.FGF."left.html";break;
		    case 'right' : $files = $DIRS.PLUBPATH.FGF."right.html";break;
		    case 'bottom': $files = $DIRS.PLUBPATH.FGF."bottom.html";break;
	    }
		$str = file_exists($files) ? file_get_contents($files) : '';
	    return $str;
	}

	//解析非版块头部底部模板
	public function topandbottom2($type,$skins=''){
        $str='';
		$Mobile_Is = (defined('PLUBPATH')) ? config('Mobile_Is',PLUBPATH) : Mobile_Is;
        switch($type){
		    case 'indexhead'  : $files = 'head.html';break;
		    case 'indexleft'  : $files = 'left.html';break;
		    case 'indexright' : $files = 'right.html';break;
		    case 'indexbottom': $files = 'bottom.html';break;
	    }
		if(defined('MOBILE') && $Mobile_Is==1){
		 	if(defined('USERPATH')){
			    $DIRS=VIEWPATH.'mobile'.FGF.'user'.FGF.Mobile_User_Dir;
		 	}elseif(defined('HOMEPATH')){
			    $DIRS=VIEWPATH.'mobile'.FGF.'home'.FGF.Mobile_Home_Dir;
		 	}else{
			    $DIRS=VIEWPATH.'mobile'.FGF.'skins'.FGF.Mobile_Skins_Dir;
		 	}
		}else{ //PC
		 	if(defined('USERPATH')){
			    $DIRS=VIEWPATH.'pc'.FGF.'user'.FGF.Pc_User_Dir;
		 	}elseif(defined('HOMEPATH')){
			    $DIRS=VIEWPATH.'pc'.FGF.'home'.FGF.Pc_Home_Dir;
		 	}else{
			    $DIRS=VIEWPATH.'pc'.FGF.'skins'.FGF.Pc_Skins_Dir;
		 	}
		}
        $files = $DIRS.'sys'.FGF.$files;
		$str = file_exists($files) ? file_get_contents($files) : '';
        return $str;
	}

	//全局会员登陆框
	public function logkuang(){
		if(defined('USERPATH')){ //会员中心
			$type='user';
		}elseif(defined('HOMEPATH')){ //会员空间
			$type='home';
		}else{
			$type=PLUBPATH;
		}
		if(defined('HOMEPATH')){
			if(Home_Ym==1){
				$arr = explode('.', $_SERVER['HTTP_HOST']);
				$uid = $arr[0];
			}else{
				$uid = $this->uri->segment(1);
			}
			$type.='/'.$uid;
		}
		$Mark_Text="<div id='cscms_login'></div>
		        <script type='text/javascript'>
		            var cscms_loginlink = '".get_link('api/ulog/log')."/".$type."';
		            var cscms_loginaddlink = '".get_link('api/ulog/login')."';
		            var cscms_logoutlink = '".get_link('api/ulog/logout')."';
		            cscms.login();
		        </script>";
		return $Mark_Text;
	}

	//解析全局标签
	public function cscms_common($str,$skins=''){
		//解析头部、底部、左右分栏
		$str = $this->topandend($str,$skins);
		$str  = str_replace("{cscms:webname}",Web_Name,$str);
		$str  = str_replace("{cscms:weburl}",Web_Url,$str);
		$str  = str_replace("{cscms:webpath}",Web_Path,$str);
		$str  = str_replace("{cscms:path}",is_ssl().Web_Url.Web_Path,$str);
		$str  = str_replace("{cscms:indextempurl}",Skins_Dir($skins),$str);
		$str  = str_replace("{cscms:tempurl}",Skins_Dir($skins),$str);
		$str  = str_replace("{cscms:stat}",str_decode(Web_Count),$str);
		$str  = str_replace("{cscms:notice}",str_decode(Web_Notice),$str);
		$str  = str_replace("{cscms:regxy}",str_decode(User_Regxy),$str);

		//SEO代码
		$seo  = PLUBPATH!='sys' ? config('Seo') : '';
		$title=(!empty($seo['title']))?$seo['title']:str_decode(Web_Title);
		$str  = str_replace("{cscms:title}",$title,$str);
		$keywords=(!empty($seo['keywords']))?$seo['keywords']:str_decode(Web_Keywords);
		$str  = str_replace("{cscms:keywords}",$keywords,$str);
		$description=(!empty($seo['description']))?$seo['description']:str_decode(Web_Description);
		$str  = str_replace("{cscms:description}",$description,$str);

		$str  = str_replace("{cscms:mail}",Admin_Mail,$str); 
		$str  = str_replace("{cscms:qq}",Admin_QQ,$str);
		$str  = str_replace("{cscms:tel}",Admin_Tel,$str);
		$str  = str_replace("{cscms:icp}",Web_Icp,$str);

		//判断登录状态
		if(isset($_SESSION['cscms__id']) && isset($_SESSION['cscms__login'])){
			$str  = str_replace("{cscms:login}","ok",$str);
			$str  = str_replace("{cscms:uid}",$_SESSION['cscms__id'],$str);
			$upkey = sys_auth(addslashes(serialize(array('id'=>$_SESSION['cscms__id'],'login'=>$_SESSION['cscms__login']))),'E');
			$str  = str_replace("{cscms:upkey}",$upkey,$str);
		}else{
			$str  = str_replace("{cscms:login}","no",$str);
			$str  = str_replace("{cscms:uid}",0,$str);
			$str  = str_replace("{cscms:upkey}","",$str);
		}

		//网站部分链接
		if(Web_Mode==2){
			$str=str_replace('{cscms:gbooklink}',is_ssl().Web_Url.Web_Path.'gbook',$str);
		}else{
			$str=str_replace('{cscms:gbooklink}',get_link('gbook'),$str);
		}
		//搜索链接
		$solink='';
		if(PLUBPATH != 'sys'){
			$Ym_Mode=config('Ym_Mode',PLUBPATH); //二级域名状态
			$Ym_Url=config('Ym_Url',PLUBPATH);   //二级域名地址
			if($Ym_Mode==1){
				$solink=is_ssl().$Ym_Url.Web_Path.'index.php/search';
			}else{
				$solink=Web_Path.'index.php/'.PLUBPATH.'/search';
			}
		}

		$str=str_replace('{cscms:solink}',$solink,$str);
		$str=str_replace('{cscms:codes}',get_link('api/codes'),$str);
		$str=str_replace('{cscms:userlink}',get_link('user'),$str);
		$str=str_replace('{cscms:loginlink}',get_link('user/login'),$str);
		$str=str_replace('{cscms:reglink}',get_link('user/reg'),$str);
		$str=str_replace('{cscms:passlink}',get_link('user/pass'),$str);
		$str=str_replace('{cscms:qqlink}',get_link('user/open/login/qq'),$str);
		$str=str_replace('{cscms:weibolink}',get_link('user/open/login/weibo'),$str);
		$str=str_replace('{cscms:kaixinlink}',get_link('user/open/login/kaixin'),$str);
		$str=str_replace('{cscms:baidulink}',get_link('user/open/login/baidu'),$str);
		$str=str_replace('{cscms:doubanlink}',get_link('user/open/login/douban'),$str);
		$str=str_replace('{cscms:sohulink}',get_link('user/open/login/sohu'),$str);
		$str=str_replace('{cscms:wangyilink}',get_link('user/open/login/netease'),$str);
		$str=str_replace('{cscms:renrenlink}',get_link('user/open/login/renren'),$str);
		$str=str_replace('{cscms:weixinlink}',get_link('user/open/login/weixin'),$str);
		$str=userurl($str);
		return $str;
	}

	//组装SQL标签
	public function cscms_sql($fields, $para, $str_arr, $label, $sorts='', $autoarr=array(), $cid=0, $sql='') {
		preg_match_all("/([a-z]+)\=[\"]?([^\"]+)[\"]?/i", stripslashes($para), $matches, PREG_SET_ORDER);
		$arr = array('sql', 'field', 'table', 'loop', 'pagesize', 'order', 'sort', 'start', 'where', 'limit');

		//获取数据表
		$table=$this->arr_val('table',$matches);
		//获取要查询的字段
		$field=$this->arr_val('field',$matches);
		if(!$field) $field="*";

		if($sql==''){
			$sql = $this->arr_val('sql',$matches);
			if(!$sql){
				//模板标签错误，缺少table参数
				if($table==''){  
					$strs = str_replace($label,".....",$str_arr);
					msg_txt(vsprintf(L('skins_table'),array($strs)));
				}
				//数据表不存在
				if(!$this->db->table_exists(CS_SqlPrefix.$table)){
					$strs = str_replace($label,".....",$str_arr);
					msg_txt(vsprintf(L('skins_table_err'),array($strs,$table)));
				}
				//SQL语句
				$sql = "select ".$field." from `".CS_SqlPrefix.$table."`";
			}
		}
		if(!empty($sql)){
			$sql = str_replace("{field}",$field,$sql);
		}
		//where查询条件
		$w = array();
		$where=$this->arr_val('where',$matches);
		if(!empty($where)){
			$warr = explode("|",$where);
			foreach ($warr as $vv) {
				$w[] = $vv;
			}
		}
		$warr = array();
		//原始条件 类似cid=1
		foreach ($matches as $v) {
			if(in_array($v[1], $arr)) {
				$v[1] = $v[2];
				continue;
			}
			if(strpos($sql, $v[1].'=')===false){
				$warr[] = $v[1].'='.$v[2];
			}
		}
		//隐藏和回收站默认值
		$yid=$hid=0;
		foreach ($warr as $vv) {
			$v = explode("=",$vv);
			//解析字母搜索标签
			if($v[0]=='zm'){
				$zmall=explode(",",$v[1]);
				if($this->db->field_exists($zmall[0], $table)){ //判断条件字段是否存在
					$zimu_arr=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
					$zimu_arr1=array(-20319,-20283,-19775,-19218,-18710,-18526,-18239,-17922,-1,-17417,-16474,-16212,-15640,-15165,-14922,-14914,-14630,-14149,-14090,-13318,-1,-1,-12838,-12556,-11847,-11055);
					$zimu_arr2=array(-20284,-19776,-19219,-18711  ,-18527,-18240,-17923,-17418,-1,-16475,-16213,-15641,-15166,-14923,-14915,-14631,-14150,-14091,-13319,-12839,-1,-1,-12557,-11848,-11056,-2050);
					if(!in_array(strtoupper($zmall[1]),$zimu_arr)){ //其他
						$w[]="substring( ".$zmall[0].", 1, 1 ) NOT REGEXP '^[a-zA-Z]' and substring( ".$zmall[0].", 1, 1 ) REGEXP '^[u4e00-u9fa5]'";
					}else{
						$posarr=array_keys($zimu_arr,strtoupper($zmall[1]));
						$pos=$posarr[0];
						$w[]="(((ord( substring(convert(".$zmall[0]." USING gbk), 1, 1 ) ) -65536>=".($zimu_arr1[$pos])." and  ord( substring(convert(".$zmall[0]." USING gbk), 1, 1 ) ) -65536<=".($zimu_arr2[$pos]).")) or UPPER(substring(convert(".$zmall[0]." USING gbk), 1, 1 ))='".$zimu_arr[$pos]."')";
					}
				}
			}elseif ($this->db->field_exists($v[0], $table)){ //判断条件字段是否存在

				if($v[1]=='auto' || substr($v[1],0,5)=='auto,'){ //当前分类
					$auall=explode(",",$v[1]);
					if(!empty($auall[1])){
						if(empty($autoarr[$auall[1]])){
							$vs=0;
						}else{
							$vs=(is_array($autoarr))?$autoarr[$auall[1]]:$autoarr;
						}
					}else{
						if(empty($autoarr[$v[0]]) && empty($autoarr)){
							$vs=0;
						}else{
							$vs=(is_array($autoarr))?$autoarr[$v[0]]:$autoarr;
						}
					}
					if($v[0]=='tags'){ //TAGS标签
						$w[]="(".$this->gettags($vs).")";
					}elseif(!empty($vs)){
						if($v[0]=='cid' && is_numeric($vs)){
						    if($this->db->table_exists(CS_SqlPrefix.$table.'_list')){
						        $vs=$this->Csdb->getchild($vs,$table.'_list');
							}
						}
						if(strpos($vs,',') === FALSE){
							if(is_numeric($vs)){
								$w[]=$v[0]."=".$vs;
							}else{
								$w[]=$v[0]."='".$vs."'";
							}
						}else{
							$w[]=$v[0]." in (".$vs.")";
						}
					}
				}else{
					if($v[0]=='cid' && is_numeric($v[1])){
						if($this->db->table_exists(CS_SqlPrefix.$table.'_list')){
							$v[1]=$this->Csdb->getchild($v[1],$table.'_list');
						}
					}
					if(strpos($v[1],',') === FALSE){
						$w[]=$v[0]."=".$v[1];
					}else{
						$w[]=$v[0]." in (".$v[1].")";
					}
				}
			}
		}
		if($cid>0) $w[]='cid='.$cid;
		//判断审核字段
		if(strpos($sql,'yid=')===FALSE && $this->db->field_exists('yid', $table)){
			$w[]='yid=0';
		}
		//组装查询SQL
		if(!empty($w)){
			$where = implode(' and ', $w);
			if(strpos(strtolower($sql),'where') === FALSE ){
				$sql.= ' where '.$where;
			}else{
				$sql.= ' and '.$where;
			}
		}
		//排序
		if(strpos(strtolower($sql),' order ') === FALSE){
			$order=$this->arr_val('order',$matches);
			if(!empty($order) && $order!='desc' && $order!='asc'){
				$sql.=" order by ".$order; 
			}else{
				$sort = !empty($sorts) ? $sorts : $this->arr_val('sort',$matches);
				if($sort=='rand') $sort='rand()';
				if(empty($sort)) $sort='id';
				if(empty($order)) $order = 'desc';
				$sql.=" order by ".$sort." ".$order; 
			}
		}
		//去掉1=1
		$sql=str_replace('1=1 and ','',$sql);
		//记录数量
		if(strpos(strtolower($sql),' limit ') === FALSE){
			$limit=$this->arr_val('limit',$matches);
			if(empty($limit)){
				$loop=(int)$this->arr_val('loop',$matches);
				$start=(int)$this->arr_val('start',$matches);
				if(!$start) $start=1;
				if($loop>0) $sql.=" limit ".($start-1).",".($loop)."";
			}else{
				$sql.=" limit ".$limit;
			}
		}
		unset($matches);
		return $sql;
	}

	//解析SQL标签
	public function cscms_sql_to($fields, $para, $str_arr, $label, $str, $autoarr) {
		$sql=$this->cscms_sql($fields, $para, $str_arr, $label, '', $autoarr);
		$result_array=$this->db->query($sql)->result_array();
		if(!$result_array){
			$Data_Content="";
			$str=str_replace($str_arr,$Data_Content,$str);
		}else{
			$Data_Content='';$sorti=1;
			foreach ($result_array as $row) {
		        $Data_Content.=$this->cscms_skins($fields,$str,$label,$row,$sorti,$autoarr);
				$sorti++;
			}
		}
		$str=str_replace($str_arr,$Data_Content,$str);	
		return $str;
	}

	//解析CSCMS标签
	public function cscms_skins($field, $str, $label, $row, $sorti=1, $autoarr=array()){
		//正规匹配-
		preg_match_all('/\['.$field.':\s*([0-9a-zA-Z\_\-]+)([\s]*[link|ulink|dir|level|zd|len|style]*)[=]??([\d0-9a-zA-Z\,\_\{\}\/\-\\\\:\s]*)\]/',$str,$field_arr);
		if(!empty($field_arr[0])){
			$time1 = microtime();
			for($i=0;$i<count($field_arr[0]);$i++){
				$type=$field_arr[1][$i];
				if(array_key_exists($type,$row) && trim($field_arr[2][$i])!='zd' && !($type=='user' && trim($field_arr[2][$i])=='level')){
				    if($type=='addtime'){
				         $label=str_replace('['.$field.':'.$type.']',date('Y-m-d H:i:s',$row[$type]),$label);
					}else{
				         $label=str_replace('['.$field.':'.$type.']',$row[$type],$label);
					}

					//判断自定义标签
				    if(!empty($field_arr[2][$i]) && !empty($field_arr[3][$i])){
						//格式化时间
						if(trim($field_arr[2][$i])=='style' && trim($field_arr[3][$i])=='time'){
							$label=str_replace($field_arr[0][$i],datetime($row[$type]),$label);
						//获取IP地区
						}elseif(trim($field_arr[2][$i])=='style' && trim($field_arr[3][$i])=='city'){
							$this->load->library('ip');
							$label=str_replace($field_arr[0][$i],$this->ip->address($row[$type]),$label);
						//自定义时间
						}elseif(trim($field_arr[2][$i])=='style'){
							$label=str_replace($field_arr[0][$i],date(str_replace('f','i',$field_arr[3][$i]),$row[$type]),$label);
						//图片地址
						}elseif(trim($field_arr[2][$i])=='dir'){
							$lall=explode(",",$field_arr[3][$i]);
							$lass=count($lall)>1?$lall[1]:'';
							$pic=piclink($lall[0],$row[$type],$lass);
							$label=str_replace($field_arr[0][$i],$pic,$label);
						}
						//字符截取
						if(trim($field_arr[2][$i])=='len'){
							$label=str_replace($field_arr[0][$i],sub_str(str_checkhtml($row[$type]),$field_arr[3][$i]),$label);
						}
					}
				}else{  //外部字段

				 	switch($type){
							//序
							case 'i'  :  
								//判断从几开始
								if(trim($field_arr[2][$i])=='len'){
									$label=str_replace($field_arr[0][$i],($sorti+$field_arr[3][$i]),$label);
								}else{
									$label=str_replace($field_arr[0][$i],$sorti,$label);
								}
							break;
							//IP地理位置
							case 'addres'  :  
								if(trim($field_arr[2][$i])=='zd' && !empty($field_arr[3][$i]) && array_key_exists($field_arr[3][$i],$row)){
									$zd=$field_arr[3][$i];
									$this->load->library('ip');
									$label=str_replace($field_arr[0][$i],$this->ip->address($row[$zd]),$label);
								}
							break;
							//自定义字段
							case 'zdy'  :  
								if(trim($field_arr[2][$i])=='zd' && !empty($field_arr[3][$i])){
								    $arr=explode(',',$field_arr[3][$i]);
								    if(array_key_exists($arr[2],$row)){
								    	$czd=empty($arr[3])?'id':$arr[3];
										$szd=$row[$arr[2]];
								        $label=str_replace($field_arr[0][$i],getzd($arr[0],$arr[1],$szd,$czd),$label);
									}
								}
							break;
							//数据统计
							case 'count'  : 
								if(trim($field_arr[2][$i])=='zdy' && !empty($field_arr[3][$i])){
									$count=0;
									$arr=explode(',',$field_arr[3][$i]);
									$table=$arr[0];
								    $czd=empty($arr[1])?'id':$arr[1];
								    $szd=empty($arr[2])?'id':$arr[2];
									if(array_key_exists($szd,$row)){
									    $uid=!empty($row[$szd])?$row[$szd]:(!empty($row['uid'])?$row['uid']:0);
									    if(!empty($table) && $this->db->table_exists(CS_SqlPrefix.$table) && $this->db->field_exists($czd, CS_SqlPrefix.$table)){
											if(!empty($arr[3]) && (!empty($arr[4]) || (int)$arr[4]==0)){
												if($this->db->field_exists($arr[3], CS_SqlPrefix.$table)){
													$count_sql ="SELECT count(*) as count FROM ".CS_SqlPrefix.$table." where ".$czd."='".$uid."' and ".$arr[3]."='".$arr[4]."'";
													$query = $this->db->query($count_sql)->result_array();
													$count = $query[0]['count'];
												}
											}else{
												$count_sql ="SELECT count(*) as count FROM ".CS_SqlPrefix.$table." where ".$czd."='".$uid."'";
												$query = $this->db->query($count_sql)->result_array();
												$count = $query[0]['count'];
											}
									    }
									}
								    $label=str_replace($field_arr[0][$i],$count,$label);
								}
							break;
							//会员信息
							case 'user'  :  
							       if(($field=='user' or array_key_exists('uid',$row) or array_key_exists('uidb',$row)) && trim($field_arr[2][$i])=='zd' && !empty($field_arr[3][$i])){
										$ziduan=$field_arr[3][$i];
										$zdneir=($field=='gbook' or $field=='pl')?'游客':'null';
										if($field=='user'){
											$uid=$row['id'];
										}else{
											$lall=explode(",",$ziduan);
											if(!empty($lall[1])){
							                	$uid=!empty($lall[1])?$lall[1]:0;
												$ziduan=$lall[1];
											}else{
												if(!empty($row['uid'])){
													$uid=$row['uid'];
												}else{
													$uid=!empty($row['uidb'])?$row['uidb']:0;
												}
											}
										}
										$czd=($ziduan=='nichen')?$ziduan.',name':$ziduan;
										if($this->db->field_exists($ziduan, CS_SqlPrefix.'user')){
											$rowu=$this->db->query("SELECT ".$czd." FROM ".CS_SqlPrefix."user where id='".$uid."'")->row();
											if($rowu){
												$zdneir=($field_arr[3][$i]=='nichen' && empty($rowu->$ziduan))?$rowu->name:$rowu->$ziduan;
											}
										}
										if($ziduan=='logo'){
											if($uid==0) $zdneir='';
							                $zdneir=piclink('logo',$zdneir);
										}
										if($ziduan=='zid'){
											if($zdneir==0) $zdneir=1;
							                $zdneir=getzd('userzu','name',$zdneir);
										}
										if($ziduan=='qianm'){
											if(empty($zdneir)) $zdneir='暂时没有签名...';
										}
										if($ziduan=='city'){
											if(empty($zdneir)) $zdneir='保密';
										}
							            $label=str_replace($field_arr[0][$i],$zdneir,$label);
								   //会员等级
							       }elseif(($field=='user' or array_key_exists('uid',$row) or array_key_exists('uidb',$row)) && trim($field_arr[2][$i])=='level' && !empty($field_arr[3][$i])){
									    $zdneir='';
										if($field=='user'){
											$uid=$row['id'];
										}else{
											$uid=!empty($row['uid'])?$row['uid']:(!empty($row['uidb'])?$row['uidb']:0);
										}
										$jinyan=getzd('user','jinyan',$uid);
							            if($field_arr[3][$i]=='1'){ //星星数
							                $zdneir=getlevel($jinyan,1);
										}
							            if($field_arr[3][$i]=='2'){ //下个级别需要经验
							                $zdneir=getlevel($jinyan,2);
										}
							            if($field_arr[3][$i]=='3'){ //下个级别剩余经验
							                $zdneir=getlevel($jinyan,3);
										}
							            if($field_arr[3][$i]=='4'){ //剩余百分比
							                $zdneir=getlevel($jinyan,4);
										}
							            if($field_arr[3][$i]=='5'){ //名称
							                $zdneir=getlevel($jinyan,5);
										}
							            $label=str_replace($field_arr[0][$i],$zdneir,$label);
								   }
							break;
							//歌手信息
							case 'singer'  :  
							       if(array_key_exists('singerid',$row) && trim($field_arr[2][$i])=='zd' && !empty($field_arr[3][$i])){
										$zdneir='null';
										if($this->db->table_exists(CS_SqlPrefix.'singer')){  //歌手表存在
										    $ziduan=$field_arr[3][$i];
										    if($this->db->field_exists($ziduan, CS_SqlPrefix.'singer')){
										        $rows=$this->db->query("SELECT ".$ziduan." FROM ".CS_SqlPrefix."singer where id='".$row['singerid']."'")->row();
										        if($rows){
											        $zdneir=$rows->$ziduan;
											    }
											}
											if($ziduan=='pic'){
												if($row['singerid']==0) $zdneir='';
							                	$zdneir=piclink('singer',$zdneir);
											}
										}
							            $label=str_replace($field_arr[0][$i],$zdneir,$label);
							       }
							break;
							//版块链接
							case 'murl'  :  
							        if(array_key_exists('dir',$row)){
								    	$link=cscmslink($row['dir']);
							        	$label=str_replace($field_arr[0][$i],$link,$label);
							        } 
							break;
							//网站链接
							case 'url'  : 
								//全局
								if(array_key_exists('id',$row) && trim($field_arr[2][$i])=='link' && !empty($field_arr[3][$i])){
									$lall=explode(",",$field_arr[3][$i]);
									$lass=count($lall)>1?$lall[1]:'';
									$link=linkurl($lall[0],$lass,$row['id']);
									$label=str_replace($field_arr[0][$i],$link,$label);
								}
								//会员
								if((array_key_exists('uid',$row) || array_key_exists('uidb',$row) || $field=='user') && trim($field_arr[2][$i])=='ulink' && !empty($field_arr[3][$i])){
									$link='';
									if($field=='user'){
										if(array_key_exists('id',$row) && array_key_exists('name',$row)){
										  $lall=explode(",",$field_arr[3][$i]);
										  $lass=count($lall)>1?$lall[1]:'';
										  $link=userlink($lall[0],$row['id'],$row['name'],$lass);
										}
									}else{
										$uid=!empty($row['uid'])?$row['uid']:(!empty($row['uidb'])?$row['uidb']:0);
										$rowu=$this->db->query("SELECT id,name FROM ".CS_SqlPrefix."user where id='".$uid."'")->row();
										if(!$rowu){
										  $link=is_ssl().Web_Url.Web_Path;
										}else{
										  $lall=explode(",",$field_arr[3][$i]);
										  $lass=count($lall)>1?$lall[1]:'';
										  $link=userlink($lall[0],$rowu->id,$rowu->name,$lass);
										}
									}
									$label=str_replace($field_arr[0][$i],$link,$label);
								}
								//会员中心...
								if(trim($field_arr[2][$i])=='userlink' && !empty($field_arr[3][$i])){
									$link=spacelink($field_arr[3][$i],PLUBPATH);
									$label=str_replace($field_arr[0][$i],$link,$label);
								}
								//自定义URL，板块，字段，参数，参数...
								if(trim($field_arr[2][$i])=='zdy' && !empty($field_arr[3][$i])){
									$lall=explode(",",$field_arr[3][$i]);
									if(!array_key_exists($lall[1],$row) || $row[$lall[1]]==0){
										$link=is_ssl().Web_Url.Web_Path;
									}else{
										$lass=count($lall)>3?$lall[3]:'';
										$link=linkurl($lall[2],$lass,$row[$lall[1]],1,$lall[0]);
									}
									$label=str_replace($field_arr[0][$i],$link,$label);
								}
							break;
					 	}
				}
			}
			//判断是否嵌套二级
			preg_match_all('/{cscmstype:([\S]+)\s+(.*?)}([\s\S]+?){\/cscmstype:\1}/',$label,$type_arr);
			if(!empty($type_arr[0])){
				for($i=0;$i<count($type_arr[0]);$i++){
					$label=$this->cscms_sql_to($type_arr[1][$i],$type_arr[2][$i],$type_arr[0][$i],$type_arr[3][$i],$label,$row['id']);
				}
			}
			unset($type_arr);
		}
		unset($field_arr);
		return $label;
	}

    // 会员主页模板标签处理
    public function cscms_web($web,$content,$str) {
        $dir=VIEWPATH.'pc'.FGF.'home';
		$newweb='';
	    if (is_dir($dir)){
		    if ($dh = opendir($dir)){
			    while (($file = readdir($dh))!= false){
				    $filePath = $dir.FGF.$file;
					if ($file!='.' && $file!='..' && is_dir($filePath)){
                      $confiles=$filePath.FGF.'config.php';
				      if (file_exists($confiles)){
						  $con=require_once($confiles);
						  $vip='全部级别';
						  if($con['vip']>0){
                              $vip=getzd('userzu','name',$con['vip']);
						  }
						  $level='全部等级';
						  if($con['level']>0){
                              $level=getzd('userlevel','name',$con['level']);
						  }
						  $pic=file_exists($filePath.FGF.'preview.jpg')?Web_Path.'tpl/pc/home/'.$con['dir'].'/preview.jpg':Web_Path.'packs/images/skins.jpg';
						  $ystr=array('[web:pic]','[web:name]','[web:dir]','[web:path]','[web:vip]','[web:level]','[web:cion]');
						  $xstr=array($pic,$con['name'],$con['dir'],$con['path'],$vip,$level,$con['cion']);
                          $newweb.=str_replace($ystr,$xstr,$content);
					  }
					}
			    }
			    closedir($dh);
		    }
	    }
        $str=str_replace($web,$newweb,$str);
		return $str;
    }
    // php标签处理
    public function cscms_php($php,$content,$str) {
		$evalstr=" return $content";
		$newsphp=eval($evalstr);
        $str=str_replace($php,$newsphp,$str);
		return $str;
    }

	//if标签处理
	public function labelif($Mark_Text){
		$Mark_Text = $this->labelif2($Mark_Text);
		$ifRule = "{if:(.*?)}(.*?){end if}";
		$ifRule2 = "{elseif";
		$ifRule3 = "{else}";
		$elseIfFlag = false;
		$ifFlag = false;
		preg_match_all('/'.$ifRule.'/is',$Mark_Text,$arr);
		if(!empty($arr[1])){
			for($i=0;$i<count($arr[1]);$i++){
				$strIf = $arr[1][$i];
				$strThen = $arr[2][$i];
				if (strpos($strThen, $ifRule2) !== FALSE) {
					$elseIfArr = explode($ifRule2, $strThen);
					$elseIfNum = count($elseIfArr);
					$elseIfSubArr = explode($ifRule3, $elseIfArr[$elseIfNum-1]);
					$resultStr = $elseIfSubArr[1];
					$elseIfstr = $elseIfArr[0];
					eval("if($strIf){\$resultStr=\"$elseIfstr\";}");
					for ($k = 1;$k < $elseIfNum;$k++){
						$temp = explode(":", $elseIfArr[$k], 2);$content = explode("}", $temp[1], 2);
						$strElseIf = $content[0];
						$temp1 = strpos($elseIfArr[$k],"}")+strlen("}");$temp2 = strlen($elseIfArr[$k])+1;
						$strElseIfThen = substr($elseIfArr[$k],$temp1,$temp2-$temp1);
						eval("if($strElseIf){\$resultStr=\"$strElseIfThen\";}");
						eval("if($strElseIf){\$elseIfFlag=true;}else{\$elseIfFlag=false;}");
						if ($elseIfFlag) {break;}
					}
					$temp = explode(":", $elseIfSubArr[0], 2);$content = explode("}", $temp[1], 2);
					$strElseIf0 = $content[0];
					$temp1 = strpos($elseIfSubArr[0],"}")+strlen("}");$temp2 = strlen($elseIfSubArr[0])+1;
					$strElseIfThen0 = substr($elseIfSubArr[0],$temp1,$temp2-$temp1);
					eval("if($strElseIf0){\$resultStr=\"$strElseIfThen0\";\$elseIfFlag=true;}");
					$Mark_Text=str_replace($arr[0][$i],$resultStr,$Mark_Text);
				}else{
					if(strpos($strThen, $ifRule3) !== FALSE) {
						$elsearray = explode($ifRule3, $strThen);
						$strThen1 = $elsearray[0];
						$strElse1 = $elsearray[1];
						eval("if($strIf){\$ifFlag=true;}else{\$ifFlag=false;}");
						if ($ifFlag){
							$Mark_Text=str_replace($arr[0][$i],$strThen1,$Mark_Text);
						}else{
							$Mark_Text=str_replace($arr[0][$i],$strElse1,$Mark_Text);
						}
					} else {
						eval("if  ($strIf) { \$ifFlag=true;} else{ \$ifFlag=false;}");
						if ($ifFlag){
							$Mark_Text=str_replace($arr[0][$i],$strThen,$Mark_Text);
						}else{
							$Mark_Text=str_replace($arr[0][$i],"",$Mark_Text);
						}
					}
				}
			}
		}
		return $Mark_Text;
	}

	//if嵌套标签处理
	public function labelif2($Mark_Text){
		$ifRule = "{toif:(.*?)}(.*?){end toif}";
		$ifRule2 = "{elsetoif";
		$ifRule3 = "{elseto}";
		$elseIfFlag = false;
		$ifFlag = false;
		preg_match_all('/'.$ifRule.'/is',$Mark_Text,$arr);
		if(!empty($arr[1])){
			for($i=0;$i<count($arr[1]);$i++){
				$strIf = $arr[1][$i];
				$strThen = $arr[2][$i];
				if (strpos($strThen, $ifRule2) !== FALSE) {
					$elseIfArr = explode($ifRule2, $strThen);
					$elseIfNum = count($elseIfArr);
					$elseIfSubArr = explode($ifRule3, $elseIfArr[$elseIfNum-1]);
					$resultStr = $elseIfSubArr[1];
					$elseIfstr = $elseIfArr[0];
					eval("if($strIf){\$resultStr=\"$elseIfstr\";}");
					for ($k = 1;$k < $elseIfNum;$k++){
						$temp = explode(":", $elseIfArr[$k], 2);$content = explode("}", $temp[1], 2);
						$strElseIf = $content[0];
						$temp1 = strpos($elseIfArr[$k],"}")+strlen("}");$temp2 = strlen($elseIfArr[$k])+1;
						$strElseIfThen = substr($elseIfArr[$k],$temp1,$temp2-$temp1);
						eval("if($strElseIf){\$resultStr=\"$strElseIfThen\";}");
						eval("if($strElseIf){\$elseIfFlag=true;}else{\$elseIfFlag=false;}");
						if ($elseIfFlag) {break;}
					}
					$temp = explode(":", $elseIfSubArr[0], 2);$content = explode("}", $temp[1], 2);
					$strElseIf0 = $content[0];
					$temp1 = strpos($elseIfSubArr[0],"}")+strlen("}");$temp2 = strlen($elseIfSubArr[0])+1;
					$strElseIfThen0 = substr($elseIfSubArr[0],$temp1,$temp2-$temp1);
					eval("if($strElseIf0){\$resultStr=\"$strElseIfThen0\";\$elseIfFlag=true;}");
					$Mark_Text=str_replace($arr[0][$i],$resultStr,$Mark_Text);
				}else{
					if(strpos($strThen, "{else}") !== FALSE) {
						$elsearray = explode($ifRule3, $strThen);
						$strThen1 = $elsearray[0];
						$strElse1 = empty($elsearray[1]) ? '' : $elsearray[1];
						eval("if($strIf){\$ifFlag=true;}else{\$ifFlag=false;}");
						if ($ifFlag){
							$Mark_Text=str_replace($arr[0][$i],$strThen1,$Mark_Text);
						}else{
							$Mark_Text=str_replace($arr[0][$i],$strElse1,$Mark_Text);
						}
					} else {
						eval("if  ($strIf) { \$ifFlag=true;} else{ \$ifFlag=false;}");
						if ($ifFlag){
							$Mark_Text=str_replace($arr[0][$i],$strThen,$Mark_Text);
						}else{
							$Mark_Text=str_replace($arr[0][$i],"",$Mark_Text);
						}
					}
				}
			}
		}
		return $Mark_Text;
	}

	//查找数组指定元素
	public function arr_val($key,$array){
		foreach ($array as $v) {
			if(strtolower($v[1])==$key){
				return $v[2];
				break;
			}
		}
		return NULL;
	}

    //相关标签转换
	public function gettags($tags){
		$tags_list=$tags_key="";
		$tags=trim($tags);
		$Str=" @,@，@|@/@_";
		$StrArr=explode('@',$Str);
		for($i=0;$i<=5;$i++){
			if(stristr($tags,$StrArr[$i])){
				$tags_key=explode($StrArr[$i],$tags);
			}
		}
		if(is_array($tags_key)){
			for($j=0;$j<count($tags_key);$j++){
				$tags_list.=" tags like '%".$tags_key[$j]."%' or";
			}
		}else{
			$tags_list.=" tags like '%".$tags_key."%'";
		}
		if(substr($tags_list,-2)=='or'){
			$tags_list=substr($tags_list,0,-2);
		}
		return $tags_list;
	}
}
