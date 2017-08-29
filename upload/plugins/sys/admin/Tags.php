<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-08-01
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Tags extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
		$this->lang->load('admin_tags');
	}

    //TAG标签列表
	public function index(){
        $sql_string = "SELECT * FROM ".CS_SqlPrefix."tags where fid=0 order by xid asc";
        $query = $this->db->query($sql_string); 
        $data['tags'] = $query->result();
        $this->load->view('tags.html',$data);
	}

    //新增标签
	public function add_save(){
        $data['fid']=intval($this->input->get_post('fid', TRUE));
        $data['xid']=intval($this->input->get_post('xid', TRUE));
	    $data['name']=$this->input->get_post('name', TRUE);

        if(empty($data['name'])){
            getjson(L('plub_01'));
		}

		$this->Csdb->get_insert('tags',$data);
        $info['url'] = site_url('tags');
        getjson($info,0);
	}

    //批量修改
	public function save(){
        $ids=$this->input->post('id', TRUE); 
        if(empty($ids)){
            getjson(L('plub_03'));
		}
		foreach ($ids as $id) {
            $data['name']=$this->input->post('name_'.$id, TRUE);
            $data['xid']=intval($this->input->post('xid_'.$id, TRUE)); 
            $this->Csdb->get_update('tags',$id,$data);
		}
        $info['url'] = site_url('tags');
        $info['msg'] = L('plub_02');
        getjson($info,0);
	}

    //删除标签
	public function del(){
        $fid=intval($this->input->get_post('fid', TRUE));
        $id=intval($this->input->get_post('id', TRUE));

        if($fid>0){
		    $this->db->query("delete from ".CS_SqlPrefix."tags where fid=".$id."");
		}
		$this->Csdb->get_del('tags',$id);
		if($fid==1){
			$info['turn'] = 1;
		}
        $info['url'] = site_url('tags');
        $info['msg'] = L('plub_02');
        getjson($info,0);
	}

}

