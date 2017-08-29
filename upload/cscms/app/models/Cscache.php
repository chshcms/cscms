<?php
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2018 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2017-05-12
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');
class Cscache extends CI_Model{

    function __construct (){
	    parent:: __construct ();
        $this->_time = PLUBPATH == 'sys' ? Cache_Time : config('Cache_Time');;
        $this->_is   = PLUBPATH == 'sys' ? Cache_Is : config('Cache_Is');
        //后台跳过
        if(defined('IS_ADMIN')) $this->_is = 0;
        //加载CI缓存机制
        if($this->_is == 1){
            //文件缓存
            if(Cache_Mx == 'file'){
                //缓存路径
                $cache_dir = $this->config->item('cache_path');
                //创建文件夹
                mkdirss($cache_dir);
            }
            //装载驱动
            $this->load->driver('cache', array('adapter' => Cache_Mx, 'backup' => 'file', 'key_prefix' => CS_SqlPrefix));
            //判断主机是否支持当前缓存
            $mx = Cache_Mx;
            if(!$this->cache->$mx->is_supported()){
                msg_txt('当前主机不支持 '.$mx.' 缓存适配器~');
            }
        }
    }

    //获取缓存
    function get($id){
        //缓存关闭状态
        if($this->_is==0) return false;
        //缓存唯一标示
        $this->_id = md5($id);
        //获取缓存
        $data = $this->cache->get($this->_id);
        //缓存存在并有效
        if($data !== false  && !empty($data)){
            echo $data;exit;
        }else{ //不存在
            ob_start();
            ob_implicit_flush(false);
            return false;
        }
    }

    //写入缓存
    function save(){
        if($this->_is==1){
            $data = ob_get_contents();
            //写入缓存
            $this->cache->save($this->_id, $data, $this->_time);
            ob_end_clean();
            echo $data;
        }
    }
}