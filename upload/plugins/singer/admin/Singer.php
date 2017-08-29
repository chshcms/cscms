<?php if ( ! defined('IS_ADMIN')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-16
 */
class Singer extends Cscms_Controller {

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
            $table= "singer_verify";
        }elseif($yid==3){
            $table= "singer_hui";
        }else{
            $table= "singer";
        }
        $sql_string = "SELECT id,name,pic,uid,hits,reco,cid,addtime FROM ".CS_SqlPrefix.$table." where 1=1";

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
        $data['singer'] = $query->result();

        $base_url = site_url('singer/admin/singer')."?yid=".$yid."&zd=".$zd."&key=".$key."&cid=".$cid."&sort=".$sort."&reco=".$reco."&page=";
        $data['page_data'] = page_data($total,$page,$totalPages); //获取分页类
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('singer.html',$data);
	}

    //推荐、锁定操作
	public function init($yid){
        $yid = (int)$yid;
        $id = intval($this->input->get_post('id'));
        if($id==0 ){
            getjson('参数错误');
        }
        if($yid<2){//未审核
            $row = $this->Csdb->get_row_arr('singer','*',$id);
            $row['did'] = $id;
            unset($row['id']);
            $res = $this->Csdb->get_insert('singer_verify',$row);
            if($res){
                $this->Csdb->get_del('singer',$id);
            }
        }else{
            $row = $this->Csdb->get_row_arr('singer_verify','*',$id);
            if($row['did']==0){
                unset($row['id']);
            }else{
                $row['id'] = $row['did'];
            }
            unset($row['did']);
            $res = $this->Csdb->get_insert('singer',$row);
            if($res){
                $this->Csdb->get_del('singer_verify',$id);
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
        $this->Csdb->get_update('singer',$id,$edit);
        getjson('',0);
    }

    //歌手新增、修改
	public function edit(){
        $id   = intval($this->input->get('id'));
        $yid   = intval($this->input->get('yid'));
        if($yid==3){
            $table= 'singer_hui';
        }elseif($yid==2){
            $table= 'singer_verify';
        }else{
            $table= 'singer';
        }
		if($id==0){
            $data['id']=0;
            $data['cid']=0;
            $data['uid']=0;
            $data['reco']=0;
            $data['name']='';
            $data['pic']='';
            $data['color']='';
            $data['bname']='';
            $data['hits']=0;
            $data['yhits']=0;
            $data['zhits']=0;
            $data['rhits']=0;
            $data['content']='';
            $data['tags']='';
            $data['nichen']='';
            $data['sex']='男';
            $data['nat']='中国';
            $data['yuyan']='国语';
            $data['city']='暂无';
            $data['sr']='暂无';
            $data['xingzuo']='暂无';
            $data['height']='暂无';
            $data['weight']='暂无';
            $data['title']='';
            $data['keywords']='';
            $data['description']='';
            $data['title2'] = '添加歌手';
            $data['user'] = '';
		}else{
            $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix.$table." where id=".$id."")->row(); 
		    if(!$row) admin_info('该条记录不存在~!');  //记录不存在
            if($row->uid>0){
                $user = $this->db->query("SELECT name FROM ".CS_SqlPrefix."user WHERE id=".$row->uid)->row();
                $data['user'] = $user?$user->name:'';
            }else{
                $data['user'] = '';
            }
            
            $data['id']=$row->id;
            $data['cid']=$row->cid;
            $data['uid']=$row->uid;
            $data['reco']=$row->reco;
            $data['name']=$row->name;
            $data['pic']=$row->pic;
            $data['color']=$row->color;
            $data['bname']=$row->bname;
            $data['hits']=$row->hits;
            $data['yhits']=$row->yhits;
            $data['zhits']=$row->zhits;
            $data['rhits']=$row->rhits;
            $data['content']=$row->content;
            $data['tags']=$row->tags;
            $data['nichen']=$row->nichen;
            $data['sex']=$row->sex;
            $data['nat']=$row->nat;
            $data['yuyan']=$row->yuyan;
            $data['city']=$row->city;
            $data['sr']=$row->sr;
            $data['xingzuo']=$row->xingzuo;
            $data['height']=$row->height;
            $data['weight']=$row->weight;
            $data['title']=$row->title;
            $data['keywords']=$row->keywords;
            $data['description']=$row->description;
            $data['title2'] = '修改歌手';
            $data['row'] = $row;
		}
        $data['yid'] = $yid;
        $this->load->view('singer_edit.html',$data);
	}

    //歌手保存
	public function save(){
        $id   = intval($this->input->post('id'));
        $name = $this->input->post('name',true);
		$tags = $this->input->post('tags',true);
        $content = remove_xss($this->input->post('content'));
        $addtime = $this->input->post('addtime',true);
        $data['cid']=intval($this->input->post('cid'));
        $yid   = intval($this->input->post('yid'));

        if(empty($name)||empty($data['cid'])){
            getjson('抱歉，歌手名称、分类不能为空~!');
        }

        $user = $this->input->post('user',true);
        if(!empty($user)){
            $data['uid'] = (int)getzd('user','id',$user,'name');
        }else{
            $data['uid'] = 0;
        }
       
        //自动获取TAGS标签
        if(empty($tags)){
            $tags = gettag($name,$content);
        }
        $data['reco']=intval($this->input->post('reco'));
        $data['name']=$name;
        $data['tags']=$tags;
        $data['pic']=$this->input->post('pic',true);
        $data['color']=$this->input->post('color',true);
        $data['bname']=$this->input->post('bname',true);
        $data['hits']=intval($this->input->post('hits'));
        $data['yhits']=intval($this->input->post('yhits'));
        $data['zhits']=intval($this->input->post('zhits'));
        $data['rhits']=intval($this->input->post('rhits'));
        $data['nichen']=$this->input->post('nichen',true);
        $data['sex']=$this->input->post('sex',true);
        $data['nat']=$this->input->post('nat',true);
        $data['yuyan']=$this->input->post('yuyan',true);
        $data['city']=$this->input->post('city',true);
        $data['sr']=$this->input->post('sr',true);
        $data['xingzuo']=$this->input->post('xingzuo',true);
        $data['height']=$this->input->post('height',true);
        $data['weight']=$this->input->post('weight',true);
        $data['content']=$content;
        $data['title']=$this->input->post('title',true);
        $data['keywords']=$this->input->post('keywords',true);
        $data['description']=$this->input->post('description',true);

        if($yid==3){
            $table= 'singer_hui';
        }elseif($yid==2){
            $table= 'singer_verify';
        }else{
            $table= 'singer';
        }

		if($id==0){ //新增
			$data['addtime']=time();
            $this->Csdb->get_insert($table,$data);
		}else{
		     if($addtime=='ok') $data['addtime']=time();
             $this->Csdb->get_update($table,$id,$data);
		}
		$info['url'] = site_url('singer/admin/singer').'?yid='.$yid.'&v='.rand(100,999);
		$info['msg'] = '恭喜您，操作成功~!';
		getjson($info,0);
	}

    //歌手删除
	public function del(){
        $yid = intval($this->input->get('yid'));
        $ids = $this->input->get_post('id');
        $ac = $this->input->get_post('ac');
		//清空回收站
		if($ac=='hui'){
		    $result=$this->db->query("SELECT id,pic FROM ".CS_SqlPrefix."singer_hui")->result();
		    $this->load->library('csup');
		    foreach ($result as $row) {
                if(!empty($row->pic)){
				    $this->csup->del($row->pic,'singer'); //删除图片
			    }
				$this->Csdb->get_del('singer_hui',$row->id);
			}
			$info['url'] = site_url('singer/admin/singer').'?yid=3&v='.rand(100,999);
			$info['msg'] = '恭喜您，回收站清空成功~!';
			getjson($info,0);
		}
		if(empty($ids)) getjson('请选择要删除的数据~!');
		if(is_array($ids)){
		     $idss=implode(',', $ids);
		}else{
		     $idss=$ids;
		}
        //直接删除
		if($yid==3){
            $result=$this->db->query("SELECT pic FROM ".CS_SqlPrefix."singer_hui where id in(".$idss.")")->result();
            $this->load->library('csup');
            foreach ($result as $row) {
                if(!empty($row->pic)){
                    $this->csup->del($row->pic,'singer'); //删除图片
                }
            }
            $this->Csdb->get_del('singer_hui',$ids);
		}else{
			$table = $yid==2 ? 'singer_verify' : 'singer';
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
                $res = $this->Csdb->get_insert('singer_hui',$row);
                if($res){
                    $this->Csdb->get_del($table,$rowid);
                }
            }
		}
        $info['url'] = site_url('singer/admin/singer').'?yid='.$yid.'&v='.rand(100,999);
        getjson($info,0);
	}

    //歌手还原
	public function hy(){
        $ids = $this->input->get_post('id');
		if(empty($ids)) getjson('请选择要还原的数据~!');
		if(is_array($ids)){
		     $idss=implode(',', $ids);
		}else{
		     $idss=$ids;
		}
		if(is_numeric($ids)){
            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."singer_hui where id=".$idss)->result_array();
        }else{
            $result=$this->db->query("SELECT * FROM ".CS_SqlPrefix."singer_hui where id in(".$idss.")")->result_array();
        }
        foreach ($result as $row) {
            $rowid = $row['id'];
            if($row['hid']==1){
                $table = 'singer_verify';
                unset($row['id']);
            }else{
                $table = 'singer';
                $row['id'] = $row['did'];
                unset($row['did']);
            }
            unset($row['hid']);
            $res = $this->Csdb->get_insert($table,$row);
            if($res){
                $this->Csdb->get_del('singer_hui',$rowid);
            }
        }
        $info['msg'] = "恭喜您，数据还原成功~!";
        $info['url'] = site_url('singer/admin/singer').'?yid=3&v='.rand(1000,9999);
        getjson($info,0);
	}

    //同步远程图片到本地
	public function downpic(){
        $page = intval($this->input->get('page'));
        $pagejs = intval($this->input->get('pagejs'));

        $sql_string = "SELECT id,pic FROM ".CS_SqlPrefix."singer where Lower(Left(pic,7))='http://' order by addtime desc";
        $query = $this->db->query($sql_string); 
        $total = $query->num_rows();

        if($page > $pagejs || $total==0){
        	$info['url'] = site_url('singer/admin/singer').'?v='.rand(100,999);
        	$info['msg'] = '恭喜您，所有远程图片全部同步完成~!';
            admin_info($info,1);
		}

        if($page==0) $page = 1;
        $per_page = 20; 
        $totalPages = ceil($total / $per_page); // 总页数
        if($total<$per_page){
           $per_page=$total;
        }
		if($pagejs==0) $pagejs=$totalPages;
        $sql_string.=' limit 20';
        $query = $this->db->query($sql_string); 

		//保存目录
		if(UP_Mode==1 && UP_Pan!=''){
		    $pathpic = UP_Pan.'/attachment/singer/'.date('Ym').'/'.date('d').'/';
			$pathpic = str_replace("//","/",$pathpic);
		}else{
		    $pathpic = FCPATH.'attachment/singer/'.date('Ym').'/'.date('d').'/';
		}
		if (!is_dir($pathpic)) {
            mkdirss($pathpic);
        }

		$this->load->library('watermark');
        $this->load->library('csup');

        echo '<link href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><script src="'.base_url().'packs/js/jquery.min.js"></script>';

        echo "<div style='font-size:14px;'><b>正在开始同步第<font style='color:red; font-size:12px; font-style:italic'>".$page."</font>页，共<font style='color:red; font-size:12px; font-style:italic'>".$pagejs."</font>页，剩<font style='color:red; font-size:12px; font-style:italic'>".$totalPages."</font>页</b><br><br>";
       
        foreach ($query->result() as $row) {
			ob_end_flush();//关闭缓存
			$up='no';
			if(!empty($row->pic)){
                   $picdata=htmlall($row->pic);
				   $file_ext = strtolower(trim(substr(strrchr($row->pic, '.'), 1)));
                   if($file_ext!='jpg' && $file_ext!='png' && $file_ext!='gif'){
				       $file_ext = 'jpg';
				   }
                   //新文件名
                   $file_name=date("YmdHis") . rand(10000, 99999) . '.' . $file_ext;
		           $file_path=$pathpic.$file_name;
                   if(!empty($picdata)){
					   //保存图片
                       if(write_file($file_path, $picdata)){
                               $up='ok';
                               //判断水印
                               if(CS_WaterMark==1){
                                     $this->watermark->imagewatermark($file_path);
                               }
				               //判断上传方式
				               $res=$this->csup->up($file_path,$file_name);
				               if(!$res){
				                   $up='no';
				               }
					   }
				   }
				   $filepath=(UP_Mode==1)?'/'.date('Ym').'/'.date('d').'/'.$file_name : '/'.date('Ymd').'/'.$file_name;
			}
			//成功
			if($up=='ok'){
                //修改数据库
                $this->db->query("update ".CS_SqlPrefix."singer set pic='".$filepath."' where id=".$row->id."");
                echo "同步<font color=red>".$row->pic."</font>&nbsp;图片成功!&nbsp;&nbsp;新图片名：<a href=\"".piclink('singer',$filepath)."\" target=_blank>".$file_name."</a></br>";
			}else{
               	//修改数据库
                $this->db->query("update ".CS_SqlPrefix."singer set pic='' where id=".$row->id."");
                echo "<font color=red>".$row->pic."</font>远程图片不存在!</br>";
			}
			echo "<script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
			ob_flush();flush();
		}
        echo "第".$page."页图片同步完毕,暂停3秒后继续同步．．．．．．<script language='javascript'>setTimeout('ReadGo();',".(3000).");function ReadGo(){location.href='".site_url('singer/admin/singer/downpic')."?page=".($page+1)."&pagejs=".$pagejs."';}</script><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script></div>";
	}

    //歌手转移分类
	public function zhuan(){
        $cid = intval($this->input->get_post('cid'));
        $yid = intval($this->input->get('yid'));
        $ids = $this->input->get_post('id');
		if(empty($ids)) getjson('请选择要转移的数据~!');
		if(is_array($ids)){
		    $idss=implode(',', $ids);
		}else{
		    $idss=$ids;
		}
		$data['cid']=$cid;
		$this->Csdb->get_update('singer',$ids,$data);
		$info['url'] = site_url('singer/admin/singer').'?yid='.$yid.'&v='.rand(100,999);
		$info['msg'] = '恭喜您，数据已经转移成功~!';
		getjson($info,0);
	}
}
