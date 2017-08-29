<?php
/**
 * @Cscms 3.5 open source management system
 * @copyright 2009-2013 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2013-04-27
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

 class Csbackup extends CI_Model
 {
    function __construct (){
	       parent:: __construct ();
    }

    //导出数据表结构
    function repair($table){
        $output="";
	    $query = $this->db->query("SHOW CREATE TABLE `".$this->db->database.'`.`'.$table.'`');
	    $i = 0;
	    $result = $query->result_array();
	    foreach ($result[0] as $val){
		    if ($i++ % 2){
			      $output .= $val.';';
		    }
		}
		return $output;
	}

    //备份数据表结构
    function backup_table($bkfile,$tables){
        $output="";
        $newline="[cscms_backup]\n";
        $bkfile.= FGF."tables_".substr(md5(time().mt_rand(1000,5000)),0,16).".sql"; //名称
        $tables=$this->db->list_tables();   
        foreach ((array)$tables as $table){ 
            if(strpos($table,CS_SqlPrefix) !== FALSE){
				$query = $this->db->query("SHOW CREATE TABLE `".$this->db->database.'`.`'.$table.'`');
				if ($query === FALSE){
					continue;
				}
				$output .= 'DROP TABLE IF EXISTS '.str_replace(CS_SqlPrefix,'{Prefix}',$table).';'.$newline;
				$i = 0;
				$result = $query->result_array();
				foreach ($result[0] as $val){
				  if ($i++ % 2){
					  $ytable = str_replace(CS_SqlPrefix,'',$table);
					  $output .= str_replace('`'.$table.'`','`{Prefix}'.$ytable.'`',$val).';'.$newline;
				  }
				}
            }
        }
        //写文件
        if(!write_file($bkfile, $output)){
            return FALSE;
        }else{
            return TRUE;
        }
    }

    //备份数据内容
    function backup_data($bkfile,$table){
        $bkfile.= FGF."datas_".substr(md5(time().mt_rand(1000,5000)),0,16).".sql";
        $output="";
        $newline="[cscms_backup]\n";
        $query = $this->db->query("SELECT * FROM $table");
        if ($query->num_rows() == 0){
			return TRUE;
        }
        $i = 0;
        foreach ($query->result_array() as $row){
			   $field = array();
               $val = array();
               $i = 0;
               foreach ($row as $k=>$v){
				   $field[] = $k;
                   $val[] = $this->db->escape($v);
               }
               $output.= 'INSERT INTO '.str_replace(CS_SqlPrefix,'{Prefix}',$table).' (`'.implode("`,`",$field).'`) VALUES ('.implode(', ',$val).');'.$newline;
        }
        //写文件
        if (!write_file($bkfile, $output)){
            return FALSE;
        }else{
            return TRUE;
        }
    }

    //还原数据
    function restore($name){
		$this->load->helper('file');
        $path="./attachment/backup/".$name."/";
        $strs = get_dir_file_info($path, $top_level_only = TRUE);
		$filearr = array();
        foreach ($strs as $value) {
            if(!is_dir($path.$value['name'])){
			    $fullpath = $path.$value['name'];
				//还原表结构
				if(substr($value['name'],0,7)=="tables_"){
					$tabel_stru = file_get_contents($fullpath);
					$tabelarr = explode("[cscms_backup]\n",$tabel_stru);
					for($i=0;$i<count($tabelarr)-1;$i++){	
						if(!empty($tabelarr[$i])){
							$sql = str_replace('{Prefix}',CS_SqlPrefix,$tabelarr[$i]);
							$this->db->query($sql);
						}
					}
				} 
				if(substr($value['name'],0,6)=="datas_"){
					//数据列表
					$filearr[] = $fullpath;
				}
			}
		}
        //开始还原数据
	    for($i=0;$i<count($filearr);$i++){
		    $tabel_datas = file_get_contents(trim($filearr[$i]));
		    $dataarr = explode("[cscms_backup]\n",$tabel_datas);
		    for($j=0;$j<count($dataarr);$j++){
				if(!empty($dataarr[$j])){
					$sql = str_replace('{Prefix}',CS_SqlPrefix,$dataarr[$j]);
					$this->db->query($sql);
				}
		    }
	    }
        return TRUE;
    }
}


