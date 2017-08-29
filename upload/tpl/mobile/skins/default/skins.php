<?php
if (!defined('CSCMSPATH')) exit('No permission resources');
return array(
	//全局模版
	'head.html' => '网站顶部',
	'bottom.html' => '网站底部',
	'ulogin.html' => '会员登陆前',
	'uinfo.html' => '会员登录后',
	'pl.html' => '评论模板',
	//系统板块
	'sys' => array(
		'index.html' => '主页模板',
		'gbook.html' => '留言框架模板',
		'gbook_ajax.html'=>'留言列表模版',
	),
	//歌曲板块
	'dance' => array(
		'index.html' => '歌曲主页模板',
		'down.html' => '歌曲下载页',
		'list.html' => '歌曲列表页',
		'play.html' => '歌曲播放页',
		'play-lrc.html' => '带LRC歌词播放页',
		'playsong.html' => '歌曲联播页',
		'search.html' => '歌曲搜索页',
		'topic.html' => '专辑列表页',
		'topic-show.html' => '专辑内容页',
		'opt-ding.html' => '被顶排行',
		'opt-fav.html' => '收藏排行',
		'opt-hot.html' => '总人气排行',
		'opt-yue.html' => '本月排行页',
		'opt-zhou.html' => '本周排行页',
		'opt-ri.html' => '今日排行页',
		'opt-new.html' => '最新歌曲页',
		'opt-reco.html' => '推荐排行页',
	),
	//视频板块
	'vod' => array(
		'index.html' => '视频主页模板',
		'down.html' => '下载页模板',
		'list.html' => '视频列表页',
		'play.html' => '视频播放页',
		'show.html' => '视频内容页',
		'search.html' => '视频搜索页',
		'topic.html' => '专题列表页',
		'topic-show.html' => '专题内容页',
		'opt-hot.html' => '年度排行页',
		'opt-yue.html' => '本月排行页',
		'opt-zhou.html' => '本周排行页',
		'opt-day.html' => '今日排行页',
		'opt-new.html' => '最新视频页',
		'opt-tv.html' => '电视直播页'
	),
	//歌手板块
	'singer' => array(
		'index.html' => '歌手主页模板',
		'list.html' => '歌手列表页',
		'show.html' => '歌手主页',
		'album.html' => '歌手专辑页',
		'info.html' => '歌手详细页',
		'music.html' => '歌手歌曲页',
		'mv.html' => '歌手MV页',
		'pic.html' => '歌手图片页'
	),
	//文章板块
	'news' => array(
		'index.html' => '文章主页模板',
		'list.html' => '文章列表页',
		'show.html' => '文章内容页',
		'search.html' => '文章搜索页',
		'search-zm.html' => '字母搜索页',
		'topic.html' => '专题列表页',
		'topic-show.html' => '专题内容页',
		'opt-hot.html' => '年度排行页',
		'opt-yue.html' => '本月排行页',
		'opt-zhou.html' => '本周排行页',
		'opt-day.html' => '今日排行页',
		'opt-new.html' => '最新文章页'
	),
	//图片板块
	'pic' => array(
		'index.html' => '相册主页模板',
		'list.html' => '相册列表页',
		'show.html' => '相册内容页',
		'search.html' => '相册搜索页',
		'opt-hot.html' => '年度排行页',
		'opt-yue.html' => '本月排行页',
		'opt-zhou.html' => '本周排行页',
		'opt-day.html' => '今日排行页',
		'opt-new.html' => '最新相册页'
	)
);
