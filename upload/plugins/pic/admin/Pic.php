<?php if ( ! defined('IS_ADMIN')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-16
 */
class Pic extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
	}

	public function index(){
        $cid  = intval($this->input->get_post('cid'));
        $sid  = intval($this->input->get_post('sid'));
        $yid  = intval($this->input->get_post('yid'));
        $zd   = $this->input->get_post('zd',true);
        $key  = $this->input->get_post('key',true);
        $page = intval($this->input->get('page'));
        if($page==0) $page=1;

        $data['page'] = $page;
        $data['cid'] = $cid;
        $data['sid'] = $sid;
        $data['yid'] = $yid;
        $data['zd'] = $zd;
        $data['key'] = $key;

		if($yid==2){
            $table= "pic_verify";
        }elseif($yid==3){
            $table= "pic_hui";
        }else{
            $table= "pic";
        }
        $sql_string = "SELECT * FROM ".CS_SqlPrefix.$table." where 1=1";

		if($cid>0){
             $sql_string.= " and cid=".$cid."";
		}
		if($sid>0){
             $sql_string.= " and sid=".$sid."";
		}
		if(!empty($key)){
			if($zd=='id' || $zd=='sid'){
			    $sql_string.= " and ".$zd."=".$key."";
			}else{
			    $sql_string.= " and ".$zd." like '%".$key."%'";
			}
		}
        $sql_string.= " order by id desc";
        $total = $this->Csdb->get_allnums($sql_string);

        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        $data['nums'] = $total;
        if($total<$per_page){
              $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);
        $data['pic'] = $query->result();

        $base_url = site_url('pic/admin/pic')."?yid=".$yid."&zd=".$zd."&key=".$key."&cid=".$cid."&sid=".$sid."&page=";
        $data['page_data'] = page_data($total,$page,$totalPages); //获取分页类
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('pic.html',$data);
	}

    //推荐、锁定操作
	public function init(){
        $id   = intval($this->input->get_post('id'));
        $sid  = intval($this->input->get_post('sign'));
		
		if($sid<2){//未审核
            $row = $this->Csdb->get_row_arr('pic','*',$id);
            $row['did'] = $id;
            unset($row['id']);
            $res = $this->Csdb->get_insert('pic_verify',$row);
            if($res){
                $this->Csdb->get_del('pic',$id);
            }
        }else{
            $row = $this->Csdb->get_row_arr('pic_verify','*',$id);
			$this->dt($id,'pic_verify');
            if($row['did']==0){
                unset($row['id']);
            }else{
                $row['id'] = $row['did'];
            }
            unset($row['did']);
            $res = $this->Csdb->get_insert('pic',$row);
            if($res){
                $this->Csdb->get_del('pic_verify',$id);
            }
        }
        getjson('',0);
	}

    //图片新增、修改
	public function edit(){
        $id   = intval($this->input->get('id'));
        $yid   = intval($this->input->get('yid'));
        if($yid==3){
            $table= 'pic_hui';
        }elseif($yid==2){
            $table= 'pic_verify';
        }else{
            $table= 'pic';
        }
		if($id==0){
            $data['id']=0;
            $data['cid']=0;
            $data['sid']=0;
            $data['pic']='';
            $data['user']='';
            $data['content']='';
            $data['title2'] = '添加图片';
		}else{
            $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix.$table." where id=".$id."")->row(); 
		    if(!$row) admin_msg('该条记录不存在~!','javascript:history.back();','no');  //记录不存在
            $data['id']=$row->id;
            $data['cid']=$row->cid;
            $data['sid']=$row->sid;
            $data['pic']=$row->pic;
            $data['user']=getzd('user','name',$row->uid);
            $data['content']=$row->content;
            $data['title2'] = '修改图片';
            $data['row'] = $row;
		}
		$data['yid'] = $yid;
        $this->load->view('pic_edit.html',$data);
	}

    //图片保存
	public function save(){
        $id   = intval($this->input->post('id'));
        $sid = intval($this->input->post('sid'));
        $cid = intval($this->input->post('cid'));
        $content = remove_xss($this->input->post('content'));
        $addtime = $this->input->post('addtime',true);
        $pic = $this->input->post('pic',true);
        $user = $this->input->post('user',true);
        $yid   = intval($this->input->post('yid'));

        if($sid==0 || $cid==0 || empty($pic)){
            getjson('抱歉，所属相册、分类、图片地址不能为空~!');
		}

        $data['cid']=$cid;
        $data['sid']=$sid;
        $data['pic']=$pic;
        $data['uid']=intval(getzd('user','id',$user,'name'));
        $data['content']=$content;

		if($yid==3){
            $table= 'pic_hui';
        }elseif($yid==2){
            $table= 'pic_verify';
        }else{
            $table= 'pic';
        }

		if($id==0){ //新增
			$data['addtime']=time();
            $this->Csdb->get_insert('pic',$data);
		}else{
			if($yid==0) $this->dt($id,$table);
            if($addtime=='ok') $data['addtime']=time();
            $this->Csdb->get_update($table,$id,$data);
		}
        $info['url'] = site_url('pic/admin/pic');
        getjson($info,0);
	}

    //图片删除
	public function del(){
        $yid = intval($this->input->get('yid'));
        $ids = $this->input->get_post('id');
        $ac = $this->input->get_post('ac');
		//回收站
		if($ac=='hui'){
		    $result=$this->db->query("SELECT id,pic FROM ".CS_SqlPrefix."pic")->result();
		    $this->load->library('csup');
		    foreach ($result as $row) {
                if(!empty($row->pic) && substr($row->pic,0,7)!='http://'){
				    $this->csup->del($row->pic,'pic'); //删除图片
			    }
				$this->Csdb->get_del('pic',$row->id);
			}
            $info['msg'] = '恭喜您，回收站清空成功~!';  //操作成功
            $info['url'] = site_url('pic/admin/pic').'?yid=3&v='.rand(100,999);
            getjson($info,0);
		}
		if(empty($ids)) getjson('请选择要删除的数据~!');
		if(is_array($ids)){
		    $idss=implode(',', $ids);
		}else{
		    $idss=$ids;
		}
		if($yid==3){
		    $result=$this->db->query("SELECT id,pic FROM ".CS_SqlPrefix."pic where id in(".$idss.")")->result();
		    $this->load->library('csup');
		    foreach ($result as $row) {
                if(!empty($row->pic) && substr($row->pic,0,7)!='http://'){
				    $this->csup->del($row->pic,'pic'); //删除图片
			    }
			}
		    $this->Csdb->get_del('pic',$ids);
            $info['msg'] = '恭喜您，删除成功~!';
            $info['url'] = site_url('pic/admin/pic').'?yid=3&v='.rand(100,999);
            getjson($info,0);
		}else{
			$table = $yid==2 ? 'pic_verify' : 'pic';
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
                $res = $this->Csdb->get_insert('pic_hui',$row);
                if($res){
                    $this->Csdb->get_del($table,$rowid);
                }
            }
		}
		$info['url'] = site_url('pic/admin/pic').'?yid='.$yid.'&v='.rand(100,999);
        getjson($info,0);
	}

    //图片还原
	public function hy(){
        $ids = $this->input->get_post('id');
		if(empty($ids)) getjson('请选择要还原的数据~!');
		if(is_array($ids)){
		     $idss=implode(',', $ids);
		}else{
		     $idss=$ids;
		}
		if(is_numeric($ids)){
            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."pic_hui where id=".$idss)->result_array();
        }else{
            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."pic_hui where id in(".$idss.")")->result_array();
        }
        foreach ($result as $row) {
            $rowid = $row['id'];
            if($row['hid']==1){
                $table = 'pic_verify';
                unset($row['id']);
            }else{
                $table = 'pic';
                $row['id'] = $row['did'];
                unset($row['did']);
            }
            unset($row['hid']);
            $res = $this->Csdb->get_insert($table,$row);
            if($res){
                $this->Csdb->get_del('pic_hui',$rowid);
            }
        }
        $info['msg'] = "恭喜您，数据还原成功~!";
        $info['url'] = site_url('pic/admin/pic').'?yid=3&v='.rand(1000,9999);
        getjson($info,0);
	}

    //选择相册列表
	public function res($sid=0){
		$sid = intval($sid);
	    $page = intval($this->input->get('page'));
        if($page==0) $page=1;
        $data['page'] = $page;
        $data['sid'] = $sid;
        $sql_string = "SELECT * FROM ".CS_SqlPrefix."pic_type order by id desc";
        $query = $this->db->query($sql_string); 
        $total = $query->num_rows();

        $per_page = 8; 
        $totalPages = ceil($total / $per_page); // 总页数
        $data['nums'] = $total;
        if($total<$per_page){
              $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);
        $data['pic_type'] = $query->result();

        $base_url = site_url('pic/admin/pic/res/'.$sid).'?page=';
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('pic_res.html',$data);
	}

	//审核图片增加积分、经验、同时动态显示
	public function dt($id,$table='pic',$yid=0){
	    $sid=getzd('pic','sid',$id);
		$dt=$this->db->query("SELECT id,yid,name FROM ".CS_SqlPrefix."dt where link='".linkurl('show','id',$sid,1,'pic')."'")->row();
		if($dt){
              $uid=getzd($table,'uid',$id);
			  if($yid>0){ //删除回收站

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
			      $add['name']='图片被删除';
			      $add['neir']='您的图片《'.$dt->name.'》被删除，系统同时扣除您'.User_Cion_Del.'个金币，'.User_Jinyan_Del.'个经验';
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
			      //发送图片审核通知
			      $add['uida']=$uid;
			      $add['uidb']=0;
			      $add['name']='图片审核通知';
			      $add['neir']='恭喜您，您的图片《'.$dt->name.'》已经审核通过，'.$str.'感谢您的支持~~';
			      $add['addtime']=time();
        	      $this->Csdb->get_insert('msg',$add);
			  }
		}
	}
}
