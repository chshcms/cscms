<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-01-25
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Down extends Cscms_Controller {
	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	}

    //歌曲下载
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
        if(config('Web_Mode')==3 && $html['down']['check']==1){
            //获取静态路径
			$Htmllink=LinkUrl('play','id',$id,0,'dance');
			header("Location: ".$Htmllink);
			exit;
		}

		//获取当前分类下二级分类ID
		$arr['cid']=getChild($row['cid']);
		$arr['uid']=$row['uid'];
		$arr['singerid']=$row['singerid'];
		$arr['tags']=$row['tags'];

		//标签加超级连接
		$zdy['[dance:tags]'] = tagslink($row['tags']);
		unset($row['tags']);
		//评论
		$zdy['[dance:pl]'] = get_pl('dance',$id);
		//当前地址
		$zdy['[dance:link]'] = LinkUrl('play','id',$row['id'],1,'dance');
		//分类地址、名称
		$zdy['[dance:classlink]'] = LinkUrl('lists','id',$row['cid'],1,'dance');
		$zdy['[dance:classname]'] = getzd('dance_list','name',$row['cid']);
		//专辑
		if($row['tid']==0){
		    $zdy['[dance:topiclink]'] = '###';
		    $zdy['[dance:topicname]'] = '未加入';
		}else{
		    $zdy['[dance:topiclink]'] = LinkUrl('topic','show',$row['tid'],1,'dance');
		    $zdy['[dance:topicname]'] = getzd('dance_topic','name',$row['tid']);
		}

		//获取模板，为了判断标签是否存在
		$skin = 'down.html';
		if(defined('MOBILE') && $Mobile_Is==1){
			$tplfile = VIEWPATH.'mobile'.FGF.'skins'.FGF.Mobile_Skins_Dir.FGF.PLUBPATH.FGF.$skin;
		}else{
			$tplfile = VIEWPATH.'pc'.FGF.'skins'.FGF.Pc_Skins_Dir.FGF.PLUBPATH.FGF.$skin;
		}
		$tplstr = file_exists($tplfile) ? file_get_contents($tplfile) : '';
        if(strpos($tplstr,'[dance:qurl]') !== false || strpos($tplstr,'[dance:qxurl]') !== false){
			 $purl=$row['purl'];
			 $durl=$row['durl'];
             if($row['fid']>0){
                  $rowf=$this->db->query("Select purl,durl from ".CS_SqlPrefix."dance_server where id=".$row['fid']."")->row_array();
				  if($rowf){
			           $purl=$rowf['purl'].$row['purl'];
			           $durl=$rowf['durl'].$row['durl'];
				  }
			 }
			 $zdy['[dance:qurl]'] = annexlink($purl);
			 $zdy['[dance:qxurl]'] = annexlink($durl);
		}
		//装载模板并输出
        $this->Cstpl->plub_show('dance',$row,$arr,false,'down.html',$row['name'],$row['name'],'','',$zdy);
	}

	//下载到电脑
	public function load($id = 0){
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
        header("Cache-Control: no-cache, must-revalidate"); 
        header("Pragma: no-cache");
	    $this->load->model('Csuser');
		$login='no';
        $id = (int)$id;   //ID
        //判断ID
        if($id==0) msg_url(L('dance_12'),Web_Path);
        //获取数据
	    $row=$this->Csdb->get_row_arr('dance','id,cid,name,durl,fid,uid,cion,vip,level',$id);
	    if(!$row) msg_url(L('dance_12'),Web_Path);
	    if(empty($row['durl'])) msg_url(L('dance_12'),Web_Path);
		$durl=$row['durl'];
        if($row['fid']>0){
            $rowf=$this->db->query("Select durl from ".CS_SqlPrefix."dance_server where id=".$row['fid']."")->row_array();
			if($rowf){
		         $durl=$rowf['durl'].$row['durl'];
			}
		}
		//自动补上完整路径
		$durl=annexlink($durl);
		if(substr($durl,0,7)!='http://' && substr($durl,0,8)!='https://'){  
		      $durl=is_ssl().Web_Url.Web_Path.$durl;
		}

		//判断收费
        if($row['vip']>0 || $row['level']>0 || $row['cion']>0 || User_YkDown==0){
              $this->Csuser->User_Login();
			  $rowu=$this->Csdb->get_row_arr('user','vip,zutime,level,cion,zid',$_SESSION['cscms__id']);
			  if($rowu['zutime']<time()){
					$this->db->query("update ".CS_SqlPrefix."user set zid=1,zutime=0 where id=".$_SESSION['cscms__id']."");
                    $rowu['zid']=1;
			  }
		}

        //判断会员组下载权限
		if($row['vip']>0 && $row['uid']!=$_SESSION['cscms__id'] && $rowu['vip']==0){
			  if($row['vip']>$rowu['zid']){
                   msg_url(L('dance_13'),'javascript:window.close();');
			  }
		}

        //判断会员等级下载权限
		if($row['level']>0 && $row['uid']!=$_SESSION['cscms__id']){
			  if($row['level']>$rowu['level']){
                   msg_url(L('dance_14'),'javascript:window.close();');
			  }
		}

        //判断金币下载
		$down=0;
		if($row['cion']>0 && $row['uid']!=$_SESSION['cscms__id']){

			//判断是否下载过
			$rowd=$this->db->query("SELECT id,addtime FROM ".CS_SqlPrefix."dance_down where did='".$id."' and uid='".$_SESSION['cscms__id']."'")->row_array();
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
					msg_url(vsprintf(L('dance_15'),array($row['cion'])),'javascript:window.close();');
				}else{
					//扣币
					$edit['cion']=$rowu['cion']-$row['cion'];
					$this->Csdb->get_update('user',$_SESSION['cscms__id'],$edit);
					//写入消费记录
					$add2['title']=L('dance_16').'《'.$row['name'].'》';
					$add2['uid']=$_SESSION['cscms__id'];
					$add2['dir']='dance';
					$add2['nums']=$row['cion'];
					$add2['ip']=getip();
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
							$add3['title']=vsprintf(L('dance_17'),array($row['name']));
							$add3['uid']=$row['uid'];
							$add3['dir']='dance';
							$add3['nums']=$scion;
							$add3['ip']=getip();
							$add3['addtime']=time();
							$this->Csdb->get_insert('income',$add3);
						}
					}
				}
			}
			//增加下载记录，只记录扣币歌曲
			if($down==0){
			   $add['name']=$row['name'];
			   $add['cid']=$row['cid'];
			   $add['did']=$id;
			   $add['ip']=getip();
			   $add['uid']=$_SESSION['cscms__id'];
			   $add['cion']= $row['cion'];
			   $add['addtime']=time();
			   $this->Csdb->get_insert('dance_down',$add);
			}else{
				//修改下载时间
				if($rowd){
					$this->db->query("update ".CS_SqlPrefix."dance_down set addtime=".time()." where id=".$rowd['id']."");
				}
			}
		}
		//同一数据24小时内只记录一次会员下载动态
        if(!empty($_SESSION['cscms__id'])){
		    $rowx=$this->db->query("SELECT id FROM ".CS_SqlPrefix."dt where link='".linkurl('play','id',$id,0,'dance')."' and uid=".$_SESSION['cscms__id']."")->row_array();
			if(!$rowx){
		        $dt['dir']='dance';
		        $dt['uid']=$_SESSION['cscms__id'];
		        $dt['did']=$id;
		        $dt['name']=$row['name'];
		        $dt['link']=linkurl('play','id',$id,0,'dance');
		        $dt['title']=L('dance_16');
		        $dt['addtime']=time();
		        $this->Csdb->get_insert('dt',$dt);
			}else{
		        //修改动态时间
		        $this->db->query("update ".CS_SqlPrefix."dt set addtime=".time()." where id=".$rowx['id']."");
			}
		}

		//增加下载人气
		$this->db->query("update ".CS_SqlPrefix."dance set xhits=xhits+1 where id=".$id."");

	    //------------------开始下载文件操作--------------------------------------
		//判断是否支持CURL
        if (! function_exists ( 'curl_init' )) { //不支持CURL
              header("Location: ".$durl);
        }else{

              //将中文编码下载
			  $durl = rawurlencode($durl);
			  $durl = str_replace('%3A',':',$durl);
			  $durl = str_replace('%2F','/',$durl);

			  //判断302跳转
		      $a_array = get_headers($durl,true);
		      if(strpos($a_array[0],'302') !== FALSE){  //302跳转
                  header("Location: ".$durl);
				  exit;
		      }
		      //文件大小
		      $filesize = $a_array['Content-Length'];
		      //后缀
		      $file_ext = strtolower(trim(substr(strrchr($durl, '.'), 1)));
		      //名称
		      $filename = $row['name'].'.'.$file_ext;
		      //大小
		      $fsize = sprintf("%u", $filesize);
		      //下载
		      $file_path = $durl;
              if(ob_get_length() !== false) @ob_end_clean();
              header('Pragma: public');
              header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
              header('Cache-Control: no-store, no-cache, must-revalidate');
              header('Cache-Control: pre-check=0, post-check=0, max-age=0');
              header('Content-Transfer-Encoding: binary');
              header('Content-Encoding: none');
              header('Content-type: application/force-download');
              header('Content-Disposition: attachment; filename="'.$filename.'"');
              header('Content-length: '.$filesize);
              $curl = curl_init();
              curl_setopt($curl, CURLOPT_URL, $file_path);
              curl_exec($curl);
              curl_close($curl);
        }
	}

	//下载歌词
	public function lrc($id = 0){
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
        header("Cache-Control: no-cache, must-revalidate"); 
        header("Pragma: no-cache");
	    $this->load->model('Csuser');
		$login='no';
        $id = (int)$id;   //ID
        //判断ID
        if($id==0) msg_url(L('dance_25'),Web_Path);
        //获取数据
	    $row=$this->Csdb->get_row_arr('dance','name,lrc',$id);
	    if(!$row) msg_url(L('dance_25'),Web_Path);
	    if(empty($row['lrc'])) msg_url(L('dance_25'),Web_Path);
	    $data = $row['lrc'];
	    $name = $row['name'].'.lrc';
		$this->load->helper('download');
	    force_download($name, $data);
	}
}



