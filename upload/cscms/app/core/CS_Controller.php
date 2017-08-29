<?php
// --------------------------------------------------
// 全局控制器
// --------------------------------------------------

class Cscms_Controller extends CI_Controller {
	function __construct() {
		parent::__construct();
		header('X-Generator: '.cs_base64_decode('Q3NjbXMgdjQgKGh0dHA6Ly93d3cuY2hzaGNtcy5jb20p'));
	}
}