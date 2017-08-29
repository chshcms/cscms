<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-09-20
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Playsong extends Cscms_Controller {
	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	    $this->load->model('Csuser');
	}

    //歌曲播放
	public function index(){
        $ids=$this->input->get('id',TRUE);
        $num = 30; //默认随机歌曲数量
        if(empty($ids)){
            $sqlstr="select id from ".CS_SqlPrefix."dance order by rand() desc LIMIT ".$num;
	        $result=$this->db->query($sqlstr);
	        $recount=$result->num_rows();
            if($recount>0){
	            foreach ($result->result() as $row) {
                    $ids.=$row->id.",";
                }
            }
        }
        if(substr($ids,-1)==",") $ids=substr($ids,0,-1);
        $zdy['{cscms:lbid}'] = $ids;
		//装载模板并输出
        $this->Cstpl->plub_show('dance',array(),$ids,FALSE,'playsong.html','音乐盒 - '.Web_Name,'','','',$zdy);
	}

    //当前ID数据
	public function data(){
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
        header("Cache-Control: no-cache, must-revalidate"); 
        header("Pragma: no-cache");
        if(empty($_SERVER['HTTP_REFERER'])){exit('QQ:848769359');}
        $ids=$this->input->get_post('id',TRUE);
	    $callback = $this->input->get('callback',true);
        $Arr=explode(',',$ids);
        $lists=array();
        $zd = $this->input->get_post('zd',TRUE);
        $zdarr = array();
        if(!empty($zd)){
            $zdarr = explode(',',$zd);
            $zd = ','.$zd;
        }
        for($j=0;$j<count($Arr);$j++){
            $id=intval($Arr[$j]);
			if($id>0){
			    $row=$this->db->query("select id,cid,singerid,name,tid,fid,purl,sc,lrc,dhits".$zd." from ".CS_SqlPrefix."dance where id=".$id."")->row();
			    if($row){
				    $lrc=str_checkhtml($row->lrc);
                    if(empty($lrc)) $lrc='0';
                    $tpic=piclink('dancetopic','');
                    $topic='-';
                    $topiclink='###';
					if($row->tid>0){
			            $rowt=$this->db->query("select id,pic,name from ".CS_SqlPrefix."dance_topic where id=".$row->tid."")->row();
						if($rowt){
                               $topiclink=LinkUrl('topic/show','id',$row->tid,1,'dance');
                               $tpic=piclink('dancetopic',$rowt->pic);
							   $topic=$rowt->name;
						}
					}
					$purl=$row->purl;
					if($row->fid>0){
    					$rowf=$this->db->query("Select purl from ".CS_SqlPrefix."dance_server where id=".$row->fid."")->row();
						if($rowf){
	     					$purl=$rowf->purl.$row->purl;
						}
					}
					$purl=annexlink($purl);
                    $singer=getzd('singer','name',$row->singerid);
                    $lists[$j]['name']       = $row->name;
                    $lists[$j]['singer']     = empty($singer)?'':$singer;
                    $lists[$j]['singerlink'] = LinkUrl('show','id',$row->singerid,1,'singer');
                    $lists[$j]['downlink']   = LinkUrl('down','id',$row->id,1,'dance');
                    $lists[$j]['tpic']       = $tpic;
                    $lists[$j]['topic']      = $topic;
                    $lists[$j]['topiclink']  = $topiclink;
                    $lists[$j]['url']        = $purl;
                    $lists[$j]['id']         = $row->id;
                    $lists[$j]['time']       = $this->get_time($row->sc);
                    $lists[$j]['lrc']        = $lrc;
                    $lists[$j]['plink']      = LinkUrl('play','id',$row->id,1,'dance');
                    $lists[$j]['dhits']      = $row->dhits;
                    $lists[$j]['cid']        = $row->cid;
                    if(!empty($zdarr)){
                        foreach ($zdarr as $k => $v) {
                            $lists[$j][$v]   = isset($row->$v) ? $row->$v : '';
                        }
                    }
			    }
			}
		}
		getjson($lists,0,1,$callback);
	}

    //随便听听
	public function rand(){
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
        header("Cache-Control: no-cache, must-revalidate"); 
        header("Pragma: no-cache");
        if(empty($_SERVER['HTTP_REFERER'])){exit('QQ:848769359');}
	    $callback = $this->input->get('callback',true);
        $num = intval($this->input->get_post('num'));
        $num = $num<1 || $num>100 ? 30 : $num;
        $lists=array();
        $zd = $this->input->get_post('zd',TRUE);
        $zdarr = array();
        if(!empty($zd)){
            $zdarr = explode(',',$zd);
            $zd = ','.$zd;
        }
        $result=$this->db->query("select id,singerid,name,tid,fid,purl,sc,lrc".$zd." from ".CS_SqlPrefix."dance order by rand() desc LIMIT ".$num);
		$j=0;
		foreach ($result->result() as $row) {
		    $lrc=str_checkhtml($row->lrc);
            if(empty($lrc)) $lrc='0';
            $tpic=piclink('dancetopic','');
            $topic='-';
            $topiclink='###';
			if($row->tid>0){
	            $rowt=$this->db->query("select id,pic,name from ".CS_SqlPrefix."dance_topic where id=".$row->tid."")->row();
				if($rowt){
                       $topiclink=LinkUrl('topic/show','id',$row->tid,1,'dance');
                       $tpic=piclink('dancetopic',$rowt->pic);
					   $topic=$rowt->name;
				}
			}
			$purl=$row->purl;
			if($row->fid>0){
				$rowf=$this->db->query("Select purl from ".CS_SqlPrefix."dance_server where id=".$row->fid."")->row();
				if($rowf){
 					$purl=$rowf->purl.$row->purl;
				}
			}

            $singer=getzd('singer','name',$row->singerid);
            $lists[$j]['name']       = $row->name;
            $lists[$j]['singer']     = empty($singer)?'':$singer;
            $lists[$j]['singerlink'] = LinkUrl('show','id',$row->singerid,1,'singer');
            $lists[$j]['downlink']   = LinkUrl('down','id',$row->id,1,'dance');
            $lists[$j]['tpic']       = $tpic;
            $lists[$j]['topic']      = $topic;
            $lists[$j]['topiclink']  = $topiclink;
            $lists[$j]['url']        = $purl;
            $lists[$j]['id']         = $row->id;
            $lists[$j]['time']       = $this->get_time($row->sc);
            $lists[$j]['lrc']        = $lrc;
            $lists[$j]['plink']      = LinkUrl('play','id',$row->id,1,'dance');
            $lists[$j]['dhits']      = $row->dhits;
            $lists[$j]['cid']        = $row->cid;
            if(!empty($zdarr)){
                foreach ($zdarr as $k => $v) {
                    $lists[$j][$v]   = isset($row->$v) ? $row->$v : '';
                }
            }
			$j++;
		}
		getjson($lists,0,1,$callback);
	}

    //我喜欢的
	public function favs(){
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
        header("Cache-Control: no-cache, must-revalidate"); 
        header("Pragma: no-cache");
        if(empty($_SERVER['HTTP_REFERER'])){exit('QQ:848769359');}
		$lists=array();
        $zd = $this->input->get_post('zd',TRUE);
        $zdarr = array();
        if(!empty($zd)){
            $zdarr = explode(',',$zd);
            $zd = ','.$zd;
        }
	    $callback = $this->input->get('callback',true);
        $num = intval($this->input->get_post('num'));
        $num = $num<1 || $num>100 ? 30 : $num;
	    if(!$this->Csuser->User_Login(1)){
            $lists['error']='login';
			echo $callback."(".json_encode($lists).")"; 
            exit();
		}
        $result=$this->db->query("select did from ".CS_SqlPrefix."dance_fav where uid='".$_SESSION['cscms__id']."' order by id desc LIMIT ".$num);
		$j=0;
		foreach ($result->result() as $row2) {
           $row=$this->db->query("select id,singerid,name,tid,fid,purl,sc,lrc".$zd." from ".CS_SqlPrefix."dance where id=".$row2->did."")->row();

		   if($row){

			    $lrc=str_checkhtml($row->lrc);
                if(empty($lrc)) $lrc='0';
                $tpic=piclink('dancetopic','');
                $topic='-';
                $topiclink='###';
				if($row->tid>0){
		            $rowt=$this->db->query("select id,pic,name from ".CS_SqlPrefix."dance_topic where id=".$row->tid."")->row();
					if($rowt){
                           $topiclink=LinkUrl('topic/show','id',$row->tid,1,'dance');
                           $tpic=piclink('dancetopic',$rowt->pic);
						   $topic=$rowt->name;
					}
				}
				$purl=$row->purl;
				if($row->fid>0){
					$rowf=$this->db->query("Select purl from ".CS_SqlPrefix."dance_server where id=".$row->fid."")->row();
					if($rowf){
     					$purl=$rowf->purl.$row->purl;
					}
				}
                $singer=getzd('singer','name',$row->singerid);
                $lists[$j]['name']       = $row->name;
                $lists[$j]['singer']     = empty($singer)?'':$singer;
                $lists[$j]['singerlink'] = LinkUrl('show','id',$row->singerid,1,'singer');
                $lists[$j]['downlink']   = LinkUrl('down','id',$row->id,1,'dance');
                $lists[$j]['tpic']       = $tpic;
                $lists[$j]['topic']      = $topic;
                $lists[$j]['topiclink']  = $topiclink;
                $lists[$j]['url']        = $purl;
                $lists[$j]['id']         = $row->id;
                $lists[$j]['time']       = $this->get_time($row->sc);
                $lists[$j]['lrc']        = $lrc;
                $lists[$j]['plink']      = LinkUrl('play','id',$row->id,1,'dance');
                $lists[$j]['dhits']      = $row->dhits;
                $lists[$j]['cid']        = $row->cid;
                if(!empty($zdarr)){
                    foreach ($zdarr as $k => $v) {
                        $lists[$j][$v]   = isset($row->$v) ? $row->$v : '';
                    }
                }
		        $j++;
		   }
		}
		getjson($lists,0,1,$callback);
	}

    //判断登录
	public function log(){
	    $callback = $this->input->get('callback',true);
	    if(!$this->Csuser->User_Login(1)){
              $str['error']='no';
	    }else{
              $str['error']='ok';
			  $nichen=getzd('user','nichen',$_SESSION['cscms__id']);
              $str['nichen']='<a style="color:#888999;" href="'.spacelink('space').'" target="_blank">'.get_bm($nichen,'gbk','utf-8').'</a>';
			  //收藏总数
	          $favnums=$this->db->query("SELECT id FROM ".CS_SqlPrefix."dance_fav where uid=".$_SESSION['cscms__id']."")->num_rows();
              $str['favnums']=$favnums;
		}
		getjson($str,0,1,$callback);
	}

    //猜你喜欢
	public function cais(){
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
        header("Cache-Control: no-cache, must-revalidate"); 
        header("Pragma: no-cache");
        if(empty($_SERVER['HTTP_REFERER'])){exit('QQ:848769359');}
        $id=intval($this->input->get_post('id',TRUE));
        $num = intval($this->input->get_post('num'));
        $num = $num<1 || $num>100 ? 10 : $num;
	    $callback = $this->input->get('callback',true);
        $lists=array();
        $row2=$this->db->query("select cid from ".CS_SqlPrefix."dance where id='".$id."'")->row();
        $zd = $this->input->get_post('zd',TRUE);
        $zdarr = array();
        if(!empty($zd)){
            $zdarr = explode(',',$zd);
            $zd = ','.$zd;
        }
        $result2=$this->db->query("select id,singerid,name,tid,fid,purl,sc,lrc".$zd." from ".CS_SqlPrefix."dance where cid=".$row2->cid." order by rand() desc LIMIT ".$num);
		$j=0;
		foreach ($result2->result() as $row) {
		    $lrc=str_checkhtml($row->lrc);
            if(empty($lrc)) $lrc='0';
            $tpic=piclink('dancetopic','');
            $topic='-';
            $topiclink='###';
			if($row->tid>0){
	            $rowt=$this->db->query("select id,pic,name from ".CS_SqlPrefix."dance_topic where id=".$row->tid."")->row();
				if($rowt){
                    $topiclink=LinkUrl('topic/show','id',$row->tid,1,'dance');
                    $tpic=piclink('dancetopic',$rowt->pic);
                    $topic=$rowt->name;
				}
			}
			$purl=$row->purl;
			if($row->fid>0){
				$rowf=$this->db->query("Select purl from ".CS_SqlPrefix."dance_server where id=".$row->fid."")->row();
				if($rowf){
 					$purl=$rowf->purl.$row->purl;
				}
			}

            $singer=getzd('singer','name',$row->singerid);
            $lists[$j]['name']  = $row->name;
            $lists[$j]['singer']     = empty($singer)?'':$singer;
            $lists[$j]['singerlink'] = LinkUrl('show','id',$row->singerid,1,'singer');
            $lists[$j]['downlink']   = LinkUrl('down','id',$row->id,1,'dance');
            $lists[$j]['tpic']       = $tpic;
            $lists[$j]['topic']      = $topic;
            $lists[$j]['topiclink']  = $topiclink;
            $lists[$j]['url']        = $purl;
            $lists[$j]['id']         = $row->id;
            $lists[$j]['time']       = $this->get_time($row->sc);
            $lists[$j]['lrc']        = $lrc;
            $lists[$j]['plink']      = LinkUrl('play','id',$row->id,1,'dance');
            $lists[$j]['dhits']      = $row->dhits;
            $lists[$j]['cid']        = $row->cid;
            if(!empty($zdarr)){
                foreach ($zdarr as $k => $v) {
                    $lists[$j][$v]   = isset($row->$v) ? $row->$v : '';
                }
            }
			$j++;
		}
		getjson($lists,0,1,$callback); 
	}

    //收藏歌曲
	public function fav_add(){
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
        header("Cache-Control: no-cache, must-revalidate"); 
        header("Pragma: no-cache");
	    if(!$this->Csuser->User_Login(1)){
            $str='login';
		}
        $id=intval($this->input->get_post('id',TRUE));
	    $callback = $this->input->get('callback',true);
		$fav['did']=$id;
		if($id>0){
            $row=$this->db->query("SELECT name,cid FROM ".CS_SqlPrefix."dance where id='".$id)->row(); 
            if(!$row){
                    $str=L('dance_10'); 
            }else{
                    $fav['name']=$row->name;
                    $fav['cid']=$row->cid;
                    //判断是否已经收藏
                    $rowv=$this->db->query("SELECT id FROM ".CS_SqlPrefix."dance_fav where sid=1 and did='".$id."' and uid=".$_SESSION['cscms__id']."")->row();  
                    if($rowv){
				          //删除收藏
                          $this->db->query("delete from ".CS_SqlPrefix."dance_fav where id='".$rowv->id."'");
	                      $this->db->query("update ".CS_SqlPrefix."dance set shits=shits-1,dhits=dhits-1 where id='".$id."'");
					      $str='del';
					}else{

                         $fav['uid']=$_SESSION['cscms__id'];
                         $fav['addtime']=time();
                         $res=$this->Csdb->get_insert('dance_fav',$fav);
                         if($res>0){
                                //增加收藏人气
	                            $this->db->query("update ".CS_SqlPrefix."dance set shits=shits+1,dhits=dhits+1 where id='".$id."'");
                                $str='ok'; 
						 }else{
                                $str=L('dance_21'); 
						 }
					}
			}
		}
		$arr['str'] = $str;
		getjson($arr,0,1,$callback);
	}

    //时长格式化
    function get_time($sc) {
        if(empty($sc)){
            return '00:00';
        }else{
            $strss=explode(":",$sc);
            if(count($strss)>2){
                if($strss[0]=='00'){
                    $sc=$strss[1].':'.$strss[2];
                }else{
                    $s=$strss[0];
                    $f=$strss[1];
                    if(substr($s,0,1)=='0'){
                       $s=substr($s,1);
                    }
                    if(substr($f,0,1)=='0'){
                       $f=substr($f,1);
                    }
                    $f=$s*60+$f;
                    if($f<10) $f="0".$f;
                    $sc=$f.':'.$strss[2];
                }
            }elseif(count($strss)>1){
                $f=$strss[0];
                if(substr($f,0,1)=='0'){
                    $f=substr($f,1);
                }
                if($f<10) $f="0".$f;
                $sc=$f.':'.$strss[1];
            }
        }
        return $sc;
    }
}