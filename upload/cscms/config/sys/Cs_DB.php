<?php
/**
 * @Cscms 3.5 open source management system
 * @copyright 2009-2013 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2013-04-27
 */

//服务器IP 一般为localhost或者127.0.0.1
define('CS_Sqlserver','127.0.0.1');

//数据库名称
define('CS_Sqlname','cscms');

//数据库表前缀
define('CS_SqlPrefix','v41_');

//数据库用户名
define('CS_Sqluid','root');

//数据库密码
define('CS_Sqlpwd','root');

//数据库方式
define('CS_Dbdriver','mysqli');

//Mysql数据库编码
define('CS_Sqlcharset','utf8');

//数据库缓寸开关
define('CS_Cache_On',FALSE);

//数据库缓寸目录
define('CS_Cache_Dir','sql');

//数据库缓寸时间
define('CS_Cache_Time',7200);

//encryption_key密钥
define('CS_Encryption_Key','cscms_vlMAA4ki9S');

//后台口令卡总开关,1为开，0为关闭
define('CS_Safe_Card',1);

//session存储方式，1为文件存储，2为Mysql数据库
define('CS_Session_Is',2);

//session存储时间
define('CS_Session_Time',3600);

//Cookie 前缀，同一域名下安装多套系统时，请修改Cookie前缀
define('CS_Cookie_Prefix','cscms_');

//Cookie_Domain 作用域,使用多个二级域名时可以启用，格式如 .chshcms.com
define('CS_Cookie_Domain','');

//Cookie 生命周期，0 表示随浏览器进程
define('CS_Cookie_Ttl',0);
