<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-01-17
 */
if (!defined('FCPATH')) exit('No direct script access allowed');

/**
 * 视频下载来源配置
 */

$down_config[0]['name']='HTTP下载';
$down_config[0]['form']='http';
$down_config[0]['des']='HTTP直连下载';

$down_config[1]['name']='FTP下载';
$down_config[1]['form']='ftp';
$down_config[1]['des']='FTP直连下载';

$down_config[2]['name']='迅雷下载';
$down_config[2]['form']='thunder';
$down_config[2]['des']='迅雷下载';

$down_config[3]['name']='快车下载';
$down_config[3]['form']='flashget';
$down_config[3]['des']='快车下载';

$down_config[4]['name']='旋风下载';
$down_config[4]['form']='qqdl';
$down_config[4]['des']='QQ旋风下载';

$down_config[5]['name']='BT下载';
$down_config[5]['form']='bt';
$down_config[5]['des']='BT下载';

$down_config[6]['name']='磁力链';
$down_config[6]['form']='magnet';
$down_config[6]['des']='磁力链下载';

$down_config[7]['name']='电炉下载';
$down_config[7]['form']='ed2k';
$down_config[7]['des']='电炉下载';
