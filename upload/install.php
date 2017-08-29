<?php
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2018 chshcms.com. All rights reserved.
 * @Author:Cheng Kai Jie
 * @Dtime:2017-03-17
 */
define('IS_INSTALL', TRUE); // 安装标识
define('ADMINSELF', pathinfo(__FILE__, PATHINFO_BASENAME)); // 文件名
define('SELF', ADMINSELF);
define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR); // 网站根目录
$uri = parse_url('http://cscms'.$_SERVER['REQUEST_URI']);
$path = current(explode(SELF, $uri['path']));
define("install_path",$path);
define("install_url",install_path.'install.php/');
require('index.php'); // 引入主文件
