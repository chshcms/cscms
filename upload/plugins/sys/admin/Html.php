<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-09-20
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Html extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
		$this->lang->load('admin_html');
		$this->load->model('Cstpl');
		$this->load->helper('directory');
	}

	public function index(){
	    if(Web_Mode!=3){
	    	admin_info(L('plub_01'));
		}
        $this->load->view('html.html');
	}

	//生成
	public function save($op='index'){
		$ac = $this->input->post('ac',true);
		define('IS_HTML',true);
		if($ac!='pc' && !defined('MOBILE') && config('Mobile_Is')==1){
			define('MOBILE', true);	
		}
		$this->load->get_templates(); //转换视图为前台
		if($op == 'index'){
			$html = $this->Cstpl->home(TRUE);
	        if(!defined('MOBILE')){
	        	$file = FCPATH.Html_Index;
	        }else{
	        	$file = FCPATH.FGF.Html_Wap_Dir.FGF.Html_Index;
	        }
			//主页
			if(!write_file($file, $html)){
	            getjson($file.L('plub_02'));
			}else{
			    $info['msg'] = L('plub_03');
			    $info['url'] = site_url('html').'?v='.rand(1000,1999);
			    $info['time'] = 2000;
				getjson($info,0);
			}

		}else{

			//获取自定义模板
			if(!defined('MOBILE')){
	        	$path = VIEWPATH.'pc'.FGF.'skins'.FGF.str_replace('/', FGF, Pc_Skins_Dir).PLUBPATH.FGF;
	        }else{
	        	$path = VIEWPATH.'mobile'.FGF.'skins'.FGF.str_replace('/', FGF, Mobile_Skins_Dir).PLUBPATH.FGF;
	        }
	        $dir_arr = directory_map($path, 1);
	        $skinsdirs = array();
		    if ($dir_arr) {
			    foreach ($dir_arr as $t) {
				    if (!is_dir($path.$t)) {
						if(substr($t,0,4)=='opt-'){
	              	        ob_end_flush();//关闭缓存 
				  	        $t = str_replace(".html","",$t);
				  	        $t = str_replace("opt-","",$t);
				  	        $Mark_Text = $this->Cstpl->opt($t,true);
					        if($ac=='pc'){
					        	$file = FCPATH.'opt'.FGF.$t.'.html';
					        }else{
					        	$file = FCPATH.Html_Wap_Dir.FGF.'opt'.FGF.$t.'.html';
					        }
	                        //生成
			                write_file(FCPATH.$file,$Mark_Text);
	                        ob_flush();flush();
						}
				    }
			    }
		    }
		    $info['msg'] = L('plub_04');
			$info['time'] = 2000;
		    $info['url'] = site_url('html').'?v='.rand(1000,1999);
			getjson($info,0);
		}
	}
}


