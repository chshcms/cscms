<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-10-03
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Label extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
		$this->lang->load('admin_label');
        $this->Csadmin->Admin_Login();
	}

	public function index(){
	    $page = intval($this->input->get('page'));
        if($page==0) $page=1;

        $sql_string = "SELECT * FROM ".CS_SqlPrefix."label order by addtime desc";
        $query = $this->db->query($sql_string); 
        $total = $query->num_rows();

        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        if($totalPages < 1) $totalPages = 1;
        if($page > $totalPages) $page = $totalPages;
        $data['nums'] = $total;
        if($total<$per_page){
              $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);

        $base_url = site_url('label/index').'?page=';
        $data['label'] = $query->result();
        $data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类

        $this->load->view('label.html',$data);
	}

    //新增
	public function add(){
        $data['id']=0;
        $data['name']='';
        $data['selflable']='';
        $data['neir']='';
        $this->load->view('label_edit.html',$data);
	}

    //修改
	public function edit(){
        $id   = intval($this->input->get('id'));
        if($id==0){
            $data['id']=0;
            $data['name']='';
            $data['selflable']='';
            $data['neir']='';
        }else{
            $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix."label where id=".$id."")->row(); 
            if(!$row) admin_msg(L('plub_01'),site_url('label'),'no');  //记录不存在

            $data['id']=$row->id;
            $data['name']=$row->name;
            $data['selflable']=$row->selflable;
            $data['neir']=$row->neir;
        }
        $this->load->view('label_edit.html',$data);
	}

    //入库
	public function save(){
        $id=intval($this->input->post('id'));
        $data['name']=$this->input->post('name',true);
        $data['neir']=$this->input->post('neir',true);
        $data['selflable']=str_encode($this->input->post('selflable'));
        $data['addtime']=time();
		if(empty($data['name'])) getjson(L('plub_02'));//标题、地址不能为空

		if($id==0){ //新增
             $row=$this->db->query("SELECT id FROM ".CS_SqlPrefix."label where name='".$data['name']."'")->row();
		     if($row) getjson(L('plub_03'));//标签名称已经存在
             $this->Csdb->get_insert('label',$data);
		}else{
             $this->Csdb->get_update('label',$id,$data);
		}
		$info['url'] = site_url('label/index').'?v='.rand(1000,9999);
		$info['parent'] = 1;
        getjson($info,0);
	}

    //删除
	public function del(){
        $id = $this->input->get_post('id');
		if(empty($id)) getjson(L('plub_04'));
		$this->Csdb->get_del('label',$id);
		$info['url'] = site_url('label/index').'?v='.rand(1000,9999);
        getjson($info,0);
	}

    //JS标签
	public function js(){
	    $page = intval($this->input->get('page'));
        if($page==0) $page=1;

        $sql_string = "SELECT * FROM ".CS_SqlPrefix."ads order by addtime desc";
        $query = $this->db->query($sql_string); 
        $total = $query->num_rows();

        $base_url = site_url('label/js');
        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        if($totalPages<1) $totalPages = 1;
        if($page>$totalPages) $page = $totalPages;
        $data['nums'] = $total;
        if($total<$per_page){
            $per_page = $total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);

        $data['label'] = $query->result();

        $base_url = site_url('label/js').'?page=';
        $data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('label_js.html',$data);
	}

    //JS标签浏览
	public function js_look(){
        $js=$this->input->get('js',true);
		echo "<script src='".Web_Path."attachment/js/".$js.".js'></script>";
	}

    //JS标签新增
	public function js_add(){
	    $this->load->helper('string');
        $data['id']=0;
        $data['name']='';
        $data['html']='';
        $data['js']=date('YmdHis').random_string('numeric', 5);
        $data['neir']='';
        $this->load->view('label_js_edit.html',$data);
	}

    //JS标签修改
	public function js_edit(){
        $id   = intval($this->input->get('id'));
        $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix."ads where id=".$id."")->row(); 
		if(!$row) exit(L('plub_05'));

        $data['id']=$row->id;
        $data['name']=$row->name;
        $data['html']=$row->html;
        $data['js']=$row->js;
        $data['neir']=$row->neir;

        $this->load->view('label_js_edit.html',$data);
	}

    //JS标签入库
	public function js_save(){
        $id=intval($this->input->post('id'));
        $data['name']=$this->input->post('name',true);
        $data['neir']=$this->input->post('neir',true);
        $data['html']=str_encode($this->input->post('html'));
        $data['js']=$this->input->post('js',true);
        $data['addtime']=time();

		if(empty($data['name']) || empty($data['js'])) getjson(L('plub_06'));

		if($id==0){ //新增
            $row=$this->db->query("SELECT id FROM ".CS_SqlPrefix."ads where name='".$data['name']."'")->row(); 
            if($row) getjson(L('plub_03'));//标签名已存在
            $row=$this->db->query("SELECT id FROM ".CS_SqlPrefix."ads where js='".$data['js']."'")->row(); 
            if($row) getjson(L('plub_08'));//标签JS文件名已经存在

            $this->Csdb->get_insert('ads',$data);
		}else{
            $this->Csdb->get_update('ads',$id,$data);
		}
        $strs = htmltojs($this->input->post('html'));
        //写文件
        if (!write_file('.'.Web_Path.'attachment/js/'.$data['js'].'.js', $strs)){
            getjson(L('plub_09'));//写JS文件失败,目录无权限
        }
        $info['url'] = site_url('label/js').'?v='.rand(1000,9999);
        $info['parent'] = 1;
        getjson($info,0);
	}

    //JS标签删除
	public function js_del(){
        $id = $this->input->get_post('id');
		if(empty($id)) getjson(L('plub_04'));
		//删除文件
		if(is_array($id)){
		   foreach ($id as $ids) {
			    $row=$this->db->query("SELECT js FROM ".CS_SqlPrefix."ads where id='".$ids."'")->row();
			    if($row){
                    $jsurl='.'.Web_Path.'attachment/js/'.$row->js.'.js';
			        @unlink($jsurl);
			    }
		   }
		}else{
			    $row=$this->db->query("SELECT js FROM ".CS_SqlPrefix."ads where id='".$id."'")->row();
			    if($row){
                    $jsurl='.'.Web_Path.'attachment/js/'.$row->js.'.js';
			        @unlink($jsurl);
			    }
		}
		$this->Csdb->get_del('ads',$id);
        $info['url'] = site_url('label/js').'?v='.rand(1000,9999);
        getjson($info,0);
	}

    //页面标签
	public function page(){
        $page = intval($this->input->get('page'));
        if($page==0) $page=1;

        $sql_string = "SELECT * FROM ".CS_SqlPrefix."page order by addtime desc";
        $query = $this->db->query($sql_string); 
        $total = $query->num_rows();

        $base_url = site_url('label/page');
        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        if($totalPages < 1) $totalPages = 1;
        if($page > $totalPages) $page = $totalPages;
        $data['nums'] = $total;
        if($total<$per_page){
            $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);

        $data['label'] = $query->result();
        $base_url = site_url('label/page').'?page=';
        $data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类

        $this->load->view('label_page.html',$data);
	}

    //页面标签新增
	public function page_add(){
        $data['id']=0;
        $data['sid']=0;
        $data['name']='';
        $data['html']='';
        $data['url']=Web_Path.'diy/'.date('YmdHis').'.html';
        $data['neir']='';
        $this->load->view('label_page_edit.html',$data);
	}

    //页面标签修改
	public function page_edit(){
            $id   = intval($this->input->get('id'));
            $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix."page where id=".$id."")->row(); 
			if(!$row) exit(L('plub_05'));//记录不存在

            $data['id']=$row->id;
            $data['sid']=$row->sid;
            $data['name']=$row->name;
            $data['html']=$row->html;
            $data['url']=$row->url;
            $data['neir']=$row->neir;

            $this->load->view('label_page_edit.html',$data);
	}

    //页面标签入库
	public function page_save(){
        $id=intval($this->input->post('id'));
        $data['sid']=intval($this->input->post('sid'));
        $data['name']=$this->input->post('name',true);
        $data['neir']=$this->input->post('neir',true);
        $data['html']=str_encode($this->input->post('html'));
        $data['addtime']=time();
        $url=$this->input->post('url',true);

		if(empty($data['name'])) getjson(L('plub_10'));  //标题不能为空

        if($data['sid']==1){ //静态
		      if(empty($url)) getjson(L('plub_11'));  //URL地址不能为空
              $file_ext = strtolower(trim(substr(strrchr($url, '.'), 1)));
			  if($file_ext!='html' && $file_ext!='htm' && $file_ext!='shtm' && $file_ext!='shtml' && $file_ext!='xml') {
                    getjson(L('plub_12')); //后缀非法
		      }
              $data['url']=$url;
		}else{
              $data['url']=Web_Path.'index.php/page/index/'.$data['name'];
		}

		if($id==0){ //新增
            $row=$this->db->query("SELECT id FROM ".CS_SqlPrefix."page where name='".$data['name']."'")->row(); 
            if($row) getjson(L('plub_03'));  //标签名称已经存在

            $row=$this->db->query("SELECT id FROM ".CS_SqlPrefix."page where url='".$data['url']."'")->row(); 
		     if($row) getjson(L('plub_13')); //标签URL地址已经存在
            $this->Csdb->get_insert('page',$data);
		}else{
            $this->Csdb->get_update('page',$id,$data);
		}
        $info['url'] = site_url('label/page').'?v='.rand(1000,9999);
        $info['parent'] = 1;
        getjson($info,0);
	}

    //页面标签静态生成
	public function page_html(){
        $id = intval($this->input->get_post('id'));
		$row = $this->Csdb->get_row_arr('page','url,html,name',$id);
		if($row){
            $path=str_replace("//","/",FCPATH.$row['url']);
			$this->load->model('Cstpl');
			$neir=$this->Cstpl->page($row,true);
            if (!write_file($path, $neir)){
                getjson(L('plub_14'));//生成失败，目录无权限
            }else{
                getjson('',0);
			}
		}
	}

    //页面标签删除
	public function page_del(){
        $id = $this->input->get_post('id');
		if(empty($id)) getjson(L('plub_04'));//参数错误

		//删除文件
		if(is_array($id)){
		   foreach ($id as $ids) {
			    $row=$this->db->query("SELECT sid,url FROM ".CS_SqlPrefix."page where id='".$ids."'")->row();
			    if($row && $row->sid==1){
                    $html='.'.$row->url;
			        @unlink($html);
			    }
		   }
		}else{
			    $row=$this->db->query("SELECT sid,url FROM ".CS_SqlPrefix."page where id='".$id."'")->row();
			    if($row && $row->sid==1){
                    $html='.'.$row->url;
			        @unlink($html);
			    }
		}

		$this->Csdb->get_del('page',$id);
        $info['url'] = site_url('label/page').'?v='.rand(1000,9999);
        getjson($info,0);
	}

	//批量删除数据
	public function deldata(){
	    //所有板块
        $sql_string = "SELECT dir,name FROM ".CS_SqlPrefix."plugins order by id asc";
        $query = $this->db->query($sql_string); 
	    $data['plugins']=$query->result();
        $this->load->view('label_deldata.html',$data);
	}

	//清空表数据
	public function deldata_save(){
        $dir = $this->input->post('dir',true);
        $table = $this->input->post('table_'.$dir,true);
        $ids = $this->input->post('ids',true);

		if(empty($table)) getjson(L('plub_15'));//请选择要清空的数据表

        $this->db->query("delete from ".CS_SqlPrefix.$table." ");
		//修复主键ID
		if($ids=='ok'){
            $this->db->query("TRUNCATE TABLE ".CS_SqlPrefix.$table." ");
		}
        $info['url'] = site_url('label/deldata').'?v='.rand(1000,9999);
        getjson($info,0);
	}

	//批量修改数据
	public function editdata(){
	    //所有板块
        $sql_string = "SELECT dir,name FROM ".CS_SqlPrefix."plugins order by id asc";
        $query = $this->db->query($sql_string); 
	    $data['plugins']=$query->result();
        $this->load->view('label_editdata.html',$data);
	}

	//修改数据操作
	public function editdata_save(){
        $dir = $this->input->post('dir',true);
        $table = $this->input->post('table_'.$dir,true);
        $field = $this->input->post('field',true);
        $old = $this->input->post('old',true,true);
        $new = $this->input->post('new',true,true);

		if(empty($table) || $old===FALSE) getjson(L('plub_16'));//请选择要替换的数据库表、字段、内容

        $sql="select id,".$field." from ".CS_SqlPrefix.$table." where ".$field." like '%".$old."%'";
		$result=$this->db->query($sql);
		foreach ($result->result() as $row) { 
			$newneir=str_replace($old,$new,$row->$field);
			$sql3="update ".CS_SqlPrefix.$table." set ".$field."='".$newneir."' where id=".$row->id."";
			$this->db->query($sql3);
		}
        $info['url'] = site_url('label/editdata').'?v='.rand(1000,9999);
        getjson($info,0);
	}

	//列出表字段
	public function fields(){
		$this->load->model('Csbackup');
		$table = $this->input->get('table',true);
		$table = $this->Csbackup->repair(CS_SqlPrefix.$table);
		$arr=explode('auto_increment', $table);
        if(empty($arr[1])){				
		    $arr=explode('AUTO_INCREMENT', strtoupper($table));
		}
        preg_match_all('/`([\s\S]+?)` ([\s\S]+?) COMMENT \'([\s\S]+?)\',/',$arr[1],$tarr);
		$str='<label class="layui-form-label">'.L('plub_15').'</label><div class="layui-input-inline"><select name="field">';//选择表字段
        foreach ($tarr[1] as $k=>$v) {
				$str.='<option value="'.$tarr[1][$k].'">'.$tarr[3][$k].'</option>';
		}
		$str.='</select>';
		echo $str;
	}
}