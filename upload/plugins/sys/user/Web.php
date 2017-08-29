<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-03-30
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Web extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	    $this->load->model('Csuser');
		$this->Csuser->User_Login();
		$this->load->helper('string');
		$this->lang->load('user');
	}

    //主页模板
	public function index(){
		//模板
		$tpl='web.html';
		//URL地址
	    $url='web/index';
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title=L('web_01');
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];
        $this->Cstpl->user_list($row,$url,1,$tpl,$title,'id','',$ids);
	}

    //背景图片
	public function pic(){
		//模板
		$tpl='web-pic.html';
		//URL地址
	    $url='web/pic';
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title=L('web_02');
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];

		$zdy['[user:bgpicsave]'] = spacelink('web,picsave');
        $this->Cstpl->user_list($row,$url,1,$tpl,$title,'id','',$ids,false,'user',$zdy);
	}

    //保存背景图片
	public function picsave(){
	   $filename=$this->upload('bgpic');
	   $bgpic=getzd('user','bgpic',$_SESSION['cscms__id']);
	   //删除原来的图片
	   $this->load->library('csup');
	   $this->csup->del($bgpic,'bgpic'); //删除附件

	   $edit['bgpic']=date('Ym').'/'.date('d').'/'.$filename;
	   $this->Csdb->get_update('user',$_SESSION['cscms__id'],$edit);

	   $pic=piclink('bgpic',$edit['bgpic']).'?size=720*186';

        echo '<script type="text/javascript" src="'.Web_Path.'packs/js/jquery.min.js"></script><script type="text/javascript" src="'.Web_Path.'packs/js/cscms.js"></script><script type="text/javascript">
				  parent.$(".file_working").hide();
				  parent.$(".banner_clip").show();
				  parent.$(".banner_clip").css("background","url('.$pic.')");
			</script>';
	}

	//图片上传
    public function upload($ac) {  

		$FILES=$_FILES[$ac];
		//文件保存目录路径
		$save_path = FCPATH.'/attachment/bgpic/';
		//定义允许上传的文件扩展名
		$ext_arr = array('gif', 'jpg', 'png');
		//最大文件大小
		$max_size = 2*1024*1024;
		//PHP上传失败
		if (!empty($FILES['error'])) {
		    switch($FILES['error']){
		        case '1':
		            $error = L('web_05');
		            break;
		        case '2':
		            $error = L('web_06');
		            break;
		        case '3':
		            $error = L('web_07');
		            break;
		        case '4':
		            $error = L('web_08');
		            break;
		        case '6':
		            $error = L('web_09');
		            break;
		        case '7':
		            $error = L('web_10');
		            break;
		        case '8':
		            $error = 'File upload stopped by extension。';
		            break;
		        case '999':
		        default:
		            $error = L('web_11');
		    }
		    $this->alert($error);
		}
		//有上传文件时
		if (!empty($_FILES) === false) {
		    $this->alert(L('web_12'));
		}
		//原文件名
		$file_name = $FILES['name'];
		//服务器上临时文件名
		$tmp_name = $FILES['tmp_name'];
		//文件大小
		$file_size = $FILES['size'];
		//检查文件名
		if (!$file_name) {
		   $this->alert(L('web_13'));
		}
		//检查是否已上传
		if (@is_uploaded_file($tmp_name) === false) {
		     $this->alert(L('web_16'));
		}
		//检查文件大小
		if ($file_size > $max_size) {
		     $this->alert(L('web_17'));
		}
		//获得文件扩展名
		$file_ext = strtolower(trim(substr(strrchr($file_name, '.'), 1)));
		//检查扩展名
		if (in_array($file_ext, $ext_arr) === false) {
		     $this->alert(L('web_18',array(implode(",", $ext_arr))));
		}
		//创建目录
		$save_path .= date("Ym")."/".date("d")."/";
		mkdirss($save_path);
		//新文件名
		$this->load->helper('string');
		$new_file_name = random_string('alnum',10) . '.' . $file_ext;

		//检查图片文件是否正确
		$aa=getimagesize($tmp_name);
		$weight=$aa["0"];////获取图片的宽
		$height=$aa["1"];///获取图片的高
		if(intval($weight)<1 || intval($height)<1){
		    @unlink($tmp_name);
		    $this->alert(L('web_19'));
		}
		//移动文件
		$file_path = $save_path . $new_file_name;
		if(move_uploaded_file($tmp_name, $file_path) === false) {
		    $this->alert(L('web_20'));
		}
		@chmod($file_path, 0644);

		$filepath=(UP_Mode==1)?'/'.date('Ym').'/'.date('d').'/'.$file_name : '/'.date('Ymd').'/'.$file_name;

		//判断上传方式
		$this->load->library('csup');
		$res=$this->csup->up($file_path,$new_file_name);
		if($res){
			return $new_file_name;
		}else{
			@unlink($file_path);
			$this->alert(L('web_20'));
		}
	}

    public function alert($msg) { 
		echo '<script type="text/javascript">
			  parent.do_alert("'.$msg.'");
			  parent.$(".file_working").hide();
			  parent.$(".banner_clip").show();
			  </script>';
		exit();
	}
}
