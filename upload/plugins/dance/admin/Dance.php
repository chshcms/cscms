<?php if ( ! defined('IS_ADMIN')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-16
 */
class Dance extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
	}

	public function index(){
        $sort = $this->input->get_post('sort',true);
        $cid  = intval($this->input->get_post('cid'));
        $fid  = intval($this->input->get_post('fid'));
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
        $data['fid'] = $fid;
        $data['zd'] = $zd;
        $data['key'] = $key;
        $data['reco'] = $reco;
		if(!in_array($sort,array('id','addtime','hits','rhits','zhits','yhits'))) $sort="addtime";

        if($yid==2){
            $table= "dance_verify";
        }elseif($yid==3){
            $table= "dance_hui";
        }else{
            $table= "dance";
        }

        $sql_string = "SELECT id,name,pic,hits,reco,singerid,cid,uid,addtime FROM ".CS_SqlPrefix.$table." where 1=1";
		if($fid>0){
			 $sql_string.= " and fid=".$fid."";
		}
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
        $sql_string.= " order by ".$sort." desc";
        $total = $this->Csdb->get_allnums($sql_string);
        $per_page = 15; 
        $totalPages = ceil($total / $per_page)?ceil($total / $per_page):1; // 总页数
        $page = ($page>$totalPages)?$totalPages:$page;
        $data['nums'] = $total;
        if($total<$per_page){
            $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);
        $data['dance'] = $query->result();

        $base_url = site_url('dance/admin/dance')."?yid=".$yid."&zd=".$zd."&key=".$key."&cid=".$cid."&fid=".$fid."&sort=".$sort."&reco=".$reco."&page=";
        $data['page_data'] = page_data($total,$page,$totalPages); //获取分页类
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('dance.html',$data);
	}

    public function init($ac){
        $id = intval($this->input->get_post('id'));
        $sign = intval($this->input->get_post('sign'));
        if($id==0 || $ac==''){
            getjson('参数错误');
        }
        if($ac == 'yid'){
            if($sign == 2){ //审核
                $row = $this->Csdb->get_row_arr('dance_verify','*',$id);
                $this->dt($row['id'],'dance_verify');
                if($row['did']==0){
                    unset($row['id']);
                }else{
                	$row['id'] = $row['did'];
                }
                unset($row['did']);
                $res = $this->Csdb->get_insert('dance',$row);
                if($res){
                    $this->Csdb->get_del('dance_verify',$id);
                }
            }else{  //未审核
                $row = $this->Csdb->get_row_arr('dance','*',$id);
                $row['did'] = $id;
                unset($row['id']);
                $res = $this->Csdb->get_insert('dance_verify',$row);
                if($res){
                    $this->Csdb->get_del('dance',$id);
                }
            }
            getjson('',0);
        }elseif($ac=='zt'){
            $edit[$ac] = $sign;
        }else{
            if($sign == 1){
                $edit[$ac] = 0;
            }else{
                $edit[$ac] = 1;
            }
        }
        $this->Csdb->get_update('dance',$id,$edit);
        getjson('',0);
    }

    public function tj(){
        $id = intval($this->input->get_post('id'));
        $sid = intval($this->input->get_post('sid'));
        if($id==0){
            getjson('参数错误');
        }
        $edit['reco'] = $sid;
        $this->Csdb->get_update('dance',$id,$edit);
        getjson('',0);
    }

    //歌曲新增、修改
    public function edit(){
        $id   = intval($this->input->get('id'));
        $sid   = intval($this->input->get('sid'));
        if($id==0){
            $data['title2'] = '新增歌曲';
            $data['id']=0;
            $data['cid']=0;
            $data['tid']=0;
            $data['reco']=0;
            $data['uid']=0;
            $data['fid']=0;
            $data['name']='';
            $data['pic']='';
            $data['lrc']='';
            $data['color']='';
            $data['cion']=0;
            $data['vip']=0;
            $data['level']=0;
            $data['tags']='';
            $data['hits']=0;
            $data['yhits']=0;
            $data['zhits']=0;
            $data['rhits']=0;
            $data['dhits']=0;
            $data['chits']=0;
            $data['shits']=0;
            $data['xhits']=0;
            $data['zc']='';
            $data['zq']='';
            $data['bq']='';
            $data['hy']='';
            $data['singerid']=0;
            $data['dx']='';
            $data['yz']='';
            $data['sc']='';
            $data['text']='';
            $data['purl']='';
            $data['durl']='';
            $data['wpurl']='';
            $data['wppass']='';
            $data['skins']='play.html';
            $data['title']='';
            $data['keywords']='';
            $data['description']='';
        }else{
            if($sid==3){
                $table= 'dance_hui';
            }elseif($sid==2){
                $table= 'dance_verify';
            }else{
                $table= 'dance';
            }
            $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix.$table." where id=".$id."")->row(); 
            if(!$row) exit('抱歉,记录不存在~!');
            $data['title2'] = '修改歌曲';
            $data['id']=$row->id;
            $data['cid']=$row->cid;
            $data['tid']=$row->tid;
            $data['fid']=$row->fid;
            $data['reco']=$row->reco;
            $data['uid']=$row->uid;
            $data['name']=$row->name;
            $data['pic']=$row->pic;
            $data['lrc']=$row->lrc;
            $data['color']=$row->color;
            $data['cion']=$row->cion;
            $data['vip']=$row->vip;
            $data['level']=$row->level;
            $data['tags']=$row->tags;
            $data['hits']=$row->hits;
            $data['yhits']=$row->yhits;
            $data['zhits']=$row->zhits;
            $data['rhits']=$row->rhits;
            $data['dhits']=$row->dhits;
            $data['chits']=$row->chits;
            $data['shits']=$row->shits;
            $data['xhits']=$row->xhits;
            $data['zc']=$row->zc;
            $data['zq']=$row->zq;
            $data['bq']=$row->bq;
            $data['hy']=$row->hy;
            $data['singerid']=$row->singerid;
            $data['dx']=$row->dx;
            $data['yz']=$row->yz;
            $data['sc']=$row->sc;
            $data['text']=$row->text;
            $data['purl']=$row->purl;
            $data['durl']=$row->durl;
            $data['wpurl']=$row->wpurl;
            $data['wppass']=$row->wppass;
            $data['skins']=$row->skins;
            $data['title']=$row->title;
            $data['keywords']=$row->keywords;
            $data['description']=$row->description;
            $data['row'] = $row;
        }
        $data['sid']=$sid;
        $this->load->view('dance_edit.html',$data);
    }

    //歌曲保存
    public function save(){
        $id   = intval($this->input->post('id'));
        $sid   = intval($this->input->post('sid'));
        $name = $this->input->post('name',true);
        $user = $this->input->post('user',true);
        $singer = $this->input->post('singer',true);
		$tags = $this->input->post('tags',true);
		$lrc = $this->input->post('lrc',true);
		$text = remove_xss($this->input->post('text'));
        $addtime = $this->input->post('addtime',true);
        $data['cid']=intval($this->input->post('cid'));

        if(empty($name)||empty($data['cid'])){
            getjson('抱歉，歌曲名称、分类不能为空~!');
		}
		//自动获取TAGS标签
        if(empty($tags)){
            $tags = gettag($name);
		}

        $data['fid']=intval($this->input->post('fid'));
        $data['tid']=intval($this->input->post('tid'));
        $data['reco']=intval($this->input->post('reco'));
        $data['uid']=intval(getzd('user','id',$user,'name'));
        $data['name']=$name;
        $data['pic']=$this->input->post('pic',true);
        $data['lrc']=$lrc;
        $data['text']=$text;
        $data['color']=$this->input->post('color',true);
        $data['cion']=intval($this->input->post('cion'));
        $data['vip']=intval($this->input->post('vip'));
        $data['level']=intval($this->input->post('level'));
        $data['tags']=$tags;
        $data['hits']=intval($this->input->post('hits'));
        $data['yhits']=intval($this->input->post('yhits'));
        $data['zhits']=intval($this->input->post('zhits'));
        $data['rhits']=intval($this->input->post('rhits'));
        $data['dhits']=intval($this->input->post('dhits'));
        $data['chits']=intval($this->input->post('chits'));
        $data['shits']=intval($this->input->post('shits'));
        $data['xhits']=intval($this->input->post('xhits'));
        $data['zc']=$this->input->post('zc',true);
        $data['zq']=$this->input->post('zq',true);
        $data['bq']=$this->input->post('bq',true);
        $data['hy']=$this->input->post('hy',true);
        $data['singerid']=intval(getzd('singer','id',$singer,'name'));
        $data['dx']=$this->input->post('dx',true);
        $data['yz']=$this->input->post('yz',true);
        $data['sc']=$this->input->post('sc',true);
        $data['purl']=$this->input->post('purl',true);
        $data['durl']=$this->input->post('durl',true);
        $data['wpurl']=$this->input->post('wpurl',true);
        $data['wppass']=$this->input->post('wppass',true);
        $data['skins']=$this->input->post('skins',true);
        $data['title']=$this->input->post('title',true);
        $data['keywords']=$this->input->post('keywords',true);
        $data['description']=$this->input->post('description',true);

        if($sid==3){
            $table= 'dance_hui';
        }elseif($sid==2){
            $table= 'dance_verify';
        }else{
            $table= 'dance';
        }
		if($id==0){ //新增
			$data['addtime']=time();
            $this->Csdb->get_insert($table,$data);
		}else{
            if($addtime=='ok') $data['addtime']=time();
            $this->Csdb->get_update($table,$id,$data);
		}
        $info['url'] = site_url('dance/admin/dance').'?yid='.$sid.'&v='.rand(1000,9999);
        getjson($info,0);
	}

    public function del(){
        $yid = intval($this->input->get('yid'));
        $ids = $this->input->get_post('id');
        $ac = $this->input->get_post('ac');
        //清空回收站
        if($ac=='hui'){
            $result=$this->db->query("SELECT id,pic,purl FROM ".CS_SqlPrefix."dance_hui")->result();
            $this->load->library('csup');
            foreach ($result as $row) {
                if(!empty($row->pic)){
                    $this->csup->del($row->pic,'dance'); //删除图片
                }
                if(!empty($row->purl)){
                    $this->csup->del($row->purl,'music'); //删除歌曲视听文件
                }
                $this->Csdb->get_del('dance_hui',$row->id);
            }
            $info['msg'] = '恭喜你，回收站清空成功';
            $info['url'] = site_url('dance/admin/dance').'?yid=3&v='.rand(1000,9999);
            getjson($info,0);
        }
        if(empty($ids)) getjson('请选择要删除的数据');
        if(is_array($ids)){
            $idss=implode(',', $ids);
        }else{
            $idss=$ids;
        }
        //直接删除回收站
        if($yid==3){
            $result=$this->db->query("SELECT pic,purl FROM ".CS_SqlPrefix."dance_hui where id in(".$idss.")")->result();
            $this->load->library('csup');
            foreach ($result as $row) {
                if(!empty($row->pic)){
                    $this->csup->del($row->pic,'dance'); //删除图片
                }
                if(!empty($row->purl)){
                    $this->csup->del($row->purl,'music'); //删除歌曲视听文件
                }
            }
            $this->Csdb->get_del('dance_hui',$ids);
        }else{
            $table = $yid==2 ? 'dance_verify' : 'dance';
            if(is_numeric($ids)){
                $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix.$table." where id=".$idss)->result_array();
            }else{
                $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix.$table." where id in(".$idss.")")->result_array();
            }
            foreach ($result as $row) {
                $id2 = $row['id'];
                if($yid==2){
                    $this->dt($row['id'],$table,1);
                    $row['hid'] = 1;
                    unset($row['did']);
                }else{
                    $row['hid'] = 0;
                }
                $row['did'] = $row['id'];
                unset($row['id']);
                $res = $this->Csdb->get_insert('dance_hui',$row);
                if($res){
                    $this->Csdb->get_del($table,$id2);
                }
            }
        }
        $info['url'] = site_url('dance/admin/dance').'?yid='.$yid.'&v='.rand(1000,9999);
        getjson($info,0);
    }

    //歌曲还原
	public function hy(){
        $ids = $this->input->get_post('id');
		if(empty($ids)) getjson('请选择要还原的数据~!');
		if(is_array($ids)){
		     $idss=implode(',', $ids);
		}else{
		     $idss=$ids;
		}
        if(is_numeric($ids)){
            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."dance_hui where id=".$idss)->result_array();
        }else{
            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."dance_hui where id in(".$idss.")")->result_array();
        }
        foreach ($result as $row) {
            $id2 = $row['id'];
            if($row['hid']==1){
                $table = 'dance_verify';
            }else{
                $table = 'dance';
            }
            $row['id'] = $row['did'];
            unset($row['hid']);
            unset($row['did']);
            $res = $this->Csdb->get_insert($table,$row);
            if($res){
                $this->Csdb->get_del('dance_hui',$id2);
            }
        }
        $info['msg'] = "恭喜您，数据还原成功~!";
        $info['url'] = site_url('dance/admin/dance').'?yid=3&v='.rand(1000,9999);
        getjson($info,0);
	}

    //歌曲批量
	public function pledit(){
        $data['id'] = $this->input->get_post('id');
        $data['sid'] = $this->input->get_post('sid');
		$this->load->view('pl_edit.html',$data);
	}

    //批量修改操作
	public function pl_save(){
        $sid = intval($this->input->get_post('sid'));
        $xid=intval($this->input->post('xid'));
        $csid=$this->input->post('csid');
        $id=$this->input->post('id',true);
	    $cids=intval($this->input->post('cids'));

	    $fid=intval($this->input->post('fid'));
	    $cid=intval($this->input->post('cid'));
	    $hid=intval($this->input->post('hid'));
	    $tid=intval($this->input->post('tid'));
	    $yid=intval($this->input->post('yid'));
	    $user=$this->input->post('user',true);
	    $singer=$this->input->post('singer',true);
	    $skins=$this->input->post('skins',true);
	    $reco=intval($this->input->post('reco'));
	    $cion=intval($this->input->post('cion'));
	    $vip=intval($this->input->post('vip'));
	    $hits=intval($this->input->post('hits'));
	    $yhits=intval($this->input->post('yhits'));
	    $zhits=intval($this->input->post('zhits'));
	    $rhits=intval($this->input->post('rhits'));

        if($sid==2){
            $table= 'dance_verify';
        }else{
            $table= 'dance';
        }

        if(empty($csid)) getjson('请选择要操作的数据~!');

        if($xid==1){  //按ID操作
            if(empty($id)) getjson('请选择要操作的歌曲ID~!');
            foreach ($csid as $v) {
                if($v=="cid"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set cid=".$cid." where id in (".$id.")");
                }elseif($v=="yid"){
                    if($yid==0){ //通过审核
                        if(is_numeric($id)){
                            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."dance_verify where id=".$id)->result_array();
                        }else{
                            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."dance_verify where id in(".$id.")")->result_array();
                        }
                        foreach ($result as $row) {
                            $id2 = $row['id'];
                            if($row['did']==0){
                                //增加金币、经验
                                $this->dt($id2,'dance_verify');
                            	unset($row['id']);
                            }else{
                            	$row['id'] = $row['did'];
                            }
                            unset($row['did']);
                            $res = $this->Csdb->get_insert('dance',$row);
                            if($res){
                                $this->Csdb->get_del('dance_verify',$id2);
                            }
                        }
                    }else{  //未审核
                        if(is_numeric($id)){
                            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."dance where id=".$id)->result_array();
                        }else{
                            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."dance where id in(".$id.")")->result_array();
                        }
                        foreach ($result as $row) {
                        	$id2 = $row['id'];
                            $row['did'] = $id2;
                            unset($row['id']);
                            $res = $this->Csdb->get_insert('dance_verify',$row);
                            if($res){
                                $this->Csdb->get_del('dance',$id2);
                            }
                        }
                    }
                }elseif($v=="fid"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set fid=".$fid." where id in (".$id.")");
                }elseif($v=="tid"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set tid=".$tid." where id in (".$id.")");
                }elseif($v=="reco"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set reco=".$reco." where id in (".$id.")");
                }elseif($v=="cion"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set cion=".$cion." where id in (".$id.")");
                }elseif($v=="vip"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set vip=".$vip." where id in (".$id.")");
                }elseif($v=="hits"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set hits=".$hits." where id in (".$id.")");
                }elseif($v=="yhits"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set yhits=".$yhits." where id in (".$id.")");
                }elseif($v=="zhits"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set zhits=".$zhits." where id in (".$id.")");
                }elseif($v=="rhits"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set rhits=".$rhits." where id in (".$id.")");
                }elseif($v=="skins"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set skins='".$skins."' where id in (".$id.")");
                }elseif($v=="user"){
                    $uid=intval(getzd('user','id',$user,'name'));
                    $this->db->query("update ".CS_SqlPrefix.$table." set uid=".$uid." where id in (".$id.")");
                }elseif($v=="singer"){
                    $singerid=intval(getzd('singer','id',$singer,'name'));
                    $this->db->query("update ".CS_SqlPrefix.$table." set singerid=".$singerid." where id in (".$id.")");
                }elseif($v=="hid"){
                    if($hid==2){
                        $this->Csdb->get_del('dance',$id);
                    }else{
                        if(is_numeric($id)){
                            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix.$table." where id=".$id)->result_array();
                        }else{
                            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix.$table." where id in(".$id.")")->result_array();
                        }
                        foreach ($result as $row) {
                            $id2 = $row['id'];
                            //删除金币、经验
                            $this->dt($row['id'],$table,1);
                            $row['hid'] = $sid==2 ? 1 : 0;
                            $row['did'] = $row['id'];
                            unset($row['id']);
                            $res = $this->Csdb->get_insert('dance_hui',$row);
                            if($res){
                                $this->Csdb->get_del($table,$id2);
                            }
                        }
                    }
                }
            }
		}else{ //按分类操作
            if(empty($cids)) admin_msg(L('plub_16'),'javascript:history.back();','no');
            foreach ($csid as $v) {
                if($v=="cid"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set cid=".$cid." where cid=".$cids);
                }elseif($v=="yid"){
                    if($yid==0){ //通过审核
                        $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."dance_verify where cid=".$cids)->result_array();
                        foreach ($result as $row) {
                            $id2 = $row['id'];
                            if($row['did']==0){
                                //增加金币、经验
                                $this->dt($row['id'],'dance_verify');
                                unset($row['id']);
                            }else{
                            	$row['id'] = $row['did'];
                            }
                            unset($row['did']);
                            $res = $this->Csdb->get_insert('dance',$row);
                            if($res){
                                $this->Csdb->get_del('dance_verify',$id2);
                            }
                        }
                    }else{  //未审核
                        $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."dance where cid=".$cids)->result_array();
                        foreach ($result as $row) {
                            $row['did'] = $row['id'];
                            $res = $this->Csdb->get_insert('dance_verify',$row);
                            if($res){
                                $this->Csdb->get_del('dance',$row['id']);
                            }
                        }
                    }
                }elseif($v=="fid"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set fid=".$fid." where cid=".$cids);
                }elseif($v=="tid"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set tid=".$tid." where cid=".$cids);
                }elseif($v=="reco"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set reco=".$reco." where cid=".$cids);
                }elseif($v=="cion"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set cion=".$cion." where cid=".$cids);
                }elseif($v=="vip"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set vip=".$vip." where cid=".$cids);
                }elseif($v=="hits"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set hits=".$hits." where cid=".$cids);
                }elseif($v=="yhits"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set yhits=".$yhits." where cid=".$cids);
                }elseif($v=="zhits"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set zhits=".$zhits." where cid=".$cids);
                }elseif($v=="rhits"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set rhits=".$rhits." where cid=".$cids);
                }elseif($v=="skins"){
                    $this->db->query("update ".CS_SqlPrefix.$table." set skins='".$skins."' where cid=".$cids);
                }elseif($v=="user"){
                    $uid=intval(getzd('user','id',$user,'name'));
                    $this->db->query("update ".CS_SqlPrefix.$table." set uid=".$uid." where cid=".$cids);
                }elseif($v=="singer"){
                    $singerid=intval(getzd('singer','id',$singer,'name'));
                    $this->db->query("update ".CS_SqlPrefix.$table." set singerid=".$singerid." where cid=".$cids);
                }elseif($v=="hid"){
                    if($hid==2){
                        $this->Csdb->get_del('dance',$id);
                    }else{
                        $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix.$table." where cid=".$cids)->result_array();
                        foreach ($result as $row) {
                            $id2 = $row['id'];
                            //删除金币、经验
                            $this->dt($row['id'],$table,1);
                            $row['hid'] = $sid==2 ? 1 : 0;
                            $row['did'] = $row['id'];
                            unset($row['id']);
                            $res = $this->Csdb->get_insert('dance_hui',$row);
                            if($res){
                                $this->Csdb->get_del($table,$id2);
                            }
                        }
                    }
                }
            }
		}
        $info['url'] = site_url('dance/admin/dance').'?v='.rand(1000,9999);
        $info['parent'] = 1;
		getjson($info,0);
	}

	//审核歌曲增加积分、经验、同时动态显示
	public function dt($id,$table='dance',$sid=0){
		$dt=$this->db->query("SELECT id,name,yid FROM ".CS_SqlPrefix."dt where link='".linkurl('play','id',$id,1,'dance')."'")->row();
		if($dt){
              $uid=getzd($table,'uid',$id);
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
			      //发送歌曲删除通知
			      $add['uida']=$uid;
			      $add['uidb']=0;
			      $add['name']='歌曲被删除';
			      $add['neir']='您的歌曲《'.$dt->name.'》被删除，系统同时扣除您'.User_Cion_Del.'个金币，'.User_Jinyan_Del.'个经验';
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
			      //发送歌曲审核通知
			      $add['uida']=$uid;
			      $add['uidb']=0;
			      $add['name']='歌曲审核通知';
			      $add['neir']='您的歌曲《'.$dt->name.'》已通过审核，系统同时给您奖励了'.User_Cion_Add.'个金币，'.User_Jinyan_Del.'个经验';
			      $add['addtime']=time();
        	      $this->Csdb->get_insert('msg',$add);
			  }
		}
	}
}
