<?php
if (!defined('FCPATH')) exit('No direct script access allowed');

return array(

	//后台菜单部分	
	'admin' => array(
		array(
			'name' => '音乐管理',
			'menu' => array(
				array(
					'name' => '歌曲管理',
					'link' => 'admin/dance'
				),
				array(
					'name' => '歌曲分类',
					'link' => 'admin/lists'
				),
				array(
					'name' => '服务器组',
					'link' => 'admin/server'
				),
				array(
					'name' => '歌曲专辑',
					'link' => 'admin/topic'
				),
				array(
					'name' => '歌曲扫描',
					'link' => 'admin/saomiao'
				),
				array(
					'name' => '收藏下载记录',
					'link' => 'admin/opt/fav'
				),
				array(
					'name' => '歌曲资源采集',
					'link' => 'admin/apiku'
				)
			)
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
					'name' => '生成播放页',
					'link' => 'admin/html/play'
				),
				array(
					'name' => '生成下载页',
					'link' => 'admin/html/down'
				),
				array(
					'name' => '生成专辑页',
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
			'name' => '歌曲管理',
			'menu' => array(
				array(
					'name' => '我的歌曲',
					'link' => 'user/dance',
				),
				array(
					'name' => '上传歌曲',
					'link' => 'user/dance/add',
				),
				array(
					'name' => '我的专辑',
					'link' => 'user/album',
				)
			)
		),
	),
);
