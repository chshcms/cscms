<?php if ( ! defined('IS_ADMIN')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-16
 */
class News extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
	}

	public function index(){
        $sort = $this->input->get_post('sort',true);
        $desc = $this->input->get_post('desc',true);
        $cid  = intval($this->input->get_post('cid'));
        $yid  = intval($this->input->get_post('yid'));
		$reco = intval($this->input->get_post('reco'));
        $zd   = $this->input->get_post('zd',true);
        $key  = $this->input->get_post('key',true);
	    $page = intval($this->input->get('page'));
        if($page==0) $page=1;

        $data['page'] = $page;
        $data['sort'] = $sort;
        $data['cid'] = $cid;
        $data['yid'] = $yid;
        $data['zd'] = $zd;
        $data['key'] = $key;
        $data['reco'] = $reco;
		if(empty($sort)) $sort="addtime";

        if($yid==2){
            $table= "news_verify";
        }elseif($yid==3){
            $table= "news_hui";
        }else{
            $table= "news";
        }
        
        $sql_string = "SELECT id,name,pic,hits,reco,cid,uid,addtime FROM ".CS_SqlPrefix.$table." where 1=1";
		if($cid>0){
             $sql_string.= " and cid=".$cid."";
		}
		if(!empty($key)){
			if($zd=='user'){
				$uid=getzd('user','id',$key,'name');
			    $sql_string.= " and uid='".intval($uid)."'";
			}elseif($zd=='id'){
			    $sql_string.= " and id='".intval($key)."'";
			}else{
			    $sql_string.= " and ".$zd." like '%".$key."%'";
			}
		}
		if($reco>0){
            $sql_string.= " and reco=".$reco."";
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
        $data['news'] = $query->result();

        $base_url = site_url('news/admin/news')."?yid=".$yid."&zd=".$zd."&key=".$key."&cid=".$cid."&sort=".$sort."&reco=".$reco."&page=";
        $data['page_data'] = page_data($total,$page,$totalPages); //获取分页类
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('news.html',$data);
	}
    //推荐、锁定操作
    public function init(){
        $id = intval($this->input->get_post('id'));
        $yid = intval($this->input->get_post('sign'));
        if($id==0){
            getjson('参数错误');
        }
        if($yid<2){//未审核
            $row = $this->Csdb->get_row_arr('news','*',$id);
            $row['did'] = $id;
            unset($row['id']);
            $res = $this->Csdb->get_insert('news_verify',$row);
            if($res){
                $this->Csdb->get_del('news',$id);
            }
        }else{
            $row = $this->Csdb->get_row_arr('news_verify','*',$id);
            if($row['did']==0){
                unset($row['id']);
            }else{
                $row['id'] = $row['did'];
            }
            unset($row['did']);
            $res = $this->Csdb->get_insert('news',$row);
            if($res){
                $this->Csdb->get_del('news_verify',$id);
            }
        }
        getjson('',0);
    }
    public function tj(){
        $id = intval($this->input->get_post('id'));
        $sid = intval($this->input->get_post('sid'));
        if($id==0){
            getjson('参数错误');
        }
        $edit['reco'] = $sid;
        $this->Csdb->get_update('news',$id,$edit);
        getjson('',0);
    }

    //新闻新增、修改
	public function edit(){
        $id   = intval($this->input->get('id'));
        $yid   = intval($this->input->get('yid'));
        if($yid==3){
            $table= 'news_hui';
        }elseif($yid==2){
            $table= 'news_verify';
        }else{
            $table= 'news';
        }
		if($id==0){
            $data['id']=0;
            $data['cid']=0;
            $data['tid']=0;
            $data['reco']=0;
            $data['uid']=0;
            $data['name']='';
            $data['pic']='';
            $data['info']='';
            $data['color']='';
            $data['bname']='';
            $data['cion']=0;
            $data['vip']=0;
            $data['level']=0;
            $data['tags']='';
            $data['pic2']='';
            $data['hits']=0;
            $data['yhits']=0;
            $data['zhits']=0;
            $data['rhits']=0;
            $data['dhits']=0;
            $data['chits']=0;
            $data['content']='';
            $data['skins']='';
            $data['title']='';
            $data['keywords']='';
            $data['description']='';
            $data['title2'] = '添加新闻';
		}else{
            $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix.$table." where id=".$id."")->row(); 
		    if(!$row) admin_msg('该条记录不存在~!','javascript:history.back();','no');  //记录不存在
            $data['id']=$row->id;
            $data['cid']=$row->cid;
            $data['tid']=$row->tid;
            $data['reco']=$row->reco;
            $data['uid']=$row->uid;
            $data['name']=$row->name;
            $data['pic']=$row->pic;
            $data['info']=$row->info;
            $data['color']=$row->color;
            $data['bname']=$row->bname;
            $data['cion']=$row->cion;
            $data['vip']=$row->vip;
            $data['level']=$row->level;
            $data['tags']=$row->tags;
            $data['pic2']=$row->pic2;
            $data['hits']=$row->hits;
            $data['yhits']=$row->yhits;
            $data['zhits']=$row->zhits;
            $data['rhits']=$row->rhits;
            $data['dhits']=$row->dhits;
            $data['chits']=$row->chits;
            $data['content']=$row->content;
            $data['skins']=$row->skins;
            $data['title']=$row->title;
            $data['keywords']=$row->keywords;
            $data['description']=$row->description;
            $data['title2'] = '修改新闻';
            $data['row'] = $row;
		}
        $data['yid'] = $yid;
        $this->load->view('news_edit.html',$data);
	}

    //新闻保存
	public function save(){
        $id   = intval($this->input->post('id'));
        $name = $this->input->post('name',true);
        $content = remove_xss($this->input->post('content'));
        $user = $this->input->post('user',true);
		$tags = $this->input->post('tags',true);
		$info = $this->input->post('info',true);
		$skins = $this->input->post('skins');
        $addtime = $this->input->post('addtime',true);
        $data['cid']=intval($this->input->post('cid'));
        $yid   = intval($this->input->post('yid'));

        if(empty($name)||empty($data['cid'])){
            getjson('抱歉，新闻名称、分类不能为空~!');
		}

		//自动获取TAGS标签
        if(empty($tags)){
            $tags = gettag($name,$content);
		}

        $data['tid']=intval($this->input->post('tid'));
        $data['reco']=intval($this->input->post('reco'));
        $data['uid']=intval(getzd('user','id',$user,'name'));
        $data['name']=$name;
        $data['pic']=$this->input->post('pic',true);
        if(empty($info)){
            $data['info']=sub_str($content,125);
        }else{
            $data['info']=$info;
        }
        $data['color']=$this->input->post('color',true);
        $data['bname']=$this->input->post('bname',true);
        $data['cion']=intval($this->input->post('cion'));
        $data['vip']=intval($this->input->post('vip'));
        $data['level']=intval($this->input->post('level'));
        $data['tags']=$tags;
        $data['pic2']=$this->input->post('pic2',true);
        $data['hits']=intval($this->input->post('hits'));
        $data['yhits']=intval($this->input->post('yhits'));
        $data['zhits']=intval($this->input->post('zhits'));
        $data['rhits']=intval($this->input->post('rhits'));
        $data['dhits']=intval($this->input->post('dhits'));
        $data['chits']=intval($this->input->post('chits'));
        $data['skins']=empty($skins)?'show.html':$skins;
        $data['content']=$content;
        $data['title']=$this->input->post('title',true);
        $data['keywords']=$this->input->post('keywords',true);
        $data['description']=$this->input->post('description',true);

        if($yid==3){
            $table= 'news_hui';
        }elseif($yid==2){
            $table= 'news_verify';
        }else{
            $table= 'news';
        }

		if($id==0){ //新增
			$data['addtime']=time();
            $this->Csdb->get_insert('news',$data);
		}else{
			if($yid==0) $this->dt($id);
            if($addtime=='ok') $data['addtime']=time();
            $this->Csdb->get_update($table,$id,$data);
		}
        $info2['msg'] = '恭喜您，操作成功~!';
        $info2['url'] = site_url('news/admin/news').'?yid='.$yid.'&v='.rand(1000,9999);
        getjson($info2,0);
	}

    //新闻删除
	public function del(){
        $yid = intval($this->input->get('yid'));
        $ids = $this->input->get_post('id');
        $ac = $this->input->get_post('ac');
		//回收站
		if($ac=='hui'){
		     $result=$this->db->query("SELECT id,pic,pic2 FROM ".CS_SqlPrefix."news_hui")->result();
		     $this->load->library('csup');
		     foreach ($result as $row) {
                if(!empty($row->pic)){
				    $this->csup->del($row->pic,'news'); //删除图片
                }
                if(!empty($row->pic2)){
				    $this->csup->del($row->pic2,'news'); //删除幻灯图
			    }
				$this->Csdb->get_del('news_hui',$row->id);
			}
            $info['url'] = site_url('news/admin/news').'?yid='.$yid.'&v='.rand(1000,9999);
            $info['msg'] = '恭喜您，回收站清空成功~!';
            getjson($info,0);  //操作成功
		}
		if(empty($ids)) getjson('请选择要删除的数据~!');
		if(is_array($ids)){
		     $idss=implode(',', $ids);
		}else{
		     $idss=$ids;
		}
		if($yid==3){
            $result=$this->db->query("SELECT pic,pic2 FROM ".CS_SqlPrefix."news where id in(".$idss.")")->result();
            $this->load->library('csup');
            foreach ($result as $row) {
                if(!empty($row->pic)){
                $this->csup->del($row->pic,'news'); //删除图片
                }
                if(!empty($row->pic2)){
                    $this->csup->del($row->pic2,'news'); //删除幻灯图
                }
            }
            $this->Csdb->get_del('news',$ids);
            $info['url'] = site_url('news/admin/news').'?yid=3&v='.rand(1000,9999);
            getjson($info,0);  //操作成功
		}else{
            $table = $yid==2 ? 'news_verify' : 'news';
            if(is_numeric($ids)){
                $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix.$table." where id=".$idss)->result_array();
            }else{
                $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix.$table." where id in(".$idss.")")->result_array();
            }
            foreach ($result as $row) {
                if($yid==2){
                    $row['hid'] = 1;
                }else{
                    $row['hid'] = 0;
                    $row['did'] = $row['id'];
                }
                $rowid = $row['id'];
                unset($row['id']);
                $res = $this->Csdb->get_insert('news_hui',$row);
                if($res){
                    $this->Csdb->get_del($table,$rowid);
                }
            }
		}
        $info['url'] = site_url('news/admin/news').'?yid='.$yid.'&v='.rand(100,999);
        getjson($info,0);
	}

    //新闻还原
    public function hy(){
        $ids = $this->input->get_post('id');
        if(empty($ids)) getjson('请选择要还原的数据~!');
        if(is_array($ids)){
             $idss=implode(',', $ids);
        }else{
             $idss=$ids;
        }
        if(is_numeric($ids)){
            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."news_hui where id=".$idss)->result_array();
        }else{
            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."news_hui where id in(".$idss.")")->result_array();
        }
        foreach ($result as $row) {
            $rowid = $row['id'];
            if($row['hid']==1){
                $table = 'news_verify';
                unset($row['id']);
            }else{
                $table = 'news';
                $row['id'] = $row['did'];
                unset($row['did']);
            }
            unset($row['hid']);
            $res = $this->Csdb->get_insert($table,$row);
            if($res){
                $this->Csdb->get_del('news_hui',$rowid);
            }
        }
        $info['msg'] = "恭喜您，数据还原成功~!";
        $info['url'] = site_url('news/admin/news').'?yid=3&v='.rand(1000,9999);
        getjson($info,0);
	}

    //新闻批量
	public function pledit(){
        $data['id'] = $this->input->get_post('id');
        $data['sid'] = $this->input->get_post('yid');
		$this->load->view('pl_edit.html',$data);
	}

    //批量修改操作
	public function pl_save(){
        $xid=intval($this->input->post('xid'));
        $csid=$this->input->post('csid');
        $id=$this->input->post('id',true);
	    $cids=intval($this->input->post('cids'));

	    $cid=intval($this->input->post('cid'));
	    $hid=intval($this->input->post('hid'));
	    $tid=intval($this->input->post('tid'));
	    $yid=intval($this->input->post('yid'));
	    $user=$this->input->post('user',true);
	    $reco=intval($this->input->post('reco'));
	    $cion=intval($this->input->post('cion'));
	    $vip=intval($this->input->post('vip'));
	    $hits=intval($this->input->post('hits'));
	    $yhits=intval($this->input->post('yhits'));
	    $zhits=intval($this->input->post('zhits'));
	    $rhits=intval($this->input->post('rhits'));
        $sid = intval($this->input->post('sid'));

        if(empty($csid)) getjson('请选择要操作的数据~!');

        if($xid==1){  //按ID操作
			if(empty($id)) getjson('请选择要操作的新闻ID~!');
            foreach ($csid as $v){
                if($v=="cid"){
                    $this->db->query("update ".CS_SqlPrefix."news set cid=".$cid." where id in (".$id.")");
                }elseif($v=="yid"){
                    if($yid==0 && $sid==2){//news_verify -> 通过审核
                        $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."news_verify where id in (".$id.")")->result_array();
                        foreach ($result as $key => $value) {
                            $row = $value;
                            $id2 = $row['id'];
                            if($row['did']>0){
                                $row['id'] = $row['did'];
                            }else{
                                unset($row['id']);
                            }
                            unset($row['did']);
                            $res = $this->Csdb->get_insert('news',$row);
                            if($res){
                                $this->Csdb->get_del('news_verify',$id2);
                            }
                        }
                    }
                    if($yid==1 && $sid<2){//news -> 取消审核
                        $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."news where id in (".$id.")")->result_array();
                        foreach ($result as $key => $value) {
                            $row = $value;
                            $row['did'] = $row['id'];
                            unset($row['id']);
                            $res = $this->Csdb->get_insert('news_verify',$row);
                            if($res){
                                $this->Csdb->get_del('news',$row['did']);
                            }
                        }      
                    }
                }elseif($v=="tid"){
                    $this->db->query("update ".CS_SqlPrefix."news set tid=".$tid." where id in (".$id.")");
                }elseif($v=="reco"){
                    $this->db->query("update ".CS_SqlPrefix."news set reco=".$reco." where id in (".$id.")");
                }elseif($v=="cion"){
                    $this->db->query("update ".CS_SqlPrefix."news set cion=".$cion." where id in (".$id.")");
                }elseif($v=="vip"){
                    $this->db->query("update ".CS_SqlPrefix."news set vip=".$vip." where id in (".$id.")");
                }elseif($v=="hits"){
                    $this->db->query("update ".CS_SqlPrefix."news set hits=".$hits." where id in (".$id.")");
                }elseif($v=="yhits"){
                    $this->db->query("update ".CS_SqlPrefix."news set yhits=".$yhits." where id in (".$id.")");
                }elseif($v=="zhits"){
                    $this->db->query("update ".CS_SqlPrefix."news set zhits=".$zhits." where id in (".$id.")");
                }elseif($v=="rhits"){
                    $this->db->query("update ".CS_SqlPrefix."news set rhits=".$rhits." where id in (".$id.")");
                }elseif($v=="user"){
                    $uid=intval(getzd('user','id',$user,'name'));
                    $this->db->query("update ".CS_SqlPrefix."news set uid=".$uid." where id in (".$id.")");
                }elseif($v=="hid"){
                    if($hid==2){
                        $this->Csdb->get_del('news',$id);
                    }else{
                        if($hid==1 && $sid<3){
                            $table = $sid==2 ? 'news_verify' : 'news';
                            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix.$table." where id in(".$id.")")->result_array();
                            foreach ($result as $row) {
                                if($sid==2){
                                    $row['hid'] = 1;
                                }else{
                                    $row['hid'] = 0;
                                    $row['did'] = $row['id'];
                                }
                                $rowid = $row['id'];
                                unset($row['id']);
                                $res = $this->Csdb->get_insert('news_hui',$row);
                                if($res){
                                    $this->Csdb->get_del($table,$rowid);
                                }
                            }
                        }
                        if($hid==0 && $sid==3){
                            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."news_hui where id in(".$id.")")->result_array();
                            foreach ($result as $row) {
                                $rowid = $row['id'];
                                if($row['hid']==1){
                                    $table = 'news_verify';
                                    unset($row['id']);
                                }else{
                                    $table = 'news';
                                    $row['id'] = $row['did'];
                                    unset($row['did']);
                                }
                                unset($row['hid']);
                                $res = $this->Csdb->get_insert($table,$row);
                                if($res){
                                    $this->Csdb->get_del('news_hui',$rowid);
                                }
                            }
                        }
                    }
                }
            }
		}else{ //按分类操作
			if(empty($cids)) getjson('请选择要操作的新闻分类~!');
            foreach ($csid as $v) {
                if($v=="cid"){
                    $this->db->query("update ".CS_SqlPrefix."news set cid=".$cid." where cid in (".$cids.")");
                }elseif($v=="yid"){
                    
                    if($yid==0 && $sid==2){//news_verify -> 通过审核
                        $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."news_verify where cid in (".$cids.")")->result_array();
                        foreach ($result as $key => $value) {
                            $row = $value;
                            $id2 = $row['id'];
                            if($row['did']>0){
                                $row['id'] = $row['did'];
                            }else{
                                unset($row['id']);
                            }
                            unset($row['did']);
                            $res = $this->Csdb->get_insert('news',$row);
                            if($res){
                                $this->Csdb->get_del('news_verify',$id2);
                            }
                        }
                    }
                    if($yid==1 && $sid<2){//news -> 取消审核
                        $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."news where cid in (".$cids.")")->result_array();
                        foreach ($result as $key => $value) {
                            $row = $value;
                            $row['did'] = $row['id'];
                            unset($row['id']);
                            $res = $this->Csdb->get_insert('news_verify',$row);
                            if($res){
                                $this->Csdb->get_del('news',$row['did']);
                            }
                        }      
                    }
                }elseif($v=="tid"){
                    $this->db->query("update ".CS_SqlPrefix."news set tid=".$tid." where cid in (".$cids.")");
                }elseif($v=="reco"){
                    $this->db->query("update ".CS_SqlPrefix."news set reco=".$reco." where cid in (".$cids.")");
                }elseif($v=="cion"){
                    $this->db->query("update ".CS_SqlPrefix."news set cion=".$cion." where cid in (".$cids.")");
                }elseif($v=="vip"){
                    $this->db->query("update ".CS_SqlPrefix."news set vip=".$vip." where cid in (".$cids.")");
                }elseif($v=="hits"){
                    $this->db->query("update ".CS_SqlPrefix."news set hits=".$hits." where cid in (".$cids.")");
                }elseif($v=="yhits"){
                    $this->db->query("update ".CS_SqlPrefix."news set yhits=".$yhits." where cid in (".$cids.")");
                }elseif($v=="zhits"){
                    $this->db->query("update ".CS_SqlPrefix."news set zhits=".$zhits." where cid in (".$cids.")");
                }elseif($v=="rhits"){
                    $this->db->query("update ".CS_SqlPrefix."news set rhits=".$rhits." where cid in (".$cids.")");
                }elseif($v=="user"){
                    $uid=intval(getzd('user','id',$user,'name'));
                    $this->db->query("update ".CS_SqlPrefix."news set uid=".$uid." where cid in (".$cids.")");
                }elseif($v=="hid"){
                    if($hid==2){
                        $this->Csdb->get_del('news',$cids);
                    }else{
                        if($hid==1 && $sid<3){
                            $table = $sid==2 ? 'news_verify' : 'news';
                            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix.$table." where cid in(".$cids.")")->result_array();
                            foreach ($result as $row) {
                                if($sid==2){
                                    $row['hid'] = 1;
                                }else{
                                    $row['hid'] = 0;
                                    $row['did'] = $row['id'];
                                }
                                $rowid = $row['id'];
                                unset($row['id']);
                                $res = $this->Csdb->get_insert('news_hui',$row);
                                if($res){
                                    $this->Csdb->get_del($table,$rowid);
                                }
                            }
                        }
                        if($hid==0 && $sid==3){
                            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."news_hui where cid in(".$cids.")")->result_array();
                            foreach ($result as $row) {
                                $rowid = $row['id'];
                                if($row['hid']==1){
                                    $table = 'news_verify';
                                    unset($row['id']);
                                }else{
                                    $table = 'news';
                                    $row['id'] = $row['did'];
                                    unset($row['did']);
                                }
                                unset($row['hid']);
                                $res = $this->Csdb->get_insert($table,$row);
                                if($res){
                                    $this->Csdb->get_del('news_hui',$rowid);
                                }
                            }
                        }
                    }
                }
            }
		}
		$info['url'] = site_url('news/admin/news').'?yid='.$sid.'&v='.rand(1000,9999);
        $info['parent'] = 1;
        getjson($info,0);
	}

	//审核文章增加积分、经验、同时动态显示
	public function dt($id,$sid=0){
		$dt=$this->db->query("SELECT id,yid,name FROM ".CS_SqlPrefix."dt where link='".linkurl('show','id',$id,1,'news')."'")->row();
		if($dt){
              $uid=getzd('news','uid',$id);
			  if($sid>0){ //删除回收站

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
			      //发送文章删除通知
			      $add['uida']=$uid;
			      $add['uidb']=0;
			      $add['name']='文章被删除';
			      $add['neir']='您的文章《'.$dt->name.'》被删除，系统同时扣除您'.User_Cion_Del.'个金币，'.User_Jinyan_Del.'个经验';
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
			      //发送文章审核通知
			      $add['uida']=$uid;
			      $add['uidb']=0;
			      $add['name']='文章审核通知';
			      $add['neir']='恭喜您，您的文章《'.$dt->name.'》已经审核通过，'.$str.'感谢您的支持~~';
			      $add['addtime']=time();
        	      $this->Csdb->get_insert('msg',$add);
			  }
		}
	}
}
