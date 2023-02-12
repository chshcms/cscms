<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-09-20
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Upload extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
		$this->load->helper('string');
		$this->lang->load('admin_upload');
	}
	//网站附件管理
	public function index(){
        $this->Csadmin->Admin_Login();
	    $this->load->helper('file');
        $path = $this->input->get('path',true);
        $page = $this->input->get('page',true);
        if(empty($page)) $page=1;
        if(empty($path)){
			$path=Web_Path."attachment/";
		    if(UP_Pan!=''){
		        $path = UP_Pan.$path;
			}
		}
		if(substr($path,0,1)!='/' && UP_Pan=='') $path="/".$path;
        if(substr(str_replace(array(UP_Pan,Web_Path),array("",""),$path),0,10)!='attachment'){
            admin_msg(L('plub_01'),'javascript:history.back();','no');
        }
		$path = str_replace('..','',$path);
		$path = str_replace("//","/",$path);
        $paths=(UP_Pan!='')?$path:str_replace(Web_Path."attachment/",FCPATH."attachment/",$path);
        $showarr=get_dir_file_info($paths, $top_level_only = TRUE);
        $dirs=$list=array();
	    if ($showarr) {
		    foreach ($showarr as $t) {
			    if (is_dir($t['server_path'])) {
				    $dirs[] = array(
					    'name' => $t['name'],
					    'date' => date('Y-m-d H:i:s',$t['date']),
					    'icon' => Web_Path.'packs/admin/images/ext/dir.gif',
					    'link' => site_url('upload')."?path=".$path.$t['name']."/",
					    'dellink' => site_url('upload/del')."?path=".$path.$t['name']."/",
				    );
			    } else {
					$exts = trim(strrchr($t['name'], '.'), '.');
                    if(UP_Pan!=''){
                         $link=UP_Url.str_replace(UP_Pan,"",$path.$t['name']);
					}else{
                         $link=is_ssl().Web_Url.$path.$t['name'];
					}
					$list[] = array(
						'name' => $t['name'],
						'ext' => get_extpic($exts),
					    'date' => date('Y-m-d H:i:s',$t['date']),
					    'size' => formatsize($t['size']),
						'icon' => Web_Path.'packs/admin/images/ext/'.get_extpic($exts).'.gif',
						'link' => $link,
					    'dellink' => site_url('upload/del')."?path=".$path.$t['name'],
					);
			    }
		    }
	    }
        $data['path']=$path;
		$data['dirs']=$dirs;
		$data['show']=$list;

        if(str_replace(array(UP_Pan,Web_Path),array("",""),$path)=="attachment"){
            $data['uppage']="###";
        }else{
            $data['uppage']="javascript:history.back();";
        }
        $this->load->view('upload_dir.html',$data);
	}

	//删除附件
	public function del(){
        $this->Csadmin->Admin_Login();
	    $path = $this->input->get('path',true);
		$path = str_replace('..','',$path);
		if(empty($path)){
			getjson(L('plub_01'));
		}
		if(Web_Path=='/'){
             if(substr($path,0,12)!='/attachment/'){
             	getjson(L('plub_02'));
             }
		}else{
             $paths = str_replace(Web_Path,'',$path);
			 if(substr($paths,0,11)!='attachment/'){
			 	getjson(L('plub_02'));
			 }
		}
		$path=FCPATH.$path;
        if (is_dir($path)) {
             deldir($path);
		}else{
			 @unlink($path);
		}
        getjson(L('plub_03'),0);
	}

    //上传附件
	public function up(){
        $this->Csadmin->Admin_Login();
	    $nums=intval($this->input->get('nums')); //支持数量
	    $types=$this->input->get('type',true);  //支持格式
        $fhid = $this->input->get('fhid',true); //返回ID参数
        $dir = $this->input->get('dir',true);   //上传目录
        $data['fhid']=(empty($fhid))?"pic":$fhid;
        $data['sid']=intval($this->input->get('sid')); //返回输入框方法，0替换、1换行增加
        $data['fid']=$this->input->get('fid',true);   //返回ID，一个页面多个返回可以用到
        $data['upsave']=site_url('upload/up_save');
        $data['size'] = UP_Size.'kb';
        $data['types'] =(empty($types))?"gif,png,jpg,jpeg":str_replace(array(';*.',';','*.'),array(',','',''),$types);
        $data['nums']=($nums==0)?1:$nums;
		if($data['fid']==='undefined') $data['fid']='';
		$str['id']=$_SESSION['admin_id'];
		$str['name']=$_SESSION['admin_name'];
		$str['pass']=$_SESSION['admin_pass'];
        $key = sys_auth(addslashes(serialize($str)),'E');
        $params = array();
		$this->load->library('csup');
        if(UP_Mode == 3){ //七牛
	        $token = $this->csup->qiniu_uptoken();
	        $params['token'] = $token;
	        $data['dir'] = date('Ymd').'/';
	        $data['upsave'] = is_ssl().'upload.qiniu.com/';
        }elseif(UP_Mode == 4){ //阿里云OSS
	        $params = $this->csup->osssign();
	        $data['dir'] = date('Ymd').'/';
	        $data['upsave'] = $params['host'];
        }else{ //本地
	        $data['dir'] = $dir;
	        $params['dir'] = $dir;
	        $params['upkey'] = $key;
        }
        $data['params'] = json_encode($params);
		$data['fhhost'] = '';
		if(UP_Mode>1 && ($dir=='music' || $dir=='video')){
			$fhhost = $this->csup->down(UP_Mode);
			if(substr($fhhost,-1) != '/') $fhhost .= '/';
			$data['fhhost'] = $fhhost;
		}
        $this->load->view('upload.html',$data);
	}

    //保存附件
	public function up_save(){
        $key=$this->input->post('upkey',true);
        $this->Csadmin->Admin_Login($key);
        $dir=$this->input->post('dir',true);
		if(empty($dir) || !preg_match('/^[0-9a-zA-Z\_]*$/', $dir)) {  
            $dir='other';
		}
		//上传目录
		if(UP_Mode==1 && UP_Pan!=''){
		    $path = UP_Pan.'/attachment/'.$dir.'/'.date('Ym').'/'.date('d').'/';
			$path = str_replace("//","/",$path);
		}else{
		    $path = FCPATH.'attachment/'.$dir.'/'.date('Ym').'/'.date('d').'/';
		}
		if (!is_dir($path)) {
            mkdirss($path);
        }
		$tempFile = $_FILES['file']['tmp_name'];
		$file_name = $_FILES['file']['name'];
		$file_size = filesize($tempFile);
        $file_ext = strtolower(trim(substr(strrchr($file_name, '.'), 1)));
        $file_type = $_FILES['file']['type'];

        //判断文件MIME类型
        if($file_type != 'application/octet-stream'){
			$mimes = get_mimes();
			if(!is_array($mimes[$file_ext])) $mimes[$file_ext] = array($mimes[$file_ext]);
			if(isset($mimes[$file_ext]) && $file_type !== false && !in_array($file_type,$mimes[$file_ext],true)){
				getjson(L('plub_04'),1,1);
			}
		}

        //检查扩展名
		$ext_arr = explode("|", UP_Type);
        if(!in_array($file_ext,$ext_arr,true)){
            getjson(L('plub_04'),1,1);
		}elseif(in_array($file_ext, array('gif', 'jpg', 'jpeg', 'jpe', 'png'), TRUE) && @getimagesize($tempFile) === FALSE){
            getjson(L('plub_05'),1,1);
		}
        //PHP上传失败
        if (!empty($_FILES['file']['error'])) {
            switch($_FILES['file']['error']){
	            case '1':$error = L('plub_06');break;
	            case '2':$error = L('plub_07');break;
	            case '3':$error = L('plub_08');break;
	            case '4':$error = L('plub_09');break;
	            case '6':$error = L('plub_10');break;
	            case '7':$error = L('plub_11');break;
	            case '8':$error = 'File upload stopped by extension。';break;
	            case '999':default:$error = L('plub_12');
            }
            getjson($error,1,1);
        }
        //新文件名
		$file_name=random_string('alnum', 20). '.' . $file_ext;
		$file_path=$path.$file_name;
		if (move_uploaded_file($tempFile, $file_path) !== false) { //上传成功

                $filepath=(UP_Mode==1)?'/'.date('Ym').'/'.date('d').'/'.$file_name : '/'.date('Ymd').'/'.$file_name;

                //判断水印
                if($dir!='links' && CS_WaterMark==1){
					if($file_ext=='jpg' || $file_ext=='png' || $file_ext=='gif' || $file_ext=='bmp' || $file_ext=='jpge'){
	                     $this->load->library('watermark');
                         $this->watermark->imagewatermark($file_path);
					}
                }

				//判断上传方式
                $this->load->library('csup');
				$res=$this->csup->up($file_path,$file_name);
				if($res){
					if($dir=='music' || $dir=='video'){
						if(UP_Mode==1){
					    	$filepath = 'attachment/'.$dir.$filepath;
						}else{
							$filepath = annexlink($filepath);
						}
					}
					getjson(array('msg'=>'ok','fileurl'=>$filepath),1,1);
				}else{
					@unlink($file_path);
                    getjson('no',1,1);
				}

		}else{ //上传失败
			  getjson('no',1,1);
		}
	}

	//网站附件
	public function myattach(){
	        $this->Csadmin->Admin_Login();
		    $this->load->helper('directory');
 	        $path = $this->input->get('path',true);
 	        $ext = $this->input->get('ext',true);

			$path = str_replace('..','',$path);
			$ext = str_replace('..','',$ext);

            if(empty($ext)) $ext=UP_Type;

            if(empty($path)){
				$path=Web_Path."attachment/";
			    if(UP_Pan!=''){
			        $path = UP_Pan.$path;
				    $path = str_replace("//","/",$path);
				}
			}
			if(substr($path,0,1)!='/' && UP_Pan=='') $path="/".$path;
            if(substr(str_replace(array(UP_Pan,Web_Path),array("",""),$path),0,10)!='attachment'){
                    admin_msg(L('plub_01'),'javascript:history.back();','no');
            }
            $paths=(UP_Pan!='')?$path:str_replace(Web_Path."attachment/",FCPATH."attachment/",$path);

			$dirs = $list = array();
			$ext2 = explode('|', $ext);
			$path=str_replace('//','/',$path);
            $arrs=directory_map($paths, 1);
		    if ($arrs) {
			    foreach ($arrs as $t) {
				    if (is_dir($paths.$t)) {
					    $name = trim($t, DIRECTORY_SEPARATOR);
					    $dirs[] = array(
						    'name' => $name,
						    'icon' => Web_Path.'packs/admin/images/ext/dir.gif',
						    'link' => site_url('upload/myattach')."?ext=".$ext."&path=".$path.$name."/",
					    );
				    } else {
					    $exts = trim(strrchr($t, '.'), '.');
					    if (($ext=='*' || in_array($exts, $ext2)) && $exts != 'php' && $exts != 'html') {
                            if(UP_Pan!=''){
                                 $link=UP_Url.str_replace(UP_Pan,"",$path.$t);
						    }else{
                                 $link=is_ssl().Web_Url.$path.$t;
						    }
						    $list[] = array(
							    'name' => $t,
							    'ext'  => get_extpic($exts),
							    'icon' => Web_Path.'packs/admin/images/ext/'.get_extpic($exts).'.gif',
							    'link' => $link,
						    );
					    }
				    }
			    }
		    }
            $data['path']=$path;
            $data['ext']=$ext;
            $data['url']=site_url('upload/myattach')."?ext=".$ext."&path=".$path;
            $data['dirs']=$dirs;
            $data['show']=$list;

            if(str_replace(array(UP_Pan,Web_Path),array("",""),$path)=="attachment"){
                $data['uppage']=site_url('upload/myattach');
            }else{
                $data['uppage']="javascript:history.back();";
            }
            $this->load->view('myattach.html',$data);
	}

	//编辑器上传保存，返回json数据
	public function up_save_json(){
        $this->Csadmin->Admin_Login();
        $dir = $this->input->get('dir',true);
		if(empty($dir) || !preg_match('/^[0-9a-zA-Z\_]*$/', $dir)) {  
            $dir = 'other';
		}
		//上传目录
		if(UP_Mode==1 && UP_Pan!=''){
		    $path = UP_Pan.'/attachment/'.$dir.'/'.date('Ym').'/'.date('d').'/';
			$path = str_replace("//","/",$path);
		}else{
		    $path = FCPATH.'attachment/'.$dir.'/'.date('Ym').'/'.date('d').'/';
		}
		if (!is_dir($path)) {
            mkdirss($path);
        }
        if(!empty($_FILES['Filedata'])){
        	$_file = $_FILES['Filedata'];
        }elseif (!empty($_FILES['file'])) {
        	$_file = $_FILES['file'];
        }else{
        	if($dir=='lrc'){
            	$data['code'] = 1;
            	$data['msg'] = L('plub_14');
            	$data['data'] = array();
            	echo json_encode($data);exit;
            }else{
            	getjson(L('plub_14'));
            }
        }
		$tempFile = $_file['tmp_name'];
		$file_name = $_file['name'];
		$file_size = filesize($tempFile);
        $file_ext = strtolower(trim(substr(strrchr($file_name, '.'), 1)));
		$file_type = $_file['type'];

        //判断文件MIME类型
        $mimes = get_mimes();
		if(!is_array($mimes[$file_ext])) $mimes[$file_ext] = array($mimes[$file_ext]);
        if(isset($mimes[$file_ext]) && $file_type !== false && !in_array($file_type,$mimes[$file_ext],true)){
            if($dir=='lrc'){
            	$data['code'] = 1;
            	$data['msg'] = L('plub_15');
            	$data['data'] = array();
            	echo json_encode($data);exit;
            }else{
            	getjson(L('plub_15'));
            }
        }
        //检查扩展名
		$ext_arr = explode("|", UP_Type);
        if(!in_array($file_ext,$ext_arr,true)){
            if($dir=='lrc'){
            	$data['code'] = 1;
            	$data['msg'] = L('plub_15');
            	$data['data'] = array();
            	echo json_encode($data);exit;
            }else{
            	getjson(L('plub_15'));
            }
		}elseif(in_array($file_ext, array('gif', 'jpg', 'jpeg', 'jpe', 'png'), TRUE) && @getimagesize($tempFile) === FALSE){
            if($dir=='lrc'){
            	$data['code'] = 1;
            	$data['msg'] = L('plub_15');
            	$data['data'] = array();
            	echo json_encode($data);exit;
            }else{
            	getjson( L('plub_16'));
            }
		}
        //PHP上传失败
        if (!empty($_file['error'])) {
            switch($_file['error']){
	            case '1':$error = L('plub_06');break;
	            case '2':$error = L('plub_07');break;
	            case '3':$error = L('plub_08');break;
	            case '4':$error = L('plub_09');break;
	            case '6':$error = L('plub_10');break;
	            case '7':$error = L('plub_11');break;
	            case '8':$error = 'File upload stopped by extension。';break;
	            case '999':default:$error = L('plub_12');
            }
            if($dir=='lrc'){
            	$data['code'] = 1;
            	$data['msg'] = $error;
            	$data['data'] = array();
            	echo json_encode($data);exit;
            }else{
            	getjson($error);
            }

        }
        //新文件名
		$file_name=random_string('alnum', 20). '.' . $file_ext;
		$file_path=$path.$file_name;
		if (move_uploaded_file($tempFile, $file_path) !== false) { //上传成功

                $filepath=(UP_Mode==1)?'/'.date('Ym').'/'.date('d').'/'.$file_name : '/'.date('Ymd').'/'.$file_name;

                //判断水印
                if($dir!='links' && CS_WaterMark==1){
					if($file_ext=='jpg' || $file_ext=='png' || $file_ext=='gif' || $file_ext=='bmp' || $file_ext=='jpeg'){
	                     	$this->load->library('watermark');
                         	$this->watermark->imagewatermark($file_path);
					}
                }
				//判断上传方式
                $this->load->library('csup');
				$res=$this->csup->up($file_path,$file_name);
				if($res){
					if($dir=='lrc' || $dir=='music' || $dir=='video'){
						if(UP_Mode==1){
							if($dir=='lrc'){
					    		$filepath = is_ssl().Web_Url.Web_Path.'attachment/'.$dir.$filepath;
							}else{
					    		$filepath = 'attachment/'.$dir.$filepath;
							}
						}else{
							$filepath = annexlink($filepath);
						}
					}
				    if($dir=='lrc'){
		            	$data['code'] = 0;
		            	$data['msg'] = L('plub_24');
		            	$data['data'] = array(
		            		'src'=> $filepath
		            	);
		            	echo json_encode($data);exit;
		            }else{
		            	$info['url'] = $filepath;
				    	getjson($info,0);
		            }
				}else{
					@unlink($file_path);
                    getjson(L('plub_14'));
                    if($dir=='lrc'){
		            	$data['code'] = 1;
		            	$data['msg'] = L('plub_14');
		            	$data['data'] = array();
		            	echo json_encode($data);exit;
		            }else{
		            	getjson(L('plub_14'));
		            }
				}

		}else{ //上传失败
			if($dir=='lrc'){
            	$data['code'] = 1;
            	$data['msg'] = L('plub_14');
            	$data['data'] = array();
            	echo json_encode($data);exit;
            }else{
            	getjson(L('plub_14'));
            }
		}
	}

	//采集导入项目，返回json数据
	function caiji(){
		$path = FCPATH.'attachment/other/';
		if (!is_dir($path)) {
            mkdirss($path);
        }
		$tempFile = $_FILES['userfile']['tmp_name'];
		$file_name = $_FILES['userfile']['name'];
		$file_size = filesize($tempFile);
        $file_ext = strtolower(trim(substr(strrchr($file_name, '.'), 1)));

        //检查扩展名
        if ($file_ext != 'txt') {
            getjson(L('plub_15'));
		}
        //PHP上传失败
        if (!empty($_FILES['userfile']['error'])) {
            switch($_FILES['userfile']['error']){
	            case '1':$error = L('plub_06');break;
	            case '2':$error = L('plub_07');break;
	            case '3':$error = L('plub_08');break;
	            case '4':$error = L('plub_09');break;
	            case '6':$error = L('plub_10');break;
	            case '7':$error = L('plub_11');break;
	            case '8':$error = 'File upload stopped by extension。';break;
	            case '999':default:$error = L('plub_12');
            }
            getjson($error);
        }
		$file_name=random_string('alnum', 20). '.' . $file_ext;
		$file_path=$path.$file_name;
		if (move_uploaded_file($tempFile, $file_path) !== false){
			$info['name'] = $file_name;
			getjson($info,0);
		}else{
			getjson(L('plub_25'));
		}
	}
}