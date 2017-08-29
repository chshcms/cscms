<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-09-20
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->helper('vod');
	    $this->load->model('Cstpl'); //装载视图模型
	}

    //搜索列表
	public function index(){
	    $data['zhuyan'] = $this->input->get_post('zhuyan',true,true); //主演
	    $data['daoyan'] = $this->input->get_post('daoyan',true,true); //导演
	    $data['yuyan'] = $this->input->get_post('yuyan',true,true); //语言
	    $data['diqu'] = $this->input->get_post('diqu',true,true); //地区
	    $data['year'] = $this->input->get_post('year',true,true); //年份
	    $data['tags'] = $this->input->get_post('tags',true,true); //TAGS标签
	    $data['type'] = $this->input->get_post('type',true,true); //剧情
	    $data['key'] = $this->input->get_post('key',true,true);   //关键字
	    $data['cid'] = intval($this->input->get_post('cid',true)); //分类ID
        $page  = intval($this->input->get_post('page',true));   //页数
        if($page==0) $page=1;
		//搜索字母
	    $zm = $this->input->get_post('zm',true,true);
		$data['zm']['zd'] = 'name'; //要搜索的字母的字段
		$data['zm']['zm'] = $zm; //要搜索的字母
        //剧情分类ID
		$data['sid'] = $data['cid'];
		if($data['cid']>0){
			$fid=getzd('vod_list','fid',$data['cid']);
			$data['sid'] = ($fid==0)?$data['cid']:$fid;
			//获取下级分类ID
			$cids = getChild($data['cid']);
			if(!is_numeric($cids)){
				$cid2 = array(
					0 => $data['cid'],
					$data['cid'] => explode(',',$cids)
				);
				$data['cid'] = $cid2;
			}
		}
		//装载模板并输出
        $this->Cstpl->plub_search('vod',$data,$page);
	}
}


