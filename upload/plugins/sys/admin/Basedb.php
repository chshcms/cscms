<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-10-31
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Basedb extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
		$this->load->model('Csbackup');
		$this->lang->load('admin_basedb');
        $this->Csadmin->Admin_Login();
	}

	public function index(){
		$data['tables']=$this->db->query("SHOW TABLE STATUS FROM `".CS_Sqlname."`")->result();
        $this->load->view('basedb.html',$data);
	}

    //还原数据库
	public function restore(){
		$this->load->helper('directory');
        $data['map'] = directory_map(FCPATH.'attachment/backup/', 1);
        $this->load->view('basedb_hy.html',$data);
	}

    //优化表
	public function optimize(){
		$error = array();
	    $this->load->dbutil();
	    $tables = $this->input->get('table',true);
		if(empty($tables)){
	        $tables = $this->input->post('id',true);
		    if(empty($tables)){
		    	getjson(L('plub_01'));
		    }
		    foreach($tables as $table) {
			    if(!$this->dbutil->optimize_table($table)){
                    $error[]=$table;
			    }
		    }
		}else{
			if(!$this->dbutil->optimize_table($tables)){
                $error[]=$tables;
			}
		}
		if(!empty($error)){
			getjson(L('plub_02'));
		}else{
			$info['url'] = site_url('basedb/index').'?v='.rand(1000,9999);
			$info['msg'] = L('plub_03');
            getjson($info,0);
		}
	}

    //修复表
	public function repair(){
		$error=array();
	    $this->load->dbutil();
	    $tables = $this->input->get_post('table',true);
		if(empty($tables)){
	        $tables = $this->input->get_post('id',true);
		    if(empty($tables)){
		    	getjson(L('plub_01'));
		    }
		    foreach($tables as $table) {
			    if(!$this->dbutil->repair_table($table)){
                    $error[]=$table;
			    }
		    }
		}else{
			if(!$this->dbutil->repair_table($tables)){
                $error[]=$tables;
			}
		}
		if(!empty($error)){
            getjson(L('plub_04'));
		}else{
			$info['url'] = site_url('basedb/index').'?v='.rand(1000,9999);
            $info['msg'] = L('plub_05');
            getjson($info,0);
		}
	}

    //备份数据库
	public function backup(){
		$table = $this->input->get_post('table',true);
		$bkdir =  $this->input->get_post('bkdir',true);
		$ok =  (int)$this->input->get_post('ok',true);
		if(empty($table)){
			getjson(L('plub_06'));
		}
		//备份路径
		if(empty($bkdir)){
			$bkdir = date('Ymd');
	        if(is_dir(FCPATH.'attachment'.FGF.'backup'.FGF.'Cscms_v4_'.$bkdir)){
	        	$bkdir = date('Ymd').'_'.time();
	        }
		}
		$bkfile = FCPATH.'attachment'.FGF.'backup'.FGF.'Cscms_v4_'.$bkdir;
		//备份表结构
		if(is_array($table)){
			$res=$this->Csbackup->backup_table($bkfile,$table);
			if(!$res){
				getjson(L('plub_13'));
			}else{
	            $info['msg'] = L('plub_14');
				$info['bkdir'] = $bkdir;
	            getjson($info,0);
			}
		}
        //备份表数据
        $res=$this->Csbackup->backup_data($bkfile,$table);
		if(!$res){
			getjson(L('plub_08',array($table)));
		}else{
			$info['msg'] = L('plub_15',array($table));
			if($ok==1){
				$info['msg'] = L('plub_07');
				$info['url'] = site_url('basedb/restore').'?v='.rand(1000,9999);
			}
			getjson($info,0);
		}
	}

	//还原数据库
	public function restore_save(){
		$dirs = str_replace("//","/",str_replace("..","",$this->input->get_post('dir',true)));
		if(empty($dirs)){
			getjson(L('plub_09'));
		}
		$this->Csbackup->restore($dirs);
        $info['url'] = site_url('basedb/restore').'?v='.rand(1000,9999);
        $info['msg'] = L('plub_10');
        getjson($info,0);
	}

	//备份打包下载
	public function zip(){
		$dirs = str_replace("//","/",str_replace("..","",$this->input->get('dir',true)));
		if(empty($dirs)) admin_msg(L('plub_11'),'javascript:history.back();','no');
	    $this->load->library('zip');
        $path = FCPATH.'attachment/backup/'.$dirs.'/';
	    $this->zip->read_dir($path, FALSE);
	    $this->zip->download($dirs.'.zip'); 
	}

	//查看表结构
	public function fields(){
		$table = $this->input->get('table',true);
		$data['table'] = $this->Csbackup->repair($table);
        $this->load->view('basedb_field.html',$data);
	}

	//备份删除
	public function del(){
		$dir = str_replace("//","/",str_replace("..","",$this->input->get_post('id',true)));
		if(empty($dir)){
			getjson(L('plub_11'));
		}
		$dirs = array();
		if(!is_array($dir)){
			$dirs[] = $dir;
		}else{
			$dirs = $dir;
		}
		foreach($dirs as $dir) {
            deldir(FCPATH.'attachment/backup/'.$dir);
		}
		$info['msg'] = L('plub_12');
		$info['url'] = site_url('basedb/restore').'?v='.rand(1000,9999);
        getjson($info,0);
	}
}


