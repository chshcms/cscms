<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-09-20
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lists extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl'); //装载视图模型
	}

    //分类列表
	public function index($fid='id', $id = 0, $page = 0){
        $id = intval($id);   //ID
        $page  = intval($page);   //页数
        if($page==0) $page=1;
        //判断ID
        if($id==0) msg_url('出错了，ID不能为空！',Web_Path);
        //获取数据
	    $row=$this->Csdb->get_row_arr('news_list','*',$id);
	    if(!$row || $row['yid']>0){
                 msg_url('出错了，该分类不存在！',Web_Path);
	    }
        //判断运行模式,生成则跳转至静态页面
		$html=config('Html_Uri');
        if(config('Web_Mode')==3 && $html['lists']['check']==1){
            //获取静态路径
			$Htmllink=LinkUrl('lists',$fid,$id,$page,'news');
			header("Location: ".$Htmllink);
			exit;
		}
		//获取当前分类下二级分类ID
		$arr['cid']=getChild($id);
		//装载模板并输出
		$skins=empty($row['skins'])?'list.html':$row['skins'];
        $this->Cstpl->plub_list($row,$id,$fid,$page,$arr,false,$skins,'lists','news',$row['name'],$row['name']);
	}
}


