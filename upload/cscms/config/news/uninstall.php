<?php
if (!defined('CSCMSPATH')) exit('No permission resources');
return array(
	'DROP TABLE IF EXISTS `{prefix}news`;',
	'DROP TABLE IF EXISTS `{prefix}news_verify`;',
	'DROP TABLE IF EXISTS `{prefix}news_hui`;',
	'DROP TABLE IF EXISTS `{prefix}news_list`;',
	'DROP TABLE IF EXISTS `{prefix}news_look`;',
	'DROP TABLE IF EXISTS `{prefix}news_topic`;'
);
