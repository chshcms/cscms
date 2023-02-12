<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-01
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Upgrade extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
        $this->load->helper('file');
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
		$this->lang->load('admin_upgrade');
		$this->_upgrade = "http://api.chshcms.net/cscms/"; //程序升级地址
		$this->_filearr = array('.','packs','cscms'); //MD5效验目录
	}

    //系统升级
	public function index(){
		$cscms_update = htmlall($this->_upgrade.'ajax/update?version='.CS_Version.'&update='.CS_Uptime);
        $data['cscms_update'] = json_decode($cscms_update,1);
        $this->load->view('upgrade.html',$data);
	}

	//在线更新
	public function init(){
	    $id = intval($this->input->get('id'));
		if($id==0){
			getjson(L('plub_01'));//参数错误，请重试
		}

		//当前版本内容
        $versiondata = read_file(CSCMS.'lib/Cs_Version.php');

		//创建缓存文件夹
	    $dircache=FCPATH.'cache/upgrade/';
		if(!file_exists($dircache)) {
			mkdirss($dircache);
		}

        $this->load->library('csapp');
        $zip='http://www.chshcms.com/down/xia/utf8/'.$id.'.html';
        $zippath=FCPATH."attachment/other/backup_".$id.".zip";
        $files_file=$this->csapp->down($zip,$zippath);
	    if($files_file=='-1') getjson(L('plub_02'));//抱歉，补丁地址错误~!
	    if($files_file=='-2') getjson(L('plub_03'));//对不起，您的服务器不支持远程下载~!
	    if($files_file=='-3') getjson(L('plub_04'));//抱歉，目录（./attachment/other/）没有写入权限！
	    if($files_file=='10001') getjson(L('plub_05'));//抱歉，补丁下载失败~!
	    if($files_file=='10002') getjson(L('plub_06'));//抱歉，当前版本不能使用~!
        if(filesize($zippath) == 0) getjson(L('plub_07'));//抱歉，补丁下载失败~!

	    //解压缩
        $this->load->library('cszip');
	    $this->cszip->PclZip($zippath);
	    if ($this->cszip->extract(PCLZIP_OPT_PATH, $dircache, PCLZIP_OPT_REPLACE_NEWER) == 0) {
               @unlink($zippath);
               getjson(L('plub_08'));//抱歉，补丁解压失败~!
	    }

		//覆盖到根目录
		$this->copyfailnum = 0;
		$this->copymsg = array();
		$this->copydir($dircache, FCPATH);
			
		//检查文件操作权限，是否复制成功
		if($this->copyfailnum > 0) {
				//如果失败，恢复当前版本
				write_file(CSCMS.'lib/Cs_Version.php', $versiondata);
				echo '<LINK href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><br>';
				$k=1;
                foreach ($this->copymsg as $msg) {
				    echo '&nbsp;&nbsp;'.$k.'.'.$msg;
					$k++;
				}
				echo '&nbsp;&nbsp;<b style="color:red">'.L('plub_09').'&nbsp;&nbsp;<a href="'.site_url('upgrade').'">'.L('plub_10').'</a</b>';
				@unlink($zippath);
				exit;
		}

		//检测执行SQL语句
		$this->upsql($dircache);
        //删除文件
		@unlink($zippath);
		//删除文件夹
 		deldir($dircache);
        //成功提示
        $info['url'] = site_url('upgrade').'?v='.rand(1000,9999);
        $info['msg'] = L('plub_11');//恭喜您，在线升级完成
        getjson($info,0);
	}

	//查看代码
	public function look(){
	    $path = $this->input->get_post('path',true);
	    $path = isset($path) && trim($path) ? stripslashes(urldecode(trim($path))) : die(L('plub_12'));
	    $path = str_replace("..","",$path);
	
	    if (!file_exists(FCPATH.$path) || is_dir($path)) {
			 die(L('plub_13'));//文件不存在~!
	    }
	    $data['html'] = str_replace('</textarea>','&lt;/textarea&gt;',file_get_contents(FCPATH.$path));
	    //判断重要文件，不能查看
	    if(strpos(strtolower($path),'cscms/lib') !== FALSE){
		    die(L('plub_14'));//重要文件，不允许在线查看！
	    }
        $this->load->view('upgrade_look.html',$data);
	}

	//文件MD5验效
	public function checkfile() {
		$data['diff']=array();
		$data['lostfile']=array();
		$data['unknowfile']=array();
	    $this->filemd5('.');

	    //读取cscms接口
	    $cscms_md5 = htmlall($this->_upgrade.'ajax/filemd5?charset='.CS_Charset.'&update='.CS_Uptime);
	    $cscms_md5_arr = json_decode($cscms_md5, 1);

	    if(!empty($cscms_md5_arr)){
	        //计算数组差集
	        $diff = array_diff($cscms_md5_arr, $this->md5_arr);

	        //丢失文件列表
	        $lostfile = array();
	        foreach($cscms_md5_arr as $k=>$v) {
		        if(!in_array($k, array_keys($this->md5_arr))) {
			        $lostfile[] = $k;
			        unset($diff[$k]);
		        }
	        }
	        $data['diff'] = $diff;
			$data['lostfile'] = $lostfile;

	        //未知文件列表
	        $data['unknowfile'] = array_diff(array_keys($this->md5_arr), array_keys($cscms_md5_arr));
		}
	    $this->load->view('upgrade_md5.html',$data);
	}
	public function check_tips(){
		$this->load->view('upgrade_tips.html');
	}

	//文件MD5数组
	private function filemd5($path='') 
	{
		$dir_arr = explode('/', dirname($path));
		if(is_dir($path)) {
			$handler = opendir($path);
			while(($filename = @readdir($handler)) !== false) {
				if(substr($filename, 0, 1) != ".") {
					$this->filemd5($path.'/'.$filename);
				}
			}
			closedir($handler);
		} else {
			if (dirname($path) == '.' || (isset($dir_arr[1]) && in_array($dir_arr[1], $this->_filearr))) {
				if(strpos($path,'cscms/lib/') === FALSE && strpos($path,'packs/install/') === FALSE){
					if(strpos($path,'cscms/tpl/') !== FALSE){
						if(strpos($path,'cscms/tpl/admin/') !== FALSE){
				             $this->md5_arr[base64_encode($path)] = md5_file($path);
						}
					}else{
				        $this->md5_arr[base64_encode($path)] = md5_file($path);
					}
				}
			}
		}
	}

    //文件复制
	public function copydir($dirfrom, $dirto) {

	    //当天备份目录
		$dirbackup=FCPATH.'attachment/other/backup_'.date('Ymd').'/';

	    $handle = opendir($dirfrom); //打开当前目录
	    //循环读取文件
	    while(false !== ($file = readdir($handle))) {
	    	if($file != '.' && $file != '..'){ //排除"."和"."
		        //生成源文件名
			    $filefrom = $dirfrom.'/'.$file;
		     	//生成目标文件名
		        $fileto = $dirto.'/'.$file;
		     	//生成备份文件名
		        $filebackup = $dirbackup.'/'.$file;

		        if(is_dir($filefrom)){ //如果是子目录，则进行递归操作
		            $this->copydir($filefrom, $fileto);
		        } else { //如果是文件，则直接用copy函数复制
					//如果文件存在则先备份原文件
					if(file_exists($fileto)){
						$ydata=read_file($fileto);
                        write_file(str_replace(FCPATH,$dirbackup,$fileto), $ydata); //备份
					}
		            //判断SQL文件
				    $exts = strtolower(trim(substr(strrchr($file, '.'), 1)));
				    if($exts!='sql' && $file!=L('plub_16')){
					    //升级覆盖
					    $data=read_file($filefrom);
					    if(!write_file($fileto, $data)) {
					         $this->copyfailnum++;
						     $this->copymsg[]=L('plub_17')." <font color=#ff3300>".str_replace("//","/",$filefrom)."</font> ".L('plub_18')." <font color=#0000ff>".str_replace("//","/",$fileto)."</font> ".L('plub_19')."<br />";
				        }
					}
		        }
	    	}
	    }
	}

    //升级SQL执行
	public function upsql($dirsql) {

	    $handle = opendir($dirsql); //打开当前目录
	    //循环读取文件
	    while(false !== ($file = readdir($handle))) {
	    	if($file != '.' && $file != '..'){ //排除"."和"."
		        //判断SQL文件
				$exts = strtolower(trim(substr(strrchr($file, '.'), 1)));
				if($exts=='sql'){
					 //获取SQL语句
					$sql=read_file($dirsql.$file);
                     //执行SQL
					if(!empty($sql)){
					   	$sqlarr=explode(";",$sql);
                       	foreach ($sqlarr as $sqls) {
                           	$this->Csdb->get_table(str_replace('{prefix}', CS_SqlPrefix, $sqls));
                       	}
					}
		        }
	    	}
	    }
	}
}

