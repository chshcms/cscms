<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @Ctcms open source management system
 * @copyright 2016-2017 www.chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2017-04-11
 */

class CS_Loader extends CI_Loader {

	public function __construct()
	{
		parent::__construct();
		log_message('debug', "MY_Loader Class Initialized");
	}
	/**
	 * View Loader
	 *
	 * Loads "view" files.
	 *
	 * @param	string	$view	View name
	 * @param	array	$vars	An associative array of data
	 *				to be extracted for use in the view
	 * @param	bool	$return	Whether to return the view output
	 *				or leave it to the Output class
	 * @return	object|string
	 */
	public function view($view, $vars = array(), $return = FALSE)
	{
		//后台自定义字段
		if(defined('IS_ADMIN')){
			$dir = PLUBPATH == 'sys' ? 'user' : PLUBPATH;
	        if(is_file(CSCMS.'sys'.FGF.'Cs_Field.php')) {
	            $field = require_once(CSCMS.'sys'.FGF.'Cs_Field.php');
	        }else{
	            $field = array();
	        }
			//数据库表名称
			$table = isset($vars['table']) ? $vars['table'] : $dir;
	        $optfield = array();
            if(isset($field[$dir])){
                foreach ($field[$dir] as $key => $value){
					$zd = $value['zd'];
                    if($value['table']==$table && $value['status']==1){
                    	if(!isset($vars['row']) || empty($vars['row'])){
                        	$optfield[$zd] = '';
                    	}else{
                    		$row = $vars['row'];
                    		if(gettype($row) === 'object'){
                        		$optfield[$zd] = $row->$zd;
                    		}elseif(isset($row[$zd])){
                        		$optfield[$zd] = $row[$zd];
                        	}else{
                        		$optfield[$zd] = '';
							}
                    	}
                    }
                }
            }
			if(!empty($optfield)){
				$fres = opt_field($dir,$optfield,$table);
			}else{
				$fres = array('gctime'=>'','str'=>'');
			}
			$vars['opt_gc'] = $fres['gctime'];
			$vars['opt_field'] = $fres['str'];
		}
		return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_prepare_view_vars($vars), '_ci_return' => $return));
	}

    public function get_templates($dir='',$skins='')
    {
		if(defined('IS_INSTALL')){
        	$this->_ci_view_paths = array(VIEWPATH.'install'.FGF => TRUE);
		}elseif(defined('IS_ADMIN') && !defined('IS_HTML')){
        	$this->_ci_view_paths = array(VIEWPATH.'admin'.FGF.PLUBPATH.FGF => TRUE);
    	}else{
            //手机视图
            $Mobile_Is = config('Mobile_Is',PLUBPATH);
			if(defined('MOBILE') && $Mobile_Is==1){
				if(!empty($dir)){
                    $dirs = 'mobile'.FGF.$dir.FGF;
				}elseif(defined('HOMEPATH')){
					if(empty($skins)) $skins = Mobile_Home_Dir;
                    $dirs = 'mobile'.FGF.'home'.FGF.str_replace('/', FGF, $skins).PLUBPATH.FGF;
				}elseif(defined('USERPATH')){
                    $dirs = 'mobile'.FGF.'user'.FGF.str_replace('/', FGF, Mobile_User_Dir).PLUBPATH.FGF;
				}else{
               		$dirs = 'mobile'.FGF.'skins'.FGF.str_replace('/', FGF, Mobile_Skins_Dir).PLUBPATH.FGF;
				}
			//PC视图
			}else{
				if(!empty($dir)){
                    $dirs = 'pc'.FGF.$dir.FGF;
				}elseif(defined('HOMEPATH')){
					if(empty($skins)) $skins = Pc_Home_Dir;
                    $dirs = 'pc'.FGF.'home'.FGF.str_replace('/', FGF, $skins).PLUBPATH.FGF;
				}elseif(defined('USERPATH')){
                    $dirs = 'pc'.FGF.'user'.FGF.str_replace('/', FGF, Pc_User_Dir).PLUBPATH.FGF;
				}else{
               		$dirs = 'pc'.FGF.'skins'.FGF.str_replace('/', FGF, Pc_Skins_Dir).PLUBPATH.FGF;
				}
			}
			//自定义模版
			if(defined('OPT_DIR')){
				$dirs = str_replace(FGF.'sys'.FGF, FGF.OPT_DIR.FGF, $dirs);
			}
        	$this->_ci_view_paths = array(VIEWPATH.$dirs => TRUE);
    	}
    }
}