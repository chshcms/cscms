<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2017 chshcms.com. All rights reserved.
 * @Author:zhwdeveloper
 * @Dtime:2016-12-20
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Index extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
	}

	public function index($sign=''){
		$data['code']=cs_base64_encode(arraystring(array(
		   'self' => Web_Path.SELF,
		   'version' => CS_Version,
		   'charset' => CS_Charset,
		   'uptime' => CS_Uptime,
		)));
		//顶部横向菜单栏
		$plugins = $this->Csdb->getres('plugins','','name,dir','id ASC',0);
		//左侧边栏菜单列表
		$index = require CSCMS.'sys/Cs_Menu.php';
		$dir = $index;
		$plugin = array();
		foreach ($plugins as $key => $value) {
			$menufile = CSCMS.$value->dir.FGF.'menu.php';
			if(file_exists($menufile)){
				array_push($plugin, $value);
				$arr1 = require $menufile;
				$arr = $arr1['admin'];
				foreach ($arr as $k => $v) {
					$arr[$k]['title'] = $arr[$k]['name'];
					if(!isset($arr[$k]['on'])){
						$arr[$k]['on'] = 1;
					}
					$pnum = 1;
					foreach ($arr[$k]['menu'] as $k1 => $v1) {
						if($k==0){
							$k2 = $k1+1;
						}else{
							$k2 = $k1;
						}
						$pnum++;
						$arr[$k]['menu'][$k2]['name'] = $v1['name'];
						$arr[$k]['menu'][$k2]['link'] = site_url($value->dir.'/'.$v1['link']);
					}
					if($k==0){
						$arr[$k]['menu'][0]['name'] = L('index_01');
						$arr[$k]['menu'][0]['link'] = site_url('plugins/setting').'?dir='.$value->dir;
						$arr[$k]['menu'][$pnum]['name'] = L('index_02');
						$arr[$k]['menu'][$pnum]['link'] = site_url('field').'?dir='.$value->dir;
					}
					unset($arr[$k]['name']);
				}
				$dir[$value->dir] = $arr;
			}
		}
		$data['plugins'] = $plugin;
		$data['dir'] = json_encode($dir);
		if($sign=='json'){
			echo json_encode($dir);exit;
		}
		//判断初次安装
		$data['plub_install'] = 1;
		if(!file_exists(FCPATH.'packs'.FGF.'install'.FGF.'plub_install.lock')){
		    $data['plub_install'] = 0;    
		}
        $this->load->view('index.html',$data);
	}
}

