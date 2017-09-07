<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-16
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Gbook extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	    $this->load->model('Csuser');
	    $this->lang->load('home');
	    if(!defined('HOMEPATH')) define('HOMEPATH', 'home');
	}

	public function index(){
		//模板
		$tpl='gbook.html';
		//当前会员
		$uid=get_home_uid();
	    $row=$this->Csdb->get_row_arr('user','*',$uid);
		if(!$row) msg_url(L('home_01'),is_ssl().Web_Url.Web_Path);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title=$row['nichen'].L('gbook_01');
		$ids['uid']=$row['id'];
		$ids['uida']=$row['id'];
        $this->Cstpl->home_list($row,'gbook',1,$tpl,$title,$ids);
	}

	public function ajax($uid=0,$page=1){
	    $callback = $this->input->get('callback',true);
		$uid=intval($uid);
		$page=intval($page);
		if($uid==0) exit($callback."({str:".json_encode('uid error~!')."})");
		$data=$data_content=$aliasname='';
		//模板
		$tpl='gbook-ajax.html';
		//URL地址
	    $url='gbook/ajax/'.$uid;
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$uid);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
        //装载模板
		if(!defined('HOMEPATH')) define('HOMEPATH',true);
		$this->load->get_templates('',$row['skins']);
	    $Mark_Text=$this->load->view($tpl,'',true);
		$Mark_Text=$this->Csskins->cscms_common($Mark_Text,$row['skins']);
	    //预先除了分页
	    $pagenum=getpagenum($Mark_Text);
	    preg_match_all('/{cscms:([\S]+)\s+(.*?pagesize=\"([\S]+)\".*?)}([\s\S]+?){\/cscms:\1}/',$Mark_Text,$page_arr);
	    if(!empty($page_arr) && !empty($page_arr[2])){

				       $field=$page_arr[1][0]; //前缀名
			           //组装SQL数据
					   $arr['uid']=$row['id'];
		               $arr['uida']=$row['id'];
				       $sqlstr=$this->Csskins->cscms_sql($page_arr[1][0],$page_arr[2][0],$page_arr[0][0],$page_arr[3][0],'id',$arr,'');
				       $nums=$this->db->query($sqlstr)->num_rows(); //总数量
				       $Arr=spanajaxpage($sqlstr,$nums,$page_arr[3][0],$pagenum,'cscms.home_gbook',$page);
		               if($nums==0){
			                $data_content.="";
		               }else{
				            $sorti=1;
						    $result_array=$this->db->query($Arr[0])->result_array();
				            foreach ($result_array as $row2) {
					             $datatmp=$this->Csskins->cscms_skins($field,$page_arr[0][0],$page_arr[4][0],$row2,$sorti);
					             $sorti++;
					             $data_content.=$datatmp;
				            }
		               }
		               $Mark_Text=page_mark($Mark_Text,$Arr);	//分页解析
		               $Mark_Text=str_replace($page_arr[0][0],$data_content,$Mark_Text);
		}
	    unset($page_arr);
		//Token
		$Mark_Text=str_replace("[gbook:token]",get_token('gbook_token'),$Mark_Text);
		//表情
        $plfaces="";
	    for($i=1;$i<=56;$i++){
             $plfaces.="<img style='cursor:pointer;' src=\"".Web_Path."packs/images/faces/e".$i.".gif\" onclick=\"$('#cscms_gbook_content').val($('#cscms_gbook_content').val()+'[em:".$i."]');$('#cscms_faces').hide();\" />";
        }
		$Mark_Text=str_replace("[gbook:faces]",$plfaces,$Mark_Text);
        $Mark_Text=$this->Csskins->template_parse($Mark_Text,false);
		getjson(array('str'=>$Mark_Text),0,1,$callback);
	}

    //新增留言
	public function add(){
	    $callback = $this->input->get('callback',true);
		$token=$this->input->get_post('token', TRUE);
		$add['uida']=(int)$this->input->get_post('uid', TRUE);
		$add['neir']=$this->input->get_post('neir', TRUE);
		$add['neir']=facehtml(filter($add['neir']));
		$hf = 0;
		//转化回复
        preg_match_all ('/'.L('gbook_02').'@(.*)@:/i',$add['neir'],$bs);
        if(!empty($bs[0][0]) && !empty($bs[1][0])){
			  $uid=getzd('user','id',$bs[1][0],'name');
			  $nichen=getzd('user','nichen',$bs[1][0],'name');
			  $ulink=userlink('index',$uid,$bs[1][0]);
			  if(empty($nichen)) $nichen=$bs[1][0];
			  $b=L('gbook_02').'<a target="_blank" href="'.$ulink.'">@'.$nichen.'@</a>:';
              $add['neir']=str_replace($bs[0][0],$b,$add['neir']);
              $hf = 1;
		}
        unset($bs);
		if($add['uida']==0){
             getjson(L('gbook_05'),1,1,$callback);
		}elseif(!get_token('gbook_token',1,$token)){
             getjson(L('gbook_06'),1,1,$callback);
		}elseif(isset($_SESSION['gbookaddtime']) && time()<$_SESSION['gbookaddtime']+30){
             getjson(L('gbook_07'),1,1,$callback);
		}elseif(empty($add['neir'])){
             getjson(L('gbook_08'),1,1,$callback);
		}elseif(empty($_SESSION['cscms__id'])){
             getjson(L('gbook_09'),1,1,$callback);
		}else{

            $add['uidb']=$_SESSION['cscms__id'];
		    $add['fid']=intval($this->input->get_post('fid'));
		    $add['ip']=getip();
		    $add['addtime']=time();

            $ids=$this->Csdb->get_insert('gbook',$add);
		    if(intval($ids)==0){
             	getjson(L('gbook_10'),1,1,$callback);
			}else{
                //摧毁token
		        get_token('gbook_token',2);
                $_SESSION['gbookaddtime']=time();

				//发送通知
			    $addm['uida']=$add['uida'];
			    $addm['uidb']=$_SESSION['cscms__id'];
			    $addm['name']=L('gbook_03');
			    $addm['neir']=vsprintf(L('gbook_04'),array($_SESSION['cscms__name']));
			    $addm['addtime']=time();
        	    $this->Csdb->get_insert('msg',$addm);
			}
		}
		if($hf==0){
			getjson(array('msg'=>'ok'),0,1,$callback);
		}else{
			getjson(array('msg'=>'ok'),0,1,$callback);
		}
	}

    //删除留言
	public function del(){
	    $callback = $this->input->get('callback',true);
		$id = intval($this->input->get_post('id'));
		$token=$this->input->get_post('token', TRUE);
		if($id==0){
			getjson(L('gbook_11'),1,1,$callback);
		}elseif(!get_token('gbook_token',1,$token)){
			getjson(L('gbook_12'),1,1,$callback);
		}elseif(empty($_SESSION['cscms__id'])){ //未登陆
			getjson(L('gbook_13'),1,1,$callback);
		}else{
	            $row=$this->Csdb->get_row('gbook','uida,uidb',$id);
	            if(!$row){
                     getjson(L('gbook_14'),1,1,$callback);
				}elseif($row->uida!=$_SESSION['cscms__id'] && $row->uidb!=$_SESSION['cscms__id']){
                     getjson(L('gbook_15'),1,1,$callback);
				}else{
                     //权限通过删除
					 $this->Csdb->get_del('gbook',$id,'fid');
					 $this->Csdb->get_del('gbook',$id);
				}
		}
		getjson(array('msg'=>'ok'),0,1,$callback);
	}
}
