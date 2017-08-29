<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-03-07
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Funco extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	    $this->load->model('Csuser');
		$this->lang->load('user');
		$this->Csuser->User_Login();
	}

    //访客列表
	public function index($op='',$page=1){
	    $page=intval($page); //分页
		if(empty($op)) $op='you';
		//模板
		$tpl='funco.html';
		//URL地址
	    $url='funco/index/'.$op;
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//SQL
        if($op=='you'){
              $sqlstr = "select * from ".CS_SqlPrefix."funco where uida=".$_SESSION['cscms__id'];
		}else{
              $sqlstr = "select * from ".CS_SqlPrefix."funco where uidb=".$_SESSION['cscms__id'];
		}
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];
		$data=$data_content=$aliasname='';
        //装载模板
	    $template=$this->load->view($tpl,$data,true);
	    $Mark_Text=$this->Csskins->topandend($template);
	    $Mark_Text=str_replace("{cscms:title}",L('funco_01'),$Mark_Text);
	    $Mark_Text=str_replace("{cscms:keywords}",Web_Keywords,$Mark_Text);
	    $Mark_Text=str_replace("{cscms:description}",Web_Description,$Mark_Text);
	    $Mark_Text=str_replace("{cscms:fid}",$op,$Mark_Text); //当前使用的fid
	    //预先除了分页
	    $pagenum=getpagenum($Mark_Text);
        preg_match_all('/{cscms:([\S]+)\s+(.*?pagesize=\"([\S]+)\".*?)}([\s\S]+?){\/cscms:\1}/',$Mark_Text,$page_arr);
	    if(!empty($page_arr) && !empty($page_arr[2])){

				       $fields=$page_arr[1][0]; //前缀名
			           //组装SQL数据
				       $sqlstr=$this->Csskins->cscms_sql($page_arr[1][0],$page_arr[2][0],$page_arr[0][0],$page_arr[3][0],'id',$ids,0,$sqlstr);
				       $nums=$this->db->query($sqlstr)->num_rows(); //总数量
				       $Arr=userpage($sqlstr,$nums,$page_arr[3][0],$pagenum,$url,$page);
		               if($nums>0){
				            $sorti=1;
						    $result_array=$this->db->query($Arr[0])->result_array();
				            foreach ($result_array as $row2) {
								 $datatmp='';
								 $uida=$row2['uida'];
								 $uidb=$row2['uidb'];
                                 if($op=='my'){
                                      $row2['uida']=$uidb;
                                      $row2['uidb']=$uida;
								 }
					             $datatmp=$this->Csskins->cscms_skins($fields,$page_arr[0][0],$page_arr[4][0],$row2,$sorti);
					             $sorti++;
					             $data_content.=$datatmp;
				            }
		               }
		               $Mark_Text=page_mark($Mark_Text,$Arr);	//分页解析
		               $Mark_Text=str_replace($page_arr[0][0],$data_content,$Mark_Text);
		}
	    unset($page_arr);
	    $Mark_Text=$this->Csskins->cscms_common($Mark_Text);
        $Mark_Text=$this->Csskins->csskins($Mark_Text,$ids);
	    $Mark_Text=$this->Csskins->cscms_skins('user',$Mark_Text,$Mark_Text,$row);//解析当前数据标签
		$Mark_Text=$this->Csskins->cscmsumenu($Mark_Text,$_SESSION['cscms__id']);
        $Mark_Text=$this->Csskins->template_parse($Mark_Text,true);
        echo $Mark_Text;
	}
}
