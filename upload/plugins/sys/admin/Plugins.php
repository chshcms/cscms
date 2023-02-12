<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-09-11
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Plugins extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
		$this->load->helper('file');
		$this->lang->load('admin_plugins');
		$this->load->library('csapp');
	}

	//获取版块目录
	public function index(){
	    $page = intval($this->input->get('page'));
        if($page==0) $page=1;
	    $arrs = get_dir_file_info("./plugins/");
        $data['pagesize']=10;
        $data['page']=$page;
        $data['pagejs']=ceil((count($arrs)-1) / $data['pagesize']); //总页数
        $data['url']=site_url('admin/plugins')."?";
        $data['dirs']=array_slice($arrs,$data['pagesize']*(($page>$data['pagejs']?$data['pagejs']:$page)-1),$data['pagesize']);
        $this->load->view('plugins.html',$data);
	}

	//配置
	public function setting(){
	    $dir = $this->input->get('dir',true);
		$data['SITE'] = require_once(CSCMS.$dir.FGF.'site.php');
	    $data['model']= require_once(CSCMS.$dir.FGF.'setting.php');
	    $data['dir']=$dir;
        $row=$this->db->query("SELECT ak,name FROM ".CS_SqlPrefix."plugins where dir='".$dir."'")->row();
		$arrs=unarraystring(sys_auth($row->ak,'D'));
	    $data['key']=(empty($arrs) || empty($arrs['key']) || $arrs['key']=='0')?'':$arrs['key'];
        if($data['model']['name']!=$row->name) $data['model']['name']=$row->name;
        $this->load->view('plugins_setting.html',$data);
	}

	//配置保存
	public function setting_save(){
        $name = $this->input->post('name',true);
        $dir = $this->input->post('dir',true);
        $Web_Mode = intval($this->input->post('Web_Mode',true));
		$Mobile_Is = intval($this->input->post('Mobile_Is',true));
        $Ym_Mode = intval($this->input->post('Ym_Mode',true));
        $Cache_Is = intval($this->input->post('Cache_Is',true));
        $Cache_Time = intval($this->input->post('Cache_Time',true));
        $Ym_Url = $this->input->post('Ym_Url',true);
        $User_Qx = $this->input->post('user',true);
        $User_Dj_Qx = $this->input->post('user_dj',true);
        $rewrite = $this->input->post('rewrite',true);
        $html = $this->input->post('html',true);
        $seo = $this->input->post('seo',true);
        $key = $this->input->post('key',true);
		if($Web_Mode==0) $Web_Mode=1;
		if($Cache_Time==0) $Cache_Time=1800;

		if($Ym_Mode>0 && empty($Ym_Url)) getjson(L('plub_save_0'));
        $row=$this->db->query("SELECT ak,name FROM ".CS_SqlPrefix."plugins where dir='".$dir."'")->row();
		if(!empty($name) && $name!=$row->name){
            $this->db->query("update ".CS_SqlPrefix."plugins set name='".$name."' where dir='".$dir."'");
		}
        if(is_dir(FCPATH.'plugins'.FGF.$dir)){
			$data['Web_Mode']=$Web_Mode;
			$data['Mobile_Is']=$Mobile_Is;
			$data['Cache_Is']=$Cache_Is;
			$data['Cache_Time']=$Cache_Time;
			$data['Ym_Mode']=$Ym_Mode;
			$data['Ym_Url']=$Ym_Url;
			$data['User_Qx']=empty($User_Qx)?'':implode(',', $User_Qx);
			$data['User_Dj_Qx']=empty($User_Dj_Qx)?'':implode(',', $User_Dj_Qx);
			$data['Rewrite_Uri']=$rewrite;
			$data['Html_Uri']=$html;
			$data['Seo']=$seo;
			//判断开启二级域名
			global $_CS_Domain;
			if($Ym_Mode==1){
			    $_CS_Domain[$dir]=$Ym_Url;
			    arr_file_edit($_CS_Domain);
			}else{
				if(arr_key_value($_CS_Domain,$dir)){
			        unset($_CS_Domain[$dir]);
			        arr_file_edit($_CS_Domain);
				}
			}
			//伪静态模式，写入URL路由
			if($Web_Mode==2){
				global $_CS_Rewrite;
			    foreach ($rewrite as $key => $val) {
			    	if($key == 'index'){
			    		$_CS_Rewrite[$dir] = $val['url'];
			    		arr_file_edit($_CS_Rewrite,CSCMS.'sys'.FGF.'Cs_Rewrite.php');
						continue;
			    	}
					list($preg, $value) = $this->_rule_preg_value($rewrite[$key]['url']);
			        if (!$preg || !$value) {
						$preg=$rewrite[$key]['url'];
						$rewrite_uri=$rewrite[$key]['uri'];
			        }else{
						$rewrite_uri=$rewrite[$key]['uri'];
						if (!empty($value['{ji}'])){ $rewrite_uri=str_replace("{ji}",'$'.$value['{ji}'],$rewrite_uri);}
						if (!empty($value['{zu}'])){ $rewrite_uri=str_replace("{zu}",'$'.$value['{zu}'],$rewrite_uri);}
						if (!empty($value['{id}'])){ $rewrite_uri=str_replace("{id}",'$'.$value['{id}'],$rewrite_uri);}
						if (!empty($value['{page}'])){ $rewrite_uri=str_replace("{page}",'$'.$value['{page}'],$rewrite_uri);}
						if (!empty($value['{sort}'])){ $rewrite_uri=str_replace("{sort}",'$'.$value['{sort}'],$rewrite_uri);}
						if (!empty($value['{sname}'])){ $rewrite_uri=str_replace(array("{sname}","{id}"),'$'.$value['{sname}'],$rewrite_uri);}
						//去除未解析的
						$arr1 = array('{ji}','{zu}','{id}','{page}','{sort}','{sname}');
						$arr2 = array('0','0','1','1','id','null');
						$rewrite_uri = str_replace($arr1,$arr2,$rewrite_uri);
					}
					$str1 = array('{ji}','{zu}','{id}','{page}','{sort}');
					$str2 = array('0','0','0','1','id');
					$rewrite_uri = str_replace($str1,$str2,$rewrite_uri);
			        $_data[$preg] = $rewrite_uri;
					$_note[$preg]['name'] = $rewrite[$key]['title'];
					$_note[$preg]['url'] = $rewrite[$key]['url'];
			    }
				$this->_route_file(CSCMS.$dir.FGF.'rewrite.php', $_data, $_note, $dir);
			}else{
				$this->_route_file(CSCMS.$dir.FGF.'rewrite.php');
			}
			arr_file_edit($data,CSCMS.$dir.FGF.'site.php');
			$info['url'] = site_url('plugins').'?v='.rand(1000,9999);
			$info['msg'] = L('plub_save_4');
			getjson($info,0);
		}else{
            getjson(L('plub_save_3'));
		}
	}

	//安装
	public function install(){
 	    $dir = $this->input->get_post('dir',true);
        if(is_dir(FCPATH.'plugins'.FGF.$dir.FGF)){
			$model = require_once(CSCMS.$dir.FGF.'setting.php');
			if (!file_exists(CSCMS.$dir.FGF.'install.php') || $model['mid']==''){
				admin_msg(L('plub_ins_1'),'javascript:history.back();');
			}
			$SQLDB = require_once(CSCMS.$dir.FGF.'install.php');
			$row=$this->db->query("SELECT id FROM ".CS_SqlPrefix."plugins where dir='".$dir."'")->row();
			if($row){
				$info = L('plub_ins_0');
			  	getjson($info,1);
			}else{
				//判断数据库
				if(is_array($SQLDB)){
				    foreach ($SQLDB as $sql) {
				        $this->Csdb->get_table(str_replace('{prefix}', CS_SqlPrefix, $sql));
				    }
				}else{
				   	$this->Csdb->get_table(str_replace('{prefix}', CS_SqlPrefix, $SQLDB));
				}
				$add['dir'] = $dir;
				$add['name'] = $model['name'];
				$add['author'] = $model['author'];
				$add['version'] = $model['version'];
				$add['description'] = $model['description'];
				$this->Csdb->get_insert('plugins',$add);

				$data['dir'] = $dir;
				$data['mid'] = $model['mid'];
				$installurl = $this->csapp->url('plub/installs',$data);
				$backurl = htmlall($installurl);
				$info['name'] = $model['name'];
				$info['fun'] = __FUNCTION__;
				$info['msg'] = L('plub_ins_2');
				getjson($info,0);
			}
		}else{
			getjson(L('plub_ins_3'),1);
		}	 
	}

	//卸载
	public function uninstall(){
	    $dir = $this->input->get_post('dir',true);
        if(is_dir(FCPATH.'plugins'.FGF.$dir.FGF)){
            $row=$this->db->query("SELECT id FROM ".CS_SqlPrefix."plugins where dir='".$dir."'")->row(); 
            if(!$row){
                getjson(L('plub_uins_0'),1);
			}else{
				  if(is_file(CSCMS.$dir.FGF.'uninstall.php')) {
			            $SQLDB = require_once(CSCMS.$dir.FGF.'uninstall.php');
				  }else{
			            $SQLDB = '';
				  }
				  if (is_file(CSCMS.$dir.FGF.'setting.php')) {
			            $model = require_once(CSCMS.$dir.FGF.'setting.php');
				  }else{
			            $model['mid'] = 0;
				  }
				  //判断数据库
				  if(is_array($SQLDB)){
                       foreach ($SQLDB as $sql) {
                           $this->Csdb->get_table(str_replace('{prefix}', CS_SqlPrefix, $sql));
                       }
				  }else{
                       $this->Csdb->get_table(str_replace('{prefix}', CS_SqlPrefix, $SQLDB));
				  }
				  //删除数据库记录
                  $this->db->query("delete from ".CS_SqlPrefix."plugins where dir='".$dir."'");

                  $data['dir']=$dir;
                  $data['mid']=$model['mid'];
				  $uninstallurl=$this->csapp->url('plub/uninstall',$data);

        		  $info['url'] = $uninstallurl;
        		  $info['func'] = __FUNCTION__;
        		  $info['msg'] = L('plub_uins_1');
        		  getjson($info,0);

	         }
		}else{
            getjson(L('plub_uins_2'),1);
		}	
	}

	//清空数据库
	public function clear(){
	    $dir = $this->input->get_post('dir',true);
        if(is_dir(FCPATH.'plugins'.FGF.$dir.FGF)){

             $row=$this->db->query("SELECT id FROM ".CS_SqlPrefix."plugins where dir='".$dir."'")->row(); 
             if(!$row){
                getjson(L('plub_clr_0'),1);
			 }else{
				//先删除数据表
				if (is_file(CSCMS.$dir.FGF.'uninstall.php')) {
			            $SQLDB1 = require_once(CSCMS.$dir.FGF.'uninstall.php');
				  }else{
			            $SQLDB1 = '';
				  }
				  if(is_array($SQLDB1)){
                       foreach ($SQLDB1 as $sql) {
                           $this->Csdb->get_table(str_replace('{prefix}', CS_SqlPrefix, $sql));
                       }
				  }else{
                       $this->Csdb->get_table(str_replace('{prefix}', CS_SqlPrefix, $SQLDB1));
				  }

				  //在重新安装数据表
				  if (is_file(CSCMS.$dir.FGF.'install.php')) {
			            $SQLDB2 = require_once(CSCMS.$dir.FGF.'install.php');
				  }else{
			            $SQLDB2 = '';
				  }
				  if(is_array($SQLDB2)){
                       foreach ($SQLDB2 as $sql) {
                           $this->Csdb->get_table(str_replace('{prefix}', CS_SqlPrefix, $sql));
                       }
				  }else{
                       $this->Csdb->get_table(str_replace('{prefix}', CS_SqlPrefix, $SQLDB2));
				  }
        		  $info['func'] = __FUNCTION__;
        		  $info['msg'] = L('plub_clr_1');
        		  getjson($info,0);
	         }
		}else{
            getjson(L('plub_clr_2'),1);
		}
	}

	//删除
	public function del(){
	    $dir = $this->input->get_post('dir',true);
	    if($dir==''){
	    	getjson(L('plub_del_0'),1);
	    }
        deldir(FCPATH.'plugins'.FGF.$dir.FGF);
		//删除配置目录
        deldir(CSCMS.$dir.FGF);
		//删除模板目录
        deldir(FCPATH.'tpl/admin/'.$dir.FGF);
        $info['func'] = __FUNCTION__;
        $info['msg'] = L('plub_del_1');
        getjson($info,0);
	}

    //云平台
	public function yun(){
		header("Location: ".$this->csapp->url('plub')."");
	}

	//在线安装
	public function down(){
            $dir=$this->input->get_post('dir');
            $mid=$this->input->get_post('mid');
            $key=$this->input->get_post('key');

	        if (empty($mid) || empty($dir)){
	        	admin_msg(L('plub_down_0'),site_url('plugins'),'no');
			}else{  //下载
                  $data['key']=$key;
                  $zip=$this->csapp->url('plub/down/'.$mid,$data);
                  $zippath=FCPATH."attachment/other/plugins_".$dir.".zip";
                  $files_file=$this->csapp->down($zip,$zippath);
    	          if($files_file=='-1') admin_msg(L('plub_down_1'),site_url('plugins'),'no');
    	          if($files_file=='-2') admin_msg(L('plub_down_2'),site_url('plugins'),'no');
    	          if($files_file=='-3') admin_msg(L('plub_down_3'),site_url('plugins'),'no');
    	          if($files_file=='10001') admin_msg(L('plub_down_4'),site_url('plugins'),'no');
    	          if($files_file=='10002') admin_msg(L('plub_down_5'),site_url('plugins'),'no');
	              if(filesize($zippath) == 0) admin_msg(L('plub_down_6'),site_url('plugins'),'no');

		          //解压缩
	              $this->load->library('cszip');
		          $this->cszip->PclZip($zippath);
				  //尝试解压覆盖
		          if ($this->cszip->extract(PCLZIP_OPT_PATH, FCPATH, PCLZIP_OPT_REPLACE_NEWER) == 0) {
                       die(L('plub_down_7').$zippath);
		          }else{
		               if (!file_exists(CSCMS.$dir.FGF.'setting.php')) {
			                @unlink($zippath);
                            admin_msg(L('plub_down_8'),site_url('plugins'),'no');
					   }
			           @unlink($zippath);
                       admin_msg(L('plub_down_9'),site_url('plugins'));
				  }
			}
	}

	//在线升级
	public function update(){
            $mold=$this->input->get_post('mold');
            $dir=$this->input->get_post('dir');
            $markid=$this->input->get_post('mid');
            $key=$this->input->get_post('key');
            $v=$this->input->get_post('v');

	        if (empty($key) || empty($dir)){

                  $data['mid']=$markid;
                  $data['v']=$v;
                  header("Location: ".$this->csapp->url('plub/update',$data)."");

			}else{  //下载

                  $zip=$this->csapp->url('plub/update').'&code=gbk&key='.$key;
                  $zippath=FCPATH."attachment/other/plugins_".$dir."_update.zip";
                  $files_file=$this->csapp->down($zip,$zippath);
    	          if($files_file=='-1') admin_msg(L('plub_upd_0'),site_url('plugins'),'no');
    	          if($files_file=='-2') admin_msg(L('plub_upd_1'),site_url('plugins'),'no');
    	          if($files_file=='-3') admin_msg(L('plub_upd_2'),site_url('plugins'),'no');
    	          if($files_file=='10001') admin_msg(L('plub_upd_3'),site_url('plugins'),'no');
    	          if($files_file=='10002') admin_msg(L('plub_upd_4'),site_url('plugins'),'no');
    	          if($files_file=='10003') admin_msg(L('plub_upd_5'),site_url('plugins'),'no');
	              if(filesize($zippath) == 0) admin_msg(L('plub_upd_6'),site_url('plugins'),'no');

				  //先备份原始板块
	              $this->load->library('cszip');
				  $zip_path=FCPATH."attachment/other/plugins_".$dir."_backup_".date('Ymd').".zip";
		          $this->cszip->PclZip($zip_path); //创建压缩包
                  $this->cszip->create($plub_path); //增加增加目录
		          //解压缩
		          $this->cszip->PclZip($zippath);
				  //尝试解压覆盖
		          if ($this->cszip->extract(PCLZIP_OPT_PATH, FCPATH, PCLZIP_OPT_REPLACE_NEWER) == 0) {
                       die(vsprintf(L('plub_upd_7'),array($dir)).$zippath);
		          }else{
			           @unlink($zippath);
                       admin_msg(L('plub_upd_8'),site_url('plugins'));
				  }
			}
	}

	//板块后台权限划分
	public function role(){
        $id = intval($this->input->get('id',true));
        $dir = $this->input->get('dir',true);
        if(is_dir(FCPATH.'plugins'.FGF.$dir)){
			 $data['link'] = require_once(CSCMS.$dir.FGF.'menu.php');
			 $data['dir']=$dir;
			 $row=$this->db->query("SELECT app FROM ".CS_SqlPrefix."adminzu where id='".$id."'")->row(); 
			 $apparr=unarraystring($row->app);
			 $data['app']=(!empty($apparr[$dir]))?$apparr[$dir]:'';
			 $data['id']=$id;
             $this->load->view('plugins_role.html',$data);
		}else{
            exit(L('plub_role_0'));
		}
	}

	//板块后台权限修改
	public function role_save(){
        $app = $this->input->post('sys',true);
        $dir = $this->input->post('dir',true);
        $id = intval($this->input->post('id',true));
	    if(!empty($app)){
             $apps=implode(',', $app);
		}else{
             $apps='';
		}
		$row=$this->db->query("SELECT app FROM ".CS_SqlPrefix."adminzu where id='".$id."'")->row();
	    $apparr=unarraystring($row->app);
        $apparr[$dir]=$apps;
		$data['app']=arraystring($apparr);
        $this->Csdb->get_update('adminzu',$id,$data);
        $info['msg'] = L('plub_rs_0');
        $info['iframe'] = 1;
        getjson($info,0);
	}

	//URL规则说明
	public function rule(){
        $this->load->view('rule.html');
	}

	//将路由规则生成至文件
	public function _route_file($file, $data=array(), $note=array(), $dir=array()) {
		
		$string = '<?php'.PHP_EOL.PHP_EOL;
		$string.= 'if (!defined(\'BASEPATH\')) exit(\'No direct script access allowed\');'.PHP_EOL.PHP_EOL;
		$string.= L('plub_rf_0').PHP_EOL.PHP_EOL;
		
		if ($data) {
			arsort($data);
			foreach ($data as $key => $val) {
				$string.= '$route[\''.$key.'\']'.$this->_space($key).'= \''.$val.'\'; // '.$note[$key]['name'].' '.L('plub_rf_1').$note[$key]['url'].PHP_EOL;
			}
		}
		write_file($file, $string);
	}

	//正则解析
	private function _rule_preg_value($rule) {

		$rule = trim(trim($rule, '/'));
		if (preg_match_all('/\{(.*)\}/U', $rule, $match)) {

			$value = array();
			foreach ($match[0] as $k => $v) {
				$value[$v] = $k + 1;
			}
			
			$preg = preg_replace(
				array(
					'#\{id\}#U',
					'#\{page\}#U',
					'#\{sort\}#Ui',
					'#\{sname\}#Ui',
					'#\{zu\}#U',
					'#\{ji\}#U',
					'#\{.+}#U',
					'#/#'
				),
				array(
					'(\d+)',
					'(\d+)',
					'(\w+)',
					'(\w+)',
					'(\d+)',
					'(\d+)',					
					'(.+)',
					'\/'
				),
				$rule
			);
			
			return array($preg, $value);
		}
		
		return array(0, 0);
	}

	//补空格
	private function _space($name) {
		$len = strlen($name) + 2;
	    $cha = 40 - $len;
	    $str = '';
	    for ($i = 0; $i < $cha; $i ++) $str .= ' ';
	    return $str;
	}
}

