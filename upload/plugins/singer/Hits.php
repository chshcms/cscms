<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-09
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Hits extends Cscms_Controller {

	function __construct(){
		parent::__construct();
		$this->load->library('user_agent');
		if(!$this->agent->is_referral()) show_error('您访问的页面不存在~!',404,Web_Name.'提醒您');
		//关闭数据库缓存
		$this->db->cache_off();
	}

    //增加播放人气
	public function ids($id=0){
		$id = intval($id);   //ID			
		if($id==0) exit;
		$row=$this->Csdb->get_row('singer','rhits,zhits,yhits,hits',$id);
		if(!$row){
		   exit();
		}
		//增加人气
		$updata['rhits']=$row->rhits+1;
		$updata['zhits']=$row->zhits+1;
		$updata['yhits']=$row->yhits+1;
		$updata['hits']=$row->hits+1;
		$this->Csdb->get_update('singer',$id,$updata);

		//清空月人气
		$str = array();
		$month=@file_get_contents(CSCMS.PLUBPATH.FGF."month.txt");
		if($month!=date('m')){
			$str[]='yhits=0';
		    write_file(CSCMS.PLUBPATH.FGF."month.txt",date('m'));
		}
		//清空周人气
		$week=@file_get_contents(CSCMS.PLUBPATH.FGF."week.txt");
		if($week!=date('W',time())){
			$str[]='zhits=0';
		    write_file(CSCMS.PLUBPATH.FGF."week.txt",date('W',time()));
		}
		//清空日人气
		$day=@file_get_contents(CSCMS.PLUBPATH.FGF."day.txt");
		if($day!=date('d')){
			$str[]='rhits=0';
		    write_file(CSCMS.PLUBPATH.FGF."day.txt",date('d'));
		}
		if(!empty($str)){
		    $this->db->query("update ".CS_SqlPrefix."singer set ".implode(',',$str));
		}
	}

    //动态加载人气
	public function dt($op='',$id=0,$type=''){
		$id = intval($id);   //ID

		$dos = array('hits', 'yhits', 'zhits', 'rhits');
		$op= (!empty($op) && in_array($op, $dos))?$op:'hits';
		$row=$this->Csdb->get_row('singer',$op,$id);
		if(!$row){
		    echo "document.write('0');";
		}else{
			echo "document.write('".$row->$op."');";
		}
	}
}
