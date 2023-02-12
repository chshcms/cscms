<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-07
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Skin extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
	    $this->load->helper('directory');
        $this->load->library('csapp');
		$this->lang->load('admin_skin');
	}

	public function index(){
        $ac = $this->input->get('ac',true); //PC OR WAP
        $op = $this->input->get('op',true); //skins user home
        $page = intval($this->input->get('page'));
        if($page==0) $page=1;
		if($ac!='mobile') $ac='pc';
		if($op!='home' && $op!='user') $op='skins';
		//模版物理路径
        $path = VIEWPATH.$ac.FGF.$op.FGF;
        $arrs=directory_map($path, 1);
        $per_page=10;
        $totalPages=ceil((count($arrs)-1) / $per_page); //总页数
        $base_url=site_url('skin').'?ac='.$ac.'&op='.$op;
        if(!empty($arrs)){
        	$dir_arr=array_slice($arrs,$per_page*(($page>$totalPages?$totalPages:$page)-1),$per_page);
        }else{
        	$dir_arr=array();
        }
        $dirs=array();
	    if ($dir_arr) {
		    foreach ($dir_arr as $t) {
		    	$t = str_replace(array("\\","/"), "", $t);
			    if (is_dir($path.$t)) {
					$confiles = $path.$t.'/config.php';
					if (file_exists($confiles)){
						$config = require_once($confiles);
						$pic = 'tpl/'.$ac.'/'.$op.'/'.$t.'/preview.jpg';
                        if (!file_exists(FCPATH.$pic)) $pic=Web_Path.'packs/images/skins.jpg';
						if($ac=='mobile'){
							if($op=='user'){
							     $clas = Mobile_User_Dir==$config['path'] ? 'selected' : '';
							}elseif($op=='home'){
							     $clas = Mobile_Home_Dir==$config['path'] ? 'selected' : '';
							}else{
							     $clas = Mobile_Skins_Dir==$config['path'] ? 'selected' : '';
							}
						}else{
							if($op=='user'){
							     $clas = Pc_User_Dir==$config['path'] ? 'selected' : '';
							}elseif($op=='home'){
							     $clas = Pc_Home_Dir==$config['path'] ? 'selected' : '';
							}else{
							     $clas = Pc_Skins_Dir==$config['path'] ? 'selected' : '';
							}
						}
				        $dirs[] = array(
					        'clas' => $clas,
					        'pic'  => Web_Path.$pic,
					        'name' => $config['name'],
					        'path' => $config['path'],
					        'mid'  => $config['mid'],
					        'author' => $config['author'],
					        'version' => $config['version'],
					        'description' => $config['description'],
					        'link' => site_url('skin/show')."?ac=".$ac."&op=".$op."&dirs=".$t,
					        'ulink' => site_url('skin/look')."?ac=".$ac."&op=".$op."&dirs=".$t,
					        'mrlink' => site_url('skin/init')."?ac=".$ac."&op=".$op."&dirs=".$t,
					        'dellink' => site_url('skin/del')."?ac=".$ac."&op=".$op."&dirs=".$t,
				        );
					}
			    }
		    }
	    }
        $data['skins'] = $dirs;
        $data['nums'] = count($arrs);
        $data['page'] = $page;
        $data['op'] = $op;
        $data['ac'] = $ac;
		$data['pages'] = admin_page($base_url,$page,$totalPages); //获取分页类
		$data['uplink']=site_url('skin/upload')."?ac=".$ac."&op=".$op;
        $this->load->view('skin.html',$data);
	}

    //设置默认模板
	public function init(){
        $ac = $this->input->get('ac',true);
        $op = $this->input->get('op',true);
        $dir = $this->input->get('dirs',true);
		if($ac!='mobile') $ac='pc';
		if($op!='home' && $op!='user') $op='skins';

		if(empty($dir)) getjson(L('plub_01'));  //模板路径不能为空
        $confiles = VIEWPATH.$ac.FGF.$op.FGF.$dir.FGF.'config.php';
		if (file_exists($confiles)){
			$config = require_once($confiles);
		}else{
            getjson(L('plub_02'));  //模板配置文件不存在
		}
		if(empty($config['path']) || $config['mid']==''){
            getjson(L('plub_03'));  //模板配置文件不正确
		}
		//设置默认
		$this->load->helper('file');
		$conf = read_file(CSCMS.'sys'.FGF.'Cs_Config.php');
		if($ac=='mobile'){
			if($op=='user'){
	            $conf = preg_replace("/'Mobile_User_Dir','(.*?)'/","'Mobile_User_Dir','".$config['path']."'",$conf);
			}elseif($op=='home'){
	            $conf = preg_replace("/'Mobile_Home_Dir','(.*?)'/","'Mobile_Home_Dir','".$config['path']."'",$conf);
			}else{
	            $conf = preg_replace("/'Mobile_Skins_Dir','(.*?)'/","'Mobile_Skins_Dir','".$config['path']."'",$conf);
			}
        }else{
			if($op=='user'){
	            $conf = preg_replace("/'Pc_User_Dir','(.*?)'/","'Pc_User_Dir','".$config['path']."'",$conf);
			}elseif($op=='home'){
	            $conf = preg_replace("/'Pc_Home_Dir','(.*?)'/","'Pc_Home_Dir','".$config['path']."'",$conf);
			}else{
	            $conf = preg_replace("/'Pc_Skins_Dir','(.*?)'/","'Pc_Skins_Dir','".$config['path']."'",$conf);
			}
        }
	    $res = write_file(CSCMS.'sys'.FGF.'Cs_Config.php', $conf);
		if($res){
			if(!strstr($_SERVER['HTTP_REFERER'],'?')){
				$info['url'] = $_SERVER['HTTP_REFERER'].'?v='.rand(1000,9999);
			}else{
				$info['url'] = $_SERVER['HTTP_REFERER'].'&v='.rand(1000,9999);
			}
            $info['msg'] = L('plub_04');
            getjson($info,0);
		}else{
            getjson(L('plub_05'));  //设置默认模板失败
		}
	}

    //模板作者信息
	public function look(){
        $ac = $this->input->get('ac',true);
        $op = $this->input->get('op',true);
        $dir = str_replace("..","",$this->input->get('dirs',true));
		if($ac!='mobile') $ac='pc';
		if($op!='home' && $op!='user') $op='skins';

		if(empty($dir)) admin_msg(L('plub_01'),'###','no');  //模板路径不能为空
        $confiles = VIEWPATH.$ac.FGF.$op.FGF.$dir.FGF.'config.php';
		if (file_exists($confiles)){
			$config = require_once($confiles);
		}else{
            admin_msg(L('plub_02'),'###','no');  //模板配置文件不存在
		}

        $data['skin'] = $config;
        $data['ac'] = $ac;
        $data['op'] = $op;
        $data['dir'] = $dir;
        $this->load->view('skin_look.html',$data);
	}

    //模板配置修改
	public function look_save(){
        $ac = $this->input->get('ac',true);
        $op = $this->input->get('op',true);
		if($ac!='mobile') $ac='pc';
		if($op!='home' && $op!='user') $op='skins';
        $vip = intval($this->input->post('vip'));
        $level = intval($this->input->post('level'));
        $cion = intval($this->input->post('cion'));
        $name = $this->input->post('name',true);
        $dir = str_replace("..","",$this->input->post('dir',true));
		if(empty($dir)) admin_msg(L('plub_06'),'javascript:history.back();','no');
        $confiles = VIEWPATH.$ac.FGF.$op.FGF.$dir.FGF.'config.php';
		if (file_exists($confiles)){
			$skins = require_once($confiles);
		}else{
            admin_msg(L('plub_02'),'###','no');  //模板配置文件不存在
		}
		$skins['vip']=$vip;
		$skins['level']=$level;
		$skins['cion']=$cion;
		$skins['name']=$name;

        //修改
		$res = arr_file_edit($skins,$confiles);
		if(!$res) getjson(L('plub_06'));

        $info['url'] = site_url('skin/look')."?ac=".$ac."&op=".$op."&dirs=".$dir."&v=".rand(1000,9999);
        $info['msg'] = L('plub_07');
        getjson($info,0);
	}

    //模板文件修改
	public function edit(){
        $ac = $this->input->get('ac',true);
        $op = $this->input->get('op',true);
        $do = $this->input->get('do',true);
        $dir = str_replace("..","",$this->input->get('dirs',true));
        $file = str_replace("..","",$this->input->get('file'));
		$exts = strtolower(trim(strrchr($file, '.'), '.'));
		if($ac!='mobile') $ac='pc';
		if($op!='home' && $op!='user') $op='skins';
		if(empty($dir) || empty($file)) getjson(L('plub_01'));
		if($exts!='html' && $exts!='css' && $exts!='js') getjson(L('plub_08'));

        $skin_dir = VIEWPATH.$ac.FGF.$op.FGF.$dir.FGF.$file;
        if(!file_exists($skin_dir)) getjson(L('plub_09'));

        $this->load->helper('file');
        if($do=='add'){        	
			//正规验证文件名
			if(!preg_match("/^[0-9a-zA-Z\_\/\.]{1,}$/i",$file)) getjson(L('plub_08'));
	        $html = $this->input->post('html');
            //写文件
            if (!write_file($skin_dir, $html)){
                getjson(L('plub_10'));
            }else{
				$parr=explode('/',$file);
				$path='';
                for($j=0;$j<count($parr)-1;$j++){	
                    $path.=$parr[$j].'/';
				}
		        if(substr($path,-1)=='/') $path=substr($path,0,-1);
		        $info['url'] = site_url('skin/show')."?ac=".$ac."&op=".$op."&dirs=".$dir."&path=".$path."&v=".rand(1000,9999);
                $info['msg'] = L('plub_11');
                getjson($info,0);
            }
		}else{
            $html = get_bm(read_file($skin_dir));
		    $data['savelink'] = site_url('skin/edit')."?ac=".$ac."&do=add&op=".$op."&dirs=".$dir."&file=".$file;
		    $data['path'] = str_replace(array(VIEWPATH,"\\","//"),array(Web_Path.'tpl/','/','/'),$skin_dir);
		    $data['html'] = str_replace('</textarea>','&lt;/textarea&gt;',$html);
            $this->load->view('skin_edit.html',$data);
		}
	}

    //模板文件浏览
	public function show(){
        $ac = $this->input->get('ac',true);
        $op = $this->input->get('op',true);
        $dir = str_replace("..","",$this->input->get('dirs',true));
        $path = str_replace("..","",$this->input->get('path'));
		if($ac!='mobile') $ac='pc';
		if($op!='home' && $op!='user') $op='skins';

		if(empty($dir)) exit(L('plub_01'));
        $skin_dir = VIEWPATH.$ac.FGF.$op.FGF.$dir.FGF;

		//模板文件说明
		$skin_arr = array();
		$skinfiles = $skin_dir.'skins.php';
		if (file_exists($skinfiles)){
			$s_arr = require_once($skinfiles);
			$p_arr = explode('/', $path);
			if(!empty($p_arr[2]) && isset($s_arr[$p_arr[2]])){
				$skin_arr = $s_arr[$p_arr[2]];
			}
		}

		$skin_dir=(!empty($path))?$skin_dir.$path.'/':$skin_dir;
		$skin_dir=str_replace("//","/",$skin_dir);

		if (!is_dir($skin_dir)) {
            exit(L('plub_01'));
		}

        $this->load->helper('file');
		$showarr = get_dir_file_info($skin_dir, $top_level_only = TRUE);
        $dirs=$list=array();
	    if ($showarr) {
		    foreach ($showarr as $t) {
			    if (is_dir($t['server_path'])) {
					$title = L('plub_13');
					//获取板块名字
			    	$plubset = CSCMS.$t['name'].FGF.'setting.php';
			    	if(file_exists($plubset)){
			    		$SET = require $plubset;
			    		$title = $SET['name'].L('plub_14');
			    	}elseif($t['name']=='sys'){
			    		$title = L('plub_15');
			    	}
			        $dirs[] = array(
				       'name' => $t['name'],
				       'title' => $title,
				       'date' => date('Y-m-d H:i:s',$t['date']),
				       'size' => '--',
				       'icon' => Web_Path.'packs/admin/images/ext/dir.gif',
				       'link' => site_url('skin/show')."?ac=".$ac."&op=".$op."&dirs=".$dir."&path=".$path."/".$t['name'],
				       'dellink' => site_url('skin/del')."?ac=".$ac."&op=".$op."&dirs=".$dir."&file=".$path."/".$t['name'],
			        );
			    } else {
					$exts = strtolower(trim(strrchr($t['name'], '.'), '.'));
					if($exts=='css'){
						$title = L('plub_16');
					}elseif($exts=='js'){
						$title = L('plub_17');
					}else{
					    $title = arr_key_value($skin_arr,$t['name']);
					    if(!$title) $title = arr_key_value($s_arr,$t['name']);
					    if(!$title) $title = L('plub_18');
					}
					if($exts=='html' || $exts=='css' || $exts=='js'){
						$times=date('Y-m-d H:i:s',$t['date']);
					    $list[] = array(
						    'name' => $t['name'],
							'title'=> $title,
						    'ext' => get_extpic($exts),
					        'date' => (date('Y-m-d',$t['date'])==date('Y-m-d'))?'<font color=red>'.$times.'<font>':$times,
					        'size' => formatsize($t['size']),
						    'icon' => Web_Path.'packs/admin/images/ext/'.get_extpic($exts).'.gif',
						    'link' => site_url('skin/edit')."?ac=".$ac."&op=".$op."&dirs=".$dir."&file=".$path."/".$t['name'],
						    'blink' => site_url('skin/copyt')."?ac=".$ac."&op=".$op."&dirs=".$dir."&file=".$path."/".$t['name'].'&path='.$path,
					        'dellink' => site_url('skin/del')."?ac=".$ac."&op=".$op."&dirs=".$dir."&file=".$path."/".$t['name'],
					    );
					}
			    }
		    }
	    }
		$data['addlink'] = site_url('skin/add')."?ac=".$ac."&op=".$op."&dirs=".$dir.$path;
		$data['path'] = str_replace(array(VIEWPATH,"\\","//"),array(Web_Path.'tpl/','/','/'),$skin_dir);
		$data['dirs'] = $dirs;
		$data['show'] = $list;
		$data['uplink']=site_url('skin/upload')."?ac=".$ac."&op=".$op;

		$parr=explode('/',$path);
		$spath='';
        for($j=0;$j<count($parr)-1;$j++){	
             $spath.=$parr[$j].'/';
		}
		if(substr($spath,-1)=='/') $spath=substr($spath,0,-1);
		$data['slink']=empty($path)?site_url('skin')."?ac=".$ac."&op=".$op:site_url('skin/show')."?ac=".$ac."&op=".$op."&dirs=".$dir."&path=".$spath;

        $this->load->view('skin_show.html',$data);
	}

    //新增
	public function add()
	{
        $ac = $this->input->get('ac',true);
        $op = $this->input->get('op',true);
        $do = $this->input->get('do',true);
        $dir = str_replace("..","",$this->input->get('dirs',true));
        $file = str_replace("..","",$this->input->post('file',true));
		if($ac!='mobile') $ac='pc';
		if($op!='home' && $op!='user') $op='skins';

		if(empty($dir)) admin_msg(L('plub_01'),'javascript:history.back();','no');  //模板路径不能为空

        $skin_dir = VIEWPATH.$ac.FGF.$op.FGF.$dir.FGF;
		if (!is_dir($skin_dir)) {
            admin_msg(L('plub_12'),'javascript:history.back();','no');  //模板路径不能为空
		}
		if($do=='add'){
			if(empty($file)) exit('<script>alert("'.L('plub_16').'");javascript:history.back();</script>');
        	//正规验证文件名
			if(!preg_match("/^[0-9a-zA-Z\_\/\.]{1,}$/i",$file)) getjson(L('plub_08'));
			$path=$skin_dir.$file;
            $exts = strtolower(trim(strrchr($file, '.'), '.'));
			if($exts=='html' || $exts=='js' || $exts=='css'){ //增加文件
				if(file_exists($path)){
					getjson(L('plub_19'));
				}
               	if(!write_file($path, ' ')){
					getjson(L('plub_20'));
			   	}else{
                    $info['msg'] = L('plub_21');
                    $info['url'] = site_url('skin/show').'?ac='.$ac.'&dirs='.$dir.'&op='.$op;
                    $info['parent'] = 1;
                    getjson($info,0);
			   	}
			}else{  //增加目录
				if(is_dir($path)){
					getjson(L('plub_22'));
				}
               	if(!mkdirss(str_replace('.', '', $path))){
					getjson(L('plub_23'));
			   	}else{
                    $info['msg'] = L('plub_24');
                    $info['url'] = site_url('skin/show').'?ac='.$ac.'&dirs='.$dir.'&op='.$op;
                    $info['parent'] = 1;
                    getjson($info,0);
			   	}
			}
            exit;
		}else{
            $data['savelink']=site_url('skin/add')."?ac=".$ac."&op=".$op."&do=add&dirs=".$dir;
            $this->load->view('skin_add.html',$data);
		}
	}

    //文件备份
	public function copyt(){
        $ac = $this->input->get('ac',true);
        $op = $this->input->get('op',true);
        $dir = str_replace("..","",$this->input->get('dirs',true));
        $file = str_replace("..","",$this->input->get('file'));
        $path = str_replace("..","",$this->input->get('path'));
		if($ac!='mobile') $ac='pc';
		if($op!='home' && $op!='user') $op='skins';
		if(empty($dir) || empty($file)) getjson('路径不能为空'); //路径不能为空
        $skin_dir = VIEWPATH.$ac.FGF.$op.FGF.$dir.FGF.$file;
		$old_skin_dir = str_replace("//","/",$skin_dir);
		$exts = '.'.strtolower(trim(strrchr($skin_dir, '.'), '.'));
		$new_skin_dir=str_replace($exts,' - '.L('plub_25').$exts,$old_skin_dir);
		if(copy($old_skin_dir,$new_skin_dir)){
            $info['url'] = site_url('skin/show').'?ac='.$ac.'&op='.$op.'&dirs='.$dir.'&path='.$path.'&v='.rand(1000,9999);
            $info['msg'] = L('plub_26');
            getjson($info,0);
		}else{
            getjson(L('plub_27'));
		}
	}

    //文件删除
	public function del(){
        $ac = $this->input->get('ac',true);
        $op = $this->input->get('op',true);
        $dir = str_replace(".","",$this->input->get('dirs',true));
        $file = str_replace("..","",$this->input->get('file'));
		if($ac!='mobile') $ac='pc';
		if($op!='home' && $op!='user') $op='skins';
		if(empty($dir) || preg_match("/^[\/]{1,}$/i",$dir)) getjson(L('plub_27'));


        $skin_dir = VIEWPATH.$ac.FGF.$op.FGF.$dir.FGF.$file;
		if (!is_dir($skin_dir)) {  //文件
              $res=unlink($skin_dir);
		}else{  //目录
              $res=deldir($skin_dir);
		}
		if($res){
            $info['url'] = site_url('skin').'?ac='.$ac.'&op='.$op.'&v='.rand(1000,9999);
            $info['msg'] = L('plub_46');
            $info['turn'] = 1;
            getjson($info,0);
		}else{
            getjson(L('plub_28'));
		}
	}

    //模板压缩包上传
	public function upload(){
        $ac = $this->input->get('ac',true);
        $op = $this->input->get('op',true);
        $do = $this->input->get('do',true);
        $dir = str_replace("..","",$this->input->get('dirs',true));
		if($ac!='mobile') $ac='pc';
		if($op!='home' && $op!='user') $op='skins';

		if($do=='add'){
             $config['upload_path'] = FCPATH.'attachment/other/';
             $config['allowed_types'] = 'zip';
             $config['encrypt_name'] = TRUE;  //重命名
			 $this->load->library('upload', $config);
             if ( ! $this->upload->do_upload()){
                $error = array('error' => $this->upload->display_errors('<b>','</b>'));
				getjson(L('plub_29').$error['error']);
            } else{
                $data = $this->upload->data();
				$filename=$data['file_name'];
				$skin_dir = VIEWPATH.$ac.FGF.$op.FGF;

				//解压模板
                $this->load->library('cszip');
                $this->cszip->PclZip($config['upload_path'].$filename);
				if ($this->cszip->extract(PCLZIP_OPT_PATH, $skin_dir, PCLZIP_OPT_REPLACE_NEWER) == 0) {
				    //删除压缩包
					@unlink($config['upload_path'].$filename);
                    getjson(L('plub_30'));
				}else{
				    //删除压缩包
					@unlink($config['upload_path'].$filename);
                    $info['url'] = site_url('skin')."?ac=".$ac."&op=".$op."&v=".rand(1000,9999);
                    $info['msg'] = L('plub_31');
                    getjson($info,0);
				}
			}
		}else{
			 $data['savelink']=site_url('skin/upload')."?ac=".$ac."&op=".$op."&do=add";
             $this->load->view('skin_upload.html',$data);
		}
	}

    //云平台
	public function yun(){
		header("Location: ".$this->csapp->url('skins')."");
	}

    //下载模板
	public function down(){
        $mid=(int)$this->input->get_post('mid');
        $ac=$this->input->get_post('ac',true);
        $op=$this->input->get_post('op',true);
        $dir=$this->input->get_post('dir',true);
        $key=$this->input->get_post('key');
        if(empty($key) || empty($dir) || empty($mid)){
			admin_msg('<font color=red>'.L('plub_27').'</font>',site_url('skin/yun'),'no');
		}
		if($ac!='mobile') $ac='pc';
		if($op!='home' && $op!='user') $op='skins';
        $skins_path = VIEWPATH.$ac.FGF.$op.FGF;

        $data['key']=$key;
        $zip=$this->csapp->url('skins/down/'.$mid,$data);
        $zippath = FCPATH."attachment/other/skins_".$dir.".zip";
        $files_file=$this->csapp->down($zip,$zippath);
	    if($files_file=='-1') admin_msg(L('plub_32'),site_url('skin/yun'),'no');
	    if($files_file=='-2') admin_msg(L('plub_33'),site_url('skin/yun'),'no');
	    if($files_file=='-3') admin_msg(L('plub_34'),site_url('skin/yun'),'no');
	    if($files_file=='10001') admin_msg(L('plub_35'),site_url('skin/yun'),'no');
	    if($files_file=='10002') admin_msg(L('plub_36'),site_url('skin/yun'),'no');
        if(filesize($zippath) == 0) admin_msg(L('plub_35'),site_url('skin/yun'),'no');

	    //解压缩
        $this->load->library('cszip');
	    $this->cszip->PclZip($zippath);
	    if ($this->cszip->extract(PCLZIP_OPT_PATH, $skins_path, PCLZIP_OPT_REPLACE_NEWER) == 0) {
        	@unlink($zippath);
            admin_msg(L('plub_37'),site_url('skin/yun'),'no');
	    }else{
			if(!file_exists($skins_path.$dir.'/config.php')) {
			    @unlink($zippath);
			    admin_msg(L('plub_38'),site_url('skin/yun'),'no');
			}
			@unlink($zippath);
			admin_msg(L('plub_39'),site_url('skin').'?ac='.$ac.'&op='.$op);
		}
	}

    //标签向导
	public function tags(){      
	    $data['ac']='';
        $data['dir']=$this->input->get('dir',true);
		$this->load->view('skin_tags_t.html',$data);
	}

    //生成标签代码
	public function tags_save(){      
        $data['mx']=$this->input->get_post('mx');
        $data['loop']=$this->input->get_post('loop');
        $data['order']=$this->input->get_post('order');
		if(empty($data['loop'])) $data['loop']='20';

        //获得当前模型字段信息
	    $query = $this->db->query("SHOW FULL FIELDS FROM ".CS_SqlPrefix.$data['mx']."");
	    $data['ziduan'] = $query->result_array();
		$this->load->view('skin_tags_s.html',$data);
	}

	//在线升级
	public function update()
	{
        $ac=$this->input->get_post('ac');
        $op=$this->input->get_post('op');
        $dir=$this->input->get_post('dir');
        $mid=(int)$this->input->get_post('mid');
        $key=$this->input->get_post('key');
		if($ac!='mobile') $ac='pc';
		if($op!='home' && $op!='user') $op='skins';
        $skins_path = VIEWPATH.$ac.FGF.$op.FGF;

        if (empty($key) || empty($dir)){

            $data['mid']=$mid;
            header("Location: ".$this->csapp->url('skins/update',$data)."");

		}else{  //下载

			$data['key']=$key;
			$zip=$this->csapp->url('skins/update/'.$mid,$data);
			$zippath=FCPATH."attachment/other/skins_".$dir."_update.zip";
			$files_file=$this->csapp->down($zip,$zippath);
			if($files_file=='-1') admin_msg(L('plub_36'),site_url('skin/yun'),'no');
			if($files_file=='-2') admin_msg(L('plub_33'),site_url('skin/yun'),'no');
			if($files_file=='-3') admin_msg(L('plub_34'),site_url('skin/yun'),'no');
			if($files_file=='10001') admin_msg(L('plub_40'),site_url('skin/yun'),'no');
			if($files_file=='10002') admin_msg(L('plub_41'),site_url('skin/yun'),'no');
			if($files_file=='10003') admin_msg(L('plub_42'),site_url('skin/yun'),'no');
			if(filesize($zippath) == 0) admin_msg(L('plub_43'),site_url('skin/yun'),'no');


			//先备份原始板块
			$this->load->library('cszip');
			$zip_path=FCPATH."attachment/other/skins_".$dir."_backup_".date('Ymd').".zip";
			$plub_path = $skins_path.$dir; 
			$this->cszip->PclZip($zip_path); //创建压缩包
			$this->cszip->create($plub_path); //增加目录
			//解压缩
			$this->cszip->PclZip($zippath);
			//尝试解压覆盖
			if ($this->cszip->extract(PCLZIP_OPT_PATH, $plub_path, PCLZIP_OPT_REPLACE_NEWER) == 0) {
			   die(vsprintf(L('plub_44'),array($plub_path)).$zippath);
			}else{
			   @unlink($zippath);
			   admin_msg(L('plub_45'),site_url('skin').'?ac='.$ac.'&op='.$op);
			}
		}
	}
}

