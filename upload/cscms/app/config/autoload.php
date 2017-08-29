<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$autoload['packages'] = array();
$autoload['drivers'] = array();
$autoload['helper'] = array('url','common','link');
$autoload['config'] = array();
$lang = defined('IS_ADMIN') ? array() : array('cscms');
if(defined('IS_ADMIN') && file_exists(APPPATH.'language'.FGF.CS_Language.FGF.PLUBPATH.FGF.'admin'.FGF.'admin_lang.php')){
	$lang[]  = 'admin';
}elseif(file_exists(APPPATH.'language'.FGF.CS_Language.FGF.PLUBPATH.FGF.'plub_lang.php')){
	$lang[]  = 'plub';
}
$autoload['language']  = $lang;
if(!defined('IS_INSTALL')){
    $autoload['libraries'] = array('cookie','session');
    $autoload['model']     = array('Cscache','Csdb','Csskins');
}else{
    $autoload['libraries'] = array();
    $autoload['model']     = array();
}
