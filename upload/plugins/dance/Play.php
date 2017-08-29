<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-09-20
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Play extends Cscms_Controller {
	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	}

    //歌曲播放
	public function index($fid = 'id', $id = 0){
		$id = (intval($fid)>0)?intval($fid):intval($id);   //ID
		//判断ID
		if($id==0) msg_url(L('dance_09'),Web_Path);
		//获取数据
		$row=$this->Csdb->get_row_arr('dance','*',$id);
		if(!$row){
		    msg_url(L('dance_10'),Web_Path);
		}
		//判断运行模式,生成则跳转至静态页面
		$html=config('Html_Uri');
		if(config('Web_Mode')==3 && $html['play']['check']==1){
		    //获取静态路径
			$Htmllink=LinkUrl('play','id',$id,0,'dance');
			header("Location: ".$Htmllink);
			exit;
		}

		//获取当前分类下二级分类ID
		$arr['cid'] = getChild($row['cid']);
		$arr['uid'] = $row['uid'];
		$arr['did'] = $row['id'];
		$arr['singerid'] = $row['singerid'];
		$arr['tags'] = $row['tags'];

		//标签加超级连接
		$zdytpl['[dance:tags]'] = tagslink($row['tags']);
		unset($row['tags']);

		//评论
		$zdytpl['[dance:pl]'] = get_pl('dance',$id);
		//当前地址
		$zdytpl['[dance:link]'] = LinkUrl('play','id',$row['id'],1,'dance');
		//分类地址、名称
		$zdytpl['[dance:classlink]'] = LinkUrl('lists','id',$row['cid'],1,'dance');
		$zdytpl['[dance:classname]'] = getzd('dance_list','name',$row['cid']);
		//专辑
		if($row['tid']==0){
		    $zdytpl['[dance:topiclink]'] = '###';
		    $zdytpl['[dance:topicname]'] = '未加入';
		}else{
		    $zdytpl['[dance:topiclink]'] = LinkUrl('topic','show',$row['tid'],1,'dance');
		    $zdytpl['[dance:topicname]'] = getzd('dance_topic','name',$row['tid']);
		}

		//获取模板，为了判断标签是否存在
		$skin = empty($row['skins'])?'play.html':$row['skins'];
		if(defined('MOBILE')){
			$tplfile = VIEWPATH.'mobile'.FGF.'skins'.FGF.Mobile_Skins_Dir.FGF.PLUBPATH.FGF.$skin;
		}else{
			$tplfile = VIEWPATH.'pc'.FGF.'skins'.FGF.Pc_Skins_Dir.FGF.PLUBPATH.FGF.$skin;
		}
		$tplstr = file_exists($tplfile) ? file_get_contents($tplfile) : '';
		//获取上下曲
		if(strpos($tplstr,'[dance:slink]') !== false || strpos($tplstr,'[dance:sname]') !== false){
			$rowd=$this->db->query("Select id,name from ".CS_SqlPrefix."dance where id<".$id." order by id desc limit 1")->row();
			if($rowd){
			    $zdytpl['[dance:slink]'] = LinkUrl('play','id',$rowd->id,1,'dance');
			    $zdytpl['[dance:sname]'] = $rowd->name;
			    $zdytpl['[dance:sid]'] = $rowd->id;
			}else{
			    $zdytpl['[dance:slink]'] = '#';
			    $zdytpl['[dance:sname]'] = '没有了';
			    $zdytpl['[dance:sid]'] = 0;
			}
		}
		if(strpos($tplstr,'[dance:xlink]') !== false || strpos($tplstr,'[dance:xname]') !== false){
			$rowd=$this->db->query("Select id,name from ".CS_SqlPrefix."dance where id>".$id." order by id asc limit 1")->row();
			if($rowd){
			    $zdytpl['[dance:xlink]'] = LinkUrl('play','id',$rowd->id,1,'dance');
			    $zdytpl['[dance:xname]'] = $rowd->name;
			    $zdytpl['[dance:xid]'] = $rowd->id;
			}else{
			    $zdytpl['[dance:xlink]'] = '#';
			    $zdytpl['[dance:xname]'] = '没有了';
			    $zdytpl['[dance:xid]'] = 0;
			}
		}
		//歌曲完整试听地址
		if(strpos($tplstr,'[dance:qurl]') !== false){
			$purl=$row['purl'];
			if($row['fid']>0){
				$rowf=$this->db->query("Select purl from ".CS_SqlPrefix."dance_server where id=".$row['fid']."")->row_array();
				if($rowf){
					$purl=$rowf['purl'].$row['purl'];
				}
			}
			$purl=annexlink($purl);
			$zdytpl['[dance:qurl]'] = $purl;
		}
		//cmp音频播放器
		$player="<script type='text/javascript'>
		var mp3_w='".CS_Play_w."';
		var mp3_h='".CS_Play_h."';
		var mp3_i='".$id."';
		var mp3_p='".hitslink('play','dance')."';
		var mp3_t='".Web_Path."';
		dance.mp3_play();
		</script>";
		$zdytpl['[dance:player]'] = $player;
		//jp音频播放器
		$jplayer="<script type='text/javascript'>
		var mp3_i='".$id."';
		var mp3_p='".hitslink('play','dance')."';
		var mp3_n='".str_replace("'","",$row['name'])."';
		var mp3_x='".LinkUrl('down','id',$row['id'],1,'dance')."';
		var mp3_l='".LinkUrl('down','lrc',$row['id'],1,'dance')."';
		dance.mp3_jplayer();
		</script>";
		$zdytpl['[dance:jplayer]'] = $jplayer;

		//增加人气地址
		$hitslink = hitslink('hits/ids/'.$id,'dance');
		//装载模板并输出
		$this->Cstpl->plub_show('dance',$row,$arr,false,$skin,$row['name'],$row['name'],'','',$zdytpl,$hitslink);
	}

	//获取播放地址
	public function url($op='cmp', $id = 0){
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
		header("Cache-Control: no-cache, must-revalidate"); 
		header("Pragma: no-cache");
		$id = intval($id);   //ID
		//判断ID
		if($id==0) exit();
		//获取数据
		$row=$this->Csdb->get_row_arr('dance','name,purl,fid,lrc',$id);
		if(!$row) exit();
		$purl=$row['purl'];
		if($row['fid']>0){
			$rowf=$this->db->query("Select purl from ".CS_SqlPrefix."dance_server where id=".$row['fid']."")->row_array();
			if($rowf){
			    $purl=$rowf['purl'].$row['purl'];
			}
		}
		$purl=annexlink($purl);
		if($op=='cmp'){
			echo '<list><m type="" src="'.$purl.'" label="'.$row['name'].'" opened="1"></m></list>';
		}elseif($op=='wap'){
			header("location:".$purl);
		}else{
			echo 'var mp3_u="'.$purl.'";';
			echo 'var mp3_l="'.str_replace("'","",str_checkhtml($row['lrc'])).'";';
		}
	}
}


