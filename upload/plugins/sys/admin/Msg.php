<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-20
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Msg extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
		$this->lang->load('admin_msg');
        $this->Csadmin->Admin_Login();
	}
    //私信列表
	public function index(){
        $zd   = $this->input->get_post('zd',true,true);
        $key  = $this->input->get_post('key',true,true);
        $page = intval($this->input->get('page'));
        if($page==0) $page=1;

        $data['page'] = $page;
        $data['zd'] = $zd;
        $data['key'] = $key;

        $sql_string = "SELECT * FROM ".CS_SqlPrefix."msg where 1=1";
		if(!empty($key)){
			if($zd=='name'){
                $sql_string.= " and name like '%".str_replace('%','',$key)."%'";
			}else{
			    if($zd=='user'){
                    $uid=getzd('user','id',$key,'name');
			    }else{
                    $uid=$key;
			    }
			    $sql_string.= " and (uida=".intval($uid)." or uidb=".intval($uid).")";
			}
		}

        $sql_string.= " order by addtime desc";
        $count_sql = str_replace('*','count(*) as count',$sql_string);
        $query = $this->db->query($count_sql)->result_array();
        $total = $query[0]['count'];

        $per_page = 15; 
        $totalPages = ceil($total / $per_page)?ceil($total / $per_page):1; // 总页数
        $page = ($page>$totalPages)?$totalPages:$page;
        $data['nums'] = $total;
        if($total<$per_page){
            $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);
        $data['msg'] = $query->result();

        $base_url = site_url('msg')."?zd=".$zd."&key=".$key."&page=";
        $data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('msg.html',$data);
	}

    //阅读私信
	public function look(){
        $id = $this->input->get_post('id',true,true);
		if(empty($id)) exit(L('plub_01'));
        $row=$this->db->query("SELECT did,uida,uidb,name,neir,addtime FROM ".CS_SqlPrefix."msg where id=".$id."")->row();
		if(!$row) exit(L('plub_02'));
		$unamea=$unameb=L('plub_03');
        if($row->uida>0){
           $rowa=$this->db->query("SELECT name FROM ".CS_SqlPrefix."user where id=".$row->uida."")->row();
           $unamea=($rowa)?$rowa->name:L('plub_04').$row->uida;
        }
        if($row->uidb>0){
           $rowb=$this->db->query("SELECT name FROM ".CS_SqlPrefix."user where id=".$row->uidb."")->row();
           $unameb=($rowb)?$rowb->name:L('plub_04').$row->uidb;
        }
        $data['zt']=($row->did==0)?'<font class="colord">'.L('plub_05').'</font>':'<font class="colorl">'.L('plub_06').'</font>';
        $data['unamea'] = $unamea;
        $data['unameb'] = $unameb;
        $data['msg']=$row;
        $this->load->view('msg_look.html',$data);
	}
	
	//删除私信
	public function del(){
        $id = $this->input->get_post('id',true,true);
		if(empty($id)) getjson(L('plub_07'));
		$this->Csdb->get_del('msg',$id);
		$info['url'] = site_url('msg').'?v='.rand(1000,9999);
		getjson($info,0);
	}
}

