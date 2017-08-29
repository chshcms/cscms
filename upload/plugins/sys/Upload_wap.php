<?php if ( ! defined('CSCMS')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-08
 */
class Upload_wap extends Cscms_Controller {
	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	    $this->load->model('Csuser');
        $this->Csuser->User_Login();
		$this->load->helper('string');
	}
	//上传附件
	public function index(){
        if(!$this->Csuser->User_Login(1)){
            exit('No Login');
		}
		//检测会员组上传附件权限
		$zuid=getzd('user','zid',$_SESSION['cscms__id']);
		$rowu=$this->Csdb->get_row('userzu','fid',$zuid);
		if($rowu->fid==0){
            exit(L('up_01'));
		}
	    $nums=intval($this->input->get('nums')); //支持数量
	    $types=$this->input->get('type',true);  //支持格式
        $data['tsid']=$this->input->get('tsid',true); //返回提示ID
        $data['sid']=intval($this->input->get('sid')); //返回输入框方法，0替换、1换行增加
		$data['cid'] = (int)$this->input->get('cid',true);//相册id
        $data['dir']=$this->input->get('dir',true);   //上传目录
        $data['fid']=$this->input->get('fid',true);   //返回ID，一个页面多个返回可以用到
        $data['upsave'] = site_url('upload_wap/up_save');
        $data['size'] = UP_Size;
		$data['types'] = (empty($types)||$types=='undefined')?"*.jpg;*.bmp;*.jpeg;*.png;*.gif;":$types;
        $data['nums']=($nums==0)?1:$nums;
		if($data['fid']=='undefined') $data['fid']='';
		if($data['tsid']=='undefined') $data['tsid']='';
		if($data['types']=='undefined') $data['types']='*';
		if($data['dir']=='undefined') $data['dir']='other';
		$str['fid']=$rowu->fid;
		$str['id']=$_SESSION['cscms__id'];
		$str['login']=$_SESSION['cscms__login'];
        $data['key'] = sys_auth(addslashes(serialize($str)),'E');
        $this->load->get_templates('common');
        $this->load->view('upload.html',$data);
	}

    //保存附件
	public function up_save(){
        $key=$this->input->post('key',true);
        if(!$this->Csuser->User_Login(1,$key)){
             getjson('未登录');
		}
		//检测会员组上传附件权限
		$key   = unserialize(stripslashes(sys_auth($key,'D')));
        $fid   = isset($key['fid'])?intval($key['fid']):0;
		if($fid==0){
             getjson('当前所在会员组，没有上传权限!');
		}
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
		$tempFile = $_FILES['Filedata']['tmp_name'];
		$file_name = $_FILES['Filedata']['name'];
		$file_size = filesize($tempFile);
        $file_ext = strtolower(trim(substr(strrchr($file_name, '.'), 1)));
        $file_type = $_FILES['Filedata']['type'];

        //判断文件MIME类型
        $mimes = get_mimes();
		if(!is_array($mimes[$file_ext])) $mimes[$file_ext] = array($mimes[$file_ext]);
        if(isset($mimes[$file_ext]) && $file_type !== false && !in_array($file_type,$mimes[$file_ext],true)){
        	getjson(L('up_02'));
        }

        //检查扩展名
		$ext_arr = explode("|", UP_Type);
        if(!in_array($file_ext,$ext_arr,true)){
            getjson(L('up_02'));
		}elseif(in_array($file_ext, array('gif', 'jpg', 'jpeg', 'jpe', 'png'), TRUE) && @getimagesize($tempFile) === FALSE){
            getjson(L('up_03'));
		}

        //PHP上传失败
        if (!empty($_FILES['Filedata']['error'])) {
            switch($_FILES['Filedata']['error']){
	            case '1':$error = L('up_04');break;
	            case '2':$error = L('up_05');break;
	            case '3':$error = L('up_06');break;
	            case '4':$error = L('up_07');break;
	            case '6':$error = L('up_08');break;
	            case '7':$error = L('up_09');break;
	            case '8':$error = 'File upload stopped by extension。';break;
	            case '999':default:$error = L('up_10');
            }
            getjson($error);
        }
        //新文件名
		$file_name=random_string('alnum', 20). '.' . $file_ext;
		$file_path=$path.$file_name;
		if (move_uploaded_file($tempFile, $file_path) !== false) { //上传成功
            $filepath=(UP_Mode==1)?'/'.date('Ym').'/'.date('d').'/'.$file_name : '/'.date('Ymd').'/'.$file_name;
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
				$arr['msg'] = 'ok';
				$arr['url'] = $filepath;
			    getjson($arr,0,1);
			}else{
				@unlink($file_path);
                getjson(L('up_12'));
			}
		}else{ //上传失败
			getjson(L('up_12'));
		}
	}
}
