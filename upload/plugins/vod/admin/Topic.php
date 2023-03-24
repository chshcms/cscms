<?php if ( ! defined('IS_ADMIN')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-08
 */
class Topic extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
	}

    //专题列表
	public function index(){
        $sort = $this->input->get_post('sort',true);
        $yid  = intval($this->input->get_post('yid'));
		$tid = intval($this->input->get_post('tid'));
        $key  = $this->input->get_post('key',true);
        $page = intval($this->input->get('page'));
        if($page==0) $page=1;

        $data['page'] = $page;
        $data['sort'] = $sort;
        $data['yid'] = $yid;
        $data['key'] = $key;
        $data['tid'] = $tid;
		if(!in_array($sort,array('id','addtime','hits','rhits','zhits','yhits'))) $sort="addtime";

        $sql_string = "SELECT id,name,pic,hits,tid,yid,addtime FROM ".CS_SqlPrefix."vod_topic where 1=1";
		if($yid==1){
			 $sql_string.= " and yid=0";
		}
		if($yid==2){
			 $sql_string.= " and yid=1";
		}
		if(!empty($key)){
			 $sql_string.= " and name like '%".$key."%'";
		}
		if($tid>0){
             $sql_string.= " and tid=".($tid-1)."";
		}
        $total = $this->Csdb->get_allnums($sql_string);
        $sql_string.= " order by ".$sort." desc";

        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        $data['nums'] = $total;
        if($total<$per_page){
              $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);
        $data['topic'] = $query->result();

        $base_url = site_url('vod/admin/topic')."?yid=".$yid."&key=".$key."&sort=".$sort."&tid=".$tid."&page=";
        $data['page_data'] = page_data($total,$page,$totalPages); //获取分页类
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('topic.html',$data);
	}

    //锁定操作
	public function init($sid){
        $id   = intval($this->input->get_post('id'));
        $sign  = intval($this->input->get_post('sign'));
        if($id==0){
            getjson('参数错误');
        }
        if($sid=='yid') $edit['yid'] = $sign?0:1;
        if($sid=='tid') $edit['tid'] = $sign?0:1;
        
        $this->Csdb->get_update('vod_topic',$id,$edit);
        getjson('',0);
	}

    //专题新增、修改
	public function edit(){
        $id = intval($this->input->get('id'));
		if($id==0){
            $data['id']=0;
            $data['yid']=0;
            $data['tid']=0;
            $data['name']='';
            $data['pic']='';
            $data['toppic']='';
            $data['neir']='';
            $data['bname']='';
            $data['hits']=0;
            $data['yhits']=0;
            $data['zhits']=0;
            $data['rhits']=0;
            $data['skins']='topic-show.html';
            $data['title']='';
            $data['keywords']='';
            $data['description']='';
            $data['title2'] = '增加视频专题';
		}else{
            $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix."vod_topic where id=".$id."")->row(); 
		    if(!$row) admin_info('该条记录不存在~!');  //记录不存在

            $data['id']=$row->id;
            $data['yid']=$row->yid;
            $data['tid']=$row->tid;
            $data['name']=$row->name;
            $data['pic']=$row->pic;
            $data['toppic']=$row->toppic;
            $data['neir']=$row->neir;
            $data['bname']=$row->bname;
            $data['hits']=$row->hits;
            $data['yhits']=$row->yhits;
            $data['zhits']=$row->zhits;
            $data['rhits']=$row->rhits;
            $data['skins']=$row->skins;
            $data['title']=$row->title;
            $data['keywords']=$row->keywords;
            $data['description']=$row->description;
            $data['title2'] = '修改视频专题';
            $data['row'] = $row;
		}
        $data['table'] = 'vod_topic';
        $this->load->view('topic_edit.html',$data);
	}

    //专题保存
	public function save(){
        $id   = intval($this->input->post('id'));
        $addtime = $this->input->post('addtime',true);
        $data['yid']=intval($this->input->post('yid'));
        $data['tid']=intval($this->input->post('tid'));
        $data['name']=$this->input->post('name',true);
        $data['pic']=$this->input->post('pic',true);
        $data['toppic']=$this->input->post('toppic',true);
        $data['neir']=remove_xss($this->input->post('neir'));
        $data['bname']=$this->input->post('bname',true);
        $data['hits']=intval($this->input->post('hits'));
        $data['yhits']=intval($this->input->post('yhits'));
        $data['zhits']=intval($this->input->post('zhits'));
        $data['rhits']=intval($this->input->post('rhits'));
        $data['skins']=$this->input->post('skins',true);
        $data['title']=$this->input->post('title',true);
        $data['keywords']=$this->input->post('keywords',true);
        $data['description']=$this->input->post('description',true);
		if($addtime=='ok') $data['addtime']=time();

        if(empty($data['name'])){
            getjson('抱歉，专题名称不能为空~!');
		}

		if($id==0){ //新增
            $this->Csdb->get_insert('vod_topic',$data);
		}else{
            $this->Csdb->get_update('vod_topic',$id,$data);
		}
        $info['url'] = site_url('vod/admin/topic').'?v='.rand(100,999);
        getjson($info,0);
	}

    //专题删除
	public function del(){
        $ids = $this->input->get_post('id');
		if(empty($ids)) getjson('请选择要删除的数据~!');
		if(is_array($ids)){
            $idss=implode(',', $ids);
		}else{
            $idss=$ids;
		}
		$result=$this->db->query("SELECT pic,toppic FROM ".CS_SqlPrefix."vod_topic where id in(".$idss.")")->result();
		$this->load->library('csup');
		foreach ($result as $row) {
            if(!empty($row->pic)){
                $this->csup->del($row->pic,'vodtopic'); //删除图片
            }
            if(!empty($row->toppic)){
                $this->csup->del($row->toppic,'vodtopic'); //删除顶部图
            }
		}
		$this->Csdb->get_del('vod_topic',$ids);
        $info['url'] = site_url('vod/admin/topic').'?v='.rand(100,999);
        getjson($info,0);
	}
}

