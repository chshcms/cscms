<?php if ( ! defined('HOMEPATH')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-18
 */
class Pic extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	}

	public function index($cid=0,$page=1){
        $cid = (int)$cid;   //CID
        $page = (int)$page;   //页数
		//模板
		$tpl='pic.html';
		//当前会员
		$uid=get_home_uid();
	    $row=$this->Csdb->get_row_arr('user','*',$uid);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title=$row['nichen'].'的图集';
		$ids['uid']=$row['id'];
		$ids['uida']=$row['id'];

        $sql = '';
		if($cid>0){
			$cids = getChild($cid);
			if(is_numeric($cids)){
            	$sql = "SELECT {field} FROM ".CS_SqlPrefix."pic_type where cid=".$cids;
			}else{
            	$sql = "SELECT {field} FROM ".CS_SqlPrefix."pic_type where cid in (".$cids.")";
			}
    	}
        $this->Cstpl->home_list($row,'pic',$page,$tpl,$title,$ids,$cid,$sql);
	}
}

