<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-08-01
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Check extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
		$this->lang->load('admin_check');
        $this->Csadmin->Admin_Login();
	}

	public function index(){
        $this->load->view('check.html');
	}

	public function pinfo(){
        phpinfo();
	}

	public function init(){
	    @header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
	    @header("Cache-Control: no-cache, must-revalidate"); 
	    @header("Pragma: no-cache");
        $id = intval($this->input->get('id'));
        $str = 'ok';
		switch($id){
			    //上传参数检测
            case '1':
                    $post = intval(@ini_get("post_max_size"));
                    $file = intval(@ini_get("upload_max_filesize"));
                    $strs='';
                    if ($file >= $post) {
                        $strs.= '<tr><td><i class="fa fa-close " style="color: red" ></i> '.L('plub_01').'</td></tr>';
                    }
                    if ($file < 10) {
                        $strs.= '<tr><td><i class="fa fa-close " style="color: red" ></i> '.L('plub_02',array($file)).'</td></tr>';
                    }
                    if ($post < 10) {
                        $strs.= '<tr><td><i class="fa fa-close " style="color: red" ></i> '.L('plub_03',array($post)).'</td></tr>';
                    }
					$str=$strs;
				break;
            case '2':
                    if (SELF == 'admin.php') {
	                    $str= '<tr><td><i class="fa fa-close " style="color: red" ></i> '.L('plub_04').'</td></tr>';
                    }
				break;
            case '3':
                    if (!ini_get('allow_url_fopen')) {
	                    $str= '<tr><td><i class="fa fa-close " style="color: red" ></i> '.L('plub_05').'</td></tr>';
                    }
				break;
            case '4':
                    if (!function_exists('curl_init')) {
	                    $str= '<tr><td><i class="fa fa-close " style="color: red" ></i> '.L('plub_06').'</td></tr>';
                    }
				break;
            case '5':
                    if (!function_exists('openssl_open')) {
	                    $str= '<tr><td><i class="fa fa-close " style="color: red" ></i> '.L('plub_07').'</td></tr>';
                    }
				break;
            case '6':
                    if (CS_Smtpmode=="FALSE") {
	                    $str= '<tr><td><i class="fa fa-close " style="color: red" ></i> '.L('plub_08').'</td></tr>';
			        }
				break;
            case '7':
				    $strs='';
			        $sql = "SHOW TABLE STATUS FROM `{$this->db->database}`";
			        $table = $this->db->query($sql)->result_array();
			        if (!$table) {
    			         $strs.='<tr><td><i class="fa fa-close " style="color: red" ></i> '.L('plub_09',array($sql)).'</td></tr>';
			        }

			        $sql = 'SHOW FULL COLUMNS FROM `'.CS_SqlPrefix.'admin`';
			        $field = $this->db->query($sql)->result_array();
			        if (!$field) {
   			             $strs.='<tr><td><i class="fa fa-close " style="color: red" ></i> '.L('plub_10',array($sql)).'</td></tr>';
			        }
				break;
		}
		echo $str;
	}
}

