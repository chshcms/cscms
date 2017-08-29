<?php if ( ! defined('IS_ADMIN')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-03
 */
class Type extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
	}

    //视频剧情
	public function index(){
        $sql_string = "SELECT id,name FROM ".CS_SqlPrefix."vod_list where fid=0 order by xid asc";
        $query = $this->db->query($sql_string); 
        $data['vod_type'] = $query->result();
        $this->load->view('vod_type.html',$data);
	}

    //显示、隐藏操作
	public function init(){
        $id   = intval($this->input->get_post('id'));
        $sid  = intval($this->input->get_post('sign'));
        if($id==0){
            getjson('参数错误');
        }
        $edit['yid'] = $sid?0:1;
        $this->Csdb->get_update('vod_type',$id,$edit);
        getjson('',0);
	}

    //批量修改
	public function plsave(){
        $ids=$this->input->post('id', TRUE); 
        if(empty($ids)){
            getjson('请选择要操作的数据~!');
		}
		foreach ($ids as $id) {
            $data['name']=$this->input->post('name_'.$id, TRUE);
            $data['xid']=intval($this->input->post('xid_'.$id, TRUE)); 
            $this->Csdb->get_update('vod_type',$id,$data);
		}
		$info['url'] = site_url('vod/admin/type').'?v='.rand(100,999);
        getjson($info,0);
	}

    //剧情新增、修改
	public function edit(){
        $id   = intval($this->input->get('id'));
        $cid  = intval($this->input->get('cid'));
		if($id==0){
            $data['id']=0;
            $data['cid']=$cid;
            $data['yid']=0;
            $data['xid']=0;
            $data['name']='';
		}else{
            $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix."vod_type where id=".$id."")->row(); 
		    if(!$row) admin_info('该条记录不存在~!');  //记录不存在
            $data['id']=$row->id;
            $data['cid']=$row->cid;
            $data['yid']=$row->yid;
            $data['xid']=$row->xid;
            $data['name']=$row->name;
            $data['row'] = $row;
		}
        $data['table'] = 'vod_type';
        $this->load->view('type_edit.html',$data);
	}

    //剧情保存
	public function save(){
        $id   = intval($this->input->post('id'));
        $data['yid']=intval($this->input->post('yid'));
        $data['cid']=intval($this->input->post('cid'));
        $data['xid']=intval($this->input->post('xid'));
        $data['name']=$this->input->post('name',true);

		if($id==0){ //新增
             $this->Csdb->get_insert('vod_type',$data);
		}else{
             $this->Csdb->get_update('vod_type',$id,$data);
		}
		$info['url'] = site_url('vod/admin/type').'?v='.rand(100,999);
		$info['parent'] = 1;
		getjson($info,0);
	}

    //剧情删除
	public function del(){
        $ids = $this->input->get_post('id');
		if(empty($ids)) getjson('请选择要删除的数据~!');
		if(is_array($ids)){
		     $idss=implode(',', $ids);
		}else{
		     $idss=$ids;
		}
		$this->Csdb->get_del('vod_type',$ids);
		$info['url'] = site_url('vod/admin/type').'?v='.rand(100,999);
		getjson($info,0);
	}
}

