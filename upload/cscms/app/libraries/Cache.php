<?php
/**
 * @Cscms 3.5 open source management system
 * @copyright 2009-2013 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2013-04-27
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 文件缓存类
 */
class Cache {

    function __construct (){
		$this->_time = PLUBPATH == 'sys' ? Cache_Time : config('Cache_Time');;
		$this->_is   = PLUBPATH == 'sys' ? Cache_Is : config('Cache_Is');
	}

    //读取缓存
	function get($cacheid){
		$this->_id = md5($cacheid);
    	$CI = &get_instance();
		$CI->load->driver('cache', array('adapter' => Cache_Mx, 'backup' => 'file', 'key_prefix' => CS_SqlPrefix));
		return $CI->cache->get($this->_id);
	}

    //写入缓存
	function save($data){
		//创建文件夹
		mkdirss($this->_dir);
		//写缓存
		return $this->ci->cache->save($this->_id, $data, $this->_time);
	}

    //获取缓存
	function start($id){
		if($this->_is==0){ //关闭缓存
		    return false;
		}
		$data = $this->get($id);
		if($data !== false  && !empty($data)){
			exit($data);
			return true;
		}else{
			ob_start();
			ob_implicit_flush(false);
			return false;
		}
	}

	function end(){
		if($this->_is==1){
		    $data = ob_get_contents();
		    ob_end_clean();
			$this->save($data);
		    echo $data;
		}
	}
}

