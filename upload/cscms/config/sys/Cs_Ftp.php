<?php
define('UP_Mode',1);      //会员上传附件方式  1站内，2FTP，3七牛，4阿里云，5又拍云..... 
define('UP_Size',20480);      //上传支持的最大KB 
define('UP_Type','mp3|mp4|jpg|gif|png|txt|zip|rar');  //上传支持的格式 
define('UP_Url','');  //本地访问地址 
define('UP_Pan','');  //本地存储路径 
define('FTP_Url','http://demo.chshcms.com/');      //远程FTP连接地址     
define('FTP_Server','127.0.0.1');      //远程FTP服务器IP    
define('FTP_Dir','');      //远程FTP目录    
define('FTP_Port','21');      //远程FTP端口    
define('FTP_Name','111');      //远程FTP帐号    
define('FTP_Pass','111');      //远程FTP密码    
define('FTP_Ive',TRUE);      //是否使用被动模式   