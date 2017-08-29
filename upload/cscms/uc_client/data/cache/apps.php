<?php
$_CACHE['apps'] = array (
  1 => 
  array (
    'appid' => '1',
    'type' => 'OTHER',
    'name' => 'cscms v3.5',
    'url' => 'http://127.0.0.1/index.php',
    'ip' => '',
    'viewprourl' => '',
    'apifilename' => 'uc',
    'charset' => '',
    'dbcharset' => '',
    'synlogin' => '1',
    'recvnote' => '1',
    'extra' => false,
    'tagtemplates' => '<?xml version="1.0" encoding="ISO-8859-1"?>
<root>
	<item id="template"><![CDATA[]]></item>
</root>',
    'allowips' => '',
  ),
  2 => 
  array (
    'appid' => '2',
    'type' => 'UCHOME',
    'name' => '个人家园',
    'url' => 'http://127.0.0.1/home',
    'ip' => '',
    'viewprourl' => '',
    'apifilename' => 'uc.php',
    'charset' => 'gbk',
    'dbcharset' => 'gbk',
    'synlogin' => '1',
    'recvnote' => '1',
    'extra' => false,
    'tagtemplates' => '<?xml version="1.0" encoding="ISO-8859-1"?>
<root>
	<item id="template"><![CDATA[<a href="{url}" target="_blank">{subject}</a>]]></item>
	<item id="fields">
		<item id="subject"><![CDATA[日志标题]]></item>
		<item id="uid"><![CDATA[用户 ID]]></item>
		<item id="username"><![CDATA[用户名]]></item>
		<item id="dateline"><![CDATA[日期]]></item>
		<item id="spaceurl"><![CDATA[空间地址]]></item>
		<item id="url"><![CDATA[日志地址]]></item>
	</item>
</root>',
    'allowips' => '',
  ),
);

?>
