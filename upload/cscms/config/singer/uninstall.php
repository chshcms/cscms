<?php
if (!defined('CSCMSPATH')) exit('No permission resources');
return array(
	'DROP TABLE IF EXISTS `{prefix}singer`;',
	'DROP TABLE IF EXISTS `{prefix}singer_list`;',
	'DROP TABLE IF EXISTS `{prefix}singer_verify`;',
	'DROP TABLE IF EXISTS `{prefix}singer_hui`;'
);
