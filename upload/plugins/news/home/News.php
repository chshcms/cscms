<?php if ( ! defined('HOMEPATH')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-18
 */
class News extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	}

	public function index($cid=0,$page=1){
        $cid = (int)$cid;   //CID
        $page = (int)$page;   //页数
		//模板
		$tpl='news.html';
		//当前会员
		$uid=get_home_uid();
	    $row=$this->Csdb->get_row_arr('user','*',$uid);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title=$row['nichen'].'的文章';
		$ids['uid']=$row['id'];
		$ids['uida']=$row['id'];

		$sql = '';
		if($cid>0){
			$cids = getChild($cid);
			if(is_numeric($cids)){
            	$sql = "SELECT {field} FROM ".CS_SqlPrefix."news where cid=".$cids;
			}else{
            	$sql = "SELECT {field} FROM ".CS_SqlPrefix."news where cid in (".$cids.")";
			}
    	}
        $this->Cstpl->home_list($row,'news',$page,$tpl,$title,$ids,$cid,$sql);
	}
}

