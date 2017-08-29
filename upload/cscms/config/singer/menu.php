<?php

if (!defined('FCPATH')) exit('No direct script access allowed');

return array(

	//后台菜单部分	
	'admin' => array(
		array(
			'name' => '歌手管理',
			'menu' => array(
				array(
					'name' => '歌手管理',
					'link' => 'admin/singer'
				),
				array(
					'name' => '歌手分类',
					'link' => 'admin/lists'
				),
			),
		),
		
		array(
			'name' => '静态生成',
			'menu' => array(
				array(
					'name' => '生成歌手首页',
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
					'name' => '生成自定义页',
					'link' => 'admin/html/opt'
				)
			),
		)
	
	),
	
	//会员中心菜单部分
	'user' => array(
		array(
			'name' => '歌手管理',
			'menu' => array(
				array(
					'name' => '我的歌手',
					'link' => 'user/singer',
				),
				array(
					'name' => '新增歌手',
					'link' => 'user/singer/add',
				)
			)
		),
	),
	
	//会员空间菜单部分
	'home' => array(
	),
);
