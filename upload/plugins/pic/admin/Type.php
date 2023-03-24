<?php if ( ! defined('IS_ADMIN')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-02-25
 */
class Type extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
	}

	public function index(){
        $sort = $this->input->get_post('sort',true);
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
		if(!in_array($sort,array('id','addtime','hits','rhits','zhits','yhits'))) $sort="addtime";

		if($yid==2){
            $table= "pic_type_verify";
        }elseif($yid==3){
            $table= "pic_type_hui";
        }else{
            $table= "pic_type";
        }
        $sql_string = "SELECT id,name,pic,hits,rhits,reco,singerid,uid,cid,addtime FROM ".CS_SqlPrefix.$table." where 1=1";
		if($cid>0){
            $sql_string.= " and cid=".$cid."";
		}
		if(!empty($key)){
			if($zd=='user'){
				$uid=getzd('user','id',$key,'name');
			    $sql_string.= " and uid='".intval($uid)."'";
			}elseif($zd=='singer'){
				$singerid=getzd('singer','id',$key,'name');
			    $sql_string.= " and singerid='".intval($singerid)."'";
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

        $per_page = 8; 
        $totalPages = ceil($total / $per_page); // 总页数
        $data['nums'] = $total;
        if($total<$per_page){
              $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);
        $data['pic'] = $query->result();

        $base_url = site_url('pic/admin/type')."?yid=".$yid."&zd=".$zd."&key=".$key."&cid=".$cid."&sort=".$sort."&reco=".$reco."&page=";
        $data['page_data'] = page_data($total,$page,$totalPages); //获取分页类
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('pic_type.html',$data);
	}

    public function init(){
        $id = intval($this->input->get_post('id'));
        $sid = intval($this->input->get_post('sign'));
        
        if($sid<2){//未审核
            $row = $this->Csdb->get_row_arr('pic_type','*',$id);
            $row['did'] = $id;
            unset($row['id']);
            $res = $this->Csdb->get_insert('pic_type_verify',$row);
            if($res){
                $this->Csdb->get_del('pic_type',$id);
            }
        }else{
            $row = $this->Csdb->get_row_arr('pic_type_verify','*',$id);
            if($row['did']==0){
                unset($row['id']);
            }else{
                $row['id'] = $row['did'];
            }
            unset($row['did']);
            $res = $this->Csdb->get_insert('pic_type',$row);
            if($res){
                $this->Csdb->get_del('pic_type_verify',$id);
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
        $this->Csdb->get_update('pic_type',$id,$edit);
        getjson('',0);
    }
    //相册新增、修改
	public function edit(){
        $id = intval($this->input->get('id'));
        $yid = intval($this->input->get('yid'));
        if($yid==3){
            $table= 'pic_type_hui';
        }elseif($yid==2){
            $table= 'pic_type_verify';
        }else{
            $table= 'pic_type';
        }
		if($id==0){
            $data['id']=0;
            $data['cid']=0;
            $data['reco']=0;
            $data['uid']=0;
            $data['name']='';
            $data['bname']='';
            $data['pic']='';
            $data['tags']='';
            $data['hits']=0;
            $data['yhits']=0;
            $data['zhits']=0;
            $data['rhits']=0;
            $data['dhits']=0;
            $data['chits']=0;
            $data['singerid']=0;
            $data['skins']='show.html';
            $data['title']='';
            $data['keywords']='';
            $data['description']='';
            $data['title2'] = '添加相册';
		}else{
            $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix.$table." where id=".$id."")->row(); 
		    if(!$row) admin_info('该条记录不存在！');  //记录不存在

            $data['id']=$row->id;
            $data['cid']=$row->cid;
            $data['reco']=$row->reco;
            $data['uid']=$row->uid;
            $data['name']=$row->name;
            $data['bname']=$row->bname;
            $data['pic']=$row->pic;
            $data['tags']=$row->tags;
            $data['hits']=$row->hits;
            $data['yhits']=$row->yhits;
            $data['zhits']=$row->zhits;
            $data['rhits']=$row->rhits;
            $data['dhits']=$row->dhits;
            $data['chits']=$row->chits;
            $data['singerid']=$row->singerid;
            $data['skins']=$row->skins;
            $data['title']=$row->title;
            $data['keywords']=$row->keywords;
            $data['description']=$row->description;
            $data['title2'] = '修改相册';
            $data['row'] = $row;
		}
        $data['table'] = 'pic_type';
        $data['yid'] = $yid;
        $this->load->view('pic_type_edit.html',$data);
	}

    //相册保存
	public function save(){
	    $id   = intval($this->input->post('id'));
	    $name = $this->input->post('name',true);
	    $user = $this->input->post('user',true);
	    $singer = $this->input->post('singer',true);
		$tags = $this->input->post('tags',true);
	    $addtime = $this->input->post('addtime',true);
	    $data['cid']=intval($this->input->post('cid'));
		$yid   = intval($this->input->post('yid'));

	    if(empty($name)||empty($data['cid'])){
	        getjson('抱歉，相册名称、分类不能为空！');
		}
		//自动获取TAGS标签
	    if(empty($tags)){
		    $tags = gettag($name);
		}

		if($yid==3){
            $table= 'pic_type_hui';
        }elseif($yid==2){
            $table= 'pic_type_verify';
        }else{
            $table= 'pic_type';
        }
	    $data['reco']=intval($this->input->post('reco'));
	    $data['uid']=intval(getzd('user','id',$user,'name'));
	    $data['name']=$name;
	    $data['bname']=$this->input->post('bname',true);
	    $data['pic']=$this->input->post('pic',true);
	    $data['tags']=$tags;
	    $data['hits']=intval($this->input->post('hits'));
	    $data['yhits']=intval($this->input->post('yhits'));
	    $data['zhits']=intval($this->input->post('zhits'));
	    $data['rhits']=intval($this->input->post('rhits'));
	    $data['dhits']=intval($this->input->post('dhits'));
	    $data['chits']=intval($this->input->post('chits'));
	    $data['skins']=$this->input->post('skins',true);
	    $data['title']=$this->input->post('title',true);
	    $data['keywords']=$this->input->post('keywords',true);
	    $data['description']=$this->input->post('description',true);

		if($this->db->table_exists(CS_SqlPrefix.'singer')){  //歌手表存在
	        $data['singerid']=intval(getzd('singer','id',$singer,'name'));
		}

		if($id==0){ //新增
			$data['addtime']=time();
	        $this->Csdb->get_insert('pic_type',$data);
		}else{
			if($yid==0) $this->dt($id);
	        if($addtime=='ok') $data['addtime']=time();
	        $this->Csdb->get_update($table,$id,$data);
		}
		$info['url'] = site_url('pic/admin/type').'?yid='.$yid.'&v='.rand(100,999);
	    getjson($info,0);
	}

    //相册删除
	public function del(){
        $yid = intval($this->input->get('yid'));
        $ids = $this->input->get_post('id');
        $ac = $this->input->get_post('ac');
		//回收站
		if($ac=='hui'){
		     $result=$this->db->query("SELECT id,pic FROM ".CS_SqlPrefix."pic_type_hui")->result();
		     $this->load->library('csup');
		     foreach ($result as $row) {
				 //删除相册图片
				$result2=$this->db->query("SELECT id,pic FROM ".CS_SqlPrefix."pic where sid=".$row->id."")->result();
		        foreach ($result2 as $row2) {
                    if(!empty($row2->pic)){
				        $this->csup->del($row2->pic,'pic'); //删除图片
			        }
				    $this->Csdb->get_del('pic',$row2->id);
				}
                if(!empty($row->pic)){
				    $this->csup->del($row->pic,'pic'); //删除相册
			    }
				$this->Csdb->get_del('pic_type_hui',$row->id);
			}
			$info['url'] = site_url('pic/admin/type').'?yid=3&v='.rand(100,999);
			$info['msg'] = '恭喜您，回收站清空成功！';
			getjson($info,0);
		}
		if(empty($ids)) getjson('请选择要删除的数据！');
		if(is_array($ids)){
		    $idss=implode(',', $ids);
		}else{
		    $idss=$ids;
		}
		if($yid==3){
			$result=$this->db->query("SELECT pic FROM ".CS_SqlPrefix."pic_type_hui where id in(".$idss.")")->result();
			$this->load->library('csup');
			foreach ($result as $row) {
			 	//删除相册图片
				$result2=$this->db->query("SELECT id,pic FROM ".CS_SqlPrefix."pic where sid=".$row->id."")->result();
				foreach ($result2 as $row2) {
				    if(!empty($row2->pic)){
				        $this->csup->del($row2->pic,'pic'); //删除图片
				    }
				    $this->Csdb->get_del('pic',$row2->id);
				}
				if(!empty($row->pic)){
				    $this->csup->del($row->pic,'pic'); //删除图片
				}
			}
			$this->Csdb->get_del('pic_type_hui',$ids);
			$info['msg'] = '恭喜你，操作成功';
			$info['url'] = site_url('pic/admin/type').'?yid=3&v='.rand(100,999);
			getjson($info,0);
		}else{
			$table = $yid==2 ? 'pic_type_verify' : 'pic_type';
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
                $res = $this->Csdb->get_insert('pic_type_hui',$row);
                if($res){
                    $this->Csdb->get_del($table,$rowid);
                }
            }
		}
		$info['url'] = site_url('pic/admin/type').'?yid='.$yid.'&v='.rand(100,999);
        getjson($info,0);
	}

    //相册还原
	public function hy(){
        $ids = $this->input->get_post('id');
		if(empty($ids)) getjson('请选择要还原的数据！');
		if(is_array($ids)){
		    $idss=implode(',', $ids);
		}else{
		    $idss=$ids;
		}
		if(is_numeric($ids)){
            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."pic_type_hui where id=".$idss)->result_array();
        }else{
            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."pic_type_hui where id in(".$idss.")")->result_array();
        }
        foreach ($result as $row) {
            $rowid = $row['id'];
            if($row['hid']==1){
                $table = 'pic_type_verify';
                unset($row['id']);
            }else{
                $table = 'pic_type';
                $row['id'] = $row['did'];
                unset($row['did']);
            }
            unset($row['hid']);
            $res = $this->Csdb->get_insert($table,$row);
            if($res){
                $this->Csdb->get_del('pic_type_hui',$rowid);
            }
        }
        $info['msg'] = "恭喜您，数据还原成功~!";
        $info['url'] = site_url('pic/admin/type').'?yid=3&v='.rand(1000,9999);
        getjson($info,0);
	}

    //相册批量
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
	    $yid=intval($this->input->post('yid'));
	    $user=$this->input->post('user',true);
	    $singer=$this->input->post('singer',true);
	    $skins=$this->input->post('skins',true);
	    $reco=intval($this->input->post('reco'));
	    $hits=intval($this->input->post('hits'));
	    $yhits=intval($this->input->post('yhits'));
	    $zhits=intval($this->input->post('zhits'));
	    $rhits=intval($this->input->post('rhits'));
	    $sid = intval($this->input->post('sid'));

        if(empty($csid)) getjson('请选择要操作的数据！');
        if($xid==1){  //按ID操作
			if(empty($id)) getjson('请选择要操作的相册ID！');
            foreach ($csid as $v){
                if($v=="cid"){
				    $this->db->query("update ".CS_SqlPrefix."pic_type set cid=".$cid." where id in (".$id.")");
                }elseif($v=="yid"){
					
					if($yid==0 && $sid==2){//pic_type_verify -> 通过审核
                        $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."pic_type_verify where id in (".$id.")")->result_array();
                        foreach ($result as $key => $value) {
                            $row = $value;
                            $id2 = $row['id'];
                            if($row['did']>0){
                                $row['id'] = $row['did'];
                            }else{
                                unset($row['id']);
                            }
                            unset($row['did']);
                            $res = $this->Csdb->get_insert('pic_type',$row);
                            if($res){
                                $this->Csdb->get_del('pic_type_verify',$id2);
                            }
                        }
                    }
                    if($yid==1 && $sid<2){//pic_type -> 取消审核
                        $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."pic_type where id in (".$id.")")->result_array();
                        foreach ($result as $key => $value) {
                            $row = $value;
                            $row['did'] = $row['id'];
                            unset($row['id']);
                            $res = $this->Csdb->get_insert('pic_type_verify',$row);
                            if($res){
                                $this->Csdb->get_del('pic_type',$row['did']);
                            }
                        }      
                    }

                }elseif($v=="reco"){
			        $this->db->query("update ".CS_SqlPrefix."pic_type set reco=".$reco." where id in (".$id.")");
                }elseif($v=="hits"){
			        $this->db->query("update ".CS_SqlPrefix."pic_type set hits=".$hits." where id in (".$id.")");
				}elseif($v=="yhits"){
				  	$this->db->query("update ".CS_SqlPrefix."pic_type set yhits=".$yhits." where id in (".$id.")");
				}elseif($v=="zhits"){
				  	$this->db->query("update ".CS_SqlPrefix."pic_type set zhits=".$zhits." where id in (".$id.")");
				}elseif($v=="rhits"){
				  	$this->db->query("update ".CS_SqlPrefix."pic_type set rhits=".$rhits." where id in (".$id.")");
				}elseif($v=="skins"){
				  	$this->db->query("update ".CS_SqlPrefix."pic_type set skins='".$skins."' where id in (".$id.")");
				}elseif($v=="user"){
				  	$uid=intval(getzd('user','id',$user,'name'));
				  	$this->db->query("update ".CS_SqlPrefix."pic_type set uid=".$uid." where id in (".$id.")");
				}elseif($v=="singer"){
				  	$singerid=intval(getzd('singer','id',$singer,'name'));
				  	$this->db->query("update ".CS_SqlPrefix."pic_type set singerid=".$singerid." where id in (".$id.")");
				}elseif($v=="hid"){
			        if($hid==2){
                        $this->Csdb->get_del('pic',$id);
			        }else{
						if($hid==1 && $sid<3){
                            $table = $sid==2 ? 'pic_type_verify' : 'pic_type';
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
                                $res = $this->Csdb->get_insert('pic_type_hui',$row);
                                if($res){
                                    $this->Csdb->get_del($table,$rowid);
                                }
                            }
                        }
                        if($hid==0 && $sid==3){
                            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."pic_type_hui where id in(".$id.")")->result_array();
                            foreach ($result as $row) {
                                $rowid = $row['id'];
                                if($row['hid']==1){
                                    $table = 'pic_type_verify';
                                    unset($row['id']);
                                }else{
                                    $table = 'pic_type';
                                    $row['id'] = $row['did'];
                                    unset($row['did']);
                                }
                                unset($row['hid']);
                                $res = $this->Csdb->get_insert($table,$row);
                                if($res){
                                    $this->Csdb->get_del('pic_type_hui',$rowid);
                                }
                            }
                        }
			        }
		        }
		   	}
		}else{ //按分类操作
		   if(empty($cids)) getjson('请选择要操作的相册分类！');
           foreach ($csid as $v) {
				if($v=="cid"){
				  $this->db->query("update ".CS_SqlPrefix."pic_type set cid=".$cid." where cid in (".$cids.")");
				}elseif($v=="yid"){

					if($yid==0 && $sid==2){//pic_type_verify -> 通过审核
                        $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."pic_type_verify where cid in (".$cids.")")->result_array();
                        foreach ($result as $key => $value) {
                            $row = $value;
                            $id2 = $row['id'];
                            if($row['did']>0){
                                $row['id'] = $row['did'];
                            }else{
                                unset($row['id']);
                            }
                            unset($row['did']);
                            $res = $this->Csdb->get_insert('pic_type',$row);
                            if($res){
                                $this->Csdb->get_del('pic_type_verify',$id2);
                            }
                        }
                    }
                    if($yid==1 && $sid<2){//pic_type -> 取消审核
                        $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."pic_type where cid in (".$cids.")")->result_array();
                        foreach ($result as $key => $value) {
                            $row = $value;
                            $row['did'] = $row['id'];
                            unset($row['id']);
                            $res = $this->Csdb->get_insert('pic_type_verify',$row);
                            if($res){
                                $this->Csdb->get_del('pic_type',$row['did']);
                            }
                        }      
                    }

				}elseif($v=="reco"){
				  $this->db->query("update ".CS_SqlPrefix."pic_type set reco=".$reco." where cid in (".$cids.")");
				}elseif($v=="hits"){
				  $this->db->query("update ".CS_SqlPrefix."pic_type set hits=".$hits." where cid in (".$cids.")");
				}elseif($v=="yhits"){
				  $this->db->query("update ".CS_SqlPrefix."pic_type set yhits=".$yhits." where cid in (".$cids.")");
				}elseif($v=="zhits"){
				  $this->db->query("update ".CS_SqlPrefix."pic_type set zhits=".$zhits." where cid in (".$cids.")");
				}elseif($v=="rhits"){
				  $this->db->query("update ".CS_SqlPrefix."pic_type set rhits=".$rhits." where cid in (".$cids.")");
				}elseif($v=="skins"){
				  $this->db->query("update ".CS_SqlPrefix."pic_type set skins='".$skins."' where cid in (".$cids.")");
				}elseif($v=="user"){
				  	$uid=intval(getzd('user','id',$user,'name'));
			        $this->db->query("update ".CS_SqlPrefix."pic_type set uid=".$uid." where cid in (".$cids.")");
		        }elseif($v=="singer"){
			        $singerid=intval(getzd('singer','id',$singer,'name'));
			        $this->db->query("update ".CS_SqlPrefix."pic_type set singerid=".$singerid." where cid in (".$cids.")");
		        }elseif($v=="hid"){
			        if($hid==2){
                        $this->Csdb->get_del('pic',$cids);
			        }else{
			            if($hid==1 && $sid<3){
                            $table = $sid==2 ? 'pic_type_verify' : 'pic_type';
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
                                $res = $this->Csdb->get_insert('pic_type_hui',$row);
                                if($res){
                                    $this->Csdb->get_del($table,$rowid);
                                }
                            }
                        }
                        if($hid==0 && $sid==3){
                            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."pic_type_hui where cid in(".$cids.")")->result_array();
                            foreach ($result as $row) {
                                $rowid = $row['id'];
                                if($row['hid']==1){
                                    $table = 'pic_type_verify';
                                    unset($row['id']);
                                }else{
                                    $table = 'pic_type';
                                    $row['id'] = $row['did'];
                                    unset($row['did']);
                                }
                                unset($row['hid']);
                                $res = $this->Csdb->get_insert($table,$row);
                                if($res){
                                    $this->Csdb->get_del('pic_type_hui',$rowid);
                                }
                            }
                        }
			        }
		        }
			}
		}
		$info['url'] = site_url('pic/admin/type').'?yid='.$sid.'&v='.rand(100,999);
		$info['msg'] = '恭喜你，批量修改成功';
		$info['parent'] = 1;
		getjson($info,0);
	}

	//审核相册增加积分、经验、同时动态显示
	public function dt($id,$sid=0){
		$dt=$this->db->query("SELECT id,yid,name FROM ".CS_SqlPrefix."dt where link='".linkurl('show','id',$id,1,'pic')."'")->row();
		if($dt){
              $uid=getzd('pic_type','uid',$id);
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
			      //发送图片删除通知
			      $add['uida']=$uid;
			      $add['uidb']=0;
			      $add['name']='相册被删除';
			      $add['neir']='您的相册《'.$dt->name.'》被删除，系统同时扣除您'.User_Cion_Del.'个金币，'.User_Jinyan_Del.'个经验';
			      $add['addtime']=time();
        	      $this->Csdb->get_insert('msg',$add);
				  //删除动态
			      $this->Csdb->get_del('dt',$dt->id);

			  }elseif($dt->yid==1){ //审核

		          $addhits=getzd('user','addhits',$uid);
			      $str='';
			      if($addhits<User_Nums_Add){
                     $this->db->query("update ".CS_SqlPrefix."user set cion=cion+".User_Cion_Add.",jinyan=jinyan+".User_Jinyan_Add.",addhits=addhits+1 where id=".$uid."");
				     $str.='<div class="jiangcheng">系统奖励：金币<span class="cg">'.User_Cion_Add.'</span>　经验<span class="cg">'.User_Jinyan_Add.'</span></div>';
			      }
                  $this->db->query("update ".CS_SqlPrefix."dt set yid=0,addtime='".time()."' where id=".$dt->id."");
			      //发送图片审核通知
			      $add['uida']=$uid;
			      $add['uidb']=0;
			      $add['name']='相册审核成功通知';
			      $add['neir']='恭喜您，新建的相册 <b>['.$dt->name.']</b> 符合标准，审核通过，赶快去上传图片吧！'.$str.'';
			      $add['addtime']=time();
        	      $this->Csdb->get_insert('msg',$add);
			  }
		}
	}
}

