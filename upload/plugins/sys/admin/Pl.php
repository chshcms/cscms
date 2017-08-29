<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-20
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Pl extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
		$this->lang->load('admin_pl');
        $this->Csadmin->Admin_Login();
	}

    //评论列表
	public function index(){
        $dir  = $this->input->get_post('dir',true);
        $zd   = $this->input->get_post('zd',true);
        $key  = $this->input->get_post('key',true);
	        $page = intval($this->input->get('page'));
        if($page==0) $page=1;

        $data['page'] = $page;
        $data['dir'] = $dir;
        $data['zd'] = $zd;
        $data['key'] = $key;

        $sql_string = "SELECT * FROM ".CS_SqlPrefix."pl where 1=1";
		if(!empty($dir)){
             $sql_string.= " and dir='".$dir."'";
		}
		if(!empty($key)){
			 if($zd=='did'){
			     $sql_string.= " and did=".intval($key)."";
			 }else{
			     if($zd=='name'){
                     $sql_string.= " and user='".$key."'";
			     }else{
                     $sql_string.= " and uid=".intval($key)."";
			     }
			 }
		}

        $sql_string.= " order by addtime desc";
        $query = $this->db->query($sql_string); 
        $total = $query->num_rows();

        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        $data['nums'] = $total;
        if($total<$per_page){
              $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);
        $data['pl'] = $query->result();

        $base_url = site_url('pl')."?zd=".$zd."&dir=".$dir."&key=".$key."&page=";
        $data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('pl.html',$data);
	}

    //删除评论
	public function del(){
        $id = $this->input->get_post('id',true);
		if(empty($id)) getjson(L('plub_01'));
        $this->Csdb->get_del('pl',$id);
        $info['url'] = site_url('pl').'?v='.rand(1000,9999);
        getjson($info,0);
	}
}

