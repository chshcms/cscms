<?php
if (!defined('CSCMSPATH')) exit('No permission resources');

return array(
   //全局模版
   'index.html' => '主页模板',
   'head.html' => '网站顶部',
   'bottom.html' => '网站底部',
   'ulogin.html' => '会员登陆前',
   'uinfo.html' => '会员登录后',
   //系统模版
   'sys' => array(
      'info.html' => '资料模板',
      'gbook.html' => '留言模板',
      'gbook-ajax.html' => '留言框模板',
      'fans.html' => '粉丝模板',
      'friend.html' => '关注模板',
      'funco.html' => '访客模板',
      'feed.html' => '近况模板',
   ),
   //歌曲模版
   'dance' => array(
      'dance.html' => '歌曲列表',
      'album.html' => '专辑列表',
   ),
   //文章模版
   'news' => array(
      'news.html' => '文章列表',
   ),
   //视频模版
   'vod' => array(
      'vod.html' => '视频列表',
   ),
   //图库模版
   'pic' => array(
      'pic.html' => '相册列表',
   ),
);
