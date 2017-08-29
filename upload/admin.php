<?php
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-08-01
 */
define('IS_ADMIN', TRUE); // 后台标识
define('ADMINSELF', pathinfo(__FILE__, PATHINFO_BASENAME)); // 后台文件名
define('SELF', ADMINSELF);
define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR); // 网站根目录
require('index.php'); // 引入主文件