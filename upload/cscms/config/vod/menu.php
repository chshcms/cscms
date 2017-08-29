<?php

if (!defined('FCPATH')) exit('No direct script access allowed');

return array(

	//后台菜单部分	
	'admin' => array(
		array(
			'name' => '视频管理',
			'menu' => array(
				array(
					'name' => '视频管理',
					'link' => 'admin/vod'
				),
				array(
					'name' => '视频分类',
					'link' => 'admin/lists'
				),
				array(
					'name' => '剧情分类',
					'link' => 'admin/type'
				),
				array(
					'name' => '视频专题',
					'link' => 'admin/topic'
				),
				array(
					'name' => '收藏观看记录',
					'link' => 'admin/opt/fav'
				),
				array(
					'name' => '视频API资源库',
					'link' => 'admin/apiku'
				),
			),
		),
		
		array(
			'name' => '静态生成',
			'menu' => array(
				array(
					'name' => '生成版块首页',
					'link' => 'admin/html/index'
				),
				array(
					'name' => '生成分类页',
					'link' => 'admin/html/type'
				),
				array(
					'name' => '生成内容页',
					'link' => 'admin/html/show'
				),
				array(
					'name' => '生成专题页',
					'link' => 'admin/html/topic'
				),
				array(
					'name' => '生成自定义页',
					'link' => 'admin/html/opt'
				),
			),
		)
	
	),
	
	//会员中心菜单部分
	'user' => array(
		array(
			'name' => '视频管理',
			'menu' => array(
				array(
					'name' => '我的视频',
					'link' => 'user/vod',
				),
				array(
					'name' => '上传视频',
					'link' => 'user/vod/add',
				),
				array(
					'name' => '我的收藏',
					'link' => 'user/fav',
				)
			)
		),
	),
);
