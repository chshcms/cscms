<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-08
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Tags extends Cscms_Controller {

	function __construct(){
		parent::__construct();
	}

    //TAGS列表
	public function index(){
		$data['fid']=$this->input->get('fid',true);   //返回ID，一个页面多个返回可以用到
		if($data['fid']=='undefined') $data['fid']='tags';
		$data['tags']=$this->db->query("SELECT * FROM ".CS_SqlPrefix."tags where fid=0 order by xid asc")->result();
		$this->load->get_templates('common');
		$this->load->view('tags.html',$data);
	}
}