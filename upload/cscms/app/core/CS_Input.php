<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class CS_Input extends CI_Input
{
	public function get($index = NULL, $xss_clean = NULL, $sql_clean = FALSE)
	{
		return $this->_fetch_from_array($_GET, $index, $xss_clean, $sql_clean);
	}

	public function post($index = NULL, $xss_clean = NULL, $sql_clean = FALSE)
	{
		return $this->_fetch_from_array($_POST, $index, $xss_clean, $sql_clean);
	}

	public function post_get($index, $xss_clean = NULL, $sql_clean = FALSE)
	{
		return isset($_POST[$index])
			? $this->post($index, $xss_clean, $sql_clean)
			: $this->get($index, $xss_clean, $sql_clean);
	}

	public function get_post($index, $xss_clean = NULL, $sql_clean = FALSE)
	{
		return isset($_GET[$index])
			? $this->get($index, $xss_clean, $sql_clean)
			: $this->post($index, $xss_clean, $sql_clean);
	}

	protected function _fetch_from_array(&$array, $index = NULL, $xss_clean = NULL, $sql_clean = FALSE)
	{
		is_bool($xss_clean) OR $xss_clean = $this->_enable_xss;

		// If $index is NULL, it means that the whole $array is requested
		isset($index) OR $index = array_keys($array);

		// allow fetching multiple keys at once
		if (is_array($index))
		{
			$output = array();
			foreach ($index as $key)
			{
				$output[$key] = $this->_fetch_from_array($array, $key, $xss_clean);
			}

			return $output;
		}

		if (isset($array[$index]))
		{
			$value = $array[$index];
		}
		elseif (($count = preg_match_all('/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches)) > 1) // Does the index contain array notation
		{
			$value = $array;
			for ($i = 0; $i < $count; $i++)
			{
				$key = trim($matches[0][$i], '[]');
				if ($key === '') // Empty notation will return the value as array
				{
					break;
				}

				if (isset($value[$key]))
				{
					$value = $value[$key];
				}
				else
				{
					return NULL;
				}
			}
		}
		else
		{
			return NULL;
		}
		if($xss_clean === TRUE){
			//CI自带过滤XSS
			$value = $this->security->xss_clean($value);
			if($sql_clean === TRUE){
				//过滤SQL语句
				$value = safe_replace($value);
			}else{
				//HTML代码转义
				$value = str_encode($value);
			}
		}
		return $value;
	}
} // END class MY_Input