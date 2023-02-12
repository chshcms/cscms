<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-08
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Down extends Cscms_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('Cstpl');
	    $this->load->helper('vod');
		$this->load->model('Csuser');
	}

    //下载
	public function index($a1 , $a2 = 0, $a3 = 0, $a4 = 0){
		if(intval($a1)>0){
		    $id = intval($a1);   //ID
		    $zu = intval($a2);   //组
		    $ji = intval($a3);   //集数
		}else{
		    $id = intval($a2);   //ID
		    $zu = intval($a3);   //组
		    $ji = intval($a4);   //集数
		}
		$login='no';

		//判断ID
		if($id==0) msg_url('出错了，ID不能为空！',Web_Path);
		//获取数据
		$row=$this->Csdb->get_row_arr('vod','*',$id);
		if(!$row){
		    msg_url('出错了，该数据不存在或者没有审核！',Web_Path);
		}
		if(empty($row['durl'])){
		    msg_url('该视频没有下载地址！',Web_Path);
		}

		//判断收费
		if(($row['vip']>0 || $row['level']>0 || $row['cion']>0) && User_YkDown==0){
			$this->Csuser->User_Login();
			$rowu=$this->Csdb->get_row_arr('user','vip,zid,zutime,level,cion',$_SESSION['cscms__id']);
			if($rowu['zutime']<time()){
				$this->db->query("update ".CS_SqlPrefix."user set zid=1,zutime=0 where id=".$_SESSION['cscms__id']."");
			    $rowu['zid']=1;
			}
		}

		//判断会员组下载权限
		if($row['vip']>0 && $row['uid']!=$_SESSION['cscms__id'] && $rowu['vip']==0){
			if($row['vip']>$rowu['zid']){
			   msg_url('抱歉，您所在的会员组不能下载该视频，请先升级！','javascript:window.close();');
			}
		}

		//判断会员等级下载权限
		if($row['level']>0 && $row['uid']!=$_SESSION['cscms__id']){
			if($row['level']>$rowu['level']){
			   msg_url('抱歉，您等级不够，不能下载该视频！','javascript:window.close();');
			}
		}

		//判断金币下载
		$down=0;
		if($row['dcion']>0 && $row['uid']!=$_SESSION['cscms__id']){

			//判断是否下载过
			$did=$id.'-'.$zu.'-'.$ji;
			$rowd=$this->db->query("SELECT id,addtime FROM ".CS_SqlPrefix."vod_look where did='".$did."' and uid='".$_SESSION['cscms__id']."' and sid=1")->row_array();
			if($rowd){
			  $down=1; //数据已经存在
			  $downtime=User_Downtime*3600+$rowd['addtime'];
			  if($downtime>time()){
			       $down=2; //在多少时间内不重复扣币
			  }
			}

			//判断会员组下载权限
			$rowz=$this->db->query("SELECT id,did FROM ".CS_SqlPrefix."userzu where id='".$rowu['zid']."'")->row_array();
			if($rowz && $rowz['did']==1){ //有免费下载权限
			   $down=2; //该会员下载不收费
			}

			if($down<2){ //判断扣币
				if($row['dcion']>$rowu['cion']){
					msg_url('这部视频下载每集需要'.$row['cion'].'个金币，您的当前金币不够，请先充值！','javascript:window.close();');
				}else{
					//扣币
					$edit['cion']=$rowu['cion']-$row['dcion'];
					$this->Csdb->get_update('user',$_SESSION['cscms__id'],$edit);
					//写入消费记录
					$add2['title']='下载视频《'.$row['name'].'》- 第'.($ji+1).'集';
					$add2['uid']=$_SESSION['cscms__id'];
					$add2['dir']='vod';
					$add2['nums']=$row['cion'];
					$add2['ip']=getip();
					$add2['addtime']=time();
					$this->Csdb->get_insert('spend',$add2);

					//判断分成
					if(User_DownFun==1 && $row['uid']>0){
						//分成比例
						$bi=(User_Downcion<10)?'0.0'.User_Downcion:'0.'.User_Downcion;
						$scion= intval($row['dcion'] * $bi);
						if($scion>0){
							$this->db->query("update ".CS_SqlPrefix."user set cion=cion+".$scion." where id=".$row['uid']."");
							//写入分成记录
							$add3['title']='视频《'.$row['name'].'》- 第'.($ji+1).'集 - 下载分成';
							$add3['uid']=$row['uid'];
							$add3['dir']='vod';
							$add3['nums']=$scion;
							$add3['ip']=getip();
							$add3['addtime']=time();
							$this->Csdb->get_insert('income',$add3);
						}
					}
				}
			}
			//增加下载记录
			if($down==0){
				$add['name']=$row['name'];
				$add['cid']=$row['cid'];
				$add['sid']=1;
				$add['did']=$did;
				$add['uid']=$_SESSION['cscms__id'];
				$add['cion']=$row['dcion'];
				$add['addtime']=time();
				$this->Csdb->get_insert('vod_look',$add);
			}
		}
		//增加下载人气
		$this->db->query("update ".CS_SqlPrefix."vod set xhits=xhits+1 where id=".$row['id']."");
		//相关搜索数组
		$arr['cid']=getChild($row['cid']);
		$arr['uid']=$row['uid'];
		$arr['tags']=$row['tags'];
		$zdy['[vod:tags]'] = tagslink($row['tags']);
		unset($row['tags']);

		$zdy['[vod:pl]'] = get_pl('vod',$id);
		$zdy['[vod:link]'] = LinkUrl('show','id',$row['id'],1,'vod');
		$zdy['[vod:classlink]'] = LinkUrl('lists','id',$row['cid'],1,'vod');
		$zdy['[vod:classname]'] = getzd('vod_list','name',$row['cid']);


		//输出下载地址
		require_once CSCMS.'vod/down.php';
		$Data_Arr=explode("#cscms#",$row['durl']);
		if($zu>=count($Data_Arr)) $zu=0;
		$DataList_Arr=explode("\n",$Data_Arr[$zu]);
		$Dataurl_Arr=explode('$',$DataList_Arr[$ji]);

		$laiyuan=$Dataurl_Arr[2]; //来源
		$url=$Dataurl_Arr[1];  //地址
		$pname=$Dataurl_Arr[0];  //当前集数
		if(substr($url,0,11)=='attachment/') $url=annexlink($url);
		$zdy['[down:url]'] = $url;
		$zdy['[down:laiy]'] = $laiyuan;
		$zdy['[down:laiyname]'] = '来源不存在';
		$zdy['[down:ji]'] = $pname;
		for ($i=0; $i<count($down_config); $i++) {
	        if($down_config[$i]['form'] == $laiyuan){
	        	$zdy['[down:laiyname]'] = $down_config[$i]['name'];
	        	break;
	        }
	    }

		//装载模板并输出
		$cacheid = 'vod_down_'.$id.'_'.$zy.'_'.$ji;
		$this->Cstpl->plub_show('vod',$row,$arr,false,'down.html',$row['name'],$row['name'],'',$cacheid,$zdy);
	}
}