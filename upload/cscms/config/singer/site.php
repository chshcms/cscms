<?php if (!defined('FCPATH')) exit('No direct script access allowed');
return array (
  'Web_Mode' => 1,
  'Mobile_Is' => 1,
  'Cache_Is' => 0,
  'Cache_Time' => 1800,
  'Ym_Mode' => 0,
  'Ym_Url' => 'singer.cscms.com',
  'User_Qx' => '',
  'User_Dj_Qx' => '',
  'Rewrite_Uri' => 
  array (
    'index' => 
    array (
      'title' => '版块主页规则',
      'uri' => 'index',
      'url' => 'singer',
    ),
    'lists' => 
    array (
      'title' => '列表页规则',
      'uri' => 'lists/index/{sort}/{id}/{page}',
      'url' => 'list-{sort}-{id}-{page}.html',
    ),
    'show' => 
    array (
      'title' => '歌手主页规则',
      'uri' => 'show/index/{id}',
      'url' => 'show-{id}.html',
    ),
    'info' => 
    array (
      'title' => '歌手资料页规则',
      'uri' => 'info/index/{id}',
      'url' => 'info-{id}.html',
    ),
    'music' => 
    array (
      'title' => '歌手歌曲页规则',
      'uri' => 'music/index/{sort}/{id}/{page}',
      'url' => 'music-{sort}-{id}-{page}.html',
    ),
    'pic' => 
    array (
      'title' => '歌手图片页规则',
      'uri' => 'pic/index/{sort}/{id}/{page}',
      'url' => 'pic-{sort}-{id}-{page}.html',
    ),
    'mv' => 
    array (
      'title' => '歌手视频页规则',
      'uri' => 'mv/index/{sort}/{id}/{page}',
      'url' => 'mv-{sort}-{id}-{page}.html',
    ),
    'album' => 
    array (
      'title' => '歌手专辑页规则',
      'uri' => 'album/index/{sort}/{id}/{page}',
      'url' => 'album-{sort}-{id}-{page}.html',
    ),
  ),
  'Html_Uri' => 
  array (
    'index' => 
    array (
      'title' => '版块主页规则',
      'url' => 'singer/',
      'check' => '1',
    ),
    'lists' => 
    array (
      'title' => '列表页规则',
      'url' => 'singer/list/{sort}-{id}-{page}.html',
      'check' => '1',
    ),
    'show' => 
    array (
      'title' => '歌手页规则',
      'url' => 'singer/index/{id}.html',
      'check' => '1',
    ),
    'info' => 
    array (
      'title' => '歌手资料页规则',
      'url' => 'singer/info/{id}.html',
      'check' => '1',
    ),
    'music' => 
    array (
      'title' => '歌手歌曲页规则',
      'url' => 'singer/music/{sort}-{id}-{page}.html',
      'check' => '1',
    ),
    'pic' => 
    array (
      'title' => '歌手图片页规则',
      'url' => 'singer/pic/{sort}-{id}-{page}.html',
      'check' => '1',
    ),
    'mv' => 
    array (
      'title' => '歌手视频页规则',
      'url' => 'singer/mv/{sort}-{id}-{page}.html',
      'check' => '1',
    ),
    'album' => 
    array (
      'title' => '歌手专辑页规则',
      'url' => 'singer/album/{sort}-{id}-{page}.html',
      'check' => '1',
    ),
  ),
  'Seo' => 
  array (
    'title' => '歌手',
    'keywords' => '歌手',
    'description' => '歌手',
  ),
);?>