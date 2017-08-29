<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2017 chshcms.com. All rights reserved.
 * @Author:zhwdeveloper
 * @Dtime:2016-12-20
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Field extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
		$this->lang->load('admin_field');
        $this->Csadmin->Admin_Login();
	}

	public function index(){
		$dir = $this->input->get('dir',true);
		if($dir!='user'){
			$row = $this->db->query("select id from ".CS_SqlPrefix."plugins where dir='".$dir."'")->row();
			if(!$row) admin_info(L('plub_01'));
		}
        //获取该版块自定义字段
        $field_list = array();
		if (is_file(CSCMS.'sys/Cs_Field.php')) {
		    $field = require(CSCMS.'sys/Cs_Field.php');
		    if(isset($field[$dir])){
		    	$field_list = $field[$dir];
		    }
		}
		$data['dir'] = $dir;
		$data['field'] = $field_list;
        $this->load->view('field_list.html',$data);
	}
	public function edit(){
		$dir = $this->input->get('dir',true);
		$zd = $this->input->get('zd',true);
		$data['dir'] = $dir;
		if($zd==''){
			$data['title'] = L('plub_18');
			$data['zd'] = '';
			$data['leix'] = '';//字段分类
			$data['table'] = '';//数据表
			$data['dot'] = 0;//小数点位数
			$data['required'] = 0;//字段必填项
			$data['status'] = 1;//状态
			$data['note'] = '';//字段注释
			$data['notice'] = '';//输入提示
			$data['attr'] = '';//附件属性
			$data['default'] = '';//默认值
			$data['regexp'] = '';//正则匹配
			$data['wrong'] = '';//错误提示
			$data['pass'] = 0;//单行文本密码框
			$data['accept'] = 'gif|png|jpg|jpeg';//图片允许类型
			$data['option'] = '';//选项值
			$data['opcls'] = '';//选项值类型
			$data['qiantai'] = 0;//是否前台显示
			$data['search'] = 0;//是否前台搜索
		}else{
			$data['title'] = L('plub_19');
			if (is_file(CSCMS.'sys/Cs_Field.php')) {
			    $field_res = require(CSCMS.'sys/Cs_Field.php');
			    if(isset($field_res[$dir])){
			    	$field = $field_res[$dir][$zd];
			    }else{
			    	$info['msg'] = L('plub_02');
					$info['url'] = site_url('field').'?dir='.$dir."?v=".rand(100,999);
					admin_info($info,2);
			    }
			}else{
				$info['msg'] = L('plub_03');
				$info['url'] = site_url('field').'?dir='.$dir."?v=".rand(100,999);
				admin_info($info,2);
			}
			$data['zd'] = $field['zd'];
			$data['leix'] = $field['leix'];
			$data['table'] = $field['table'];
			$data['dot'] = (int)$field['dot'];
			$data['required'] = (int)$field['required'];
			$data['status'] = (int)$field['status'];
			$data['note'] = $field['note'];
			$data['notice'] = $field['notice'];
			$data['attr'] = $field['attr'];
			$data['default'] = $field['default'];
			$data['regexp'] = $field['regexp'];
			$data['wrong'] = $field['wrong'];
			$data['pass'] = (int)$field['pass'];
			$data['accept'] = $field['accept'];
			$data['option'] = $field['option'];
			$data['opcls'] = $field['opcls'];
			$data['qiantai'] = (int)$field['qiantai'];
			$data['search'] = isset($field['search']) ? (int)$field['search'] : 0;
		}
        $this->load->view('field_edit.html',$data);
	}
	//字段信息保存
	function save(){
		$dir = $this->input->post('dir',true);
		$data['leix'] = $this->input->post('leix',true);
		$data['required'] = (int)$this->input->post('required');
		$data['status'] = (int)$this->input->post('status');
		$data['zd'] = $this->input->post('zd',true);
		$data['table'] = $this->input->post('table',true);
		$data['note'] = $this->input->post('note',true);
		$data['notice'] = $this->input->post('notice',true);
		$data['dot'] = (int)$this->input->post('dot',true);
		$data['pass'] = (int)$this->input->post('pass',true);
		$data['option'] = $this->input->post('option');
		$data['opcls'] = $this->input->post('opcls',true);
		$data['regexp'] = $this->input->post('regexp');
		$data['wrong'] = $this->input->post('wrong',true);
		$data['qiantai'] = (int)$this->input->post('qiantai',true);
		$data['search'] = (int)$this->input->post('search',true);
		$number_attr = $this->input->post('number_attr',true);
		$number_default = (int)$this->input->post('number_default',true);
		$text_attr = $this->input->post('text_attr',true);
		$text_default = $this->input->post('text_default',true);
		$image_accept = $this->input->post('image_accept',true);
		$image_default = $this->input->post('image_default',true);
		$select_attr = (int)$this->input->post('select_attr',true);
		$select_default = $this->input->post('select_default',true);
		$date_attr = $this->input->post('date_attr',true);
		$date_default = $this->input->post('date_default',true);
		$upload_accept = $this->input->post('upload_accept',true);
		$upload_attr = $this->input->post('upload_attr',true);
		if(empty($data['leix']) || empty($data['zd']) || empty($data['note'])){
			getjson(L('plub_04'));
		}
		if(!preg_match('/^[a-zA-Z]\w+/',$data['zd'])){
			getjson(L('plub_05'));
		}
		if($data['leix']=='datetime'&&!empty($date_default)&&!strtotime($date_default)){
			getjson(L('plub_06'));
		}
		if($data['leix']=='select' && empty($data['option'])){
			getjson(L('plub_07'));
		}
		$old_zd = $this->input->post('old_zd',true);
		$old_table = CS_SqlPrefix.$this->input->post('old_table',true);
		$table = CS_SqlPrefix.$data['table'];
		if(!$this->db->table_exists($table)){
			getjson(L('plub_08'));
		}
		//判断审核表和回收表
		$table_verify = $this->db->table_exists($table.'_verify')?$table.'_verify':'';
		$table_hui = $this->db->table_exists($table.'_hui')?$table.'_hui':'';

		$old_table_verify = $this->db->table_exists($old_table.'_verify')?$old_table.'_verify':'';
		$old_table_hui = $this->db->table_exists($old_table.'_hui')?$old_table.'_hui':'';

		$data['accept'] = '';
		$data['default'] = '';
		$data['attr'] = 0;
		$sql = '';
		switch ($data['leix']){
			case 'number':
				$data['attr'] = $number_attr;
				$data['default'] = $number_default;
				if($number_attr=='tinyint' || $number_attr=='int'){
					$sql = $number_attr.' default '.$number_default;
				}else{
					$sql = $number_attr.'(10,'.$data['dot'].') default '.$number_default;
				}
				break;
			case 'text':
				$data['attr'] = $text_attr;
				$data['default'] = $text_default;
				if($text_attr==0){
					$sql = "varchar(255) default '".$text_default."'";
				}else{
					$sql = "text";
				}
				break;
			case 'image':
				$data['accept'] = $image_accept;
				$data['default'] = $image_default;
				$sql = "varchar(255) default '".$image_default."'";
				break;
			case 'select':
				$data['attr'] = $select_attr;
				$data['default'] = $select_default;
				if($data['opcls']=='varchar'){
					$sql = "varchar(255) default '".$select_default."'";
				}else{
					$sql = $data['opcls']." default ".(int)$select_default;
				}
				break;
			case 'datetime':
				$data['attr'] = $date_attr;
				$data['default'] = $date_default;
				if($date_attr=='date'){
					$sql = "date";
				}else{
					$sql = "datetime";
				}
				if(!empty($date_default)) $sql .= " default '".$date_default."'";
				break;
			case 'upload':
				$data['accept'] = $upload_accept;
				$data['attr'] = $upload_attr;
				$sql = "varchar(255)";break;
			default:break;
		}
		if($data['required']==1){
			$sql .= " not null";
		}
		//获取原字段数据
		$field = require(CSCMS.'sys/Cs_Field.php');
		if(empty($old_zd)){//添加
			if(!$this->db->field_exists($data['zd'],$table)){
				//主表
				$sql1="ALTER TABLE ".$table." ADD ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
				$this->db->query($sql1);
				//审核表
				if(!empty($table_verify)){
					if(!$this->db->field_exists($data['zd'],$table_verify)){
						$sql2="ALTER TABLE ".$table_verify." ADD ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
						$this->db->query($sql2);
					}else{
						$sql2="ALTER TABLE ".$table_verify." change ".$data['zd']." ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
						$this->db->query($sql2);
					}
						
				}
				//回收表
				if(!empty($table_hui)){
					if(!$this->db->field_exists($data['zd'],$table_hui)){
						$sql3="ALTER TABLE ".$table_hui." ADD ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
						$this->db->query($sql3);
					}else{
						$sql3="ALTER TABLE ".$table_hui." change ".$data['zd']." ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
						$this->db->query($sql3);
					}
						
				}
			}else{
				getjson(L('plub_09'));
			}
		}else{//修改
			//判断是否非法修改字段
			if(!isset($field[$dir][$old_zd])){
				getjson(L('plub_15'));
			}	
			if($table == $old_table){
				if(!$this->db->field_exists($old_zd, $table)){
					//主表
					$sql1="ALTER TABLE ".$table." ADD ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
					$res = $this->db->query($sql1);
					//审核表
					if (!empty($table_verify)){
						if(!$this->db->field_exists($old_zd,$table_verify)){
							$sql2="ALTER TABLE ".$table_verify." ADD ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
							$this->db->query($sql2);
						}else{
							$sql2="ALTER TABLE ".$table_verify." change ".$old_zd." ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
							$this->db->query($sql2);
						}
						
					}
					//回收表
					if (!empty($table_hui)) {
						if(!$this->db->field_exists($old_zd,$table_hui)){
							$sql3="ALTER TABLE ".$table_hui." ADD ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
							$this->db->query($sql3);
						}else{
							$sql3="ALTER TABLE ".$table_hui." change ".$old_zd." ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
							$this->db->query($sql3);
						}
					}
					if(!$res){
						getjson(L('plub_10'));
					}
				}else{
					//主表
					$sql1 = "ALTER TABLE ".$table." CHANGE ".$old_zd." ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
					$res = $this->db->query($sql1);
					
					if(!empty($table_verify)){
						if(!$this->db->field_exists($old_zd,$table_verify)){
							$sql2 = "ALTER TABLE ".$table_verify." ADD ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
							$this->db->query($sql2);
						}else{
							$sql2 = "ALTER TABLE ".$table_verify." CHANGE ".$old_zd." ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
							$this->db->query($sql2);
						}
					}
					
					if(!empty($table_hui)){
						if(!$this->db->field_exists($old_zd,$table_hui)){
							$sql3 = "ALTER TABLE ".$table_hui." ADD ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
							$this->db->query($sql3);
						}else{
							$sql3 = "ALTER TABLE ".$table_hui." CHANGE ".$old_zd." ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
							$this->db->query($sql3);
						}
							
					}
					if(!$res){
						getjson(L('plub_11'));
					}
				}
			}else{
				if(!$this->db->field_exists($data['zd'],$table)){
					//主表
					$sql1="ALTER TABLE ".$table." ADD ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
					$res = $this->db->query($sql1);
					//审核表
					if (!empty($table_verify)) {
						$sql2="ALTER TABLE ".$table_verify." ADD ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
						$this->db->query($sql2);
					}
					//回收表
					if (!empty($table_hui)) {
						$sql3="ALTER TABLE ".$table_hui." ADD ".$data['zd']." ".$sql." COMMENT '".$data['note']."'";
						$this->db->query($sql3);
					}
					if($res){
						//主表
						$this->db->query("ALTER TABLE ".$old_table." DROP COLUMN ".$old_zd."");
						//审核表
						if(!empty($old_table_verify)){
							$this->db->query("ALTER TABLE ".$old_table_verify." DROP COLUMN ".$old_zd."");
						}
						//回收表
						if(!empty($old_table_hui)){
							$this->db->query("ALTER TABLE ".$old_table_hui." DROP COLUMN ".$old_zd."");
						}
					}else{
						getjson(L('plub_12'));
					}
				}else{
					getjson(L('plub_13'));
				}
			}
		}
		if(!empty($old_zd)){
			unset($field[$dir][$old_zd]);
		}
		$field[$dir][$data['zd']] = str_encode($data);
		arr_file_edit($field,CSCMS.'sys/Cs_Field.php');
		$info['url'] = site_url('field').'?dir='.$dir.'&v='.rand(100,999);
		$info['msg'] = L('plub_14');
		getjson($info,0);
	}
	//删除字段
	public function del(){
		$dir = $this->input->get('dir',true);
		$zd = $this->input->get_post('id',true);
		if (is_file(CSCMS.'sys/Cs_Field.php')) {
			$field = require(CSCMS.'sys/Cs_Field.php');
			if(isset($field[$dir][$zd])){
				//主表
				if($this->db->table_exists(CS_SqlPrefix.$field[$dir][$zd]['table']) && $this->db->field_exists($zd, CS_SqlPrefix.$field[$dir][$zd]['table'])){
					$this->db->query("ALTER TABLE ".CS_SqlPrefix.$field[$dir][$zd]['table']." DROP COLUMN ".$zd."");
				}
				//审核表
				if($this->db->table_exists(CS_SqlPrefix.$field[$dir][$zd]['table'].'_verify') && $this->db->field_exists($zd, CS_SqlPrefix.$field[$dir][$zd]['table'].'_verify')){
					$this->db->query("ALTER TABLE ".CS_SqlPrefix.$field[$dir][$zd]['table']."_verify DROP COLUMN ".$zd."");
				}
				//回收表
				if($this->db->table_exists(CS_SqlPrefix.$field[$dir][$zd]['table'].'_hui') && $this->db->field_exists($zd, CS_SqlPrefix.$field[$dir][$zd]['table'].'_hui')){
					$this->db->query("ALTER TABLE ".CS_SqlPrefix.$field[$dir][$zd]['table']."_hui DROP COLUMN ".$zd."");
				}
			}else{
				getjson(L('plub_15'));
			}
            unset($field[$dir][$zd]);
			if(empty($field[$dir])){
				unset($field[$dir]);
			}
			arr_file_edit($field,CSCMS.'sys/Cs_Field.php');
			$info['url'] = site_url('field').'?dir='.$dir.'&v='.rand(100,999);
			$info['msg'] = L('plub_16');
            getjson($info,0);
		}else{
		    getjson(L('plub_17'));
		}
	}
	//修改必填项和状态
	public function init($sid){
		$sign = (int)$this->input->get_post('sign');
		$dir = $this->input->get('dir');
		$zd = $this->input->get('zd');
		if (is_file(CSCMS.'sys/Cs_Field.php')) {
			$field = require(CSCMS.'sys/Cs_Field.php');
			if(isset($field[$dir][$zd])){
				if($sid=='required'){
					$field[$dir][$zd]['required'] = $sign?0:1;
				}else{
					$field[$dir][$zd]['status'] = $sign?0:1;
				}
				arr_file_edit($field,CSCMS.'sys/Cs_Field.php');
				$info['url'] = site_url('field').'?dir='.$dir.'&v='.rand(100,999);
				$info['msg'] = L('plub_14');
	            getjson($info,0);
			}else{
				getjson(L('plub_15'));
			}
		}else{
			getjson(L('plub_17'));
		}
	}
}

