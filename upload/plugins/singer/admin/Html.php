<?php if ( ! defined('IS_ADMIN')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-21
 */
class Html extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
        $this->Csadmin->Admin_Login();
	    $this->load->model('Cstpl');
        //判断运行模式
        if(config('Web_Mode')!=3){
            admin_info('板块浏览模式非静态，不需要生成静态文件~!');
		}
		$this->huri=config('Html_Uri');
	}

    //首页生成
	public function index(){
        if($this->huri['index']['check']==0){
			admin_info('板块主页未开启生成~!');
		}
		$this->load->view('html.html');
	}

    //首页生成操作
	public function index_save(){
		$ac = $this->input->post('ac',true);
		define('IS_HTML',true);
		if($ac!='pc' && !defined('MOBILE') && config('Mobile_Is')==1){
			define('MOBILE', true);	
		}
		//重新定义模板路径
		$this->load->get_templates();

        //获取静态路径
		$uri = config('Html_Uri');
		$index_url = adminhtml($uri['index']['url']);
		//手机版
        if(defined('MOBILE')) $index_url = Html_Wap_Dir.'/'.$index_url;

		if(!defined('MOBILE')){
			$dir = 'pc'.FGF.'skins'.FGF.str_replace('/', FGF, Pc_Skins_Dir).PLUBPATH.FGF;
		}else{
			$dir = 'mobile'.FGF.'skins'.FGF.str_replace('/', FGF, Mobile_Skins_Dir).PLUBPATH.FGF;
		}
		if(!file_exists(VIEWPATH.$dir.'index.html')){
			getjson('板块主页模版index.html不存在~!');
		}

	    $Mark_Text = $this->Cstpl->plub_index('singer','index.html',true);
		write_file(FCPATH.$index_url,$Mark_Text);

	    $info['msg'] = '恭喜你，板块主页已生成';
		$info['time'] = 2000;
	    $info['url'] = site_url('singer/admin/html').'?v='.rand(1000,1999);
		getjson($info,0);
	}

    //列表页
	public function type(){
        $this->load->view('html_type.html');
	}

    //列表页生成操作
	public function type_save(){
        if($this->huri['lists']['check']==0){
			$info['url'] = site_url('singer/admin/html/type').'?v='.rand(100,999);
        	$info['msg'] = '歌手分类未开启生成~!';
			admin_info($info,2);
		}

		$ac = $this->input->get_post('ac',true);
		define('IS_HTML',true);
		if($ac!='pc' && !defined('MOBILE') && config('Mobile_Is')==1){
			define('MOBILE', true);	
		}
		//重新定义模板路径
		$this->load->get_templates();

        $op  = $this->input->get_post('op',true); //方式
        $cid = $this->input->get_post('cid',true); //需要生成的分类ID
        $fid = $this->input->get_post('fid',true); //需要生成的排序方式
        $nums= intval($this->input->get('nums')); //分类已完成数量
        $numx= intval($this->input->get('numx')); //排序方式已完成数量
        $start= intval($this->input->get('start')); //当前页生成编号
        $pagesize= intval($this->input->get('pagesize')); //每页多少条
        $pagejs= intval($this->input->get('pagejs')); //总页数
        $datacount= intval($this->input->get('datacount')); //数据总数
        $page= intval($this->input->get('page')); //当前页
		if($start==0) $start=1;
		if(empty($fid)){
			$fid=($op=='all')?'id,news,reco,hits,yhits,zhits,rhits':'id';
		}

        //生成全部分类获取全部分类ID
        if($op=='all' && $nums==0){
			   $cid=array();
               $query = $this->db->query("SELECT id FROM ".CS_SqlPrefix."singer_list where yid=0 order by xid asc"); 
               foreach ($query->result() as $rowc) {
                   $cid[]=$rowc->id;
               }
		}
        //将数组转换成字符
		if(is_array($cid)){
		     $cid=implode(',', $cid);
		}
		if(is_array($fid)){
		     $fid=implode(',', $fid);
		}
		//没有选择分类
		if(empty($cid)){
            $info['msg'] = '请选择要生成的分类~!';
			$info['url'] = site_url('singer/admin/html/type').'?v='.rand(100,999);
            admin_info($info,2);
		}

        //公众URI
		$uri='?ac='.$ac.'&op='.$op.'&cid='.$cid.'&fid='.$fid.'&pagesize='.$pagesize.'&pagejs='.$pagejs.'&datacount='.$datacount;

        //分割分类ID
        $arr = explode(',',$cid);
        $len = count($arr);
        //分割排序方式
        $arr2 = explode(',',$fid);
        $len2 = count($arr2);

        //全部生成完毕
        if($nums>=$len){
            $info['msg'] = '所有分类全部生成完毕~!';
			$info['url'] = site_url('singer/admin/html/type').'?v='.rand(100,999);
            admin_info($info,1);
		}

		$id = (int)$arr[$nums]; //当前分类ID
		$type = $arr2[$numx]; //当前排序

		//获取分类信息
		$row = $this->Csdb->get_row_arr('singer_list','*',$id);
        $tpl = !empty($row['skins']) ? $row['skins'] : 'list.html';
		//获取子分类
		$ids = getChild($id); //获取子分类

        if($datacount==0){
			if(!defined('MOBILE')){
	        	$pathfile = VIEWPATH.'pc'.FGF.'skins'.FGF.str_replace('/', FGF, Pc_Skins_Dir).PLUBPATH.FGF.$tpl;
	        }else{
	        	$pathfile = VIEWPATH.'mobile'.FGF.'skins'.FGF.str_replace('/', FGF, Mobile_Skins_Dir).PLUBPATH.FGF.$tpl;
	        }
			if(!file_exists($pathfile)){
        		admin_info($tpl.'模板文件不存在~！');
			}
			$template = file_get_contents($pathfile);
			$pagesize = (int)str_substr('pagesize="','"',$template);
			if($pagesize==0) $pagesize = 10;

			if(is_numeric($ids)){
				$sqlstr="select id from ".CS_SqlPrefix."singer where cid=".$ids;
			}else{
				$sqlstr="select id from ".CS_SqlPrefix."singer where cid IN (".$ids.")";
			}
			$datacount = $this->Csdb->get_allnums($sqlstr); //总数量
			$pagejs = ceil($datacount/$pagesize);
		}
        if($datacount==0) $pagejs=1;


        echo '<link href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><script src="'.base_url().'packs/js/jquery.min.js"></script>';

		echo '<b style="font-size:14px;">正在开始生成分类<font color=red> '.$row["name"].' </font>按<font color=red> '.$this->gettype($type).' </font>排序的列表,分<font color=red>'.ceil($pagejs/Html_PageNum).'</font>次生成，当前第<font color=red>'.ceil($start/Html_PageNum).'</font>次</b><br/><br/>';
		ob_flush();flush();
        $n=1;
        $pagego=1;
        if($page>0 && $page<$pagejs){
	        $pagejs=$page;
        }

        for($i=$start;$i<=$pagejs;$i++){
               
			//获取静态路径
			$Htmllinks = LinkUrl('lists',$type,$id,$i,'singer',$row['bname']);
			//转换成生成路径
			$Htmllink = adminhtml($Htmllinks,'singer');

			$arri['cid'] = $ids;
			$arri['fid'] = $row['fid']==0 ? $row['id'] : $row['fid'];

			$Mark_Text=$this->Cstpl->plub_list($row,$id,$type,$i,$arri,true,$tpl,'lists','singer',$row['name'],$row['name']);

			write_file(FCPATH.$Htmllink,$Mark_Text);
			echo "<font color=blue >第".$i."页：</font><a target='_blank' href='".$Htmllinks."'>".$Htmllinks."</a><font color=green>&nbsp;&nbsp;生成完毕!</font><br/><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
			ob_flush();flush();
			$n++;
			if(Html_PageNum==$n){
			   $pagego=2;
			   break;
			}
		}

		if($pagego==2){ //操作系统设置每页数量，分多页生成
			  $url=site_url('singer/admin/html/type_save').$uri.'&nums='.$nums.'&numx='.$numx.'&start='.($i+1);
		      exit("</br><b style=\"font-size:14px;\">暂停".Html_StopTime."秒后继续下一页&nbsp;>>>>&nbsp;&nbsp;<a target='_blank' href='".$url."'>如果您的 浏览器没有跳转，请点击继续...</a></b><script>setTimeout('updatenext();',".Html_StopTime."000);function updatenext(){location.href='".$url."';}</script>");
		}else{  
	          $url=site_url('singer/admin/html/type_save').$uri.'&nums='.($nums+1);
		      $str="<b style=\"font-size:14px;\">暂停".Html_StopTime."秒后继续&nbsp;>>>>&nbsp;&nbsp;<a target='_blank' href='".$url."'>如果您的 浏览器没有跳转，请点击继续...</a></b>";
        }

        //判断生成完毕
		if(($numx+1)>=$len2){ //全部完成
            $url=site_url('singer/admin/html/type_save').$uri.'&nums='.($nums+1);
			$str="<b style=\"font-size:14px;\"><font color=#0000ff>所有排序页面全部生成完毕!</font>&nbsp;>>>>&nbsp;&nbsp;<a href='".site_url('singer/admin/html/type_save').$uri.'&nums='.($nums+1)."'>如果您的 浏览器没有跳转，请点击继续...</a></b>";
		}else{ //当前排序方式完成
            $url=site_url('singer/admin/html/type_save').$uri.'&nums='.$nums.'&numx='.($numx+1);
			$str='<b style=\"font-size:14px;\">按<font color=#0000ff>'.$this->gettype($type).'排序</font>分类生成完毕!&nbsp;>>>>&nbsp;&nbsp;<a href="'.site_url('singer/admin/html/type_save').$uri.'&nums='.$nums.'&numx='.($numx+1).'">如果您的 浏览器没有跳转，请点击继续...</a></b>';
		}
		echo("</br>".$str."<script>setTimeout('updatenext();',".Html_StopTime."000);function updatenext(){location.href='".$url."';}</script><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>");
	}

    //内容页
	public function show(){
        $this->load->view('html_show.html');
	}

    //内容页生成操作
	public function show_save(){
        if($this->huri['show']['check']==0){
			$info['msg'] = '歌手内容页未开启生成~!';
			$info['url'] = site_url('singer/admin/html/show').'?v='.rand(100,999);
            admin_info($info,2);
		}

		$ac = $this->input->get_post('ac',true);
		define('IS_HTML',true);
		if($ac!='pc' && !defined('MOBILE') && config('Mobile_Is')==1){
			define('MOBILE', true);	
		}
		//重新定义模板路径
		$this->load->get_templates();

        $ids = $this->input->get_post('ids',true); //需要生成的数据ID
        $day = intval($this->input->get_post('day',true)); //最近几天
        $fid = $this->input->get_post('fid',true); //需要生成的类别
        $cid = $this->input->get_post('cid',true); //需要生成的分类ID
        $newid= intval($this->input->get_post('newid')); //最新个数
        $ksid= intval($this->input->get_post('ksid')); //开始ID
        $jsid= intval($this->input->get_post('jsid')); //结束ID
        $kstime= $this->input->get_post('kstime',true); //开始日期
        $jstime= $this->input->get_post('jstime',true); //结束日期
        $pagesize= intval($this->input->get('pagesize')); //每页多少条
        $pagejs= intval($this->input->get('pagejs')); //总页数
        $datacount= intval($this->input->get('datacount')); //数据总数
        $page= intval($this->input->get('page')); //当前页
		if($page==0) $page=1;
		if(empty($fid)){
			$fid='show,info,music,pic,mv,album';
		}
		$str='';

        //将数组转换成字符
		if(is_array($cid)){
		     $cid=implode(',', $cid);
		}
		if(is_array($fid)){
		     $fid=implode(',', $fid);
		}
		if(is_array($ids)){
		     $ids=implode(',', $ids);
		}
        if($day>0){
			 $times=time()-86400*$day;
             $str.=' and addtime>'.$times.'';
		}
        if(!empty($cid)){
        	if(is_numeric($cid)){
             	$str.=' and cid='.$cid;
        	}else{
             	$str.=' and cid in ('.$cid.')';
        	}
		}
        if(!empty($ids)){
        	if(is_numeric($ids)){
             	$str.=' and id='.$ids;
        	}else{
             	$str.=' and id in ('.$ids.')';
        	}
		}
        if($ksid>0 && $jsid>0){
             $str.=' and id>'.($ksid-1).' and id<'.($jsid+1).'';
		}
        if(!empty($kstime) && !empty($jstime)){
			 $ktime=strtotime($kstime)-86400;
			 $jtime=strtotime($jstime)+86400;
             $str.=' and addtime>'.$ktime.' and addtime<'.$jtime.'';
		}

		$limit='';
        if($newid>0){
             $limit=' order by id desc limit '.$newid;
		}
        if($datacount==0){
			$sqlstr="select id from ".CS_SqlPrefix."singer where 1=1 ".$str.$limit;
            $datacount=$this->Csdb->get_allnums($sqlstr); //总数量
	        $pagejs = ceil($datacount/Html_PageNum);
		}
        if($datacount==0) $pagejs=1;

        $pagesize=Html_PageNum;
        if($datacount<$pagesize){
	        $pagesize=$datacount;
        }

        //全部生成完毕
        if($page>$pagejs){
            $info['msg'] = '所有歌手内容页全部生成完毕~!';
			$info['url'] = site_url('singer/admin/html/show').'?v='.rand(100,999);
            admin_info($info,1);
		}

        //公众URI
		$uri='?ac='.$ac.'&day='.$day.'&cid='.$cid.'&ids='.$ids.'&fid='.$fid.'&newid='.$newid.'&ksid='.$ksid.'&jsid='.$jsid.'&kstime='.$kstime.'&jstime='.$jstime.'&pagesize='.$pagesize.'&pagejs='.$pagejs.'&datacount='.$datacount;

       echo '<link href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><script src="'.base_url().'packs/js/jquery.min.js"></script>';
		echo '<b style="font-size:14px;">正在开始生成歌手内容,分<font color=red>'.$pagejs.'</font>次生成，当前第<font color=red>'.$page.'</font>次</b><br/>';


		if(!defined('MOBILE')){
        	$pathfile = VIEWPATH.'pc'.FGF.'skins'.FGF.str_replace('/', FGF, Pc_Skins_Dir).PLUBPATH.FGF;
        }else{
        	$pathfile = VIEWPATH.'mobile'.FGF.'skins'.FGF.str_replace('/', FGF, Mobile_Skins_Dir).PLUBPATH.FGF;
        }

        $sql_string="select * from ".CS_SqlPrefix."singer where 1=1 ".$str." order by id desc";
        $sql_string.=' limit '. $pagesize*($page-1) .','. $pagesize;
		$query = $this->db->query($sql_string);
        foreach ($query->result_array() as $row) {

			echo "<b style=\"font-size:12px\"><font style=font-size:10pt;>生成歌手:<font color=red>《".$row['name']."》</font></b><br/>";
			ob_flush();flush();
			//歌手主页
			if(strpos($fid,'show') !== FALSE && $this->huri['show']['check']==1 && file_exists($pathfile.'show.html')){
				$this->shows($row);
			}
			//歌手资料页
			if(strpos($fid,'info') !== FALSE && $this->huri['info']['check']==1 && file_exists($pathfile.'info.html')){
			    $this->info($row);
			}
			//歌手歌曲页
			if(strpos($fid,'music') !== FALSE && $this->huri['music']['check']==1 && file_exists($pathfile.'music.html')){
			    $this->music($row);
			}
			//歌手图片页
			if(strpos($fid,'pic') !== FALSE && $this->huri['pic']['check']==1 && file_exists($pathfile.'pic.html')){
			    $this->pic($row);
			}
			//歌手视频页
			if(strpos($fid,'mv') !== FALSE && $this->huri['mv']['check']==1 && file_exists($pathfile.'mv.html')){
			    $this->mv($row);
			}
			//歌手专辑页
			if(strpos($fid,'album') !== FALSE && $this->huri['album']['check']==1 && file_exists($pathfile.'album.html')){
			    $this->album($row);
			}
		}
        if(!empty($ids)){
            $url='javascript:history.back();';
		    $str="<b style=\"font-size:14px;\">全部生成完毕~!&nbsp;>>>>&nbsp;&nbsp;<a href='".$url."'>如果您的 浏览器没有跳转，请点击继续...</a></b>";
		}else{ 
	        $url=site_url('singer/admin/html/show_save').$uri.'&page='.($page+1);
		    $str="<b style=\"font-size:14px;\">暂停".Html_StopTime."秒后继续&nbsp;>>>>&nbsp;&nbsp;<a href='".$url."'>如果您的 浏览器没有跳转，请点击继续...</a></b>";
		}
		echo("</br>".$str."<script>setTimeout('updatenext();',".Html_StopTime."000);function updatenext(){location.href='".$url."';}</script><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>");
	}
    //歌手主页
	public function shows($row){
	    if($row){
			$id=$row['id'];
			//获取静态路径
			$Htmllinks = LinkUrl('show','',$row['id'],0,'singer');
			//转换成生成路径
			$Htmllink = adminhtml($Htmllinks,'singer');


			$arr['cid'] = getChild($row['cid']);
			$arr['tags']=$row['tags'];
			$arr['singerid']=$id;

			$zdy['[singer:tags]'] = tagslink($row['tags']);
			$zdy['[singer:pl]'] = get_pl('singer',$id);
			$zdy['[singer:link]'] = LinkUrl('show','id',$row['id'],1,'singer');
			$zdy['[singer:classlink]'] = LinkUrl('lists','id',$row['cid'],1,'singer');
			$zdy['[singer:classname]'] = getzd('singer_list','name',$row['cid']);
			//动态人气
			$zdy['[singer:hits]'] = "<script src='".hitslink('hits/dt/hits/'.$id,'singer')."'></script>";
			$zdy['[singer:yhits]'] = "<script src='".hitslink('hits/dt/yhits/'.$id,'singer')."'></script>";
			$zdy['[singer:zhits]'] = "<script src='".hitslink('hits/dt/zhits/'.$id,'singer')."'></script>";
			$zdy['[singer:rhits]'] = "<script src='".hitslink('hits/dt/rhits/'.$id,'singer')."'></script>";
			//摧毁动态人气字段
			unset($row['tags']);
			unset($row['hits']);
			unset($row['yhits']);
			unset($row['zhits']);
			unset($row['rhits']);
			$hitslink = hitslink('hits/ids/'.$id,'singer');
			//装载模板并输出
	        $Mark_Text = $this->Cstpl->plub_show('singer',$row,$arr,true,'show.html',$row['name'],$row['name'],'','',$zdy,$hitslink);

			//生成
			write_file(FCPATH.$Htmllink,$Mark_Text);
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font style=font-size:10pt;color:#006600>生成主页完成:<a href=".$Htmllinks." target=_blank>".$Htmllinks."</a></font><br/>";
			echo("<script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>");
			ob_flush();flush();
		}
	}

    //歌手资料
	public function info($row){ 
	    if($row){
			$id=$row['id'];
			//获取静态路径
			$Htmllinks = LinkUrl('info','',$row['id'],0,'singer');
			//转换成生成路径
			$Htmllink = adminhtml($Htmllinks,'singer');


			$arr['cid'] = getChild($row['cid']);
			$arr['tags']=$row['tags'];
			$arr['singerid']=$id;

			$zdy['[singer:tags]'] = tagslink($row['tags']);
			$zdy['[singer:pl]'] = get_pl('singer',$id);
			$zdy['[singer:link]'] = LinkUrl('show','id',$row['id'],1,'singer');
			$zdy['[singer:classlink]'] = LinkUrl('lists','id',$row['cid'],1,'singer');
			$zdy['[singer:classname]'] = getzd('singer_list','name',$row['cid']);
			//动态人气
			$zdy['[singer:hits]'] = "<script src='".hitslink('hits/dt/hits/'.$id,'singer')."'></script>";
			$zdy['[singer:yhits]'] = "<script src='".hitslink('hits/dt/yhits/'.$id,'singer')."'></script>";
			$zdy['[singer:zhits]'] = "<script src='".hitslink('hits/dt/zhits/'.$id,'singer')."'></script>";
			$zdy['[singer:rhits]'] = "<script src='".hitslink('hits/dt/rhits/'.$id,'singer')."'></script>";
			//摧毁动态人气字段
			unset($row['tags']);
			unset($row['hits']);
			unset($row['yhits']);
			unset($row['zhits']);
			unset($row['rhits']);

			//装载模板并输出
	        $Mark_Text = $this->Cstpl->plub_show('singer',$row,$arr,true,'info.html',$row['name'],$row['name'],'','',$zdy);
			//生成
			write_file(FCPATH.$Htmllink,$Mark_Text);
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font style=font-size:10pt;color:#006600>生成资料页完成:<a href=".$Htmllinks." target=_blank>".$Htmllinks."</a></font><br/>";
			echo("<script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>");
			ob_flush();flush();
		}
	}

    //歌手歌曲
	public function music($row){
	    if($row){
			$id=$row['id'];

			$tpl = 'music.html';
			if(!defined('MOBILE')){
	        	$pathfile = VIEWPATH.'pc'.FGF.'skins'.FGF.str_replace('/', FGF, Pc_Skins_Dir).PLUBPATH.FGF.$tpl;
	        }else{
	        	$pathfile = VIEWPATH.'mobile'.FGF.'skins'.FGF.str_replace('/', FGF, Mobile_Skins_Dir).PLUBPATH.FGF.$tpl;
	        }
			if(!file_exists($pathfile)){
        		admin_info($tpl.'模板文件不存在~！');
			}
			$template = file_get_contents($pathfile);
			$pagesize = (int)str_substr('pagesize="','"',$template);
			if($pagesize==0) $pagesize = 50;

			$sqlstr="select id from ".CS_SqlPrefix."dance where singerid=".$id;
			$datacount = $this->Csdb->get_allnums($sqlstr); //总数量
			$pagejs = ceil($datacount/$pagesize);
			if($pagejs==0) $pagejs=1;
			$rows = $row;

			for($i=1;$i<=$pagejs;$i++){

			   	$fids=array('id','hits');

				$arr['cid']=getChild($row['cid']);
				$arr['tags']=$rows['tags'];
				$arr['singerid']=$id;

				$zdy['[singer:tags]'] = tagslink($rows['tags']);
				$zdy['[singer:pl]'] = get_pl('singer',$id);
				$zdy['[singer:link]'] = LinkUrl('show','id',$row['id'],1,'singer');
			    $zdy['[singer:classlink]'] = LinkUrl('lists','id',$row['cid'],1,'singer');
				$zdy['[singer:classname]'] = getzd('singer_list','name',$row['cid']);
				//动态人气
				$zdy['[singer:hits]'] = "<script src='".hitslink('hits/dt/hits/'.$id,'singer')."'></script>";
				$zdy['[singer:yhits]'] = "<script src='".hitslink('hits/dt/yhits/'.$id,'singer')."'></script>";
				$zdy['[singer:zhits]'] = "<script src='".hitslink('hits/dt/zhits/'.$id,'singer')."'></script>";
				$zdy['[singer:rhits]'] = "<script src='".hitslink('hits/dt/rhits/'.$id,'singer')."'></script>";
				//摧毁动态人气字段
				unset($row['tags']);
				unset($row['hits']);
				unset($row['yhits']);
				unset($row['zhits']);
				unset($row['rhits']);

			    foreach ($fids as $fid) {

			        //获取静态路径
			        $Htmllinks = LinkUrl('music',$fid,$row['id'],$i,'singer');
			        //转换成生成路径
			        $Htmllink = adminhtml($Htmllinks,'singer');

					//装载模板并输出
					$Mark_Text = $this->Cstpl->plub_list($row, $id, $fid, $i, $arr, true, 'music.html', 'music', 'singer', $row['name'],$row['name'],'',$zdy);

				    //生成
				    write_file(FCPATH.$Htmllink,$Mark_Text);
				    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font style=font-size:10pt;color:#006600>生成歌曲页按《<font color=#0000ff>".$fid."</font>》排序,完成:<a href=".$Htmllinks." target=_blank>".$Htmllinks."</a></font><br/>";
					echo("<script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>");
			       ob_flush();flush();
			    }
			}
		}
	}

    //歌手图片
	public function pic($row){
	    if($row){
			$id=$row['id'];

			$tpl = 'pic.html';
			if(!defined('MOBILE')){
	        	$pathfile = VIEWPATH.'pc'.FGF.'skins'.FGF.str_replace('/', FGF, Pc_Skins_Dir).PLUBPATH.FGF.$tpl;
	        }else{
	        	$pathfile = VIEWPATH.'mobile'.FGF.'skins'.FGF.str_replace('/', FGF, Mobile_Skins_Dir).PLUBPATH.FGF.$tpl;
	        }
			if(!file_exists($pathfile)){
        		admin_info($tpl.'模板文件不存在~！');
			}
			$template = file_get_contents($pathfile);
			$pagesize = (int)str_substr('pagesize="','"',$template);
			if($pagesize==0) $pagesize = 50;

			$sqlstr="select id from ".CS_SqlPrefix."pic_type where singerid=".$id;
			$datacount = $this->Csdb->get_allnums($sqlstr); //总数量
			$pagejs = ceil($datacount/$pagesize);
			if($pagejs==0) $pagejs=1;
			$rows = $row;

			for($i=1;$i<=$pagejs;$i++){

			   	$fids=array('id','hits');

				$arr['cid']=getChild($row['cid']);
				$arr['tags']=$rows['tags'];
				$arr['singerid']=$id;

				$zdy['[singer:tags]'] = tagslink($rows['tags']);
				$zdy['[singer:pl]'] = get_pl('singer',$id);
				$zdy['[singer:link]'] = LinkUrl('show','id',$row['id'],1,'singer');
			    $zdy['[singer:classlink]'] = LinkUrl('lists','id',$row['cid'],1,'singer');
				$zdy['[singer:classname]'] = getzd('singer_list','name',$row['cid']);
				//动态人气
				$zdy['[singer:hits]'] = "<script src='".hitslink('hits/dt/hits/'.$id,'singer')."'></script>";
				$zdy['[singer:yhits]'] = "<script src='".hitslink('hits/dt/yhits/'.$id,'singer')."'></script>";
				$zdy['[singer:zhits]'] = "<script src='".hitslink('hits/dt/zhits/'.$id,'singer')."'></script>";
				$zdy['[singer:rhits]'] = "<script src='".hitslink('hits/dt/rhits/'.$id,'singer')."'></script>";
				//摧毁动态人气字段
				unset($row['tags']);
				unset($row['hits']);
				unset($row['yhits']);
				unset($row['zhits']);
				unset($row['rhits']);

			    foreach ($fids as $fid) {

			        //获取静态路径
			        $Htmllinks = LinkUrl('pic',$fid,$row['id'],$i,'singer');
			        //转换成生成路径
			        $Htmllink = adminhtml($Htmllinks,'singer');

					//装载模板并输出
					$Mark_Text = $this->Cstpl->plub_list($row, $id, $fid, $i, $arr, true, 'pic.html', 'pic', 'singer', $row['name'],$row['name'],'',$zdy);

				    //生成
				    write_file(FCPATH.$Htmllink,$Mark_Text);
				    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font style=font-size:10pt;color:#006600>生成相册页按《<font color=#0000ff>".$fid."</font>》排序,完成:<a href=".$Htmllinks." target=_blank>".$Htmllinks."</a></font><br/>";
					echo("<script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>");
			       ob_flush();flush();
			    }
			}
		}
	}

    //歌手视频
	public function mv($row){
	    if($row){
			$id=$row['id'];

			$tpl = 'mv.html';
			if(!defined('MOBILE')){
	        	$pathfile = VIEWPATH.'pc'.FGF.'skins'.FGF.str_replace('/', FGF, Pc_Skins_Dir).PLUBPATH.FGF.$tpl;
	        }else{
	        	$pathfile = VIEWPATH.'mobile'.FGF.'skins'.FGF.str_replace('/', FGF, Mobile_Skins_Dir).PLUBPATH.FGF.$tpl;
	        }
			if(!file_exists($pathfile)){
        		admin_info($tpl.'模板文件不存在~！');
			}
			$template = file_get_contents($pathfile);
			$pagesize = (int)str_substr('pagesize="','"',$template);
			if($pagesize==0) $pagesize = 50;

			$sqlstr="select id from ".CS_SqlPrefix."vod where singerid=".$id;
			$datacount = $this->Csdb->get_allnums($sqlstr); //总数量
			$pagejs = ceil($datacount/$pagesize);
			if($pagejs==0) $pagejs=1;
			$rows = $row;

			for($i=1;$i<=$pagejs;$i++){

			   	$fids=array('id','hits');

				$arr['cid']=getChild($row['cid']);
				$arr['tags']=$rows['tags'];
				$arr['singerid']=$id;

				$zdy['[singer:tags]'] = tagslink($rows['tags']);
				$zdy['[singer:pl]'] = get_pl('singer',$id);
				$zdy['[singer:link]'] = LinkUrl('show','id',$row['id'],1,'singer');
			    $zdy['[singer:classlink]'] = LinkUrl('lists','id',$row['cid'],1,'singer');
				$zdy['[singer:classname]'] = getzd('singer_list','name',$row['cid']);
				//动态人气
				$zdy['[singer:hits]'] = "<script src='".hitslink('hits/dt/hits/'.$id,'singer')."'></script>";
				$zdy['[singer:yhits]'] = "<script src='".hitslink('hits/dt/yhits/'.$id,'singer')."'></script>";
				$zdy['[singer:zhits]'] = "<script src='".hitslink('hits/dt/zhits/'.$id,'singer')."'></script>";
				$zdy['[singer:rhits]'] = "<script src='".hitslink('hits/dt/rhits/'.$id,'singer')."'></script>";
				//摧毁动态人气字段
				unset($row['tags']);
				unset($row['hits']);
				unset($row['yhits']);
				unset($row['zhits']);
				unset($row['rhits']);

			    foreach ($fids as $fid) {

			        //获取静态路径
			        $Htmllinks = LinkUrl('mv',$fid,$row['id'],$i,'singer');
			        //转换成生成路径
			        $Htmllink = adminhtml($Htmllinks,'singer');

					//装载模板并输出
					$Mark_Text = $this->Cstpl->plub_list($row, $id, $fid, $i, $arr, true, 'mv.html', 'mv', 'singer', $row['name'],$row['name'],'',$zdy);

				    //生成
				    write_file(FCPATH.$Htmllink,$Mark_Text);
				    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font style=font-size:10pt;color:#006600>生成视频MV页按《<font color=#0000ff>".$fid."</font>》排序,完成:<a href=".$Htmllinks." target=_blank>".$Htmllinks."</a></font><br/>";
					echo("<script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>");
			       ob_flush();flush();
			    }
			}
		}
	}

    //歌手专辑
	public function album($row){
	    if($row){
			$id=$row['id'];

			$tpl = 'album.html';
			if(!defined('MOBILE')){
	        	$pathfile = VIEWPATH.'pc'.FGF.'skins'.FGF.str_replace('/', FGF, Pc_Skins_Dir).PLUBPATH.FGF.$tpl;
	        }else{
	        	$pathfile = VIEWPATH.'mobile'.FGF.'skins'.FGF.str_replace('/', FGF, Mobile_Skins_Dir).PLUBPATH.FGF.$tpl;
	        }
			if(!file_exists($pathfile)){
        		admin_info($tpl.'模板文件不存在~！');
			}
			$template = file_get_contents($pathfile);
			$pagesize = (int)str_substr('pagesize="','"',$template);
			if($pagesize==0) $pagesize = 50;

			$sqlstr="select id from ".CS_SqlPrefix."dance_topic where yid=0 and singerid=".$id;
			$datacount = $this->Csdb->get_allnums($sqlstr); //总数量
			$pagejs = ceil($datacount/$pagesize);
			if($pagejs==0) $pagejs=1;
			$rows = $row;

			for($i=1;$i<=$pagejs;$i++){

			   	$fids=array('id','hits');

				$arr['cid']=getChild($row['cid']);
				$arr['tags']=$rows['tags'];
				$arr['singerid']=$id;

				$zdy['[singer:tags]'] = tagslink($rows['tags']);
				$zdy['[singer:pl]'] = get_pl('singer',$id);
				$zdy['[singer:link]'] = LinkUrl('show','id',$row['id'],1,'singer');
			    $zdy['[singer:classlink]'] = LinkUrl('lists','id',$row['cid'],1,'singer');
				$zdy['[singer:classname]'] = getzd('singer_list','name',$row['cid']);
				//动态人气
				$zdy['[singer:hits]'] = "<script src='".hitslink('hits/dt/hits/'.$id,'singer')."'></script>";
				$zdy['[singer:yhits]'] = "<script src='".hitslink('hits/dt/yhits/'.$id,'singer')."'></script>";
				$zdy['[singer:zhits]'] = "<script src='".hitslink('hits/dt/zhits/'.$id,'singer')."'></script>";
				$zdy['[singer:rhits]'] = "<script src='".hitslink('hits/dt/rhits/'.$id,'singer')."'></script>";
				//摧毁动态人气字段
				unset($row['tags']);
				unset($row['hits']);
				unset($row['yhits']);
				unset($row['zhits']);
				unset($row['rhits']);

			    foreach ($fids as $fid) {

			        //获取静态路径
			        $Htmllinks = LinkUrl('album',$fid,$row['id'],$i,'singer');
			        //转换成生成路径
			        $Htmllink = adminhtml($Htmllinks,'singer');

					//装载模板并输出
					$Mark_Text = $this->Cstpl->plub_list($row, $id, $fid, $i, $arr, true, 'album.html', 'album', 'singer', $row['name'],$row['name'],'',$zdy);

				    //生成
				    write_file(FCPATH.$Htmllink,$Mark_Text);
				    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font style=font-size:10pt;color:#006600>生成专辑页按《<font color=#0000ff>".$fid."</font>》排序,完成:<a href=".$Htmllinks." target=_blank>".$Htmllinks."</a></font><br/>";
					echo("<script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>");
			       ob_flush();flush();
			    }
			}
		}
	}

    //其他页
	public function opt(){
	    //获取自定义模板
		$this->load->helper('directory');
		$pc = VIEWPATH.'pc'.FGF.'skins'.FGF.str_replace('/', FGF, Pc_Skins_Dir).PLUBPATH.FGF;
        $wap = VIEWPATH.'mobile'.FGF.'skins'.FGF.str_replace('/', FGF, Mobile_Skins_Dir).PLUBPATH.FGF;
        $dir_arr=directory_map($pc, 1);
        $skinpc=array();
	    if ($dir_arr) {
		    foreach ($dir_arr as $t) {
			    if (!is_dir($pc.$t)) {
					if(substr($t,0,4)=='opt-'){
				        $skinpc[] = $t;
					}
			    }
		    }
	    }
        $dir_arr=directory_map($wap, 1);
        $skinwap=array();
	    if ($dir_arr) {
		    foreach ($dir_arr as $t) {
			    if (!is_dir($wap.$t)) {
					if(substr($t,0,4)=='opt-'){
				        $skinwap[] = $t;
					}
			    }
		    }
	    }
        $data['skins_pc'] = $skinpc;
        $data['skins_wap'] = $skinwap;
        $this->load->view('html_opt.html',$data);
	}

    //其他页生成操作
	public function opt_save(){

		$ac = $this->input->get_post('ac',true);
		define('IS_HTML',true);
		if($ac!='pc' && !defined('MOBILE') && config('Mobile_Is')==1){
			define('MOBILE', true);	
		}	
		//重新定义模板路径
		$this->load->get_templates();

        $path = $this->input->post('path',true);
        echo '<link href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><script src="'.base_url().'packs/js/jquery.min.js"></script>';
        if(!empty($path[0])){
			foreach ($path as $t) {
				$t=str_replace(".html","",$t);
				$t=str_replace("opt-","",$t);
				$Mark_Text=$this->Cstpl->opt($t,true);

				if(!defined('MOBILE')){
		        	$Htmllink = Web_Path.'opt/'.$t.'singer.html';
		        }else{
		        	$Htmllink = Web_Path.Html_Wap_Dir.'/opt/'.$t.'singer.html';
		        }

				//生成
				write_file(FCPATH.$Htmllink,$Mark_Text);
				echo "<font color=blue>生成自定义页面：<a style=\"color:red\" href=".$Htmllink." target=_blank>".$Htmllink."</a><font color=green>&nbsp;&nbsp;成功！</font></font><br/><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
				ob_flush();flush();
			}
		}
	    $info['url'] = site_url('singer/admin/html/opt');
	    $info['msg'] = '自定义页面全部生成完毕~!';
	    admin_info($info,1);
	}

    //获取排序名称
	public function gettype($fid){
	    $str="ID";
        switch($fid){
               case 'id':$str="ID";break;
               case 'news':$str="更新时间";break;
               case 'reco':$str="最新推荐";break;
               case 'hits':$str="总人气";break;
               case 'yhits':$str="月人气";break;
               case 'zhits':$str="周人气";break;
               case 'rhits':$str="日人气";break;
               case 'yue':$str="月人气";break;
               case 'zhou':$str="周人气";break;
               case 'ri':$str="日人气";break;
		}
		return $str;
	}
}
