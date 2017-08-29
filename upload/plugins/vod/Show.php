<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-02
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Show extends Cscms_Controller {
	function __construct(){
	    parent::__construct();
	    $this->load->helper('vod');
	    $this->load->model('Cstpl');
	}

    //视频内容
	public function index($fid = 'id', $id = 0, $return = FALSE){
        $id = (intval($fid)>0)?intval($fid):intval($id);   //ID
        //判断ID
        if($id==0) msg_url('出错了，ID不能为空！',Web_Path);
        //判断运行模式,生成则跳转至静态页面
		$html=config('Html_Uri');
        if(config('Web_Mode')==3 && $html['show']['check']==1){
            //获取静态路径
			$Htmllink=LinkUrl('show',$fid,$id,0,'vod');
			header("Location: ".$Htmllink);
			exit;
		}
        //获取数据
	    $row=$this->Csdb->get_row_arr('vod','*',$id);
	    if(!$row){
            msg_url('出错了，该数据不存在或者没有审核！',Web_Path);
	    }
		//获取当前分类下二级分类ID
		$arr['cid'] = getChild($row['cid']);
		$arr['uid'] = $row['uid'];
		$arr['singerid'] = $row['singerid'];
		$arr['tags'] = $row['tags'];
		$skins=getzd('vod_list','skins2',$row['cid']);
		if(empty($skins)) $skins='show.html';

		//评论
		$zdy['[vod:pl]'] = get_pl('vod',$id);
		//分类地址、名称
		$zdy['[vod:link]'] = LinkUrl('show','id',$row['id'],1,'vod');
		$zdy['[vod:playlink]'] = VodPlayUrl('play',$id);
		$zdy['[vod:classlink]'] = LinkUrl('lists','id',$row['cid'],1,'vod');
		$zdy['[vod:classname]'] = getzd('vod_list','name',$row['cid']);
		//主演、导演、标签、年份、地区、语言加超级连接
		$zdy['[vod:zhuyan]'] = tagslink($row['zhuyan'],'zhuyan');
		$zdy['[vod:daoyan]'] = tagslink($row['daoyan'],'daoyan');
		$zdy['[vod:yuyan]'] = tagslink($row['yuyan'],'yuyan');
		$zdy['[vod:diqu]'] = tagslink($row['diqu'],'diqu');
		$zdy['[vod:tags]'] = tagslink($row['tags']);
		$zdy['[vod:year]'] = tagslink($row['year'],'year');
		//评分
		$zdy['[vod:pfen]'] = getpf($row['pfen'],$row['phits']);
		$zdy['[vod:pfenbi]'] = getpf($row['pfen'],$row['phits'],2);

		unset($row['zhuyan']);
		unset($row['daoyan']);
		unset($row['yuyan']);
		unset($row['diqu']);
		unset($row['tags']);
		unset($row['year']);
		unset($row['pfen']);
		unset($row['phits']);

		//装载模板并输出
		$this->Cstpl->plub_show('vod',$row,$arr,false,$skins,$row['name'],$row['name'],'','',$zdy);
	}
}