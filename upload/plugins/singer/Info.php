<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-16
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Info extends Cscms_Controller {
	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	}

    //内容
	public function index($id = 0){
        $id = intval($id);   //ID
        //判断ID
        if($id==0) msg_url('出错了，ID不能为空！',Web_Path);
        //获取数据
	    $row=$this->Csdb->get_row_arr('singer','*',$id);
	    if(!$row){
	    	msg_url('出错了，该歌手不存在！',Web_Path);
	    }
        //判断运行模式,生成则跳转至静态页面
		$html=config('Html_Uri');
        if(config('Web_Mode')==3 && $html['show']['check']==1){
            //获取静态路径
			$Htmllink=LinkUrl('info','',$id,0,'singer');
			header("Location: ".$Htmllink);
			exit;
		}
		$arr['cid']=getChild($row['cid']);
		$arr['tags']=$row['tags'];
		$arr['singerid']=$id;

		$zdy['[singer:tags]'] = tagslink($row['tags']);
		$zdy['[singer:pl]'] = get_pl('singer',$id);
		$zdy['[singer:link]'] = LinkUrl('show','id',$row['id'],1,'singer');
		$zdy['[singer:classlink]'] = LinkUrl('lists','id',$row['cid'],1,'singer');
		$zdy['[singer:classname]'] = getzd('singer_list','name',$row['cid']);
		unset($row['tags']);

		//装载模板并输出
        $this->Cstpl->plub_show('singer',$row,$arr,false,'info.html',$row['name'],$row['name'],'','',$zdy);
	}
}