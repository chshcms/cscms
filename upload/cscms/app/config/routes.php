<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'index';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['short']	= 'api/short';
$route['picdata/(.+)']	= 'api/pic/index/$1';
$route['share/(\d+)']	= 'api/share/index/$1';
$route['sitemap.xml']	= 'api/sitemap';
$route['baidu.xml']	= 'api/baidu';
$route['google.xml']	= 'api/google';
$route['360.xml']    = 'api/s360';
$route['opt/(\w+)']	= 'opt/index/$1';

/**
 * 自定义路由
 */
 
//$route['自定义路由正则规则']	= '指向的路由URI（URI规则：控制器/方法/参数1的值/参数2的值...）';

//会员主页路由
if (PLUBPATH == 'sys') {
    if(Home_Ym==0){
        $route['([a-zA-Z0-9\_\-]+)/home/([a-zA-Z0-9]+)/(.+)']	= 'home/$2/index/$1/$3';
        $route['([a-zA-Z0-9\_\-]+)/home/([a-zA-Z0-9]+)']	= 'home/$2/index/$1';
        $route['([a-zA-Z0-9\_\-]+)/home']	= 'home/index/index/$1';
    }else{
        $route['home/gbook(.+)']	= 'home/gbook$1';
        $route['home/hits(.+)']	= 'home/hits$1';
        $route['home/ajax(.+)']	= 'home/ajax$1';
        $route['home/([a-z0-9]+)/(.+)']	= 'home/$1/index/$2';
        $route['home/([a-z0-9]+)']	= 'home/$1/index/$2';
        $route['home']	= 'home/index/index/$1';
    }
}else{
    $route['home/'.PLUBPATH.'/([0-9_\/]+)'] = 'home/'.PLUBPATH.'/index/$1';
    $route['home/'.PLUBPATH.'/(.+)'] = 'home/$1/index';
    $route['home/'.PLUBPATH.''] = 'home/'.PLUBPATH.'/index';
}
