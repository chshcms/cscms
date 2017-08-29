<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class CS_URI extends CI_URI
{

	protected function _set_uri_string($str)
	{
		global $_ERYM,$PLUBARR;
		$this->uri_string = trim(remove_invisible_characters($str, FALSE), '/');
		$this->uri_string = ($str == '/') ? '' : $str;
		//后台
        if (defined('IS_ADMIN') && PLUBPATH=='sys'){
             $this->uri_string = 'admin/'.$this->uri_string;
			 $this->uri_string = str_replace("admin/admin", "admin", $this->uri_string);
		}else{
			if(strpos($_SERVER['REQUEST_URI'],'index.php/admin') && !defined('HOMEPATH')){
				show_404("admin");
			}
		}
		//程序安装
        if(defined('IS_INSTALL')){
             $this->uri_string = 'install/'.$this->uri_string;
        }
		//判断板块二级域名
		if($_ERYM && PLUBPATH != 'sys' && cscms_uri() != PLUBPATH){
			$this->uri_string = PLUBPATH.'/'.$this->uri_string;
		}
		//判断会员中心二级域名
		if(defined('USERPATH') && strpos(REQUEST_URI,'/user/') === FALSE){
			//判断板块会员中心
			if(PLUBPATH == cscms_uri()){
				$strs = explode("/",$this->uri_string);
				unset($strs[0]);
				$this->uri_string = PLUBPATH.'/user/'.implode('/',$strs);
			}else{
				$this->uri_string = 'user/'.$this->uri_string;
			}
			$this->uri_string = str_replace('user/user', 'user', $this->uri_string);
		}
		//会员空间泛域名
		if(Home_Ym==1 && defined('HOMEPATH')){
			$Home = explode('.', $_SERVER['HTTP_HOST']);
			$name = $Home[0];
			unset($Home[0]);
			$ymext = explode('|',Home_Ymext);
			if($name!='www' && !in_array($name, $ymext) && implode('.',$Home) == Home_YmUrl){
				if(!defined('HOMEPATH')) define('HOMEPATH', TRUE);
			    $this->uri_string = "home/" . $this->uri_string;
			}
			//判断板块会员空间
			if(PLUBPATH == cscms_uri()){
				$this->uri_string = PLUBPATH.'/'.$this->uri_string;
			}
		}
		//给第二个参数加上index
		if(!defined('IS_ADMIN')){
			$strs = explode("/",$this->uri_string);
			$ui='no';
			//为了兼容3.5URL地址
			if(PLUBPATH=='dance' || PLUBPATH=='vod' || PLUBPATH=='news' || PLUBPATH=='singer'){
			    $dos = array('id', 'hits', 'yhits', 'zhits', 'rhits', 'dhits', 'chits', 'shits', 'xhits', 'news', 'reco', 'play', 'down', 'fav', 'yue', 'zhou', 'ri', 'ding', 'cai');
				if(!empty($strs[2]) && in_array($strs[2], $dos)) $ui='ok';
			}
			if(!empty($strs[2]) && 
				$strs[1]!='user' && 
				($ui=='ok' || $strs[2]=='id' || intval($strs[2])>0)
			){
				//会员主页新闻板块不增加 index
				if(!(defined('HOMEPATH') && strpos(REQUEST_URI,'/news') !== FALSE)){
					//静态跳过
					if(isset($PLUBARR[PLUBPATH]['Web_Mode']) && $PLUBARR[PLUBPATH]['Web_Mode']<2){
						$this->uri_string = $strs[0].'/'.$strs[1].'/index/';
						unset($strs[0],$strs[1]);
						$this->uri_string.= implode('/',$strs);
					}
				}
			}
		}
		//exit($this->uri_string);
		
		if ($this->uri_string !== '')
		{
			if (($suffix = (string) $this->config->item('url_suffix')) !== '')
			{
				$slen = strlen($suffix);
				if (substr($this->uri_string, -$slen) === $suffix)
				{
					$this->uri_string = substr($this->uri_string, 0, -$slen);
				}
			}
			$this->segments[0] = NULL;
			foreach (explode('/', trim($this->uri_string, '/')) as $val)
			{
				$val = trim($val);
				$this->filter_uri($val);
				if ($val !== '')
				{
					$this->segments[] = $val;
				}
			}
			unset($this->segments[0]);
		}
		if(PLUBPATH!='sys'){
			unset($this->segments[1]);
		}
	}

	public function filter_uri(&$str)
	{
		if ( ! empty($str) && ! empty($this->_permitted_uri_chars) && ! preg_match('/^['.$this->_permitted_uri_chars.']+$/i'.(UTF8_ENABLED ? 'u' : ''), urlencode($str)))
		{
			show_error('The URI you submitted has disallowed characters.', 400);
		}
	}
} // END class MY_URI