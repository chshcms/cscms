<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-20
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Gbook extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
		$this->lang->load('admin_gbook');
        $this->Csadmin->Admin_Login();
	}

    //留言列表
	public function index(){
        $op  = $this->input->get_post('op',true);
        $zd   = $this->input->get_post('zd',true);
        $key  = $this->input->get_post('key',true);
        $page = intval($this->input->get('page'));
        if($page==0) $page=1;

        $data['page'] = $page;
        $data['op'] = $op;
        $data['zd'] = $zd;
        $data['key'] = $key;

        $sql_string = "SELECT * FROM ".CS_SqlPrefix."gbook where 1=1";
		if($op=='web'){
             $sql_string.= " and cid=1";
		}
		if($op=='user'){
             $sql_string.= " and cid=0";
		}
		if(!empty($key)){
			 if($zd=='name'){
                 $uid=getzd('user','id',$key,'name');
			 }else{
                 $uid=$key;
			 }
			 $sql_string.= " and (uida=".intval($uid)." or uidb=".intval($uid).")";
		}

        $sql_string.= " order by addtime desc";
        $count_sql = str_replace('*','count(*) as count',$sql_string);
        $query = $this->db->query($count_sql)->result_array();
        $total = $query[0]['count'];

        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        $data['nums'] = $total;
        if($total<$per_page){
              $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);
        $data['gbook'] = $query->result();

        $base_url = site_url('gbook')."?zd=".$zd."&op=".$op."&key=".$key."&page=";
       	$data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('gbook.html',$data);
	}

    //回复留言
	public function hf(){
        $id = intval($this->input->get('id'));
		if($id==0) exit(L('plub_01'));
        $row=$this->db->query("SELECT neir FROM ".CS_SqlPrefix."gbook where fid=".$id."")->row();
		$data['neir']=($row)?$row->neir:'';
		$data['id']=$id;
        $this->load->view('gbook_hf.html',$data);
	}

    //回复留言入库
	public function save(){
        $data['fid'] = intval($this->input->post('fid'));
        $data['neir']= $this->input->post('neir',true);
        $data['addtime'] = time();
        $data['cid'] =1;
		if($data['fid']==0 || empty($data['neir'])) getjson(L('plub_02'));
        $row=$this->db->query("SELECT id FROM ".CS_SqlPrefix."gbook where cid=1 and fid=".$data['fid']."")->row();
		if($row){
            $this->Csdb->get_update('gbook',$row->id,$data);
		}else{
            $this->Csdb->get_insert('gbook',$data);
		}
		$info['url'] = site_url('gbook').'?v='.rand(1000,9999);
		$info['parent'] = 1;
		getjson($info,0);
	}

    //删除留言
	public function del(){
        $id = $this->input->get_post('id',true);
		if(empty($id)) getjson(L('plub_03'));
        $this->Csdb->get_del('gbook',$id,'fid');
		$this->Csdb->get_del('gbook',$id);
		$info['url'] = site_url('gbook').'?v='.rand(1000,9999);
		getjson($info,0);
	}
}

