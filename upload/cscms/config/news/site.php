<?php if (!defined('FCPATH')) exit('No direct script access allowed');
return array (
  'Web_Mode' => 1,
  'Mobile_Is' => 1,
  'Cache_Is' => 0,
  'Cache_Time' => 1800,
  'Ym_Mode' => 0,
  'Ym_Url' => 'news.cscms.com',
  'User_Qx' => '',
  'User_Dj_Qx' => '',
  'Rewrite_Uri' => 
  array (
    'index' => 
    array (
      'title' => '版块主页规则',
      'uri' => 'index',
      'url' => 'news',
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
      'uri' => 'show/index/id/{id}/{page}',
      'url' => 'show-{id}-{page}.html',
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
      'url' => 'news/',
      'check' => '1',
    ),
    'lists' => 
    array (
      'title' => '列表页规则',
      'url' => 'news/list-{sort}-{id}-{page}.html',
      'check' => '1',
    ),
    'show' => 
    array (
      'title' => '内容页规则',
      'url' => 'news/show-{id}.html',
      'check' => '1',
    ),
    'topic/lists' => 
    array (
      'title' => '专题列表规则',
      'url' => 'newstopic/{sort}-{page}.html',
      'check' => '1',
    ),
    'topic/show' => 
    array (
      'title' => '专题内容规则',
      'url' => 'newstopic/show-{id}.html',
      'check' => '1',
    ),
  ),
  'Seo' => 
  array (
    'title' => '新闻',
    'keywords' => '新闻',
    'description' => '新闻',
  ),
);?>