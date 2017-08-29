<?php
//控制器
$lang['plub_01']	= '系统配置不合理，post_max_size值必须大于upload_max_filesize值，否则会出现“进度条100%卡住”，解决方案：<a style="color:red" target="_blank" href="http://bbs.chshcms.com/show/1.html">这里</a>';
$lang['plub_02']	= '系统环境只允许上传%sMB文件，可以设置upload_max_filesize值提升上传大小，解决方案：<a style="color:red" target="_blank" href="http://bbs.chshcms.com/show/1.html">这里</a>';
$lang['plub_03']	= '系统环境要求每次发布内容不能超过%sMB（含文件），可以设置post_max_size值提升发布大小，解决方案：<a style="color:red" target="_blank" href="http://bbs.chshcms.com/show/1.html">这里</a>';
$lang['plub_04']	= '如果管理帐号泄漏，后台容易遭受攻击，为了系统安全，请修改根目录admin.php的文件名';
$lang['plub_05']	= '远程图片无法保存到本地。解决方案：在php.ini文件中allow_url_fopen设置为On';
$lang['plub_06']	= 'PHP不支持CURL扩展，一键登录可能无法登录、无法访问云平台、无法在线升级。解决方案：将php.ini中的;extension=php_curl.dll中的分号去掉';
$lang['plub_07']	= 'PHP不支持openssl，QQ登录可能无发使用。解决方案：将php.ini中的;extension=php_openssl.dll中的分号去掉';
$lang['plub_08']	= '邮件服务器尚未设置，可能系统无法发送邮件通知，设置方式：系统->系统配置->邮件系统->开启邮件发送以及修改配置';
$lang['plub_09']	= '无法通过( %s )获取到数据表结构，系统模块无法使用，解决方案：为Mysql账号开启SHOW TABLE STATUS权限';
$lang['plub_10']	= '无法通过( %s )获取到数据表字段结构，系统模块无法使用，解决方案：为Mysql账号开启SHOW FULL COLUMNS权限';

//视图
$lang['tpl_01']	= '位置';
$lang['tpl_02']	= '首页';
$lang['tpl_03']	= '系统检测';
$lang['tpl_04']	= '基本检测';
$lang['tpl_05']	= '系统全部检测完成';
