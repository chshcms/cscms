<?php
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-05-27
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');
class Csdb extends CI_Model{

    function __construct (){
		parent:: __construct ();
		//加载数据库连接
		$this->load->database();
		//加载当前模板目录
		$this->load->get_templates();
	}

    //多条件查询
    function get_all($sql){
		$query=$this->db->query($sql);
		return $query->result();
	}

    //多条件查询总数量
    function get_allnums($sql='')  {
		if(!empty($sql)){
			$sql = strtolower($sql);
			//$sql = preg_replace('/select\s*(.+)from/i', 'select count(*) as counta from', $sql);
			$pos = strpos($sql,'from');
			$sql = 'select count(*) as counta '.substr($sql,$pos);
			if(strpos($sql, ' order ')){
				$sql =current(explode(' order ', $sql));
			}
		    $rows = $this->db->query($sql)->result_array();
		    $nums = (int)$rows[0]['counta'];
		}else{
		    $nums = 0;
		}
		return $nums;
	}

    //查询总数量
    function get_nums($table,$czd='',$arr=''){
		$sql="SELECT count(*) as counta FROM ".CS_SqlPrefix.$table;
		if(!empty($czd) && !empty($arr)){
			$this->db->where($czd,$arr);
			if(is_array($arr)){
				$arr2=array();
				for ($i=0;$i<count($arr);$i++) {
				   $arr2[]=$czd[$i]."=".$arr[$i];
				}
				if(!empty($arr2)){
				   $arr2=implode(' and ', $arr2);
				   $sql.=" where ".$arr2;
				}
			}else{
			    $sql.=" where ".$czd."=".$arr;
			}
		}
		$rows=$this->db->query($sql)->result_array();
		$nums=(int)$rows[0]['counta'];
		return $nums;
	}

	/**
	 * 获取一个对象的结果行
	 * @param  $table  所查询的数据表名
	 * @param  $arr  查询条件,数组或者字符串
	 * @param  $fzd  要获取的字段列
	 * @param  $czd  要查询的字段
	 * @return 结果行
	 */
    function get_row($table,$fzd='*',$arr='',$czd='id'){
        if(is_array($arr)){
			$this->db->where($arr);
        }else{
        	$this->db->where($czd,$arr);
        }
		$this->db->select($fzd);
		$res = $this->db->get($table);
		return $res->row();	
	}
	/**
	 * 获取一个数组的结果行
	 * @param  $table  所查询的数据表名
	 * @param  $arr  查询条件,数组或者字符串
	 * @param  $fzd  要获取的字段列
	 * @param  $czd  要查询的字段
	 * @return 结果行
	 */
    function get_row_arr($table,$fzd='*',$arr='',$czd='id'){
        if(is_array($arr)){
			$this->db->where($arr);
        }else{
        	$this->db->where($czd,$arr);
        }
		$this->db->select($fzd);
		$res = $this->db->get($table);
		return $res->row_array();	
	}

	function getres($table,$where,$field='*',$order='id DESC',$limit=15){
		if(is_array($where)){
			$this->db->where($where);
		}else if(!empty($where)){
			$this->db->where('id',$where);
		}
		$this->db->order_by($order);
		if(is_array($limit)){
			$this->db->limit($limit['0'],$limit['1']);
		}else if(!empty($limit)){
			$this->db->limit($limit);
		}
		$this->db->select($field);
		$res = $this->db->get($table);
		return $res->result();
	}

    //按条件查询
    function get_select($table,$czd='',$fzd='*',$arr=''){
        if($czd && $arr){
	        $this->db->where($czd,$arr);
        }
	    $this->db->select($fzd);
	    $query=$this->db->get($table);
	    return $query->result();
	}
    //增加
    function get_insert($table,$arr){
		//检测自定义字段
    	if(PLUBPATH == 'sys'){
			$dir = $table=='user' ? 'user' : '';
		}else{
    		$dir = PLUBPATH;
    	}
		//判断待审核、回收站表
		$table2 = str_replace(array('_verify','_hui'),'',$table);
    	if(defined('IS_ADMIN')){
    		$arr = $arr + save_field($dir,$table2);
    	}else{
    		$arr = $arr + save_field($dir,$table2,1);
    	}
		if($arr){
			$this->db->insert($table,$arr);
			$ids = $this->db->insert_id();
			if($ids){
				//判断动态保留数量
				if($table=='dt' && User_Dtts>0 && !empty($_SESSION['cscms__id'])){
				    $rows=$this->db->query("select count(*) as count from ".CS_SqlPrefix."dt where uid=".$_SESSION['cscms__id']."")->result_array();
				    $nums=(int)$rows[0]['count'];
				    if($nums>User_Dtts){
						$limit=$nums-User_Dtts;
						$this->db->query("DELETE FROM ".CS_SqlPrefix."dt where uid=".$_SESSION['cscms__id']." order by addtime asc LIMIT ".$limit);
				    }
				}
				//判断说说保留数量
				if($table=='blog' && User_Ssts>0 && !empty($_SESSION['cscms__id'])){
				    $rows=$this->db->query("select count(*) as count from ".CS_SqlPrefix."blog where uid=".$_SESSION['cscms__id']."")->result_array();
				    $nums=(int)$rows[0]['count'];
				    if($nums>User_Ssts){
						$limit=$nums-User_Ssts;
				        $this->db->query("DELETE FROM ".CS_SqlPrefix."blog where uid=".$_SESSION['cscms__id']." order by addtime asc LIMIT ".$limit);
				    }
				}
				//判断访客保留数量
				if($table=='funco' && User_Fkts>0 && !empty($_SESSION['cscms__id'])){
				    $rows=$this->db->query("select count(*) as count from ".CS_SqlPrefix."funco where uida=".$_SESSION['cscms__id']."")->result_array();
				    $nums=(int)$rows[0]['count'];
				    if($nums>User_Ssts){
						$limit=$nums-User_Ssts;
				        $this->db->query("DELETE FROM ".CS_SqlPrefix."funco where uida=".$_SESSION['cscms__id']." order by addtime asc LIMIT ".$limit);
				    }
				}
				//判断粉丝保留数量
				if($table=='fans' && User_Fsts>0 && !empty($_SESSION['cscms__id'])){
				    $rows=$this->db->query("select count(*) as count from ".CS_SqlPrefix."fans where uida=".$_SESSION['cscms__id']."")->result_array();
				    $nums=(int)$rows[0]['count'];
				    if($nums>User_Ssts){
						$limit=$nums-User_Ssts;
				        $this->db->query("DELETE FROM ".CS_SqlPrefix."fans where uida=".$_SESSION['cscms__id']." order by addtime asc LIMIT ".$limit);
				    }
				}
				//判断关注保留数量
				if($table=='friend' && User_Hyts>0 && !empty($_SESSION['cscms__id'])){
				    $rows=$this->db->query("select count(*) as count from ".CS_SqlPrefix."friend where uida=".$_SESSION['cscms__id']."")->result_array();
				    $nums=(int)$rows[0]['count'];
				    if($nums>User_Ssts){
						$limit=$nums-User_Ssts;
				        $this->db->query("DELETE FROM ".CS_SqlPrefix."friend where uida=".$_SESSION['cscms__id']." order by addtime asc LIMIT ".$limit);
				    }
				}
			}
			return $ids;
		}else{
		 	return false;
		}
	}
	/**
	 * 单行或多行数据更新
	 * @param  $table  要更新的数据表名
	 * @param  $data   更新的数据数组
	 * @param  $where  更新的条件,单行更新为键值对
	 * @param  $column 更新的列
	 * @param  $sign   0->单行更新 1->多行更新
	 * @return $res 更新结果
	 */
    function get_update($table,$id,$arr,$zd='id'){
		//检测自定义字段
    	if(PLUBPATH == 'sys'){
			$dir = $table=='user' ? 'user' : '';
		}else{
    		$dir = PLUBPATH;
    	}
		//判断待审核、回收站表
		$table2 = str_replace(array('_verify','_hui'),'',$table);
    	if(defined('IS_ADMIN')){
    		$arr = $arr + save_field($dir,$table2);
    	}else{
    		$arr = $arr + save_field($dir,$table2,1);
    	}
        if(!empty($id)){
            if(is_array($id)){
	          $this->db->where_in($zd,$id);
            }else{
	          $this->db->where($zd,intval($id));
            }
            if($this->db->update($table,$arr)){
                return true;
            }else{
	            return false;
            }
        }else{
        	return false;
        }
    }

    //删除
    function get_del ($table,$ids,$zd='id'){
		if(is_array($ids)){
		    $this->db->where_in($zd,$ids);
		}else{
		    $this->db->where($zd,intval($ids));
		}
		if($this->db->delete($table)){
		    return true;
		}else{
		    return false;
		}
	}

    //创建表
    function get_table ($sql){
		//创建数据库
		$this->db->query($sql);
	}

    //获取任意字段信息
    function getzd($table,$ziduan,$id,$cha='id'){
		if($table && $ziduan && $id){
			$this->db->where($cha,$id);
			$this->db->select($ziduan);
			$row=$this->db->get($table)->row();
			if($row){
				return $row->$ziduan;
			}else{
				return "";	
			}
		}
    }

	//解析多个分类ID  如 cid=1,2,3,4,5,6
	function getchild($cid,$table){
		if(!empty($cid)){
		    $ClassArr=explode(',',$cid);
		 	for($i=0;$i<count($ClassArr);$i++){
		 		//sql语句的组织返回
		        $sql="select id from ".CS_SqlPrefix.$table." where fid='$ClassArr[$i]'";
		        $result=$this->db->query($sql);
		        if($result){
		    	    foreach ($result->result() as $row) {
		          		$ClassArr[]=$row->id;
		    	    }
				}
			}
		    $cid=implode(',',$ClassArr);
		}
		return $cid;
	}
}


