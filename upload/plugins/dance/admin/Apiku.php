<?php if ( ! defined('IS_ADMIN')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-03
 */
class Apiku extends Cscms_Controller {

	function __construct(){
		    parent::__construct();
		    $this->load->model('Csadmin');
	        $this->Csadmin->Admin_Login();
	}

    //资源库列表
	public function index()
	{
            $ac = $this->input->get('ac',true);
            $op = $this->input->get('op',true);
            $do = $this->input->get('do',true);
			$rid  = intval($this->input->get('rid'));

			if($do=='caiji'){ //入库

 	                $api  = 'aHR0cDovL2RqLmNoc2hjbXMuY29tL2RqL3Y0LnBocA';
 	                $page = intval($this->input->get('page'));
 	                $cid  = intval($this->input->get('cid'));
 	                $ac   = "cscms";
 	                $ops  = $this->input->get('op',TRUE);
 	                $key  = $keys = $this->input->get('key',TRUE);
 	                $ids  = $this->input->get('ids',TRUE);
		            if($page==0) $page=1;
		            if($ops=='24') $ops='day';
		            if($ops=='day'){
			            $op=24;
		            }elseif($ops=='week'){
			            $op=98;
		            }else{
			            $op=0;
		            }
					//绑定分类数组
                    $LIST = require_once(CSCMS.PLUBPATH.FGF.'bind.php');

		            $api_url ='?rid='.$rid.'&op='.$op.'&ac='.$ac.'&do=caiji&key='.$key.'&cid='.$cid;
                    if($api){
                          $API_URL=cs_base64_decode($api).'?ac=caiji&rid='.$rid.'&key='.$key.'&cid='.$cid.'&h='.$op.'&ids='.$ids.'&page='.$page.'&host='.Web_Url;
                          $strs=htmlall($API_URL);
                          $dance=json_decode($strs,true);
					      $dance=get_bm($dance);

			              if(empty($strs)) admin_msg('<font color=red>采集失败，请多试几次，如一直出现该错误，通常为网络不稳定或禁用了采集！</font>','javascript:history.go(-1);');

			              //分页信息
						  if(!empty($dance)){
			                   $recordcount = $dance['nums'];
			                   $pagecount = $dance['pagejs'];
			                   $pagesize = $dance['pagesize'];
			                   $pageindex = $dance['page'];
						  }else{
			                   $recordcount = 0;
			                   $pagecount = 0;
			                   $pagesize = 0;
			                   $pageindex = 0;	
						  }

						echo '<link href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><link rel="stylesheet" href="'.base_url().'packs/admin/css/font.css"><script src="'.base_url().'packs/js/jquery.min.js"></script>';
						echo '<div id="loading" style="display:none;position: fixed;left:40%;top: 45%;z-index:10;"><span style="width:100px;height:40px;line-height:40px;background-color:#eee;font-size: 20px;padding: 15px;">&nbsp;&nbsp;<i class="fa fa-spin fa-spinner colorl" style="font-size:26px;"></i>&nbsp;数据加载中...</span></div>';
						echo "&nbsp;&nbsp;<b><font class='colorn'>当前页共有".$recordcount."个数据，需要采集".$pagecount."次，每一次采集".$pagesize."个，正在执行第".$pageindex."次采集任务</font></b><br/><br><div style='font-size:14px;line-height:25px'>";

			              //组合列表
                          $s=1;
			              foreach($dance['list'] as $k=>$v){

                                  $p=($pageindex-1)*$pagesize+$s;

				                  $add['name']     = str_replace("'","",htmlspecialchars_decode($dance['list'][$k]['name']));
				                  $add['uid']      = 1;
                                  $add['purl']     = $dance['list'][$k]['url'];
                                  $add['durl']     = $add['purl'];
								  $add['addtime']  = time();
                                  //判断绑定
			                      $val=arr_key_value($LIST,$ac.'_'.$dance['list'][$k]['cid']);
			                      if(!$val){

			                        echo "&nbsp;&nbsp;&nbsp;第".$p."个歌曲&nbsp;<font color=red>".$add['name']."</font>&nbsp;&nbsp;数据没有绑定分类，不进行入库处理！<br/>";

                                  //判断数据完整性
					              }elseif(empty($add['name']) || empty($add['purl'])){

			                                   echo "&nbsp;&nbsp;&nbsp;第".$p."个歌曲&nbsp;<font color=red>".$add['name']."</font>&nbsp;&nbsp;数据不完整，不进行入库处理！<br/>";

					              }else{

				                        $add['cid']  = $val;

							            //判断数据是否存在
					                    $sql="SELECT id FROM ".CS_SqlPrefix."dance where name='".$add['name']."'";
	 		                            $row=$this->db->query($sql)->row();
			                            if(!$row){

                                               $this->Csdb->get_insert('dance',$add);
					                           echo "&nbsp;&nbsp;&nbsp;第".$p."个歌曲&nbsp;<font color=#ff00ff>".$add['name']."</font>数据库中没有记录，已入库完成！<br/>";

							            }else{

								             echo "&nbsp;&nbsp;&nbsp;第".$p."个歌曲&nbsp;<font color=#ff6600>".$add['name']."</font>&nbsp;&nbsp;数据存在，跳过!~<br/>";
							            }
					              }
			              $s++;
			              }

			              if($pageindex < $pagecount){
					            //缓存断点续采
					            $jumpurl = site_url('dance/admin/apiku').$api_url.'&page='.($page+1);
					            write_file(APPPATH."config/jumpurl.txt", $jumpurl);
					            //跳转到下一页
					            echo("</br>&nbsp;&nbsp;&nbsp;<a href='".site_url('dance/admin/apiku')."?api=".$api."&op=".$ops."&ac=".$ac."&key=".$keys."&cid=".$cid."'>紧急停止</a>&nbsp;&nbsp;&nbsp;<b>&nbsp;第<font color=red>".$page."</font>页入库完毕,暂停<font color=red>3</font>秒继续。。。。。</b><script>setTimeout('updatenext();',3000);
								function updatenext(){
									document.getElementById('loading').style.display = 'block';
									location.href='".$api_url."&page=".($page+1)."';
								}
								</script></br></br>");
			              }else{
					            //清除断点续采
					            write_file(APPPATH."config/jumpurl.txt", "0");
					            echo("</br>&nbsp;&nbsp;&nbsp;&nbsp;<b>恭喜您，全部入库完成啦。。。。。</b><script>
								setTimeout('updatenext();',3000);
								function updatenext(){
									document.getElementById('loading').style.display = 'block';
									location.href='".site_url('dance/admin/apiku').str_replace("&do=caiji","",$api_url)."';
								}
								</script>");				
			              }
		            }else{
                           
						  admin_msg('<font color=red>API错误！</font>','javascript:history.go(-1);');
		            }

			}else{  //资源库查看

 	    		$api  = 'aHR0cDovL2RqLmNoc2hjbXMuY29tL2RqL3Y0LnBocA';
 	    		$page = intval($this->input->get('page'));
 	   		    $cid  = intval($this->input->get('cid'));
 	    		$ac   = 'cscms';
 	    		$op   = $this->input->get('op',TRUE);
 	    		$key  = $this->input->get('key',TRUE);
				if($page==0) $page=1;
				if($op=='all') $op=0;

				$data['api_url'] ='?api='.$api.'&rid='.$rid.'&op='.$op.'&ac='.$ac.'&key='.$key.'&cid='.$cid;
				$data['key'] = $key;
				$data['op']  = $op;
				$data['cid'] = $cid;
				$data['rid'] = $rid;
				$data['api'] = $api;
        		if($api){
              		 $API_URL=cs_base64_decode($api).'?ac=list&rid='.$rid.'&key='.$key.'&cid='.$cid.'&h=0&ids=&page='.$page.'&host='.Web_Url;
					 $strs=htmlall($API_URL);
                     $dance=json_decode($strs,true);
					 $dance=get_bm($dance);

					 if(empty($strs)) admin_msg('<font color=red>获取列表失败，请多试几次，如一直出现该错误，通常为网络不稳定或禁用了采集！</font>','javascript:history.go(-1);');


			  		//分页信息
			 		$path=site_url('dance/admin/apiku').$data['api_url'].'&key='.$key.'&cid='.$cid.'&page=';
					$data['page_data'] = page_data($dance['nums'],$page,$dance['pagejs']); //获取分页类
		        	$data['page_list'] = admin_page($path,$page,$dance['pagejs']); //获取分页类
			  
			  		//列表
             		$data['dance']=$dance['list'];
			 		//分类
             		$data['dance_list']=$dance['type'];
            		$data['ac']=$ac;
            		$data['page']=$page;

                    $data['LIST'] = require_once(CSCMS.PLUBPATH.FGF.'bind.php');
            		$this->load->view('apiku_list.html',$data);

        		}else{
            		  admin_msg('<font color=red>API错误！</font>','javascript:history.go(-1);');
				}

			}
	}

    //绑定分类
	public function bind()
	{
	        $csid = intval($this->input->get('csid'));
 	        $ac  = $this->input->get('ac',TRUE);

            $LIST = require_once(CSCMS.PLUBPATH.FGF.'bind.php');
			$val=arr_key_value($LIST,$ac.'_'.$csid);
            $strs='<option value="0">&nbsp;|—选择目标分类</option>';
            $query = $this->db->query("SELECT id,name FROM ".CS_SqlPrefix."dance_list where fid=0 order by xid asc"); 
            foreach ($query->result() as $row) {
                        $clas=($row->id==$val)?' selected="elected"':'';
			            $strs.='<option value="'.$row->id.'"'.$clas.'>&nbsp;|—'.$row->name.'</option>';
                        $query2 = $this->db->query("SELECT id,name FROM ".CS_SqlPrefix."dance_list where fid=".$row->id." order by xid asc"); 
                        foreach ($query2->result() as $row2) {
                            $clas2=($row2->id==$val)?' selected="elected"':'';
			                $strs.='<option value="'.$row2->id.'"'.$clas2.'>&nbsp;|&nbsp;&nbsp;&nbsp;|—'.$row2->name.'</option>';
			            }
            }
            echo '<select class="select" name="cid" id="cid">'.$strs.'
                 </select><input class="button" type="button" value="提 交" onClick="submitbind(\''.$ac.'\',\''.$csid.'\');" style="cursor:pointer"> <input name="button" type="button" value="取 消" class="button" onClick="hidebind();" style="cursor:pointer">
				 ';
    }

    //绑定分类存储
	public function bind_save()
	{
 	    $ac   = $this->input->get('ac',TRUE);
	    $csid = intval($this->input->get_post('csid'));
	    $id = intval($this->input->get_post('cid'));

	    $LIST = require_once(CSCMS.PLUBPATH.FGF.'bind.php');
	    $LIST[$ac.'_'.$csid] = $id;
		arr_file_edit($LIST,CSCMS.PLUBPATH.FGF.'bind.php');
        echo 'ok';
	}

	//解除全部绑定
	public function jie_bind()
	{
 	        $api  = $this->input->get('api',TRUE);
 	        $ac   = $this->input->get('ac',TRUE);
	        $LIST = require_once(CSCMS.PLUBPATH.FGF.'bind.php');
            foreach ($LIST as $k=>$v) {
                 if(strpos($k,$ac.'_') !== FALSE){
                     unset($LIST[$k]);
				 }
			}
			arr_file_edit($LIST,CSCMS.PLUBPATH.FGF.'bind.php');
            header("Location: ".site_url('dance/admin/apiku')."?api=".$api."&ac=".$ac.""); 
    }
}

