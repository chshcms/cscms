<?php if ( ! defined('CSCMS')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-08
 */
class Picadd extends Cscms_Controller {
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
		$rowu=$this->Csdb->get_row('userzu','sid,fid',$zuid);
		if($rowu->fid==0){
            exit(L('up_01'));
		}
		$cid = (int)$this->input->get('cid',true);//分类id
		$sid = intval($this->input->get('sid')); //相册ID
		if($cid==0 || $sid==0) exit(' cid or sid error');
		//判断分类
		$rowc = $this->Csdb->get_row('pic_list','id',$cid);
		if(!$rowc) exit('cid error');
		//判断相册
		$rows = $this->Csdb->get_row('pic_type','name,uid',$sid);
		if(!$rows || $rows->uid!=$_SESSION['cscms__id']) exit('sid error');

	    $nums=intval($this->input->get('nums')); //支持数量
	    $types=$this->input->get('type',true);  //支持格式
        $dir = $this->input->get('dir',true);   //上传目录
        $data['tsid']=$this->input->get('tsid',true); //返回提示ID
        $data['fid']=$this->input->get('fid',true);   //返回ID，一个页面多个返回可以用到
        $data['upsave']=site_url('pic/user/picadd/up_save');
        $data['size'] = UP_Size.'kb';
        $data['types'] =(empty($types))?"gif,png,jpg,jpeg":str_replace(array(';*.',';','*.'),array(',','',''),$types);
        $data['nums']=($nums==0)?1:$nums;
		if($data['fid']=='undefined') $data['fid']='';
		if($data['tsid']=='undefined') $data['tsid']='';
		if($data['types']=='undefined') $data['types']='*';
		if($data['dir']=='undefined') $data['dir']='other';
		$str['fid']=$rowu->fid;
		$str['yid']=$rowu->sid;
		$str['cid']=$cid;
		$str['sid']=$sid;
		$str['pname'] = $rows->name;
		$str['id']=$_SESSION['cscms__id'];
		$str['login']=$_SESSION['cscms__login'];
        $key = sys_auth(addslashes(serialize($str)),'E');
        $params = array();
		$params['dir'] = $dir;
		$params['upkey'] = $key;
        $data['params'] = json_encode($params);
        $this->load->view('upload.html',$data);
	}

    //保存附件
	public function up_save(){
        $key=$this->input->post('upkey',true);
        if(!$this->Csuser->User_Login(1,$key)){
            exit('No Login');
		}
		//检测会员组上传附件权限
		$key = unserialize(stripslashes(sys_auth($key,'D')));
        $uid = isset($key['id'])?intval($key['id']):0;
        $fid = isset($key['fid'])?intval($key['fid']):0;
        $yid = isset($key['yid'])?intval($key['yid']):0;
        $cid = isset($key['cid'])?intval($key['cid']):0;
        $sid = isset($key['sid'])?intval($key['sid']):0;
		$pname = isset($key['pname'])?$key['pname']:'未知';
		if($cid==0 || $sid==0) exit('cid or sid error');
		if($fid==0){
             exit('You do not have permission to upload attachments of group members!');
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
		$tempFile = $_FILES['file']['tmp_name'];
		$file_name = $_FILES['file']['name'];
		$file_size = filesize($tempFile);
        $file_ext = strtolower(trim(substr(strrchr($file_name, '.'), 1)));
        $file_type = $_FILES['file']['type'];

        //判断文件MIME类型
        $mimes = get_mimes();
		if(!is_array($mimes[$file_ext])) $mimes[$file_ext] = array($mimes[$file_ext]);
        if(isset($mimes[$file_ext]) && $file_type !== false && !in_array($file_type,$mimes[$file_ext],true)){
        	exit(escape(L('up_02')));
        }

        //检查扩展名
		$ext_arr = explode("|", UP_Type);
        if (in_array($file_ext,$ext_arr) === false) {
            exit(L('up_02'));
		}elseif($file_ext=='jpg' || $file_ext=='png' || $file_ext=='gif' || $file_ext=='bmp' || $file_ext=='jpge'){
			list($width, $height, $type, $attr) = getimagesize($tempFile);
			if ( intval($width) < 10 || intval($height) < 10 || $type == 4 ) {
                exit(L('up_03'));
			}
		}
        //PHP上传失败
        if (!empty($_FILES['file']['error'])) {
            switch($_FILES['file']['error']){
	            case '1':$error = L('up_04');break;
	            case '2':$error = L('up_05');break;
	            case '3':$error = L('up_06');break;
	            case '4':$error = L('up_07');break;
	            case '6':$error = L('up_08');break;
	            case '7':$error = L('up_09');break;
	            case '8':$error = 'File upload stopped by extension。';break;
	            case '999':
	            default:$error = L('up_10');
            }
            exit($error);
        }
        //新文件名
		$file_name=random_string('alnum', 20). '.' . $file_ext;
		$file_path=$path.$file_name;
		if (move_uploaded_file($tempFile, $file_path) !== false) { //上传成功
            $filepath=(UP_Mode==1)?'/'.date('Ym').'/'.date('d').'/'.$file_name : '/'.date('Ymd').'/'.$file_name;

			//检测发表数据是否需要审核
			$table = ($yid==1)?'pic':'pic_verify';

            $data['pic'] = $filepath;
            $data['uid'] = $uid;
            $data['cid'] = $cid;
            $data['sid'] = $sid;
            $data['addtime'] = time();
            $did = $this->db->insert($table,$data);

			//增加动态
			$dt['dir'] = 'pic';
			$dt['uid'] = $uid;
			$dt['did'] = $sid;
			$dt['yid'] = $yid==1?0:1;
			$dt['title'] = '上传了图片到相册';
			$dt['name'] = $pname;
			$dt['link'] = linkurl('show','id',$sid,1,'pic');
			$dt['addtime'] = time();
			$this->Csdb->get_insert('dt',$dt);
			//如果免审核，则给会员增加相应金币、积分
			if($yid==1){
				 $addhits=getzd('user','addhits',$uid);
				 if($addhits<User_Nums_Add){
					 $this->db->query("update ".CS_SqlPrefix."user set cion=cion+".User_Cion_Add.",jinyan=jinyan+".User_Jinyan_Add.",addhits=addhits+1 where id=".$uid."");
				 }
			}

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
				if(UP_Mode==1 && ($dir=='music' || $dir=='video')){
				    $filepath='attachment/'.$dir.$filepath;
				}
				exit('ok');
			}else{
				@unlink($file_path);
                exit('no');
			}
		}else{ //上传失败
			exit('no');
		}
	}

	//修改介绍
	function picContent($sign){
		$id = (int)$this->input->get_post('id');
		if($id<1) getjson('参数错误~！');
		if($sign==0){
			$row = getzd('pic','content',$id);
			if($row=='NULL') getjson('图片不存在~!');
			getjson($row,0);
		}else{
			$content = $this->input->post('content',true,true);
			$res = $this->db->update('pic',array('content'=>$content),array('id'=>$id));
			if($res){
				getjson('',0);
			}else{
				getjson('数据异常，请刷新重试');
			}
		}
	}
}