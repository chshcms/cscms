<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-20
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Blog extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
		$this->lang->load('admin_blog');
        $this->Csadmin->Admin_Login();
	}

    //说说列表
	public function index(){
        $zd   = $this->input->get_post('zd',true);
        $key  = $this->input->get_post('key',true,true);
	        $page = intval($this->input->get('page'));
        if($page==0) $page=1;

        $data['page'] = $page;
        $data['zd'] = $zd;
        $data['key'] = $key;

        $sql_string = "SELECT * FROM ".CS_SqlPrefix."blog where 1=1";
		if(!empty($key)){
			 if($zd=='name'){
                 $uid=getzd('user','id',$key,'name');
			 }else{
                 $uid=$key;
			 }
			 $sql_string.= " and uid=".intval($uid)."";
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
        $data['blog'] = $query->result();

        $base_url = site_url('blog')."?zd=".$zd."&key=".$key."&page=";
        $data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('blog.html',$data);
	}

    //删除说说
    public function del(){
        $ids = $this->input->get_post('id');
        if(empty($ids)){
            getjson(L('plub_01'));
        }
        if(is_array($ids)){
            $this->db->where('dir','blog');
            $this->db->where_in('did',$ids);
            $this->db->delete('dt');
        }else{
            $this->db->where('dir','blog');
            $this->db->where('did',$ids);
            $this->db->delete('dt');
        }
        $this->Csdb->get_del('blog',$ids);
        $info['url'] = site_url('blog').'?v='.rand(1000,9999);
        getjson($info,0);
    }
}

