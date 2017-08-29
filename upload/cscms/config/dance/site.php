<?php if (!defined('FCPATH')) exit('No direct script access allowed');
return array (
  'Web_Mode' => 1,
  'Mobile_Is' => 1,
  'Cache_Is' => 0,
  'Cache_Time' => 1800,
  'Ym_Mode' => 0,
  'Ym_Url' => 'dance.cscms.com',
  'User_Qx' => '',
  'User_Dj_Qx' => '',
  'Rewrite_Uri' => 
  array (
    'index' => 
    array (
      'title' => '版块主页规则',
      'uri' => 'index',
      'url' => 'dance',
    ),
    'lists' => 
    array (
      'title' => '列表页规则',
      'uri' => 'lists/index/{sort}/{id}/{page}',
      'url' => 'list-{sort}-{id}-{page}.html',
    ),
    'play' => 
    array (
      'title' => '播放页规则',
      'uri' => 'play/index/id/{id}',
      'url' => 'play-{id}.html',
    ),
    'down' => 
    array (
      'title' => '下载页规则',
      'uri' => 'down/index/id/{id}',
      'url' => 'down-{id}.html',
    ),
    'topic' => 
    array (
      'title' => '专辑主页规则',
      'uri' => 'topic/index/{sort}/{page}',
      'url' => 'topic-{sort}-{page}.html',
    ),
    'topic/lists' => 
    array (
      'title' => '专辑分类规则',
      'uri' => 'topic/lists/{sort}/{id}/{page}',
      'url' => 'topic-{sort}-{id}-{page}.html',
    ),
    'topic/show' => 
    array (
      'title' => '专辑内容规则',
      'uri' => 'topic/show/{id}/{page}',
      'url' => 'topic-show-{id}-{page}.html',
    ),
  ),
  'Html_Uri' => 
  array (
    'index' => 
    array (
      'title' => '版块主页规则',
      'url' => 'music/',
      'check' => '1',
    ),
    'lists' => 
    array (
      'title' => '列表页规则',
      'url' => 'music/list-{sort}-{id}-{page}.html',
      'check' => '1',
    ),
    'play' => 
    array (
      'title' => '播放页规则',
      'url' => 'music/play/{id}.html',
      'check' => '1',
    ),
    'down' => 
    array (
      'title' => '下载页规则',
      'url' => 'music/down/{id}.html',
      'check' => '1',
    ),
    'topic/lists' => 
    array (
      'title' => '专辑列表规则',
      'url' => 'album/{sort}/{id}/{page}.html',
      'check' => '1',
    ),
    'topic/show' => 
    array (
      'title' => '专辑内容规则',
      'url' => 'album/show/{id}-{page}.html',
      'check' => '1',
    ),
  ),
  'Seo' => 
  array (
    'title' => '音乐',
    'keywords' => '音乐',
    'description' => '音乐',
  ),
);?>