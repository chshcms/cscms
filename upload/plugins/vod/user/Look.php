<?php if ( ! defined('CSCMS')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-08
 */
class Look extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	    $this->load->model('Csuser');
        $this->Csuser->User_Login();
	}

    //视频观看记录
	public function index($cid=0,$page=1){
	    $cid=intval($cid); //分类ID
	    $page=intval($page); //分页
		//模板
		$tpl='look.html';
		//URL地址
	    $url='look/index/'.$cid;
        $sqlstr = "select {field} from ".CS_SqlPrefix."vod_look where uid=".$_SESSION['cscms__id'];
        if($cid>0){
			$cids=getChild($cid);
            $sqlstr.= " and cid in(".$cids.")";
		}
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title='我观看的视频 - 会员中心';
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];

		$zdy['[vod:cid]'] = $cid;
        $this->Cstpl->user_list($row,$url,$page,$tpl,$title,'',$sqlstr,$ids,false,'user',$zdy);
	}
}

