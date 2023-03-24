<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-09-20
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Links extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
		$this->lang->load('admin_links');
        $this->Csadmin->Admin_Login();
	}

	public function index(){
        $sid  = intval($this->input->get_post('sid'));
        $cid  = intval($this->input->get_post('cid'));
        $key  = str_replace('%','',$this->input->get_post('key',true));
	    $page = intval($this->input->get('page'));
        if($page==0) $page=1;
        $data['sid'] = $sid;
        $data['key'] = $key;
        $data['cid'] = $cid;
        $sql_string = "SELECT * FROM ".CS_SqlPrefix."link where 1=1";
		if($cid>0){
            $sql_string.= " and cid=".$cid."";
		}
		if($sid>0){
            if($sid==2){
                $sid = 0;
            }
            $sql_string.= " and sid=".$sid."";
		}
		if(!empty($key)){
             $sql_string.= " and name like '%".$key."%'";
		}
        $sql_string.= " order by id desc";
        $query = $this->db->query($sql_string); 
        $total = $query->num_rows();

        $base_url = site_url('links')."?cid=".$cid."&sid=".$sid."&page=";
        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        if($totalPages<1) $totalPages = 1;
        if($page > $totalPages) $page = $totalPages;
        if($total < $per_page){
              $per_page = $total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);
        $data['links'] = $query->result();
        $data['nums'] = $total;
        $data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('links.html',$data);
	}

    //设置主页是否显示
	public function init(){
        $sid  = intval($this->input->get_post('sign'));
        $id   = intval($this->input->get_post('id'));
        if($sid==0){
            $edit['sid'] = 1;
        }else{
            $edit['sid'] = 0;
        }
        $this->Csdb->get_update('link',$id,$edit);
        getjson('',0);
	}

    //新增
	public function add(){
	    $data['id']=0;
	    $data['sid']=1;
	    $data['cid']=1;
	    $data['name']='';
	    $data['url']='http://';
	    $data['pic']='http://';
	    $this->load->view('links_edit.html',$data);
	}

    //修改
	public function edit(){
        $id   = intval($this->input->get('id'));
        $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix."link where id=".$id."")->row(); 
		if(!$row) admin_msg(L('plub_03'),site_url('links'),'no');  //记录不存在

        $data['id']=$row->id;
        $data['sid']=$row->sid;
        $data['cid']=$row->cid;
        $data['name']=$row->name;
        $data['url']=$row->url;
        $data['pic']=$row->pic;

        $this->load->view('links_edit.html',$data);
	}

    //入库
	public function save(){
        $id=intval($this->input->post('id'));
        $data['sid']=intval($this->input->post('sid'));
        $data['cid']=intval($this->input->post('cid'));
        $data['name']=$this->input->post('name',true);
        $data['url']=$this->input->post('url',true);
        $data['pic']=$this->input->post('pic',true);

		if(empty($data['name']) || empty($data['url'])){
            getjson(L('plub_01'));//地址不能为空
        }
		if($data['cid']==2 && empty($data['pic'])){
            getjson(L('plub_02'));//LOGO不能为空
        }
		if($id==0){ //新增
            $this->Csdb->get_insert('link',$data);
		}else{
			$row=$this->db->query("SELECT pic FROM ".CS_SqlPrefix."link where id=".$id."")->row();
			if(!empty($row->pic) && $row->pic!=$data['pic']){
				$this->load->library('csup');
                $this->csup->del($row->pic,'links'); //删除原附件
			}
            $this->Csdb->get_update('link',$id,$data);
		}
        $info['url'] = site_url('links/index').'?v='.rand(1000,9999);
        $info['parent'] = 1;
        getjson($info,0);
	}

    //删除
	public function del(){
        $ids = $this->input->get_post('id');
        if(empty($ids)){
            getjson(L('plub_03'));//删除项不存在
        }
		if(is_array($ids)){
            $idss=implode(',', $ids);
		}else{
            $idss=$ids;
		}
		$result=$this->db->query("SELECT pic FROM ".CS_SqlPrefix."link where id in(".$idss.")")->result();
		$this->load->library('csup');
		foreach ($result as $row) {
            if(!empty($row->pic)){
			    $this->csup->del($row->pic,'links'); //删除附件
            }
		}
		$this->Csdb->get_del('link',$ids);
        $info['url'] = site_url('links').'?v='.rand(1000,9999);
        getjson($info,0);
	}
}
