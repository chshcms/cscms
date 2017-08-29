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

    //专题首页
	public function index($fid='id',$page = 0){
        $page  = intval($page);   //页数
        if($page==0) $page=1;

        //判断运行模式,生成则跳转至静态页面
		$html=config('Html_Uri');
        if(config('Web_Mode')==3 && $html['topic/lists']['check']==1){
            //获取静态路径
			$Htmllink=LinkUrl('topic/lists',$fid,0,$page,'dance');
			header("Location: ".$Htmllink);
			exit;
		}
		$row=array();

		$zdy['[topic:link]'] = LinkUrl('topic','id',0,$page,'dance');
		$zdy['[topic:hlink]'] = LinkUrl('topic','hits',0,$page,'dance');
		$zdy['[topic:rlink]'] = LinkUrl('topic','ri',0,$page,'dance');
		$zdy['[topic:zlink]'] = LinkUrl('topic','zhou',0,$page,'dance');
		$zdy['[topic:ylink]'] = LinkUrl('topic','yue',0,$page,'dance');
		$zdy['[topic:slink]'] = LinkUrl('topic','fav',0,$page,'dance');
		//装载模板并输出
        $this->Cstpl->plub_list($row,0,$fid,$page,'',false,'topic.html','topic','','歌曲专辑','歌曲专辑','',$zdy);
	}

    //专题列表
	public function lists($fid='id', $cid=0, $page=1){
        $page  = intval($page);   //页数
        if($page==0) $page=1;
        //判断ID
        if($cid==0) msg_url(L('dance_09'),Web_Path);
        //获取数据
	    $row=$this->Csdb->get_row_arr('dance_list','*',$cid);
	    if(!$row){
                 msg_url(L('dance_18'),Web_Path);
	    }

        //判断运行模式,生成则跳转至静态页面
		$html=config('Html_Uri');
        if(config('Web_Mode')==3 && $html['topic/lists']['check']==1){
            //获取静态路径
			$Htmllink=LinkUrl('topic/lists',$fid,$cid,$page,'dance');
			header("Location: ".$Htmllink);
			exit;
		}
		//需要替换的标签
		$fidetpl = array(
			'[topic:cids]'=>$cid,
			'[topic:link]'=>LinkUrl('topic/lists','id',$cid,$page,'dance'),
			'[topic:hlink]'=>LinkUrl('topic/lists','hits',$cid,$page,'dance'),
			'[topic:rlink]'=>LinkUrl('topic/lists','ri',$cid,$page,'dance'),
			'[topic:zlink]'=>LinkUrl('topic/lists','zhou',$cid,$page,'dance'),
			'[topic:ylink]'=>LinkUrl('topic/lists','yue',$cid,$page,'dance'),
			'[topic:slink]'=>LinkUrl('topic/lists','fav',$cid,$page,'dance')
		);
		//装载模板并输出
        $this->Cstpl->plub_list($row,$cid,$fid,$page,$cid,false,'topic.html','topic/lists','',L('dance_22'),L('dance_22'),'',$fidetpl);
	}

    //专题内容
	public function show($fid='id',$id=0,$page=0){
        $id  = intval($id);
		$page = intval($page);
		if(is_numeric($fid)){
			$page = intval($id);
			$id  = intval($fid);
			$fid = 'id';
		}
        //判断ID
        if($id==0) msg_url(L('dance_09'),Web_Path);
        //获取数据
	    $row=$this->Csdb->get_row_arr('dance_topic','*',$id);
	    if(!$row || $row['yid']>0){
           	msg_url(L('dance_23'),Web_Path);
	    }
        //判断运行模式,生成则跳转至静态页面
		$html=config('Html_Uri');
        if(config('Web_Mode')==3 && $html['topic/show']['check']==1){
            //获取静态路径
			$Htmllink=LinkUrl('topic/show',$id,1,'dance');
			header("Location: ".$Htmllink);
			exit;
		}
		//装载模板并输出
		$ids['tid']=$id;
		$ids['singerid']=$row['singerid'];
		$ids['uid']=$row['uid'];

		$zdy['[topic:pl]'] = get_pl('dance',$id,1);
		$hitslink = hitslink('hits/ids/'.$id.'/topic','dance');

		//SQL
		$sql = "select {field} from ".CS_SqlPrefix."dance where tid=".$id;

		//模板
		$skin = empty($row['skins']) ? 'topic-show.html' : $row['skins'];
		//输出内容
        $this->Cstpl->plub_list($row,$id,$fid,$page,$ids,false,$skin,'topic/show','topic',$row['name'].' - '.L('dance_22'),$row['tags'],'',$zdy,$sql,$hitslink);
	}
}