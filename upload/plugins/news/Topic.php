<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-03
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Topic extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl'); //装载视图模型
	}

    //专题列表
	public function lists($fid='id',$page = 0){
        $page  = intval($page);   //页数
        if($page==0) $page=1;

        //判断运行模式,生成则跳转至静态页面
		$html=config('Html_Uri');
        if(config('Web_Mode')==3 && $html['topic/lists']['check']==1){
            //获取静态路径
			$Htmllink=LinkUrl('topic/lists',$fid,0,$page,'news');
			header("Location: ".$Htmllink);
			exit;
		}
		$row=array();

		$zdy['[topic:link]'] = LinkUrl('topic','id',0,$page,'news');
		$zdy['[topic:hlink]'] = LinkUrl('topic','hits',0,$page,'news');
		$zdy['[topic:rlink]'] = LinkUrl('topic','ri',0,$page,'news');
		$zdy['[topic:zlink]'] = LinkUrl('topic','zhou',0,$page,'news');
		$zdy['[topic:ylink]'] = LinkUrl('topic','yue',0,$page,'news');
		//装载模板并输出
        $Mark_Text=$this->Cstpl->plub_list($row,0,$fid,$page,'',false,'topic.html','topic','','新闻专题','新闻专题','',$zdy);
	}

    //专题内容
	public function show($id=0){
        $id    = intval($id);   //ID
        //判断ID
        if($id==0) msg_url('出错了，ID不能为空！',Web_Path);
        //获取数据
	    $row=$this->Csdb->get_row_arr('news_topic','*',$id);
	    if(!$row || $row['yid']>0){
                 msg_url('出错了，该专题不存在！',Web_Path);
	    }
        //判断运行模式,生成则跳转至静态页面
		$html=config('Html_Uri');
        if(config('Web_Mode')==3 && $html['topic/show']['check']==1){
            //获取静态路径
			$Htmllink=LinkUrl('topic','show',$id,1,'news');
			header("Location: ".$Htmllink);
			exit;
		}
		$zdy['[topic:pl]'] = get_pl('news',$id,1);
		$hitslink = hitslink('hits/ids/'.$id.'/topic','news');
		//装载模板并输出
        $this->Cstpl->plub_show('topic',$row,$id,true,'topic-show.html',$row['name'],$row['name'],'','',$zdy,$hitslink);
	}
}


