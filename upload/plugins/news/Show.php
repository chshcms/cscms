<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-16
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Show extends Cscms_Controller {
	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	}

    //内容
	public function index($fid = 'id', $id = 0,$page = 1){
		if(intval($fid)>0){
			$id = intval($fid);
			$page = intval($id);
		}else{
			$id = intval($id);
			$page = intval($page);
		}
		if($page>0) $p = $page>0 ? $page-1 : 0;
        //判断ID
        if($id==0) msg_url('出错了，ID不能为空！',Web_Path);
        //获取数据
	    $row=$this->Csdb->get_row_arr('news','*',$id);
	    if(!$row){
	    	msg_url('出错了，该数据不存在或者没有审核！',Web_Path);
	    }
        //判断运行模式,生成则跳转至静态页面
		$html=config('Html_Uri');
        if(config('Web_Mode')==3 && $html['show']['check']==1){
            //获取静态路径
			$Htmllink=LinkUrl('show',$fid,$id,0,'news');
			header("Location: ".$Htmllink);
			exit;
		}
		//获取当前分类下二级分类ID
		$arr['cid']=getChild($row['cid']);
		$arr['uid']=$row['uid'];
		$arr['tags']=$row['tags'];

		//标签加超级连接
		$zdy['[news:tags]'] = tagslink($row['tags']);
		unset($row['tags']);

		//文章分页内容
		$neirarr = explode('[cscms:page]', $row['content']);
		if($page>count($neirarr)) $p=count($neirarr)-1;
		$neir = $neirarr[$p];

        //文章内容,判断是否是收费文章
		if($row['vip']>0 || $row['level']>0 || $row['cion']>0){
			$content="<div id='cscms_content'></div>";
			$content.="<script type='text/javascript' src='".linkurl('show','pay',$id,$page,'news')."'></script>";
		}else{
            $content = $neir;
		}
		unset($row['content']);
		$zdy['[news:content]'] = $content;
		$zdy['[news:pl]'] = get_pl('news',$id);
		$zdy['[news:link]'] = LinkUrl('show','id',$row['id'],1,'news');
		$zdy['[news:classlink]'] = LinkUrl('lists','id',$row['cid'],1,'news');
		$zdy['[news:classname]'] = getzd('news_list','name',$row['cid']);

		//默认模板
		$skin = empty($row['skins'])?'show.html':$row['skins'];
		if(defined('MOBILE')){
			$tplfile = VIEWPATH.'mobile'.FGF.'skins'.FGF.Mobile_Skins_Dir.FGF.PLUBPATH.FGF.$skin;
		}else{
			$tplfile = VIEWPATH.'pc'.FGF.'skins'.FGF.Pc_Skins_Dir.FGF.PLUBPATH.FGF.$skin;
		}
		$tplstr = file_exists($tplfile) ? file_get_contents($tplfile) : '';
		//获取上下篇
        if(strpos($tplstr,'[news:slink]') !== false || strpos($tplstr,'[news:sname]') !== false){
			$rowd=$this->db->query("Select id,cid,name from ".CS_SqlPrefix."news where id<".$id." order by id desc limit 1")->row();
			if($rowd){
					$zdy['[news:slink]'] = LinkUrl('show','id',$rowd->id,1,'news');
					$zdy['[news:sname]'] = $rowd->name;
					$zdy['[news:sid]'] = $rowd->id;
			}else{
					$zdy['[news:slink]'] = '###';
					$zdy['[news:sname]'] = '没有了';
					$zdy['[news:sid]'] = 0;
			}
		}
        if(strpos($tplstr,'[news:xlink]') !== false || strpos($tplstr,'[news:xname]') !== false){
            $rowd=$this->db->query("Select id,cid,name from ".CS_SqlPrefix."news where id>".$id." order by id asc limit 1")->row();
			if($rowd){
					$zdy['[news:xlink]'] = LinkUrl('show','id',$rowd->id,1,'news');
					$zdy['[news:xname]'] = $rowd->name;
					$zdy['[news:xid]'] = $rowd->id;
			}else{
					$zdy['[news:xlink]'] = '###';
					$zdy['[news:xname]'] = '没有了';
					$zdy['[news:xid]'] = 0;
			}
		}
		//增加人气地址
		$hitslink = hitslink('hits/ids/'.$id,'news');
		//缓存ID
		$cacheid = 'news_show_'.$id.'_'.$page;
		//装载模板并输出
        $this->Cstpl->plub_show('news',$row,$arr,false,$skin,$row['name'],$row['name'],'',$cacheid,$zdy,$hitslink,$page,count($neirarr));
	}

    //判断权限、积分
	public function pay($id=0,$page=1){
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
        header("Cache-Control: no-cache, must-revalidate"); 
        header("Pragma: no-cache");
	    $this->load->model('Csuser');
        //判断ID
        if($id==0) exit();
		$page = intval($page);
		if($page>0) $p = $page>0 ? $page-1 : 0;
        //获取数据
	    $row=$this->Csdb->get_row_arr('news','name,uid,cid,id,vip,level,cion,content',$id);
	    if(!$row){
            exit("$('#cscms_content').html('<b style=color:red>数据没有审核，或者被删除！</b>');");
	    }

		//判断收费
        if($row['vip']>0 || $row['level']>0 || $row['cion']>0){
			$login=$this->Csuser->User_Login(1);
			if(!$login) exit("$('#cscms_content').html('<b style=color:red>抱歉，该文章需要登录才能阅读，请先登录！</b>');");
			$rowu=$this->Csdb->get_row_arr('user','zid,zutime,vip,level,cion',$_SESSION['cscms__id']);
			if($rowu['zutime']<time()){
				$this->db->query("update ".CS_SqlPrefix."user set zid=1,zutime=0 where id=".$_SESSION['cscms__id']."");
			    $rowu['zid']=1;
			}
		}
        //判断会员组权限
		if($row['vip']>0 && $row['uid']!=$_SESSION['cscms__id'] && $rowu['vip']==0){
			if($row['vip']>$rowu['zid']){
				exit("$('#cscms_content').html('<b style=color:red>抱歉，您所在的会员组不能阅读该文章，请先升级！</b>');");
			}
		}

        //判断会员等级权限
		if($row['level']>0 && $row['uid']!=$_SESSION['cscms__id']){
			if($row['level']>$rowu['level']){
				exit("$('#cscms_content').html('<b style=color:red>抱歉，您等级不够，不能阅读该文章！</b>');");
			}
		}

        //判断金币
		$down=0;
		if($row['cion']>0 && $row['uid']!=$_SESSION['cscms__id']){

			//判断是否下载过
			$did=$id;
			$rowd=$this->db->query("SELECT id,addtime FROM ".CS_SqlPrefix."news_look where did='".$did."' and uid='".$_SESSION['cscms__id']."'")->row_array();
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
				if($row['cion']>$rowu['cion']){
				    exit("$('#cscms_content').html('<b style=color:red>这编文章阅读需要".$row['cion']."个金币，您的当前金币不够，请先充值！</b>');");
				}else{
					//扣币
					$edit['cion']=$rowu['cion']-$row['cion'];
					$this->Csdb->get_update('user',$_SESSION['cscms__id'],$edit);
					//写入消费记录
					$add2['title']='阅读文章《'.$row['name'].'》';
					$add2['uid']=$_SESSION['cscms__id'];
					$add2['nums']=$row['cion'];
					$add2['ip']=getip();
					$add2['dir']='news';
					$add2['addtime']=time();
					$this->Csdb->get_insert('spend',$add2);

					//判断分成
					if(User_DownFun==1 && $row['uid']>0){
					     //分成比例
						 $bi=(User_Downcion<10)?'0.0'.User_Downcion:'0.'.User_Downcion;
					     $scion= intval($row['cion'] * $bi);
						 if($scion>0){
					          $this->db->query("update ".CS_SqlPrefix."user set cion=cion+".$scion." where id=".$row['uid']."");
					          //写入分成记录
					          $add3['title']='文章《'.$row['name'].'》 - 阅读分成';
					          $add3['uid']=$row['uid'];
					          $add3['dir']='news';
					          $add3['nums']=$scion;
					          $add3['ip']=getip();
					          $add3['addtime']=time();
					          $this->Csdb->get_insert('income',$add3);
						 }
					}
				}
			}
			//增加阅读记录
			if($down==0){
			   $add['name']=$row['name'];
			   $add['cid']=$row['cid'];
			   $add['did']=$did;
			   $add['uid']=$_SESSION['cscms__id'];
			   $add['cion']=$row['cion'];
			   $add['addtime']=time();
			   $this->Csdb->get_insert('news_look',$add);
			}
		}

		//文章分页内容
		$neirarr = explode('[cscms:page]', $row['content']);
		if($page>count($neirarr)) $p=count($neirarr)-1;
		$neir = $neirarr[$p];

		echo "var cscms_content='".escape($neir)."';$('#cscms_content').html(unescape(cscms_content));";
	}
}


