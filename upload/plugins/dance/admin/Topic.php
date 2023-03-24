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

    //专辑列表
	public function index(){
        $sort = $this->input->get_post('sort',true);
        $zd = $this->input->get_post('zd',true);
        $yid  = intval($this->input->get_post('yid'));
		$tid = intval($this->input->get_post('tid'));
        $key  = $this->input->get_post('key',true);
	    $page = intval($this->input->get('page'));
        if($page==0) $page=1;

        $data['page'] = $page;
        $data['sort'] = $sort;
        $data['zd'] = $zd;
        $data['yid'] = $yid;
        $data['key'] = $key;
        $data['tid'] = $tid;
        if(!in_array($sort,array('id','addtime','hits','rhits','zhits','yhits'))) $sort="addtime";

        $sql_string = "SELECT id,name,pic,hits,tid,yid,singerid,addtime FROM ".CS_SqlPrefix."dance_topic where 1=1";
		if($yid==1){
			 $sql_string.= " and yid=0";
		}
		if($yid==2){
			 $sql_string.= " and yid=1";
		}
		if(!empty($key)){
			if($zd=='user'){
				$uid=getzd('user','id',$key,'name');
			    $sql_string.= " and uid='".intval($uid)."'";
			}elseif($zd=='singer'){
				$singerid=getzd('singer','id',$key,'name');
			    $sql_string.= " and singerid='".intval($singerid)."'";
			}else{
			    $sql_string.= " and ".$zd." like '%".$key."%'";
			}
		}
		if($tid>0){
             $sql_string.= " and tid=".($tid-1)."";
		}
        $total = $this->Csdb->get_allnums($sql_string);
        $sql_string.= " order by ".$sort." desc";

        $per_page = 15; 
        $totalPages = ceil($total / $per_page)?ceil($total / $per_page):1; // 总页数
        $page = ($page>$totalPages)?$totalPages:$page;
        $data['nums'] = $total;
        if($total<$per_page){
            $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);
        $data['topic'] = $query->result();

        $base_url = site_url('dance/admin/topic')."?yid=".$yid."&key=".$key."&sort=".$sort."&tid=".$tid."&page=";
       	$data['page_data'] = page_data($total,$page,$totalPages); //获取分页类
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('topic.html',$data);
	}

    //推荐、锁定操作
	public function init($ac){
        $id   = intval($this->input->get_post('id'));
        $sid  = intval($this->input->get_post('sign'));
        if($ac=='zt'){ //锁定
            $edit['yid'] = $sid?0:1;
            if($sid==1) $this->dt($id);
		}elseif($ac=='tj'){  //推荐
            $edit['tid'] = $sid?0:1;
		}
        $this->Csdb->get_update('dance_topic',$id,$edit);
        getjson('',0);
	}

    //专辑新增、修改
	public function edit(){
        $id = intval($this->input->get('id'));
		if($id==0){
            $data['id']=0;
            $data['yid']=0;
            $data['cid']=0;
            $data['tid']=0;
            $data['name']='';
            $data['pic']='';
            $data['neir']='';
            $data['hits']=0;
            $data['yhits']=0;
            $data['zhits']=0;
            $data['rhits']=0;
            $data['shits']=0;
            $data['tags']='';
            $data['color']='';
            $data['singerid']=0;
            $data['uid']=0;
            $data['diqu']="大陆";
            $data['yuyan']="国语";
            $data['year']=date('Y');
            $data['fxgs']='';
            $data['skins']='topic-show.html';
            $data['title']='';
            $data['keywords']='';
            $data['description']='';
            $data['title2'] = '新增歌曲专辑';
		}else{
            $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix."dance_topic where id=".$id."")->row(); 
		    if(!$row) exit('记录不存在');

            $data['id']=$row->id;
            $data['yid']=$row->yid;
            $data['cid']=$row->cid;
            $data['tid']=$row->tid;
            $data['name']=$row->name;
            $data['pic']=$row->pic;
            $data['neir']=$row->neir;
            $data['hits']=$row->hits;
            $data['yhits']=$row->yhits;
            $data['zhits']=$row->zhits;
            $data['rhits']=$row->rhits;
            $data['shits']=$row->shits;
            $data['tags']=$row->tags;
            $data['color']=$row->color;
            $data['singerid']=$row->singerid;
            $data['uid']=$row->uid;
            $data['diqu']=$row->diqu;
            $data['yuyan']=$row->yuyan;
            $data['year']=$row->year;
            $data['fxgs']=$row->fxgs;
            $data['skins']=$row->skins;
            $data['title']=$row->title;
            $data['keywords']=$row->keywords;
            $data['description']=$row->description;
            $data['title2'] = '修改歌曲专辑';
            $data['row'] = $row;
		}
        $data['table'] = 'dance_topic';
        $this->load->view('topic_edit.html',$data);
	}

    //专辑保存
	public function save(){
        $id   = intval($this->input->post('id'));
        $user = $this->input->post('user',true);
        $singer = $this->input->post('singer',true);
        $addtime = $this->input->post('addtime',true);

        $data['yid']=intval($this->input->post('yid'));
        $data['tid']=intval($this->input->post('tid'));
        $data['cid']=intval($this->input->post('cid'));
        $data['name']=$this->input->post('name',true);
        $data['pic']=$this->input->post('pic',true);
        $data['neir']=remove_xss($this->input->post('neir'));
        $data['hits']=intval($this->input->post('hits'));
        $data['yhits']=intval($this->input->post('yhits'));
        $data['zhits']=intval($this->input->post('zhits'));
        $data['rhits']=intval($this->input->post('rhits'));
        $data['shits']=intval($this->input->post('shits'));
        $data['tags']=$this->input->post('tags',true);
        $data['color']=$this->input->post('color',true);
        $data['singerid']=intval(getzd('singer','id',$singer,'name'));
        $data['uid']=intval(getzd('user','id',$user,'name'));
        $data['neir']=remove_xss($this->input->post('neir'));
        $data['diqu']=$this->input->post('diqu',true);
        $data['yuyan']=$this->input->post('yuyan',true);
        $data['year']=$this->input->post('year',true);
        $data['fxgs']=$this->input->post('fxgs',true);
        $data['skins']=$this->input->post('skins',true);
        $data['title']=$this->input->post('title',true);
        $data['keywords']=$this->input->post('keywords',true);
        $data['description']=$this->input->post('description',true);

        if(empty($data['name'])){
        	getjson('抱歉，专辑名称不能为空~!');
		}
		if($data['singerid']==0 && $singer!=''){
			$datas['name'] = $singer;
			$datas['addtime'] = time();
			$data['singerid'] = (int)$this->Csdb->get_insert('singer',$datas);
		}
		if($id==0){ //新增
			$data['addtime']=time();
			$this->Csdb->get_insert('dance_topic',$data);
		}else{
			if($data['tid']==0) $this->dt($id);
			if($addtime=='ok') $data['addtime']=time();
			$this->Csdb->get_update('dance_topic',$id,$data);
		}
        $info['url'] = site_url('dance/admin/topic').'?v='.rand(1000,9999);
        getjson($info,0);
	}

    //专辑删除
	public function del(){
        $ids = $this->input->get_post('id');
		if(empty($ids)) getjson('请选择要删除的数据~!');
		if(is_array($ids)){
		    $idss=implode(',', $ids);
		}else{
		    $idss=$ids;
		}
		$result=$this->db->query("SELECT id,pic FROM ".CS_SqlPrefix."dance_topic where id in(".$idss.")")->result();
		$this->load->library('csup');
		foreach ($result as $row) {
            if(!empty($row->pic)){
				$this->csup->del($row->pic,'dancetopic'); //删除图片
			}
			  //删除金币和经验
			$this->dt($row->id,1);
		}
		$this->Csdb->get_del('dance_topic',$ids);
        $info['url'] = site_url('dance/admin/topic').'?v='.rand(1000,1999);
        getjson($info,0);
	}

	//审核专辑增加积分、经验、同时动态显示
	public function dt($id,$sid=0){
		$dt=$this->db->query("SELECT id,name,yid FROM ".CS_SqlPrefix."dt where link='".linkurl('topic/show','id',$id,1,'dance')."'")->row();
		if($dt){
              $uid=getzd('dance_topic','uid',$id);
			  if($sid>0){ //删除

				  $str='';
				  if(User_Jinyan_Del>0){
				      $jinyan=getzd('user','jinyan',$uid);
					  if( User_Jinyan_Del <= $jinyan){
						  $str['jinyan']=$jinyan-User_Jinyan_Del;
					  }
				  }
				  if(User_Cion_Del>0){
				      $cion=getzd('user','cion',$uid);
					  if( User_Jinyan_Del <= $jinyan){
						  $str['cion']=$cion-User_Cion_Del;
					  }
				  }
				  if($str!=''){
		              $this->Csdb->get_update('user',$uid,$str);
				  }
			      //发送删除通知
			      $add['uida']=$uid;
			      $add['uidb']=0;
			      $add['name']='专辑被删除';
			      $add['neir']='您的专辑《'.$dt->name.'》被删除，系统同时扣除您'.User_Cion_Del.'个金币，'.User_Jinyan_Del.'个经验';
			      $add['addtime']=time();
        	      $this->Csdb->get_insert('msg',$add);
				  //删除动态
			      $this->Csdb->get_del('dt',$dt->id);

			  }elseif($dt->yid==1){ //审核

		          $addhits=getzd('user','addhits',$uid);
			      $str='';
			      if($addhits<User_Nums_Add){
                     $this->db->query("update ".CS_SqlPrefix."user set cion=cion+".User_Cion_Add.",jinyan=jinyan+".User_Jinyan_Add.",addhits=addhits+1 where id=".$uid."");
				     $str.="同时为您增加".User_Cion_Add."个金币，".User_Jinyan_Add."个经验值，";
			      }
                  $this->db->query("update ".CS_SqlPrefix."dt set yid=0,addtime='".time()."' where id=".$dt->id."");
			      //发送审核通知
			      $add['uida']=$uid;
			      $add['uidb']=0;
			      $add['name']=L('plub_102');
			      $add['neir']='恭喜您，您的专辑《'.$dt->name.'》已经审核通过，'.$str.'感谢您的支持~~';
			      $add['addtime']=time();
        	      $this->Csdb->get_insert('msg',$add);
			  }
		}
	}
}

