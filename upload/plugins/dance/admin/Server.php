<?php if ( ! defined('IS_ADMIN')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-16
 */
class Server extends Cscms_Controller {
	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
	}
    //歌曲服务器组
	public function index(){
        $sql_string = "SELECT * FROM ".CS_SqlPrefix."dance_server order by id asc";
        $query = $this->db->query($sql_string); 
        $data['dance_server'] = $query->result();
        $this->load->view('dance_server.html',$data);
	}
	//新增服务器组
	public function add(){
		$id = (int)$this->input->get_post('id');
		$data['name'] = '';
		$data['purl'] = '';
		$data['durl'] = '';
		if($id!=0){
			$server = $this->db->query("select * from ".CS_SqlPrefix."dance_server where id=".$id)->row();
			if(!empty($server)){
				$data['name'] = $server->name;
				$data['purl'] = $server->purl;
				$data['durl'] = $server->durl;
                $data['row'] = $server;
			}
		}
		$data['id'] = $id;
        $data['table'] = 'dance_server';
        $this->load->view('dance_server_add.html',$data);
	}
    //批量修改
	public function plsave(){
        $ids=$this->input->post('id', TRUE); 
        if(empty($ids)){
        	getjson('请选择要操作的数据~!');
		}
		foreach ($ids as $id) {
            $data['name']=$this->input->post('name_'.$id, TRUE);
            $data['purl']=$this->input->post('purl_'.$id, TRUE);
            $data['durl']=$this->input->post('durl_'.$id, TRUE);
            $this->Csdb->get_update('dance_server',$id,$data);
		}
		$info['msg'] = '恭喜您，操作成功~!';
		$info['url'] = site_url('dance/admin/server').'?v='.rand(1000,9999);
        getjson($info,0);
	}

    //批量转移分类
	public function zhuan(){
        $ids = $this->input->get_post('id', TRUE);
        $cid = intval($this->input->get_post('cid')); 
        if(empty($ids)){
            getjson('请选择要操作的数据~!');
		}
        if($cid==0){
        	getjson('请选择目标分类~!');
		}
		$ids=implode(',', $ids);
        $this->db->query("update ".CS_SqlPrefix."dance set fid=".$fid." where fid in (".$ids.")");
        $info['msg'] = '恭喜您，操作成功~!';
		$info['url'] = site_url('dance/admin/server').'?v='.rand(1000,9999);
        getjson($info,0);
	}

    //新增保存
	public function save(){
        $data['name']=$this->input->get_post('name',true);
        $data['purl']=$this->input->get_post('purl',true);
        $data['durl']=$this->input->get_post('durl',true);
        $id = (int)$this->input->get_post('id',true);
        if($id==0){
        	$this->Csdb->get_insert('dance_server',$data);
        }else{
        	$this->Csdb->get_update('dance_server',$id,$data);
        }
        $info['msg'] = '恭喜您，操作成功~!';
		$info['url'] = site_url('dance/admin/server').'?v='.rand(1000,9999);
		$info['parent'] = 1;
        getjson($info,0);
	}

    //删除
	public function del(){
        $ids = $this->input->get_post('id');
		if(empty($ids)) getjson('请选择要删除的数据');
		if(is_array($ids)){
		    $idss=implode(',', $ids);
		}else{
		    $idss=$ids;
		}
		$this->Csdb->get_del('dance_server',$ids);
		$info['url'] = site_url('dance/admin/server').'?v='.rand(1000,9999);
        getjson($info,0);
	}
}


