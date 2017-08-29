<?php if (!defined('FCPATH')) exit('No direct script access allowed');
return array (
  'Web_Mode' => 1,
  'Mobile_Is' => 1,
  'Cache_Is' => 0,
  'Cache_Time' => 1800,
  'Ym_Mode' => 0,
  'Ym_Url' => 'vod.cscms.com',
  'User_Qx' => '',
  'User_Dj_Qx' => '',
  'Rewrite_Uri' => 
  array (
    'index' => 
    array (
      'title' => '版块主页规则',
      'uri' => 'index',
      'url' => 'vod',
    ),
    'lists' => 
    array (
      'title' => '列表页规则',
      'uri' => 'lists/index/{sort}/{id}/{page}',
      'url' => 'list-{sort}-{id}-{page}.html',
    ),
    'show' => 
    array (
      'title' => '内容页规则',
      'uri' => 'show/index/id/{id}',
      'url' => 'show-{id}.html',
    ),
    'play' => 
    array (
      'title' => '播放页规则',
      'uri' => 'play/index/id/{id}/{zu}/{ji}',
      'url' => 'play-{id}-{zu}-{ji}.html',
    ),
    'down' => 
    array (
      'title' => '下载页规则',
      'uri' => 'down/index/id/{id}/{zu}/{ji}',
      'url' => 'down-{id}-{zu}-{ji}.html',
    ),
    'topic/lists' => 
    array (
      'title' => '专题列表规则',
      'uri' => 'topic/lists/{sort}/{page}',
      'url' => 'topic-lists-{sort}-{page}.html',
    ),
    'topic/show' => 
    array (
      'title' => '专题内容规则',
      'uri' => 'topic/show/{id}',
      'url' => 'topic-show-{id}.html',
    ),
  ),
  'Html_Uri' => 
  array (
    'index' => 
    array (
      'title' => '版块主页规则',
      'url' => 'vod/',
      'check' => '1',
    ),
    'lists' => 
    array (
      'title' => '列表页规则',
      'url' => 'vod/list-{sort}-{id}-{page}.html',
      'check' => '1',
    ),
    'show' => 
    array (
      'title' => '内容页规则',
      'url' => 'vod/show-{id}.html',
      'check' => '1',
    ),
    'play' => 
    array (
      'title' => '播放页规则',
      'url' => 'vod/play-{id}/{zu}/{ji}/',
      'check' => '1',
    ),
    'topic/lists' => 
    array (
      'title' => '专题列表规则',
      'url' => 'topic/{sort}-{page}.html',
      'check' => '1',
    ),
    'topic/show' => 
    array (
      'title' => '专题内容规则',
      'url' => 'topic/show-{id}.html',
      'check' => '1',
    ),
  ),
  'Seo' => 
  array (
    'title' => '视频',
    'keywords' => '视频',
    'description' => '视频',
  ),
);?>