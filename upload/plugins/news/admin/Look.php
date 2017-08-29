<?php if ( ! defined('IS_ADMIN')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-16
 */
class Look extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
	}

    //阅读记录列表
	public function index(){
            $kstime = $this->input->get_post('kstime',true);
            $jstime = $this->input->get_post('jstime',true);
            $key  = $this->input->get_post('key',true);
 	        $page = intval($this->input->get('page'));
            if($page==0) $page=1;
			$kstimes=empty($kstime)?0:strtotime($kstime)-86400;
			$jstimes=empty($jstime)?0:strtotime($jstime)+86400;
			if($kstimes>$jstimes) $kstimes=strtotime($kstime);

	        $data['key'] = $key;
	        $data['page'] = $page;
	        $data['kstime'] = $kstime;
	        $data['jstime'] = empty($jstime)?date('Y-m-d'):$jstime;

            $sql_string = "SELECT * FROM ".CS_SqlPrefix."news_look where 1=1";
			if(!empty($key)){
				 $sql_string.= " and name like '%".$key."%'";
			}
			if($kstimes>0){
	             $sql_string.= " and addtime>".$kstimes."";
			}
			if($jstimes>0){
	             $sql_string.= " and addtime<".$jstimes."";
			}
	        $sql_string.= " order by addtime desc";
	        $query = $this->db->query($sql_string); 
	        $total = $query->num_rows();

	        $per_page = 15; 
            $totalPages = ceil($total / $per_page); // 总页数
	        $data['nums'] = $total;
            if($total<$per_page){
                  $per_page=$total;
            }
            $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
	        $query = $this->db->query($sql_string);
	        $data['look'] = $query->result();

            $base_url = site_url('news/admin/look')."?key=".$key."&kstime=".$kstime."&jstime=".$jstime."&page=";
	        $data['page_data'] = page_data($total,$page,$totalPages); //获取分页类
	        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
            $this->load->view('look.html',$data);
	}

    //记录删除
	public function del(){
        $ids = $this->input->get_post('id');
		if(empty($ids)) getjson('请选择要删除的数据~!');
		if(is_array($ids)){
		    $idss=implode(',', $ids);
		}else{
		    $idss=$ids;
		}
		$this->Csdb->get_del('news_look',$ids);
		$info['url'] = site_url('news/admin/look').'?v='.rand(1000,9999);
        getjson($info,0);  //操作成功
	}

    //记录批量删除
	public function pldel(){
        $id = intval($this->input->get('id'));
        if($id==3){
			$times=time()-86400*90;
			$this->db->query("delete from ".CS_SqlPrefix."news_look where addtime<".$times."");
			$info['msg'] = '恭喜你，成功删除了3个月以前的记录';
		}else{
			$this->db->query("delete from ".CS_SqlPrefix."news_look");
			$this->db->query("TRUNCATE TABLE ".CS_SqlPrefix."news_look");
			$info['msg'] = '恭喜你，阅读记录已清空';
		}
        $info['url'] = site_url('news/admin/look').'?v='.rand(1000,9999);
        getjson($info,0);
	}
}

