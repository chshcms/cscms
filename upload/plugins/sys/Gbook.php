<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-25
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Gbook extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	}

    //留言
	public function index(){
        $this->Cstpl->gbook();
	}

    //留言列表
	public function lists($ac='',$page=1){
	    //关闭数据库缓存
        $this->db->cache_off();
	    $callback = $this->input->get('callback',true);
        $Mark_Text = $this->Cstpl->gbook_list($page);
        getjson(array('str'=>$Mark_Text),0,1,$callback);
	}

    //新增留言
	public function add(){
	    //关闭数据库缓存
        $this->db->cache_off();
		$token=$this->input->post('token', TRUE);
		$add['neir']=$this->input->post('neir',true);
		$add['neir']=filter($add['neir']);
		if(User_BookFun==0){
            $error=L('p_14');
		}elseif(!get_token('gbook_token',1,$token)){
            $error=L('p_15');
		}elseif(isset($_SESSION['gkaddtime']) && time()<$_SESSION['gkaddtime']+30){
            $error=L('p_18');
		}elseif(empty($add['neir'])){
            $error=L('p_16');
		}else{

            $add['uidb']=isset($_SESSION['cscms__id'])?intval($_SESSION['cscms__id']):0;
		    $add['cid']=1;
		    $add['ip']=getip();
		    $add['addtime']=time();

            $ids=$this->Csdb->get_insert('gbook',$add);
		    if(intval($ids)==0){
                $error=L('p_17'); //失败
			}else{
                //摧毁token
		        get_token('gbook_token',2);
                $_SESSION['gkaddtime']=time();
                $error='ok';
			}
		}
		getjson($error);
	}
}
