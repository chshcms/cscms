<?php

if (!defined('FCPATH')) exit('No direct script access allowed');

return array(

	//后台菜单部分	
	'admin' => array(
		array(
			'name' => '图片管理',
			'menu' => array(
				array(
					'name' => '相册管理',
					'link' => 'admin/type'
				),
				array(
					'name' => '相册分类',
					'link' => 'admin/lists'
				),
				array(
					'name' => '图片管理',
					'link' => 'admin/pic'
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
					'name' => '生成自定义页',
					'link' => 'admin/html/opt'
				),
			),
		)
	
	),
	
	//会员中心菜单部分
	'user' => array(
		array(
			'name' => '相册管理',
			'menu' => array(
				array(
					'name' => '我的相册',
					'link' => 'user/pic/type',
				),
				array(
					'name' => '我的图片',
					'link' => 'user/pic',
				),
				array(
					'name' => '相册制作',
					'link' => 'user/pic/addtype',
				)
			)
		),
	),
);
