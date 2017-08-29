<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-21
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Homes extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
		$this->lang->load('admin_home');
        $this->Csadmin->Admin_Login();
	}

    //会员空间配置
	public function index(){
        $this->load->view('home.html');
	}

    //保存空间配置
	public function save(){
	    $Home_Mode = intval($this->input->post('Home_Mode'));
	    $Home_Ym = intval($this->input->post('Home_Ym'));
	    $Home_Fs = intval($this->input->post('Home_Fs'));
	    $Home_Hits = intval($this->input->post('Home_Hits'));
	    $Home_YmUrl = $this->input->post('Home_YmUrl', TRUE);
	    $Home_Ymext = $this->input->post('Home_Ymext', TRUE);

        if($Home_Fs==0) $Home_Fs=1;

        if($Home_Ym==1 && empty($Home_YmUrl)){
            getjson(L('plub_01'));
		}

        $strs="<?php"."\r\n";
        $strs.="define('Home_Mode',".$Home_Mode.");  //会员伪静态开关    \r\n";
        $strs.="define('Home_Ym',".$Home_Ym."); //二级域名开关  \r\n";
        $strs.="define('Home_Fs',".$Home_Fs.");  //会员主页地址方式，1为会员名，2为会员ID   \r\n";
        $strs.="define('Home_Hits',".$Home_Hits."); //是否开启防刷机制    \r\n";
        $strs.="define('Home_YmUrl','".$Home_YmUrl."');  //泛域名地址   \r\n";
        $strs.="define('Home_Ymext','".$Home_Ymext."');  //泛域名保留地址";

        //写文件
        if (!write_file(CSCMS.'sys/Cs_Home.php', $strs)){
            getjson('./cscms/sys/Cs_Home.php'.L('plub_02'));
        }else{
            $info['url'] = site_url('homes')."?v=".rand(1000,9999);
            getjson($info,0);
        }
	}

	//会员模板使用记录
	public function pay(){
        $zd   = $this->input->get_post('zd',true);
        $key  = $this->input->get_post('key',true,true);
	        $page = intval($this->input->get('page'));
        if($page==0) $page=1;

        $data['page'] = $page;
        $data['zd'] = $zd;
        $data['key'] = $key;

        $sql_string = "SELECT * FROM ".CS_SqlPrefix."web_pay where 1=1";
		if(!empty($key)){
			 if($zd=='name'){
                 $uid=getzd('user','id',$key,'name');
			 }else{
                 $uid=$key;
			 }
			 $sql_string.= " and uid=".intval($uid)."";
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
        $data['pay'] = $query->result();

        $base_url = site_url('homes/pay')."?zd=".$zd."&key=".$key."&page=";
        $data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('home_pay.html',$data);
	}

    //删除记录
	public function del(){
        $id = $this->input->get_post('id',true,true);
		if(empty($id)) getjson(L('plub_03'));
		$this->Csdb->get_del('web_pay',$id);
       	$info['url'] = site_url('homes/pay').'?v='.rand(1000,9999);
       	getjson($info,0);
	}
}

