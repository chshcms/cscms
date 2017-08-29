<?php if ( ! defined('IS_ADMIN')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-16
 */
class Lists extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
	}

    //歌手分类
	public function index(){
        $sql_string = "SELECT * FROM ".CS_SqlPrefix."singer_list where fid=0 order by xid asc";
        $query = $this->db->query($sql_string); 
        $data['singer_list'] = $query->result();
        $this->load->view('singer_list.html',$data);
	}

    //显示、隐藏操作
	public function init(){
        $id   = intval($this->input->get_post('id'));
        $sid  = intval($this->input->get_post('sign'));
        $edit['yid'] = 1;
        if($sid){
            $edit['yid'] = 0;
        }
        $this->Csdb->get_update('singer_list',$id,$edit);
        getjson('',0);
	}

    //批量修改分类
	public function plsave(){
        $ids=$this->input->post('id', TRUE); 
        if(empty($ids)){
            getjson('请选择要操作的数据~!');
		}
		foreach ($ids as $id) {
            $data['name']=$this->input->post('name_'.$id, TRUE);
            $data['bname']=$this->input->post('bname_'.$id, TRUE);
            $data['skins']=$this->input->post('skins_'.$id, TRUE);
            $data['xid']=intval($this->input->post('xid_'.$id, TRUE)); 
            $this->Csdb->get_update('singer_list',$id,$data);
		}
        $info['url'] = site_url('singer/admin/lists').'?v='.rand(100,999);
        $info['msg'] = '恭喜你，修改成功';
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
        $this->db->query("update ".CS_SqlPrefix."singer set cid=".$cid." where cid in (".$ids.")");
        $info['msg'] = '恭喜您，操作成功~!';
        $info['url'] = site_url('singer/admin/lists').'?v='.rand(100,999);
        getjson($info,0);
	}

    //分类新增、修改
	public function edit(){
        $id   = intval($this->input->get('id'));
        $fid  = intval($this->input->get('fid'));
		if($id==0){
            $data['id']=0;
            $data['fid']=$fid;
            $data['yid']=0;
            $data['xid']=0;
            $data['name']='';
            $data['bname']='';
            $data['skins']='list.html';
            $data['title']='';
            $data['keywords']='';
            $data['description']='';
		}else{
            $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix."singer_list where id=".$id."")->row(); 
		    if(!$row) admin_info('该条记录不存在~!');  //记录不存在

            $data['id']=$row->id;
            $data['fid']=$row->fid;
            $data['yid']=$row->yid;
            $data['xid']=$row->xid;
            $data['name']=$row->name;
            $data['bname']=$row->bname;
            $data['skins']=$row->skins;
            $data['title']=$row->title;
            $data['keywords']=$row->keywords;
            $data['description']=$row->description;
            $data['row'] = $row;
		}
        $data['table'] = 'singer_list';
        $this->load->view('list_edit.html',$data);
	}

    //分类保存
	public function save(){
        $id   = intval($this->input->post('id'));
        $data['yid']=intval($this->input->post('yid'));
        $data['fid']=intval($this->input->post('fid'));
        $data['xid']=intval($this->input->post('xid'));
        $data['name']=$this->input->post('name',true);
        $data['bname']=$this->input->post('bname',true);
        $data['skins']=$this->input->post('skins',true);
        $data['title']=$this->input->post('title',true);
        $data['keywords']=$this->input->post('keywords',true);
        $data['description']=$this->input->post('description',true);

		if($id==0){ //新增
             $this->Csdb->get_insert('singer_list',$data);
		}else{
             $this->Csdb->get_update('singer_list',$id,$data);
		}
        $info['msg'] = '恭喜您，操作成功~!';
        $info['url'] = site_url('singer/admin/lists').'?v='.rand(100,999);
        $info['parent'] = 1;
        getjson($info,0);
	}

    //分类删除
	public function del(){
        $ids = $this->input->get_post('id');
		if(empty($ids)) getjson('请选择要删除的数据~!');
		if(is_array($ids)){
            $idss=implode(',', $ids);
		}else{
            $idss=$ids;
		}
		$this->Csdb->get_del('singer_list',$ids,'fid');
		$this->Csdb->get_del('singer_list',$ids);
        $info['url'] = site_url('singer/admin/lists').'?v='.rand(100,999);
        getjson($info,0);
	}
}

