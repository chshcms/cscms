<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-16
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Show extends Cscms_Controller {
	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	}

    //内容
	public function index($fid = 'id', $id = 0){
        $id = (intval($fid)>0)?intval($fid):intval($id);   //ID
        //判断ID
        if($id==0) msg_url('出错了，ID不能为空！',Web_Path);
        //获取数据
	    $row=$this->Csdb->get_row_arr('pic_type','*',$id);
	    if(!$row){
	    	msg_url('出错了，该数据不存在或者没有审核！',Web_Path);
	    }
        //判断运行模式,生成则跳转至静态页面
		$html=config('Html_Uri');
        if(config('Web_Mode')==3 && $html['show']['check']==1){
            //获取静态路径
			$Htmllink=LinkUrl('show',$fid,$id,0,'pic');
			header("Location: ".$Htmllink);
			exit;
		}
		//获取当前分类下二级分类ID
		$arr['cid']=getChild($row['cid']);
		$arr['uid']=$row['uid'];
		$arr['tags']=$row['tags'];
		$arr['sid']=$row['id'];

		//标签加超级连接
		$zdy['[pic:tags]'] = tagslink($row['tags']);
		unset($row['tags']);

		//默认模板
		$skin = empty($row['skins'])?'show.html':$row['skins'];
		if(defined('MOBILE')){
			$tplfile = VIEWPATH.'mobile'.FGF.'skins'.FGF.Mobile_Skins_Dir.FGF.PLUBPATH.FGF.$skin;
		}else{
			$tplfile = VIEWPATH.'pc'.FGF.'skins'.FGF.Pc_Skins_Dir.FGF.PLUBPATH.FGF.$skin;
		}
		$tplstr = file_exists($tplfile) ? file_get_contents($tplfile) : '';

		$zdy['[pic:pl]'] = get_pl('pic',$id);
		$zdy['[pic:link]'] = LinkUrl('show','id',$row['id'],1,'pic');
		$zdy['[pic:classlink]'] = LinkUrl('lists','id',$row['cid'],1,'pic');
		$zdy['[pic:classname]'] = getzd('pic_list','name',$row['cid']);

		//获取当前相册总数
		$pcount=$this->db->query("Select id from ".CS_SqlPrefix."pic where sid=".$id."")->num_rows();
		$zdy['[pic:count]'] = $pcount;
		//第一张图片
		$rowp=$this->db->query("Select pic,content from ".CS_SqlPrefix."pic where sid=".$id." order by id desc limit 1")->row();
        $pics = $rowp ? $rowp->pic : '';
        $content = $rowp ? $rowp->content : '';
		$zdy['[pic:url]'] = piclink('pic',$pics);
		$zdy['[pic:content]'] = $content;

		//获取上下张
		if(strpos($tplstr,'[pic:slink]') !== false || strpos($tplstr,'[pic:sname]') !== false || strpos($tplstr,'[pic:spic]') !== false){
			$rowd=$this->db->query("Select id,cid,pic,name from ".CS_SqlPrefix."pic_type where id<".$id." order by id desc limit 1")->row();
			if($rowd){
			    $zdy['[pic:slink]'] = LinkUrl('show','id',$rowd->id,1,'pic');
			    $zdy['[pic:spic]'] = piclink('pic',$rowd->pic);
			    $zdy['[pic:sid]'] = $rowd->id;
			    $zdy['[pic:sname]'] = $rowd->name;
			}else{
			    $zdy['[pic:slink]'] = '###';
			    $zdy['[pic:spic]'] = piclink('pic','');
			    $zdy['[pic:sid]'] = 0;
			    $zdy['[pic:sname]'] = '没有了';
			}
		}
		if(strpos($tplstr,'[pic:xlink]') !== false || strpos($tplstr,'[pic:xname]') !== false || strpos($tplstr,'[pic:xpic]') !== false){
			$rowd=$this->db->query("Select id,cid,pic,name from ".CS_SqlPrefix."pic_type where id>".$id." order by id asc limit 1")->row();
			if($rowd){
			    $zdy['[pic:xlink]'] = LinkUrl('show','id',$rowd->id,1,'pic');
			    $zdy['[pic:xpic]'] = piclink('pic',$rowd->pic);
			    $zdy['[pic:xid]'] = $rowd->id;
			    $zdy['[pic:xname]'] = $rowd->name;
			}else{
			    $zdy['[pic:xlink]'] = '###';
			    $zdy['[pic:xpic]'] = piclink('pic','');
			    $zdy['[pic:xid]'] = 0;
			    $zdy['[pic:xname]'] = '没有了';
			}
		}
		//增加人气链接
		$hitslink = hitslink('hits/ids/'.$id,'pic');
		//装载模板并输出
        $this->Cstpl->plub_show('pic',$row,$arr,false,$skin,$row['name'],$row['name'],'','',$zdy,$hitslink);
	}
}