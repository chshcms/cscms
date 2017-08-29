<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-16-16
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl'); //装载视图模型
	}

    //搜索列表
	public function index(){
	    $data['tags'] = $this->input->get_post('tags',true,true);
	    $data['name'] = $this->input->get_post('name',true,true);
	    $data['zc'] = $this->input->get_post('zc',true,true);
	    $data['zq'] = $this->input->get_post('zq',true,true);
	    $data['bq'] = $this->input->get_post('bq',true,true);
	    $data['hy'] = $this->input->get_post('hy',true,true);
	    $data['key'] = $this->input->get_post('key',true,true);
	    $data['cid'] = intval($this->input->get_post('cid',true));
        $page  = intval($this->input->get_post('page',true));   //页数
        if($page==0) $page=1;
		//搜索字母
	    $zm = $this->input->get_post('zm',true,true);
		$data['zm']['zd'] = 'name'; //要搜索的字母的字段
		$data['zm']['zm'] = $zm; //要搜索的字母
		//装载模板并输出
        $this->Cstpl->plub_search('dance',$data,$page);
	}
}


