<?php
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2017 chshcms.com. All rights reserved.
 * @Author:zhwdeveloper
 * @Dtime:2016-12-01
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');
return array(
	//后台主页菜单	
	'index' => array(
		array(
			'title' => L('menu-01-0'),
			'on'	=> 1,
			'menu'	=> array(
				array(
					'name' => '<i class="fa fa-sheqel"></i>'.L('menu-01-1'),
					'link' => site_url('plugins'),
				),
				array(
					'name' => '<i class="fa fa-cog"></i>'.L('menu-01-2'),
					'link' => site_url('setting'),
				),
				array(
					'name' => '<i class="fa fa-edit"></i>'.L('menu-01-3'),
					'link' => site_url('sys/editpass'),
				),
				array(
					'name' => '<i class="fa fa-calendar-check-o"></i>'.L('menu-01-4'),
					'link' => site_url('sys/log'),
				)
			)
		),
		array(
			'title' => L('menu-01-5'),
			'on'	=> 1,
			'menu'	=> array(
				array(
					'name' => '<i class="fa fa-spinner"></i>'.L('menu-01-6'),
					'link' => site_url('opt/del_cache'),
				),
				array(
					'name' => '<i class="fa fa-home"></i>'.L('menu-01-7'),
					'link' => site_url('html'),
				),
				array(
					'name' => '<i class="fa fa-history"></i>'.L('menu-01-8'),
					'link' => site_url('check'),
				),
				array(
					'name' => '<i class="fa fa-link"></i>'.L('menu-01-9'),
					'link' => site_url('links'),
				),
				array(
					'name' => '<i class="fa fa-anchor"></i>'.L('menu-01-10'),
					'link' => site_url('tags'),
				)
			)
		)
	),
	//系统页面菜单
	'sys' => array(
		array(
			'title' => L('menu-02-0'),
			'on'	=> 1,
			'menu'	=> array(
				array(
					'name' => L('menu-02-1'),
					'link' => site_url('setting'),
				),
				array(
					'name' => L('menu-02-2'),
					'link' => site_url('setting/ftp'),
				),
				array(
					'name' => L('menu-02-3'),
					'link' => site_url('mail'),
				),
				array(
					'name' => L('menu-02-4'),
					'link' => site_url('sms'),
				),
				array(
					'name' => L('menu-02-5'),
					'link' => site_url('pay'),
				),
				array(
					'name' => L('menu-02-6'),
					'link' => site_url('setting/tb'),
				),
				array(
					'name' => L('menu-02-7'),
					'link' => site_url('setting/denglu'),
				)
			)
		),
		array(
			'title' => L('menu-03-0'),
			'on'	=> 1,
			'menu'	=> array(
				array(
					'name' => L('menu-03-1'),
					'link' => site_url('basedb'),
				),
				array(
					'name' => L('menu-03-2'),
					'link' => site_url('check'),
				),
				array(
					'name' => L('menu-03-3'),
					'link' => site_url('upgrade'),
				),
				/*array(
					'name' => L('menu-03-4'),
					'link' => site_url('upgrade/check_tips'),
				),
				*/
				array(
					'name' => L('menu-03-5'),
					'link' => site_url('upload'),
				)
			)
		),
		array(
			'title' => L('menu-04-0'),
			'on'	=> 1,
			'menu'	=> array(
				array(
					'name' => L('menu-04-1'),
					'link' => site_url('sys'),
				),
				array(
					'name' => L('menu-04-2'),
					'link' => site_url('sys/zu'),
				),
				array(
					'name' => L('menu-04-3'),
					'link' => site_url('sys/log'),
				)
			)
		)
	),
	//工具页面菜单
	'tools' => array(
		array(
			'title' => L('menu-05-0'),
			'on'	=> 1,
			'menu'	=> array(
				array(
					'name' => L('menu-05-1'),
					'link' => site_url('label'),
				),
				array(
					'name' => L('menu-05-2'),
					'link' => site_url('label/js'),
				),
				array(
					'name' => L('menu-05-3'),
					'link' => site_url('label/page'),
				),
				array(
					'name' => L('menu-05-4'),
					'link' => site_url('label/deldata'),
				),
				array(
					'name' => L('menu-05-5'),
					'link' => site_url('label/editdata'),
				)
			)
		),
		array(
			'title' => L('menu-06-0'),
			'on'	=> 1,
			'menu'	=> array(
				array(
					'name' => L('menu-06-1'),
					'link' => site_url('collect'),
				),
				array(
					'name' => L('menu-06-2'),
					'link' => site_url('collect/lists').'?op=mobile',
				),
				array(
					'name' => L('menu-06-3'),
					'link' => site_url('collect/ruku'),
				)
			)
		)
	),
	//模版风格
	'skin' => array(
		array(
			'title' => L('menu-07-0'),
			'on'	=> 1,
			'menu'	=> array(
				array(
					'name' => L('menu-07-1'),
					'link' => site_url('skin').'?ac=pc',
				),
				array(
					'name' => L('menu-07-2'),
					'link' => site_url('skin').'?ac=pc&op=user',
				),
				array(
					'name' => L('menu-07-3'),
					'link' => site_url('skin').'?ac=pc&op=home',
				)
			)
		),
		array(
			'title' => L('menu-07-4'),
			'on'	=> 1,
			'menu'	=> array(
				array(
					'name' => L('menu-07-1'),
					'link' => site_url('skin').'?ac=mobile',
				),
				array(
					'name' => L('menu-07-2'),
					'link' => site_url('skin').'?ac=mobile&op=user',
				),
				array(
					'name' => L('menu-07-3'),
					'link' => site_url('skin').'?ac=mobile&op=home',
				)
			)
		),
		array(
			'title' => L('menu-07-5'),
			'on'	=> 1,
			'menu'	=> array(
				array(
					'name' => L('menu-07-6'),
					'link' => site_url('skin/yun'),
				),
				array(
					'name' => L('menu-07-7'),
					'link' => site_url('skin/tags'),
				)
			)
		)
	),
	//会员页面菜单
	'user' => array(
		array(
			'title' => L('menu-08-0'),
			'on'	=> 1,
			'menu'	=> array(
				array(
					'name' => L('menu-08-1'),
					'link' => site_url('user'),
				),
				array(
					'name' => L('menu-08-2'),
					'link' => site_url('user/setting'),
				),
				array(
					'name' => L('menu-08-3'),
					'link' => site_url('user/zu'),
				),
				array(
					'name' => L('menu-08-4'),
					'link' => site_url('user/level'),
				),
				array(
					'name' => L('menu-08-5'),
					'link' => site_url('pay/lists'),
				),
				array(
					'name' => L('menu-08-6'),
					'link' => site_url('field').'?dir=user',
				)
			)
		),
		array(
			'title' => L('menu-09-0'),
			'on'	=> 1,
			'menu'	=> array(
				array(
					'name' => L('menu-09-1'),
					'link' => site_url('homes'),
				),
				array(
					'name' => L('menu-09-2'),
					'link' => site_url('homes/pay'),
				)
			)
		),
		array(
			'title' => L('menu-10-0'),
			'on'	=> 1,
			'menu'	=> array(
				array(
					'name' => L('menu-10-1'),
					'link' => site_url('pl'),
				),
				array(
					'name' => L('menu-10-2'),
					'link' => site_url('gbook'),
				),
				array(
					'name' => L('menu-10-3'),
					'link' => site_url('blog'),
				),
				array(
					'name' => L('menu-10-4'),
					'link' => site_url('msg'),
				),
				array(
					'name' => L('menu-10-5'),
					'link' => site_url('share'),
				)
			)
		)
	)
);
