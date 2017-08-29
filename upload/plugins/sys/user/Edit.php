<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-03-07
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Edit extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	    $this->load->model('Csuser');
		$this->lang->load('user');
		$this->Csuser->User_Login();
		$this->load->helper('string');
	}

    //资料
	public function index(){
		//模板
		$tpl='edit.html';
		//URL地址
	    $url='edit/index';
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title=L('edit_01');
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];

		$zdy['[user:token]'] = get_token();
		$zdy['[user:editsave]'] = spacelink('edit,save');

        $this->Cstpl->user_list($row,$url,1,$tpl,$title,'id','',$ids,false,'user',$zdy);
	}

    //资料修改
	public function save(){
		$token=$this->input->post('token', TRUE);
		if(!get_token('token',1,$token)) msg_url(L('edit_02'),'javascript:history.back();');

		$userinfo['nichen']=$this->input->post('usernichen', TRUE, TRUE);
		$userinfo['email']=$this->input->post('useremail', TRUE, TRUE);
		$userinfo['tel']=$this->input->post('usertel', TRUE, TRUE);
		$userinfo['qq']=$this->input->post('userqq', TRUE, TRUE);
		$userinfo['sex']=intval($this->input->post('usersex'));
		$userinfo['city']=$this->input->post('usercity', TRUE, TRUE);
		$userinfo['qianm']=$this->input->post('userqianm', TRUE);

		if(empty($userinfo['nichen']) || !is_username($userinfo['nichen'],1)) msg_url(L('edit_03'),'javascript:history.back();');
		if(empty($userinfo['email']) || !is_email($userinfo['email'])) msg_url(L('edit_04'),'javascript:history.back();');
		if(!empty($userinfo['tel']) && !is_tel($userinfo['tel'])) msg_url(L('edit_05'),'javascript:history.back();');
		if(!empty($userinfo['qq']) && !is_qq($userinfo['qq'])) msg_url(L('edit_06'),'javascript:history.back();');

        //判断昵称是否注册
		$nichen=$this->db->query("select id from ".CS_SqlPrefix."user where nichen='".$userinfo['nichen']."' and id!=".$_SESSION['cscms__id']."")->row();
		if($nichen){
               msg_url(L('edit_07'),'javascript:history.back();');
		}

        //判断邮箱是否注册
		$email=$this->db->query("select id from ".CS_SqlPrefix."user where email='".$userinfo['email']."' and id!=".$_SESSION['cscms__id']."")->row();
		if($email){
               msg_url(L('edit_08'),'javascript:history.back();');
		}

        //判断手机是否注册
        if(!empty($userinfo['tel'])){
			$tel=$this->db->query("select id from ".CS_SqlPrefix."user where tel='".$userinfo['tel']."' and id!=".$_SESSION['cscms__id']."")->row();
			if($tel){
                   msg_url(L('edit_09'),'javascript:history.back();');
			}
        }

		//修改入库
		$this->Csdb->get_update ('user',$_SESSION['cscms__id'],$userinfo);

		//摧毁token
		get_token('token',2);
        msg_url(L('edit_10'),spacelink('edit'));
	}

    //头像
	public function logo(){
		//模板
		$tpl='edit-logo.html';
		//URL地址
	    $url='edit/logo';
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title=L('edit_11');
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];

        $this->Cstpl->user_list($row,$url,1,$tpl,$title,'id','',$ids);
	}

    //上传头像
	public function logo_save(){
	    $uid=isset($_SESSION['cscms__id'])?intval($_SESSION['cscms__id']):intval($this->cookie->get_cookie('user_id'));
        $tempFile = file_get_contents("php://input");
		$picname  = $uid.".jpg";
		if(UP_Mode==1 && UP_Pan!=''){
		    $dir_pan = UP_Pan.'/';
			$dir_pan = str_replace("//","/",$dir_pan);
		}else{
		    $dir_pan = FCPATH;
		}
	    $picdirs  = date('Ym')."/".date('d')."/".$uid.".jpg";
	    $filename = $dir_pan."attachment/logo/".$picdirs; 
		$filepath = (UP_Mode==1)?'/'.date('Ym').'/'.date('d').'/'.$picname : '/'.date('Ymd').'/'.$picname;
	    if (!empty($tempFile) && $uid>0) {

			//创建当前文件件
			$dir=$dir_pan."attachment/logo/".date('Ym')."/".date('d');
			mkdirss($dir);

			if($handle=fopen($filename,"w+")) {   
				if(!fwrite($handle,$tempFile)==FALSE){   
					fclose($handle);
				}
			} 

			list($width, $height, $type, $attr) = getimagesize($filename);
			if ( intval($width) < 10 || intval($height) < 10 || $type == 4 ) {
				@unlink($filename);
			    exit('UploadPicError');
			}

			//判断水印
			if(CS_WaterMark==1){
			    $this->load->library('watermark');
			    $this->watermark->imagewatermark($filename);
			}

			//判断上传方式
			$this->load->library('csup');
			$res=$this->csup->up($filename,$picname);
			if(!$res){
				@unlink($filename);
			    exit('UploadPicError');
			}

			//删除原来的图片
			$pic=getzd('user','logo',$uid);
			if($pic!=$filepath){
			   $this->csup->del($pic,'logo');
			}

			//写入数据库
			$this->db->query("update ".CS_SqlPrefix."user set logo='".$filepath."' where id=".$uid."");

			exit('UploadPicSucceed');
                           
		} else {
		    exit('UploadPicError');
		}
	}

    //密码
	public function pass(){
		//模板
		$tpl='edit-pass.html';
		//URL地址
	    $url='edit/pass';
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title=L('edit_12');
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];

		$zdy['[user:token]'] = get_token();
		$zdy['[user:passsave]'] = spacelink('edit,pass_save');

        $this->Cstpl->user_list($row,$url,1,$tpl,$title,'id','',$ids,false,'user',$zdy);
	}

    //密码修改
	public function pass_save(){
		$token=$this->input->post('token', TRUE);
		if(!get_token('token',1,$token)) msg_url(L('edit_02'),'javascript:history.back();');

		$pass=$this->input->post('userpass', TRUE, TRUE);
		$pass1=$this->input->post('userpass1', TRUE, TRUE);
		$pass2=$this->input->post('userpass2', TRUE, TRUE);

		if(empty($pass)) msg_url(L('edit_13'),'javascript:history.back();');
		if(empty($pass1) || !is_userpass($pass1)) msg_url(L('edit_14'),'javascript:history.back();');
		if($pass1!=$pass2) msg_url(L('edit_15'),'javascript:history.back();');

        //判断原密码
		$row=$this->db->query("select code,pass from ".CS_SqlPrefix."user where id=".$_SESSION['cscms__id']."")->row();
		if($row->pass != md5(md5($pass).$row->code)){
               msg_url(L('edit_16'),'javascript:history.back();');
		}

		//修改入库
		$userinfo['code']=random_string('alnum',6);
		$userinfo['pass']=md5(md5($pass1).$userinfo['code']);
		$this->Csdb->get_update ('user',$_SESSION['cscms__id'],$userinfo);
		get_token('token',2);

        msg_url(L('edit_17'),spacelink('edit','pass'));
	}

    //绑定登录
	public function open(){
		//模板
		$tpl='edit-open.html';
		//URL地址
	    $url='edit/open';
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title=L('edit_18');
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];

        //判断是否绑定
		$qq=$wb=$bd=$rr=$kx=$db=0;
		$result=$this->db->query("select cid from ".CS_SqlPrefix."useroauth where uid=".$_SESSION['cscms__id']." order by id desc");
        foreach ($result->result() as $rows){
              if($rows->cid==1) $qq=1;
              if($rows->cid==2) $wb=1;
              if($rows->cid==3) $bd=1;
              if($rows->cid==4) $rr=1;
              if($rows->cid==5) $kx=1;
              if($rows->cid==6) $db=1;
		}
		$zdy['[user:qqmode]'] = $qq;
		$zdy['[user:wbmode]'] = $wb;
		$zdy['[user:bdmode]'] = $bd;
		$zdy['[user:rrmode]'] = $rr;
		$zdy['[user:kxmode]'] = $kx;
		$zdy['[user:dbmode]'] = $db;
		//第三方登录模式
		$zdy['[user:appmode]'] = CS_Appmode;

        $Mark_Text=$this->Cstpl->user_list($row,$url,1,$tpl,$title,'id','',$ids,false,'user',$zdy);
	}

	//手机页面上传图像
	public function logo_save_json(){
        $dir = 'logo';
        $uid = $_SESSION['cscms__id'];
        $path = FCPATH.'attachment/'.$dir.'/'.date('Ym').'/'.date('d').'/';
		if (!is_dir($path)) {
            mkdirss($path);
        }
        $_file = $_FILES['Filedata'];
		$tempFile = $_file['tmp_name'];
		$file_name = $_file['name'];
		$file_size = filesize($tempFile);
        $file_ext = strtolower(trim(substr(strrchr($file_name, '.'), 1)));
        //检查扩展名
		if($file_ext=='jpg' || $file_ext=='png' || $file_ext=='gif' || $file_ext=='bmp' || $file_ext=='jpeg'){
			list($width, $height, $type, $attr) = getimagesize($tempFile);
			if ( intval($width) < 10 || intval($height) < 10 || $type == 4 ) {
                getjson('不是合法的图片文件');
			}
		}else{
			getjson('请上传jpg/png/jpeg/bmp格式的图片');
		}
        //PHP上传失败
        if (!empty($_file['error'])) {
            switch($_file['error']){
	            case '1':
		            $error = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值';
		            break;
	            case '2':
		            $error = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
		            break;
	            case '3':
		            $error = '文件只有部分被上传';
		            break;
	            case '4':
		            $error = '没有文件被上传';
		            break;
	            case '6':
		            $error = '找不到上传的临时文件夹';
		            break;
	            case '7':
		            $error = '文件写入失败';
		            break;
	            case '8':
		            $error = 'File upload stopped by extension.';
		            break;
	            case '999':
	            default:
		            $error = '发生异常错误，上传失败';
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
				$this->db->update('user',array('logo'=>$filepath),array('id'=>$uid));
            	$info['url'] = Web_Path.'attachment/'.$dir.'/'.$filepath;
		    	getjson($info,0);
			}else{
				@unlink($file_path);
                getjson('上传失败');
			}

		}else{
            getjson('上传失败');
		}
	}
}
