<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => CS_Sqlserver,
	'username' => CS_Sqluid,
	'password' => CS_Sqlpwd,
	'database' => CS_Sqlname,
	'dbdriver' => CS_Dbdriver,
	'dbprefix' => CS_SqlPrefix,
	'pconnect' => FALSE,
	'db_debug' => ENVIRONMENT,
	'cache_on' => !defined('IS_ADMIN') ? CS_Cache_On : FALSE,
	'cachedir' => 'cache/'.CS_Cache_Dir.'/',
	'char_set' => CS_Sqlcharset,
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
