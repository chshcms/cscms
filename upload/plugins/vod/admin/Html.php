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
	    $this->load->helper('vod');
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

		if(defined('MOBILE')){
			$dir = 'pc'.FGF.'skins'.FGF.str_replace('/', FGF, Pc_Skins_Dir).PLUBPATH.FGF;
		}else{
			$dir = 'mobile'.FGF.'skins'.FGF.str_replace('/', FGF, Mobile_Skins_Dir).PLUBPATH.FGF;
		}
		if(!file_exists(VIEWPATH.$dir.'index.html')){
			getjson('板块主页模版index.html不存在~!');
		}

	    $Mark_Text = $this->Cstpl->plub_index('vod','index.html',true);
		write_file(FCPATH.$index_url,$Mark_Text);

	    $info['msg'] = '恭喜你，板块主页已生成';
		$info['time'] = 2000;
	    $info['url'] = site_url('vod/admin/html').'?v='.rand(1000,1999);
		getjson($info,0);
	}

    //专题页
	public function topic(){
        $this->load->view('html_topic.html');
	}

    //专题列表页生成操作
	public function topic_save(){
	    if($this->huri['topic/lists']['check']==0){
			$info['url'] = site_url('news/admin/html/type').'?v='.rand(100,999);
        	$info['msg'] = '专题列表未开启生成~!';
			admin_info($info,2);
		}


		$ac = $this->input->get_post('ac',true);
		define('IS_HTML',true);
		if($ac!='pc' && !defined('MOBILE') && config('Mobile_Is')==1){
			define('MOBILE', true);	
		}
		//重新定义模板路径
		$this->load->get_templates();

	    $start= intval($this->input->get('start')); //当前页生成编号
	    $pagesize= intval($this->input->get('pagesize')); //每页多少条
	    $pagejs= intval($this->input->get('pagejs')); //总页数
	    $datacount= intval($this->input->get('datacount')); //数据总数
	    $page= intval($this->input->get('page')); //当前页
		if($start==0) $start=1;

	    //公众URI
		$uri='?pagesize='.$pagesize.'&pagejs='.$pagejs.'&datacount='.$datacount;

		//获取分页信息
	    if($datacount==0){
			if(!defined('MOBILE')){
	        	$pathfile = VIEWPATH.'pc'.FGF.'skins'.FGF.str_replace('/', FGF, Pc_Skins_Dir).PLUBPATH.FGF.'topic.html';
	        }else{
	        	$pathfile = VIEWPATH.'mobile'.FGF.'skins'.FGF.str_replace('/', FGF, Mobile_Skins_Dir).PLUBPATH.FGF.'topic.html';
	        }
			if(!file_exists($pathfile)){
        		admin_info('topic.html模板文件不存在~！');
			}
			$template = file_get_contents($pathfile);
			$pagesize = (int)str_substr('pagesize="','"',$template);
			if($pagesize==0) $pagesize = 10;
			$sqlstr="select id from ".CS_SqlPrefix."vod_topic where yid=0";
			$datacount = $this->Csdb->get_allnums($sqlstr); //总数量
			$pagejs = ceil($datacount/$pagesize);
		}
	    if($datacount==0) $pagejs=1;

	    echo '<link href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><script src="'.base_url().'packs/js/jquery.min.js"></script><style>b{font-size:14px;}</style>';
		echo '<b>正在开始生成<font color=red> 专题列表 </font>按,分<font color=red>'.ceil($pagejs/Html_PageNum).'</font>次生成，当前第<font color=red>'.ceil($start/Html_PageNum).'</font>次</b><br/><br>';

	    $n=1;
	    $pagego=1;
	    if($page>0 && $page<$pagejs){
	        $pagejs=$page;
	    }

	    for($i=$start;$i<=$pagejs;$i++){
			//获取静态路径
			$Htmllinks = LinkUrl('topic','lists',0,$i,'vod');
			//转换成生成路径
			$Htmllink = adminhtml($Htmllinks,'vod');
			$Mark_Text = $this->Cstpl->plub_list(array(),0,'id',$page,'',true,'topic.html','topic/lists','','视频专题');
			write_file(FCPATH.$Htmllink,$Mark_Text);
			echo "<font color=blue style='font-size:13px;'>第".$i."页：</font><a target='_blank' href='".$Htmllinks."'>".$Htmllinks."</a><font color=green>&nbsp;&nbsp;生成完毕!</font><br/><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
			$n++;
			ob_flush();flush();

			if(Html_PageNum==$n){
			   $pagego=2;break;
			}
		}
		if($pagego==2){ //操作系统设置每页数量，分多页生成
			$url=site_url('vod/admin/html/topic_save').$uri.'&numx='.$numx.'&start='.($i+1);
			exit("</br><b>暂停".Html_StopTime."秒后继续下一页&nbsp;>>>>&nbsp;&nbsp;<a target='_blank' href='".$url."'>如果您的 浏览器没有跳转，请点击继续...</a></b><script>setTimeout('updatenext();',".Html_StopTime."000);function updatenext(){location.href='".$url."';}</script>");
		}
	    $url=site_url('vod/admin/html/topic');
		$str="<b><font color=#0000ff>所有专题列表全部生成完毕!</font>&nbsp;>>>>&nbsp;&nbsp;<a href='".site_url('vod/admin/html/topic')."'>如果您的 浏览器没有跳转，请点击继续...</a></b>";
		echo("</br>".$str."<script>setTimeout('updatenext();',".Html_StopTime."000);function updatenext(){location.href='".$url."';}</script><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>");
	}

    //专题内容页生成操作
	public function topicshow_save(){
        if($this->huri['topic/show']['check']==0){
			$info['url'] = site_url('news/admin/html/type').'?v='.rand(100,999);
        	$info['msg'] = '专题内容未开启生成~!';
			admin_info($info,2);
		}

		$ac = $this->input->get_post('ac',true);
		define('IS_HTML',true);
		if($ac!='pc' && !defined('MOBILE') && config('Mobile_Is')==1){
			define('MOBILE', true);	
		}
		//重新定义模板路径
		$this->load->get_templates();

        $tid = $this->input->get_post('tid',true); //需要生成的专题ID
        $ksid= intval($this->input->get_post('ksid')); //开始ID
        $jsid= intval($this->input->get_post('jsid')); //结束ID
        $kstime= $this->input->get_post('kstime',true); //开始日期
        $jstime= $this->input->get_post('jstime',true); //结束日期
        $pagesize= intval($this->input->get('pagesize')); //每页多少条
        $pagejs= intval($this->input->get('pagejs')); //总页数
        $datacount= intval($this->input->get('datacount')); //数据总数
        $page= intval($this->input->get('page')); //当前页
		if($page==0) $page=1;
		$str='';

        //将数组转换成字符
		if(is_array($tid)){
		    $tid=implode(',', $tid);
		}

        if($ksid>0 && $jsid>0){
            $str.=' and id>'.($ksid-1).' and id<'.($jsid+1).'';
		}
        if(!empty($kstime) && !empty($jstime)){
			$ktime=strtotime($kstime)-86400;
			$jtime=strtotime($jstime)+86400;
			$str.=' and addtime>'.$ktime.' and addtime<'.$jtime.'';
		}
        if(!empty($tid)){
        	if(is_numeric($tid)){
            	$str.=' and id='.$tid;
        	}else{
            	$str.=' and id in ('.$tid.')';
        	}
		}

        if($datacount==0){
			$sqlstr="select count(*) as count from ".CS_SqlPrefix."vod_topic where yid=0 ".$str;
			$datacount = $this->Csdb->get_allnums($sqlstr); //总数量
			$pagejs = ceil($datacount/Html_PageNum);
		}
        if($datacount==0) $pagejs=1;

        $pagesize=Html_PageNum;
        if($datacount<$pagesize){
	        $pagesize=$datacount;
        }

        //全部生成完毕
        if($datacount==0 || $page>$pagejs){
        	$info['msg'] = '所有专题全部生成完毕~!';
			$info['url'] = site_url('vod/admin/html/topic').'?v='.rand(100,999);
            admin_info($info,1);
		}

        //公众URI
		$uri='?ac='.$ac.'&tid='.$tid.'&ksid='.$ksid.'&jsid='.$jsid.'&kstime='.$kstime.'&jstime='.$jstime.'&pagesize='.$pagesize.'&pagejs='.$pagejs.'&datacount='.$datacount;

        echo '<link href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><script src="'.base_url().'packs/js/jquery.min.js"></script><style>b{font-size:14px;}</style>';
		echo '<b>正在开始生成专题内容,分<font color=red>'.$pagejs.'</font>次生成，当前第<font color=red>'.$page.'</font>次</b><br/><br/>';

        $sql_string="select * from ".CS_SqlPrefix."vod_topic where yid=0 ".$str." order by id desc";
        $sql_string.=' limit '. $pagesize*($page-1) .','. $pagesize;
		$query = $this->db->query($sql_string);
        foreach ($query->result_array() as $row) {
			$id=$row['id'];
			//动态人气
			unset($row['hits']);
			unset($row['yhits']);
			unset($row['zhits']);
			unset($row['rhits']);
			unset($row['shits']);
			//获取静态路径
			$Htmllinks = LinkUrl('topic','show',$id,1,'vod');
			//转换成生成路径
			$Htmllink = adminhtml($Htmllinks,'vod');

			//解析动态人气标签
			$zdy['[topic:hits]'] = "<script src='".hitslink('hits/dt/hits/'.$id.'/topic','vod')."'></script>";
			$zdy['[topic:yhits]'] = "<script src='".hitslink('hits/dt/yhits/'.$id.'/topic','vod')."'></script>";
			$zdy['[topic:zhits]'] = "<script src='".hitslink('hits/dt/zhits/'.$id.'/topic','vod')."'></script>";
			$zdy['[topic:rhits]'] = "<script src='".hitslink('hits/dt/rhits/'.$id.'/topic','vod')."'></script>";
			$zdy['[topic:shits]'] = "<script src='".hitslink('hits/dt/shits/'.$id.'/topic','vod')."'></script>";
			//摧毁动态人气字段数组
			unset($row['hits']);
			unset($row['yhits']);
			unset($row['zhits']);
			unset($row['rhits']);
			//装载模板并输出
			$ids['tid']=$id;
			$zdy['[topic:pl]'] = get_pl('vod',$id,1);
			$hitslink = hitslink('hits/ids/'.$id.'/topic','vod');
	        $Mark_Text = $this->Cstpl->plub_show('topic',$row,$ids,true,'topic-show.html',$row['name'],$row['name'],'','',$zdy,$hitslink);
			//生成
			write_file(FCPATH.$Htmllink,$Mark_Text);
			echo "<font style=font-size:10pt;>生成视频专题:<font color=red>".$row['name']."</font>成功:<a href=".$Htmllinks." target=_blank>".$Htmllinks."</a></font><br/>";
			echo "<script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
			ob_flush();flush();
		}
	    $url=site_url('vod/admin/html/topicshow_save').$uri.'&page='.($page+1);
		$str="<b>暂停".Html_StopTime."秒后继续&nbsp;>>>>&nbsp;&nbsp;<a href='".$url."'>如果您的 浏览器没有跳转，请点击继续...</a></b>";
		echo("</br>".$str."<script>setTimeout('updatenext();',".Html_StopTime."000);function updatenext(){location.href='".$url."';}</script><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>");
	}

    //列表页
	public function type(){
        $this->load->view('html_type.html');
	}

    //列表页生成操作
	public function type_save(){
        if($this->huri['lists']['check']==0){
			$info['url'] = site_url('vod/admin/html/type').'?v='.rand(100,999);
        	$info['msg'] = '视频分类未开启生成~!';
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
			$fid=($op=='all')?'id,news,reco,hits,yhits,zhits,rhits,xhits,shits,dhits,chits,phits':'id';
		}

        //生成全部分类获取全部分类ID
        if($op=='all' && $nums==0){
			   $cid=array();
               $query = $this->db->query("SELECT id FROM ".CS_SqlPrefix."vod_list where yid=0 order by xid asc"); 
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
			$info['url'] = site_url('vod/admin/html/type').'?v='.rand(100,999);
        	$info['msg'] = '请选择要生成的分类~!';
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
        	$info['url'] = site_url('vod/admin/html/type').'?v='.rand(100,999);
        	$info['msg'] = '所有分类全部生成完毕~!';
			admin_info($info,1);
		}

		$id=(int)$arr[$nums]; //当前分类ID
		$type=$arr2[$numx]; //当前排序


		//获取分类信息
		$row = $this->Csdb->get_row_arr('vod_list','*',$id);
        $tpl = !empty($row['skins'])?$row['skins']:'list.html';
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
				$sqlstr="select id from ".CS_SqlPrefix."vod where cid=".$ids;
			}else{
				$sqlstr="select id from ".CS_SqlPrefix."vod where cid IN (".$ids.")";
			}
			$datacount = $this->Csdb->get_allnums($sqlstr); //总数量
			$pagejs = ceil($datacount/$pagesize);
		}
        if($datacount==0) $pagejs=1;

        echo '<link href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><script src="'.base_url().'packs/js/jquery.min.js"></script><style>b{font-size:14px;}</style>';
		echo '<b>正在开始生成分类<font color=red> '.$row["name"].' </font>按<font color=red> '.$this->gettype($type).' </font>排序的列表,分<font color=red>'.ceil($pagejs/Html_PageNum).'</font>次生成，当前第<font color=red>'.ceil($start/Html_PageNum).'</font>次</b><br/><br>';

        $n=1;
        $pagego=1;
        if($page>0 && $page<$pagejs){
	        $pagejs=$page;
        }

        for($i=$start;$i<=$pagejs;$i++){
               
			//获取静态路径
			$Htmllinks = LinkUrl('lists',$type,$id,$i,'vod',$row['bname']);
			//转换成生成路径
			$Htmllink = adminhtml($Htmllinks,'vod');

			$arri['cid'] = $ids;
			$arri['fid'] = $row['fid']==0 ? $row['id'] : $row['fid'];
			$arri['sid'] = $arri['fid'];

			$Mark_Text=$this->Cstpl->plub_list($row,$id,$type,$i,$arri,true,$tpl,'lists','vod',$row['name'],$row['name']);
			//生成
			write_file(FCPATH.$Htmllink,$Mark_Text);

			echo "<font color=blue >第".$i."页：</font><a target='_blank' href='".$Htmllinks."'>".$Htmllinks."</a><font color=green>&nbsp;&nbsp;生成完毕!</font><br/><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
			$n++;
			ob_flush();flush();

			if(Html_PageNum==$n){
			   $pagego=2;
			   break;
			}
		}

	    if($pagego==2){ //操作系统设置每页数量，分多页生成
		  	$url=site_url('vod/admin/html/type_save').$uri.'&nums='.$nums.'&numx='.$numx.'&start='.($i+1);
	      	exit("</br><b>暂停".Html_StopTime."秒后继续下一页&nbsp;>>>>&nbsp;&nbsp;<a target='_blank' href='".$url."'>如果您的 浏览器没有跳转，请点击继续...</a></b><script>setTimeout('updatenext();',".Html_StopTime."000);function updatenext(){location.href='".$url."';}</script><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>");
		}else{  
	          $url=site_url('vod/admin/html/type_save').$uri.'&nums='.($nums+1);
		      $str="<b>暂停".Html_StopTime."秒后继续&nbsp;>>>>&nbsp;&nbsp;<a target='_blank' href='".$url."'>如果您的 浏览器没有跳转，请点击继续...</a></b><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
        }

        //判断生成完毕
		if(($numx+1)>=$len2){ //全部完成
            $url=site_url('vod/admin/html/type_save').$uri.'&nums='.($nums+1);
			$str="<b><font color=#0000ff>所有排序页面全部生成完毕!</font>&nbsp;>>>>&nbsp;&nbsp;<a href='".site_url('vod/admin/html/type_save').$uri.'&nums='.($nums+1)."'>如果您的 浏览器没有跳转，请点击继续...</a></b>";
		}else{ //当前排序方式完成
            $url=site_url('vod/admin/html/type_save').$uri.'&nums='.$nums.'&numx='.($numx+1);
			$str='<b>按<font color=#0000ff>'.$this->gettype($type).'排序</font>分类生成完毕!&nbsp;>>>>&nbsp;&nbsp;<a href="'.site_url('vod/admin/html/type_save').$uri.'&nums='.$nums.'&numx='.($numx+1).'">如果您的 浏览器没有跳转，请点击继续...</a></b>';
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
			admin_msg('视频内容页未开启生成~!','javascript:history.back();','no');
		}

		$ac = $this->input->get_post('ac',true);
		define('IS_HTML',true);
		if($ac!='pc' && !defined('MOBILE') && config('Mobile_Is')==1){
			define('MOBILE', true);	
		}
		//重新定义模板路径
		$this->load->get_templates();

        $day = intval($this->input->get_post('day',true)); //最近几天
        $ids = $this->input->get_post('ids',true); //需要生成的数据ID
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
		$str='';

        //将数组转换成字符
		if(is_array($cid)){
		     $cid=implode(',', $cid);
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
			$sqlstr="select id from ".CS_SqlPrefix."vod where 1=1 ".$str.$limit;
            $datacount = $this->Csdb->get_allnums($sqlstr); //总数量
	        $pagejs = ceil($datacount/Html_PageNum);
		}
        if($datacount==0) $pagejs=1;

        $pagesize=Html_PageNum;
        if($datacount<$pagesize){
	        $pagesize=$datacount;
        }

        //全部生成完毕
        if($page>$pagejs){
            $info['msg'] = '所有内容页全部生成完毕~!';
			$info['url'] = site_url('vod/admin/html/show').'?v='.rand(100,999);
            admin_info($info,1);
		}

        //公众URI
		$uri='?ac='.$ac.'&day='.$day.'&cid='.$cid.'&ids='.$ids.'&newid='.$newid.'&ksid='.$ksid.'&jsid='.$jsid.'&kstime='.$kstime.'&jstime='.$jstime.'&pagesize='.$pagesize.'&pagejs='.$pagejs.'&datacount='.$datacount;

        echo '<link href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><script src="'.base_url().'packs/js/jquery.min.js"></script><style>b{font-size:14px;}</style>';
		echo '<b>正在开始生成视频内容,分<font color=red>'.$pagejs.'</font>次生成，当前第<font color=red>'.$page.'</font>次</b><br/><br/>';

        $sql_string="select * from ".CS_SqlPrefix."vod where 1=1 ".$str." order by id desc";
        $sql_string.=' limit '. $pagesize*($page-1) .','. $pagesize;
		$query = $this->db->query($sql_string);
        foreach ($query->result_array() as $row) {
            $rows = $row;
			$id=$row['id'];
			//获取静态路径
			$Htmllinks = LinkUrl('show','id',$row['id'],0,'vod');
			//转换成生成路径
			$Htmllink = adminhtml($Htmllinks,'vod');

			//获取当前分类下二级分类ID
			$arr['cid'] = getChild($row['cid']);
			$arr['uid'] = $row['uid'];
			$arr['singerid'] = $row['singerid'];
			$arr['tags'] = $row['tags'];
			$skins=getzd('vod_list','skins2',$row['cid']);
			if(empty($skins)) $skins='show.html';

			//评论
			$zdy['[vod:pl]'] = get_pl('vod',$id);
			//分类地址、名称
			$zdy['[vod:link]'] = LinkUrl('show','id',$row['id'],1,'vod');
			$zdy['[vod:playlink]'] = VodPlayUrl('play',$id);
			$zdy['[vod:classlink]'] = LinkUrl('lists','id',$row['cid'],1,'vod');
			$zdy['[vod:classname]'] = getzd('vod_list','name',$row['cid']);
			//主演、导演、标签、年份、地区、语言加超级连接
			$zdy['[vod:zhuyan]'] = tagslink($row['zhuyan'],'zhuyan');
			$zdy['[vod:daoyan]'] = tagslink($row['daoyan'],'daoyan');
			$zdy['[vod:yuyan]'] = tagslink($row['yuyan'],'yuyan');
			$zdy['[vod:diqu]'] = tagslink($row['diqu'],'diqu');
			$zdy['[vod:tags]'] = tagslink($row['tags']);
			$zdy['[vod:year]'] = tagslink($row['year'],'year');
			//评分
			$zdy['[vod:pfen]'] = getpf($row['pfen'],$row['phits']);
			$zdy['[vod:pfenbi]'] = getpf($row['pfen'],$row['phits'],2);

			unset($row['zhuyan']);
			unset($row['daoyan']);
			unset($row['yuyan']);
			unset($row['diqu']);
			unset($row['tags']);
			unset($row['year']);
			unset($row['pfen']);
			unset($row['phits']);

			//装载模板并输出
			$Mark_Text = $this->Cstpl->plub_show('vod',$row,$arr,true,$skins,$row['name'],$row['name'],'','',$zdy);

			//生成
			write_file(FCPATH.$Htmllink,$Mark_Text);
			echo "<font style=font-size:10pt;>生成影片:<font color=red>".$row['name']."</font>成功:<a href=".$Htmllinks." target=_blank>".$Htmllinks."</a></font><br/>";
			echo "<script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
			//判断是否生成播放页
			if($this->huri['play']['check']==1){
			    $this->getplay($rows);
			}
			ob_flush();flush();
		}
        if(!empty($ids)){
            $url=$_SERVER['HTTP_REFERER'];
		    $str="<b>全部生成完毕&nbsp;>>>>&nbsp;&nbsp;<a href='".$url."'>如果您的 浏览器没有跳转，请点击继续...</a></b>";
		}else{ 
	        $url=site_url('vod/admin/html/show_save').$uri.'&page='.($page+1);
		    $str="<b>暂停".Html_StopTime."秒后继续&nbsp;>>>>&nbsp;&nbsp;<a href='".$url."'>如果您的 浏览器没有跳转，请点击继续...</a></b>";
		}
		echo("</br>".$str."<script>setTimeout('updatenext();',".Html_StopTime."000);function updatenext(){location.href='".$url."';}</script><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>");
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
		        	$Htmllink = Web_Path.'opt/'.$t.'_vod.html';
		        }else{
		        	$Htmllink = Web_Path.Html_Wap_Dir.'/opt/'.$t.'_vod.html';
		        }

				//生成
				write_file(FCPATH.$Htmllink,$Mark_Text);
				echo "<font color=blue>生成自定义页面：<a style=\"color:red\" href=".$Htmllink." target=_blank>".$Htmllink."</a><font color=green>&nbsp;&nbsp;成功！</font></font><br/><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
				ob_flush();flush();
			}
		}
	    $info['url'] = site_url('vod/admin/html/opt');
	    $info['msg'] = '自定义页面全部生成完毕~!';
	    admin_info($info,1);
	}

    //生成播放页
	public function getplay($row){
		//默认模板
		$skins = $row['skins'];
		if(empty($skins) || $skins=='play.html'){
		     $skins = getzd('vod_list','skins3',$row['cid']);
		}
		if(empty($skins)) $skins = 'play.html';

		if(defined('MOBILE')){
			$tplfile = VIEWPATH.'mobile'.FGF.'skins'.FGF.Mobile_Skins_Dir.FGF.PLUBPATH.FGF.$skins;
		}else{
			$tplfile = VIEWPATH.'pc'.FGF.'skins'.FGF.Pc_Skins_Dir.FGF.PLUBPATH.FGF.$skins;
		}
		//模版不存在
		if(!file_exists($tplfile)){
			return '';
		}

	    //播放页
	    if(!empty($row['purl'])){

			$id = $row['id'];
			//获取当前分类下二级分类ID
			$arr['cid']=getChild($row['cid']);
			$arr['uid']=$row['uid'];
			$arr['singerid']=$row['singerid'];
			$arr['tags']=$row['tags'];

			$zdy['[vod:classlink]'] = LinkUrl('lists','id',$row['cid'],1,'vod');
			$zdy['[vod:classname]'] = getzd('vod_list','name',$row['cid']);
			$zdy['[vod:link]'] = LinkUrl('show','id',$row['id'],1,'vod');
			//评论
			$zdy['[vod:pl]'] = get_pl('vod',$id);
			//主演、导演、标签、年份、地区、语言加超级连接
			$zdy['[vod:zhuyan]'] = tagslink($row['zhuyan'],'zhuyan');
			$zdy['[vod:daoyan]'] = tagslink($row['daoyan'],'daoyan');
			$zdy['[vod:yuyan]'] = tagslink($row['yuyan'],'yuyan');
			$zdy['[vod:diqu]'] = tagslink($row['diqu'],'diqu');
			$zdy['[vod:tags]'] = tagslink($row['tags']);
			$zdy['[vod:year]'] = tagslink($row['year'],'year');
			//评分
			$zdy['[vod:pfen]'] = getpf($row['pfen'],$row['phits']);
			$zdy['[vod:pfenbi]'] = getpf($row['pfen'],$row['phits'],2);

			unset($row['zhuyan']);
			unset($row['daoyan']);
			unset($row['yuyan']);
			unset($row['diqu']);
			unset($row['tags']);
			unset($row['year']);
			unset($row['pfen']);
			unset($row['phits']);
			//增加人气链接
			$hitslink = hitslink('hits/ids/'.$id,'vod');

			$Data_Arr=explode("#cscms#",$row['purl']);
			for($i=0;$i<count($Data_Arr);$i++){
			    $DataList_Arr = explode("\n",$Data_Arr[$i]);
				for($j=0;$j<count($DataList_Arr);$j++){
					//分类地址、名称
					$zdy['[vod:zu]'] = $i+1;
					$zdy['[vod:ji]'] = $j+1;
					$zdy['[vod:playlink]'] = VodPlayUrl('play',$id,$i,$j);

					//播放器
					if($i>=count($Data_Arr)) $i=0;
					$DataList_Arr=explode("\n",$Data_Arr[$i]);
					$Dataurl_Arr=explode('$',$DataList_Arr[$j]);

					$xpurl="";  //下集播放地址
					$laiyuan=str_replace("\r","",@$Dataurl_Arr[2]); //来源
					$url=$Dataurl_Arr[1];  //地址
					$pname=$Dataurl_Arr[0];  //当前集数
					if(substr($url,0,11) == 'attachment/') $url = annexlink($url);

					$zdy['[vod:laiy]'] = $laiyuan;
					$zdy['[vod:jiname]'] = $pname;
					$zdy['[vod:qurl]'] = $url;
					$zdy['[vod:wapurl]'] = $url;

					if(count($DataList_Arr)>($j+1)){
					    $DataNext=$DataList_Arr[($j+1)];
					    $DataNextArr=explode('$',$DataNext);
					    if(count($DataNextArr)==2) $DataNext=$DataNextArr[1];
					    $xurl=VodPlayUrl('play',$id,$i,($j+1));
					    $Dataurl_Arr2=explode('$',$DataList_Arr[($j+1)]);
					    $xpurl=@$Dataurl_Arr2[1];  //下集播放地址
					}else{
					    $DataNext=$DataList_Arr[$j];
					    $DataNextArr=explode('$',$DataNext);
					    if(count($DataNextArr)==2) $DataNext=$DataNextArr[1];			
					    $xurl='#';
					    $xpurl='';  //下集播放地址
					}
					if($j==0){
					    $surl='#';
					}else{
					    $surl=VodPlayUrl('play',$id,$i,($j-1));
					}
					$psname='';
					for($jj=0;$jj<count($Data_Arr);$jj++){
						   $jis='';
					       $Ji_Arr=explode("\n",$Data_Arr[$jj]);
					       for($k=0;$k<count($Ji_Arr);$k++){
					            $Ly_Arr=explode('$',$Ji_Arr[$k]);
								$jis.=$Ly_Arr[0].'$$'.@$Ly_Arr[2].'====';
						   }
						   $psname.=substr($jis,0,-4).'#cscms#';
					}
					$player_arr=str_replace("\r","",substr($psname,0,-7));
					if($laiyuan=='xgvod'||$laiyuan=='jjvod'||$laiyuan=='yyxf'||$laiyuan=='bdhd'||$laiyuan=='qvod'){
						$xpurl=str_replace("+","__",base64_encode($xpurl));
					    $url=str_replace("+","__",base64_encode($url));
					}else{
						$xpurl=escape($xpurl);
					    $url=escape($url);
					}
					$player="<script type='text/javascript' src='".hitslink('play/form','vod')."'></script><script type='text/javascript'>var cs_playlink='".VodPlayUrl('play',$id,$i,$j,1)."';var cs_did='".$id."';var player_name='".$player_arr."';var cs_pid='".$j."';var cs_zid='".$i."';var cs_vodname='".$row['name']." - ".$pname."';var cs_root='".Web_Path."';var cs_width=".CS_Play_sw.";var cs_height=".CS_Play_sh.";var cs_surl='".$surl."';var cs_xurl='".$xurl."';var cs_url='".$url."';var cs_xpurl='".$xpurl."';var cs_laiy='".$laiyuan."';var cs_adloadtime='".CS_Play_AdloadTime."';</script><iframe border=\"0\" name=\"cscms_vodplay\" id=\"cscms_vodplay\" src=\"".Web_Path."packs/vod_player/play.html\" marginwidth=\"0\" framespacing=\"0\" marginheight=\"0\" noresize=\"\" vspale=\"0\" style=\"z-index: 9998;\" frameborder=\"0\" height=\"".(CS_Play_sh+30)."\" scrolling=\"no\" width=\"100%\" allowfullscreen></iframe>";
					$zdy['[vod:player]'] = $player;
					$zdy['[vod:surl]'] = $surl;
					$zdy['[vod:xurl]'] = $xurl;

					//装载模板并输出
					$Mark_Text = $this->Cstpl->plub_show('vod',$row,$arr,true,$skins,$row['name'],$row['name'],'','',$zdy,$hitslink);
	               //获取静态路径
	               $Htmllinks = VodPlayUrl('play',$id,$i,$j);
				   //生成地址转换
				   $Htmllink = adminhtml($Htmllinks,'vod');
	               //生成
	               write_file(FCPATH.$Htmllink,$Mark_Text);
				}
			    echo "&nbsp;&nbsp;&nbsp;<font style=font-size:9pt;color:red;>--生成第".($i+1)."组播放器：<a href=".$Htmllinks." target=_blank>".$Htmllinks."</a></font><br/>";
			    echo("<script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>");
			    ob_flush();flush();
			}
	    }
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
			case 'shits':$str="收藏";break;
			case 'xhits':$str="下载";break;
			case 'dhits':$str="被顶";break;
			case 'chits':$str="被踩";break;
			case 'phits':$str="评分";break;
			case 'yue':$str="月人气";break;
			case 'zhou':$str="周人气";break;
			case 'ri':$str="日人气";break;
			case 'fav':$str="收藏";break;
			case 'down':$str="下载";break;
			case 'ding':$str="被顶";break;
			case 'cai':$str="被踩";break;
		}
		return $str;
	}
}
