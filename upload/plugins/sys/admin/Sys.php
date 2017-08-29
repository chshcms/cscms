<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-08-01
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Sys extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
		$this->lang->load('admin_sys');
	}

    //管理员列表
	public function index(){
        $sid = intval($this->input->get('sid', TRUE)); 
        $this->load->library('ip');
		if($sid>0){
            $sql_string = "SELECT * FROM ".CS_SqlPrefix."admin where sid='$sid' order by id desc";
		}else{
            $sql_string = "SELECT * FROM ".CS_SqlPrefix."admin order by id desc";
		}
        $query = $this->db->query($sql_string);
        $data['nums'] = $query->num_rows();
        $data['admin'] = $query->result();
        $this->load->view('admin_index.html',$data);
	}

    //新增管理员
	public function add(){
	    $data['adminname'] = '';
	    $data['adminpass'] = '';
	    $data['sid'] = 0;
	    $data['id'] = 0;
		$data['title'] = L('plub_01');
        $this->load->view('admin_edit.html',$data);
	}

    //修改管理员
	public function edit($id=0){
        $id=intval($id); 
        $row = $this->db->query("SELECT id,adminname,adminpass,sid FROM ".CS_SqlPrefix."admin where id='$id'")->row(); 
		if(!$row) getjson(L('plub_02'));
	    $data['adminname'] = $row->adminname;
	    $data['adminpass'] = $row->adminpass;
	    $data['id'] = $row->id;
	    $data['sid'] = $row->sid;
		$data['title'] = L('plub_03');
        $this->load->view('admin_edit.html',$data);
	}

    //管理员入库
	public function save(){
        $id=intval($this->input->post('id', TRUE)); 
	    $adminpass=$this->input->post('adminpass', TRUE);
	    $data['adminname']=$this->input->post('adminname', TRUE);
	    $data['sid']=intval($this->input->post('sid', TRUE)); 

        if(empty($data['adminname']) || $data['sid']==0){
            getjson(L('plub_04'));
		}
		if($id==0){ //新增
        	 if(empty($adminpass)){
                getjson(L('plub_05'));
			 }
			 $this->load->helper('string');
			 $data['admincode']=random_string('alnum',6);
             $data['adminpass']=md5(md5($adminpass).$data['admincode']);
             $this->Csdb->get_insert('admin',$data);
		}else{
			$row = $this->db->query("SELECT admincode FROM ".CS_SqlPrefix."admin where id='$id'")->row();
			if(!empty($adminpass)){
                $data['adminpass']=md5(md5($adminpass).$row->admincode);
			}
            $this->Csdb->get_update('admin',$id,$data);
		}
		$info['url'] = site_url('sys/index').'?v='.rand(1000,9999);
		$info['msg'] = L('plub_06');
		$info['parent'] = 1;
        getjson($info,0);
	}

    //角色列表
	public function zu(){
        $sql_string = "SELECT * FROM ".CS_SqlPrefix."adminzu order by id asc";
        $query = $this->db->query($sql_string); 
        $data['nums'] = $query->num_rows();
        $data['adminzu'] = $query->result();
        $this->load->view('admin_zu.html',$data);
	}

    //新增角色
	public function zu_add(){
	    $data['name']='';
	    $data['sys']='';
	    $data['id']=0;
		$data['title']=L('plub_01');
        $this->load->view('admin_edit_zu.html',$data);
	}

    //修改角色
	public function zu_edit($id=0){
        $id=intval($id); 
        $row = $this->db->query("SELECT id,name,sys FROM ".CS_SqlPrefix."adminzu where id='$id'")->row(); 
		if(!$row) admin_msg(L('plub_02'),site_url('sys'),'no');
	    $data['name']=$row->name;
	    $data['sys']=$row->sys;
	    $data['id']=$row->id;
		$data['title']=L('plub_03');
        $this->load->view('admin_edit_zu.html',$data);
	}

    //角色入库
	public function zu_save(){
        $id=intval($this->input->post('id', TRUE)); 
	    $data['name']=$this->input->post('name', TRUE);
	    $data['sys']=$this->input->post('sys', TRUE);
	    if(!empty($data['sys'])){
             $data['sys']=implode(',', $data['sys']);
		}else{
             $data['sys']='';
		}
        if(empty($data['name'])){
            getjson(L('plub_07'));
		}
		if($id==0){ //新增
	         $data['sys']='';
	         $data['app']='';
             $this->Csdb->get_insert('adminzu',$data);
		}else{
             $this->Csdb->get_update('adminzu',$id,$data);
		}
        $info['url'] = site_url('sys/zu').'?v='.rand(1000,9999);
        $info['msg'] =L('plub_06');
        if($id<2){
        	$info['parent'] = 1;
        }
        getjson($info,0);
	}

    //修改密码
	public function editpass(){
        $this->load->view('admin_edit_pass.html');
	}

    //保存密码
	public function editpass_save(){
	    $adminpass=$this->input->post('adminpass', TRUE);
	    $row = $this->db->query("SELECT admincode FROM ".CS_SqlPrefix."admin where id='".$_SESSION['admin_id']."'")->row();
	    $data['adminname'] = $this->input->post('adminname', TRUE);
        if(empty($data['adminname'])){
        	getjson(L('plub_08'),1);
		}
		if(!empty($adminpass)){
	        $data['adminpass'] = md5(md5($adminpass).$row->admincode);
		}else{
			getjson(L('plub_09'),1);
		}
        $this->Csdb->get_update('admin',$_SESSION['admin_id'],$data);
        $info['url'] = site_url('sys/editpass');
        getjson($info,0);
	}

    //电子口令卡
	public function card($id=0){
	    $id = intval($id);
	    $tips = L('plub_10');
	    if(CS_Safe_Card==0){
           	$tips = L('plub_11');
		}else{
			if($id==0){
				$tips = L('plub_12');
			}else{
				$this->load->library('card');
		        $row = $this->db->query("SELECT card,adminname FROM ".CS_SqlPrefix."admin where id='".$id."'")->row(); 
				$data['picurl']='';
		        if(!empty($row->card)){
				    $data['picurl']=$this->card->pic($row->card,$row->adminname);
				}
			} 
		}
		$data['id']=$id;
		$data['tips'] = $tips;
        $this->load->view('admin_card.html',$data);
	}

    //修改口令卡
	public function card_add($id=0){
	    $id=intval($id);
        if($id==0) getjson(L('plub_12'));
		$row = $this->db->query("SELECT adminname FROM ".CS_SqlPrefix."admin where id='".$id."'")->row(); 
        $this->load->library('card');
        $card=$this->card->add($row->adminname);
		//修改口令卡
        $updata['card'] = $card;
        $this->Csdb->get_update('admin',$id,$updata);
        $info['url'] = site_url('sys/card/'.$id).'?v='.rand(1000,9999);
        $info['msg'] = L('plub_13');
        getjson($info,0);
	}

    //删除口令卡
	public function card_del($id=0){
	    $id=intval($id);
        if($id==0) getjson(L('plub_12'));
		$row = $this->db->query("SELECT adminname FROM ".CS_SqlPrefix."admin where id='".$id."'")->row(); 
        $this->load->library('card');
        $str=$this->card->del($row->adminname);

		//删除口令卡
        $updata['card'] = '';
        $this->Csdb->get_update('admin',$id,$updata);
        $info['url'] = site_url('sys/card/'.$id).'?v='.rand(1000,9999);
        $info['msg'] = L('plub_14');
        getjson($info,0);
	}

    //登录日志
	public function log($id=0){
	    $id=intval($id);
        $sort = $this->input->get_post('sort',true);
        $desc = $this->input->get_post('desc',true);
		if(empty($sort)) $sort="id";
		if(empty($desc)) $desc="desc";

	    //删除三个月以前的登录日志记录
		$times = time()-86400*90;
		$this->db->query("delete from ".CS_SqlPrefix."admin_log where logintime<".$times."");
        
        $this->load->library('ip');
	    $page = intval($this->input->get('page'));
        if($page==0) $page=1;

        if($id>0){
             $sql_string = "SELECT * FROM ".CS_SqlPrefix."admin_log where uid=".$id." order by ".$sort." ".$desc;
		}else{
             $sql_string = "SELECT * FROM ".CS_SqlPrefix."admin_log order by ".$sort." ".$desc;
		}
        $query = $this->db->query($sql_string); 
        $total = $query->num_rows();

        $base_url = ($id==0)?site_url('sys/log')."?sort=".$sort."&desc=".$desc:site_url('sys/log/'.$id)."?sort=".$sort."&desc=".$desc;
        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        $data['nums'] = $total;
        if($total<$per_page){
              $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);

        $data['log'] = $query->result();
        $url = site_url('sys/log').'?sort='.$sort."&desc=".$desc.'&page=';
        $data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($url,$page,$totalPages); //获取分页类

        $this->load->view('admin_log.html',$data);
	}

    //管理员删除
	public function del($id=0){
	    $id=intval($id);
		if($id==$_SESSION['admin_id']){
            getjson(L('plub_15'));
		}
		$this->Csdb->get_del('admin',$id);
        getjson('',0);
	}

    //角色删除
	public function zu_del($id=0){
	    $id=intval($id);
		if($id==1){
            getjson(L('plub_15'));
		}
		$this->Csdb->get_del('admin',$id,'sid'); //删除该角色下的所有成员
		$this->Csdb->get_del('adminzu',$id);
        getjson('',0);
	}
}

