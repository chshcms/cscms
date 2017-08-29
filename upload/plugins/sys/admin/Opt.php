<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-08-01
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Opt extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
		$this->load->library('user_agent');
	    $this->load->model('Csadmin');
		$this->Csadmin->Admin_Login();
	}

	public function main(){
		$this->load->library('ip');
        $this->load->view('main.html');
	}

	//清空页面库缓存
	public function del_cache(){
        $sqlstr="select name,dir from ".CS_SqlPrefix."plugins order by id asc";
		$result=$this->db->query($sqlstr);
        $data['plugins']=$result->result();
        $this->load->view('cache.html',$data);        
	}
	//点击更新缓存
	public function upd_cache($dir='sys'){
		if($dir == CS_Cache_Dir){
			deldir('./cache/'.$dir,'no');
		}elseif($dir=='img'){
			deldir('./cache/suo_pic/','no');
		}else{
			if(Cache_Mx == 'file'){
				deldir('./cache/'.$dir);
			}else{
				//装载驱动
            	$this->load->driver('cache', array('adapter' => Cache_Mx, 'backup' => 'file', 'key_prefix' => CS_SqlPrefix));
            	$this->cache->clean();
			}
		}
		getjson('',0);
	}

    //初始化版块数据
	public function init(){
		echo '<link rel="stylesheet" href="'.Web_Path.'packs/layui/css/layui.css">';
		$this->load->library('csapp');
		//判断安装
		if(file_exists(FCPATH.'packs/install/plub_install.lock')){
		    echo '<div style="padding:10px;font-size:16px;font-weight: bold;color:red;">'.L('init_01').'</div>';
			echo '<script>setTimeout(\'top.location.reload();\',3000);</script>';
			exit;
		}
        //搜索本地模块
		$no = 0;
		$install = '';
		$this->load->helper('directory');
        $local = directory_map(FCPATH.'plugins'.FGF,1);
        if($local){
            foreach ($local as $dir) {
				$dir = str_replace(array("\\","/"), "", $dir);
				if(is_dir(FCPATH.'plugins'.FGF.$dir)){
				    $api_file = CSCMS.$dir.FGF.'setting.php';
                    if (is_file($api_file)) {
						 $API = require_once($api_file);
                         if(!empty($API['mid'])) {
							 $msg = $this->Csadmin->plub_install($dir,$API['name']);
							 $dname = (CS_Language == 'zh_cn') ? $API['name'] : $dir;
							 if($msg == 'ok'){
								$plub['dir'] = $dir;
								$plub['mid'] = $API['mid'];
								$installurl = $this->csapp->url('plub/install',$plub);
								$install.='<script src="'.$installurl.'"></script>';
								echo '<div style="padding:10px;font-weight: bold;color:#080;">['.$dname.']'.L('init_02').'</div>';
							 }else{
								$no++;
								echo '<div style="padding:10px;font-weight: bold;color:red;">['.$dname.']'.L('init_03').$msg.'</div>';
							 }
							 ob_flush();flush();
                         }
					}
				}
            }
            unset($local);
        }
		if($no==0){
			write_file(FCPATH.'packs/install/plub_install.lock', 'cscms');
			$install.='<script>setTimeout(\'top.location.reload();\',3000);</script>';
		}
		echo $install;
	}
}

