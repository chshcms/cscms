<?php
if (!defined('CSCMSPATH')) exit('No permission resources');
return array(
	'DROP TABLE IF EXISTS `{prefix}pic`;',
	'DROP TABLE IF EXISTS `{prefix}pic_verify`;',
	'DROP TABLE IF EXISTS `{prefix}pic_hui`;',
	'DROP TABLE IF EXISTS `{prefix}pic_list`;',
	'DROP TABLE IF EXISTS `{prefix}pic_type`;',
	'DROP TABLE IF EXISTS `{prefix}pic_type_verify`;',
	'DROP TABLE IF EXISTS `{prefix}pic_type_hui`;'
);
