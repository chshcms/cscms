<?php if ( ! defined('IS_ADMIN')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-08
 */
class Opt extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
	}

    //收藏列表
	public function fav(){
            $kstime = $this->input->get_post('kstime',true);
            $jstime = $this->input->get_post('jstime',true);
            $zd  = $this->input->get_post('zd');
            $sid  = intval($this->input->get_post('sid'));
            $key  = $this->input->get_post('key',true);
 	        $page = intval($this->input->get('page'));
            if($page==0) $page=1;
			$kstimes=empty($kstime)?0:strtotime($kstime)-86400;
			$jstimes=empty($jstime)?0:strtotime($jstime)+86400;
			if($kstimes>$jstimes) $kstimes = strtotime($kstime);

	        $data['zd'] = $zd;
	        $data['sid'] = $sid;
	        $data['key'] = $key;
	        $data['page'] = $page;
	        $data['kstime'] = $kstime;
	        $data['jstime'] = $jstime;

            $sql_string = "SELECT * FROM ".CS_SqlPrefix."dance_fav where 1=1";
			if($sid>0){
				 $sql_string.= " and sid=".($sid-1)."";
			}
			if(!empty($key)){
				if($zd=='user'){
					$uid=getzd('user','id',$key,'name');
				    $sql_string.= " and uid='".intval($uid)."'";
				}else{
				    $sql_string.= " and name like '%".$key."%'";
				}
			}
			if($kstimes>0){
	             $sql_string.= " and addtime>".$kstimes."";
			}
			if($jstimes>0){
	             $sql_string.= " and addtime<".$jstimes."";
			}
	        $sql_string.= " order by addtime desc";
            $count_sql = str_replace('*','count(*) as count',$sql_string);
	        $query = $this->db->query($count_sql)->result_array();
	        $total = $query[0]['count'];

	        $per_page = 15; 
            $totalPages = ceil($total / $per_page); // 总页数
	        $data['nums'] = $total;
            if($total<$per_page){
                  $per_page=$total;
            }
            $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
	        $query = $this->db->query($sql_string);
	        $data['fav'] = $query->result();

            $base_url = site_url('dance/admin/opt/fav')."?sid=".$sid."&zd=".$zd."&key=".$key."&kstime=".$kstime."&jstime=".$jstime."&page=";
	        $data['page_data'] = page_data($total,$page,$totalPages); //获取分页类
	        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
            $this->load->view('fav.html',$data);
	}

    //收藏记录删除
	public function fav_del(){
        $ids = $this->input->get_post('id');
		if(empty($ids)) getjson('请选择要删除的数据~!');
		if(is_array($ids)){
		     $idss=implode(',', $ids);
		}else{
		     $idss=$ids;
		}
		$this->Csdb->get_del('dance_fav',$ids);
        $info['url'] = site_url('dance/admin/opt/fav').'?v='.rand(1000,9999);
        getjson($info,0);
  	}

    //收藏记录批量删除
	public function fav_pldel(){
        $id = intval($this->input->get('id'));
        if($id==3){
			$times=time()-86400*90;
			$this->db->query("delete from ".CS_SqlPrefix."dance_fav where addtime<".$times."");
			$info['msg'] = '恭喜你，3个月以前的记录删除完毕';
		}else{
		    $this->db->query("delete from ".CS_SqlPrefix."dance_fav");
			$this->db->query("TRUNCATE TABLE ".CS_SqlPrefix."dance_fav");
			$info['msg'] = '恭喜你，记录清空完毕';
		}
        $info['url'] = site_url('dance/admin/opt/fav').'?v='.rand(1000,9999);
        getjson($info,0);
	}

    //视听记录列表
	public function play(){
        $kstime = $this->input->get_post('kstime',true);
        $jstime = $this->input->get_post('jstime',true);
        $zd  = $this->input->get_post('zd',true);
        $key  = $this->input->get_post('key',true);
	    $page = intval($this->input->get('page'));
        if($page==0) $page=1;
		$kstimes=empty($kstime)?0:strtotime($kstime)-86400;
		$jstimes=empty($jstime)?0:strtotime($jstime)+86400;
		if($kstimes>$jstimes) $kstimes=strtotime($kstime);

        $data['zd'] = $zd;
        $data['key'] = $key;
        $data['page'] = $page;
        $data['kstime'] = $kstime;
        $data['jstime'] = empty($jstime)?date('Y-m-d'):$jstime;

        $sql_string = "SELECT * FROM ".CS_SqlPrefix."dance_play where 1=1";
		if(!empty($key)){
			if($zd=='user'){
				$uid=getzd('user','id',$key,'name');
			    $sql_string.= " and uid='".intval($uid)."'";
			}else{
			    $sql_string.= " and name like '%".$key."%'";
			}
		}
		if($kstimes>0){
             $sql_string.= " and addtime>".$kstimes."";
		}
		if($jstimes>0){
             $sql_string.= " and addtime<".$jstimes."";
		}
        $sql_string.= " order by addtime desc";
        $count_sql = str_replace('*','count(*) as count',$sql_string);
        $query = $this->db->query($count_sql)->result_array();
        $total = $query[0]['count'];

        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        $data['nums'] = $total;
        if($total<$per_page){
              $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);
        $data['play'] = $query->result();

        $base_url = site_url('dance/admin/opt/play')."?zd=".$zd."&key=".$key."&kstime=".$kstime."&jstime=".$jstime."&page=";
       	$data['page_data'] = page_data($total,$page,$totalPages); //获取分页类
	    $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('play.html',$data);
	}

    //视听记录删除
	public function play_del(){
        $ids = $this->input->get_post('id');
		if(empty($ids)) getjson('请选择要删除的数据~!');
		if(is_array($ids)){
		     $idss=implode(',', $ids);
		}else{
		     $idss=$ids;
		}
		$this->Csdb->get_del('dance_play',$ids);
        $info['url'] = site_url('dance/admin/opt/play').'?v='.rand(1000,9999);
        getjson($info,0);
	}

    //视听记录批量删除
	public function play_pldel(){
        $id = intval($this->input->get('id'));
        if($id==3){
			$times=time()-86400*90;
            $this->db->query("delete from ".CS_SqlPrefix."dance_play where addtime<".$times."");
            $info['msg'] = '恭喜你，3个月以前的记录删除完毕';
		}else{
		    $this->db->query("delete from ".CS_SqlPrefix."dance_play");
			$this->db->query("TRUNCATE TABLE ".CS_SqlPrefix."dance_play");
			$info['msg'] = '恭喜你，记录清空完毕';
		}
        $info['url'] = site_url('dance/admin/opt/play').'?v='.rand(1000,9999);
        getjson($info,0);
	}

    //下载记录列表
	public function down(){
        $kstime = $this->input->get_post('kstime',true);
        $jstime = $this->input->get_post('jstime',true);
        $zd  = $this->input->get_post('zd',true);
        $key  = $this->input->get_post('key',true);
	    $page = intval($this->input->get('page'));
        if($page==0) $page=1;
		$kstimes=empty($kstime)?0:strtotime($kstime)-86400;
		$jstimes=empty($jstime)?0:strtotime($jstime)+86400;
		if($kstimes>$jstimes) $kstimes=strtotime($kstime);

        $data['zd'] = $zd;
        $data['key'] = $key;
        $data['page'] = $page;
        $data['kstime'] = $kstime;
        $data['jstime'] = empty($jstime)?date('Y-m-d'):$jstime;

        $sql_string = "SELECT * FROM ".CS_SqlPrefix."dance_down where 1=1";
		if(!empty($key)){
			if($zd=='user'){
				$uid=getzd('user','id',$key,'name');
			    $sql_string.= " and uid='".intval($uid)."'";
			}else{
			    $sql_string.= " and name like '%".$key."%'";
			}
		}
		if($kstimes>0){
             $sql_string.= " and addtime>".$kstimes."";
		}
		if($jstimes>0){
             $sql_string.= " and addtime<".$jstimes."";
		}
        $sql_string.= " order by addtime desc";
        $count_sql = str_replace('*','count(*) as count',$sql_string);
        $query = $this->db->query($count_sql)->result_array();
        $total = $query[0]['count'];

        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        $data['nums'] = $total;
        if($total<$per_page){
              $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);
        $data['down'] = $query->result();

        $base_url = site_url('dance/admin/opt/down')."?zd=".$zd."&key=".$key."&kstime=".$kstime."&jstime=".$jstime."&page=";
        $data['page_data'] = page_data($total,$page,$totalPages); //获取分页类
	    $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('down.html',$data);
	}

    //观看记录删除
	public function down_del(){
        $ids = $this->input->get_post('id');
		if(empty($ids)) getjson('请选择要删除的数据~!');
		if(is_array($ids)){
		     $idss=implode(',', $ids);
		}else{
		     $idss=$ids;
		}
		$this->Csdb->get_del('dance_down',$ids);
        $info['url'] = site_url('dance/admin/opt/fav').'?v='.rand(1000,9999);
        getjson($info,0);
	}

    //观看记录批量删除
	public function down_pldel(){
        $id = intval($this->input->get('id'));
        if($id==3){
			$times=time()-86400*90;
            $this->db->query("delete from ".CS_SqlPrefix."dance_down where addtime<".$times."");
            $info['msg'] = '恭喜你，3个月以前的记录删除完毕';
		}else{
		    $this->db->query("delete from ".CS_SqlPrefix."dance_down");
			$this->db->query("TRUNCATE TABLE ".CS_SqlPrefix."dance_down");
			$info['msg'] = '恭喜你，记录清空完毕';
		}
        $info['url'] = site_url('dance/admin/opt/fav').'?v='.rand(1000,9999);
        getjson($info,0);
	}
	//同步远程图片到本地
	public function downpic(){
		$page = intval($this->input->get('page'));
		$pagejs = intval($this->input->get('pagejs'));

		$sql_string = "SELECT id,pic FROM ".CS_SqlPrefix."dance where Lower(Left(pic,7))='http://' order by addtime desc";
		$query = $this->db->query($sql_string); 
		$total = $query->num_rows();

		if($page > $pagejs || $total==0){
			$info['msg'] = '恭喜您，所有远程图片全部同步完成~!';
			$info['url'] = site_url('dance/admin/dance').'?v='.rand(100,999);
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
		$pathpic = UP_Pan.'/attachment/dance/'.date('Ym').'/'.date('d').'/';
		$pathpic = str_replace("//","/",$pathpic);
		}else{
		$pathpic = FCPATH.'attachment/dance/'.date('Ym').'/'.date('d').'/';
		}
		if (!is_dir($pathpic)) {
		mkdirss($pathpic);
		}

		$this->load->library('watermark');
		$this->load->library('csup');

		echo '<link href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><script src="'.base_url().'packs/js/jquery.min.js"></script><style>b{font-size:14px;}</style>';
		echo "<div style='font-size:14px;'>&nbsp;&nbsp;&nbsp;<b>正在开始同步第<font style='color:red; font-size:12px; font-style:italic'>".$page."</font>页，共<font style='color:red; font-size:12px; font-style:italic'>".$pagejs."</font>页，剩<font style='color:red; font-size:12px; font-style:italic'>".$totalPages."</font>页</b><br><br>";

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
		       $this->db->query("update ".CS_SqlPrefix."dance set pic='".$filepath."' where id=".$row->id."");
		       echo "&nbsp;&nbsp;&nbsp;&nbsp;同步<font color=red>".$row->pic."</font>&nbsp;图片成功!&nbsp;&nbsp;新图片名：<a href=\"".piclink('dance',$filepath)."\" target=_blank>".$file_name."</a></br>";
		}else{
		       //修改数据库
		       $this->db->query("update ".CS_SqlPrefix."dance set pic='' where id=".$row->id."");
		       echo "&nbsp;&nbsp;&nbsp;&nbsp;<font color=red>".$row->pic."</font>远程图片不存在!</br>";
		}
		echo "<script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
		ob_flush();flush();
		}
		echo "&nbsp;&nbsp;&nbsp;&nbsp;第".$page."页图片同步完毕,暂停3秒后继续同步...<script language='javascript'>setTimeout('ReadGo();',".(3000).");function ReadGo(){location.href='".site_url('dance/admin/opt/downpic')."?page=".($page+1)."&pagejs=".$pagejs."';}</script></div><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
	}
}

