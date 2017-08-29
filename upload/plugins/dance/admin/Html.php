<?php if(!defined('IS_ADMIN')) exit('No direct script access allowed');
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
		$this->huri=config('Html_Uri');
		if(config('Web_Mode')!=3){
            admin_info('板块浏览模式非静态，不需要生成静态文件~!');
		}
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

	    $Mark_Text = $this->Cstpl->plub_index('dance','index.html',true);
		write_file(FCPATH.$index_url,$Mark_Text);

	    $info['msg'] = '恭喜你，板块主页已生成';
		$info['time'] = 2000;
	    $info['url'] = site_url('dance/admin/html').'?v='.rand(1000,1999);
		getjson($info,0);
	}

    //专辑页
	public function topic(){
        $this->load->view('html_topic.html');
	}

    //专辑列表页生成操作
	public function topic_save(){
        if($this->huri['topic/lists']['check']==0){
        	$info['url'] = site_url('dance/admin/html/topic').'?v='.rand(100,999);
        	$info['msg'] = '专辑列表未开启生成~!';
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
        $nums= intval($this->input->get('nums')); //分类已完成数量
        $numx= intval($this->input->get('numx')); //排序方式已完成数量
        $cid = $this->input->get_post('cid',true); //需要生成的分类ID
		$fid = $this->input->get_post('fid',true); //需要生成的排序方式
		if($start==0) $start=1;
        if(empty($fid)) $fid='id';

        //将数组转换成字符
		if(is_array($cid)){
		    $cid = implode(',', $cid);
		}
		if(is_array($fid)){
		    $fid = implode(',', $fid);
		}
        //公众URI
		$uri='?ac='.$ac.'&cid='.$cid.'&fid='.$fid.'&pagesize='.$pagesize.'&pagejs='.$pagejs.'&datacount='.$datacount;
        //分割分类ID
        $arr = explode(',',$cid);
        $len = count($arr);
        //分割排序方式
        $arr2 = explode(',',$fid);
        $len2 = count($arr2);
        //全部生成完毕
        if($nums>=$len){
        	$info['url'] = site_url('dance/admin/html/topic').'?v='.rand(100,999);
        	$info['msg'] = '所有分类全部生成完毕~!';
        	admin_info($info,1);
		}
		$id = (int)$arr[$nums]; //当前分类ID
		$type = $arr2[$numx]; //当前排序
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
			$sqlstr="select id,cid from ".CS_SqlPrefix."dance_topic where yid=0";
			if($id>0){
				$sqlstr.=" and cid=".$id."";
			}
			$datacount = $this->Csdb->get_allnums($sqlstr); //总数量
			$pagejs = ceil($datacount/$pagesize);
		}
        if($datacount==0) $pagejs=1;
        if($id==0){
            $row = array();
			$row['name'] = '全部';
		}else{
            $row = $this->Csdb->get_row_arr('dance_list','*',$cid);
        }
        echo '<link href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><script src="'.base_url().'packs/js/jquery.min.js"></script>';
        echo "<b style=\"font-size:14px;\">正在开始生成专辑列表<font color=red> ".$row["name"]." </font>按<font color=red> ".$this->gettype($type)." </font>排序的列表,分<font color=red>".ceil($pagejs/Html_PageNum)."</font>次生成，当前第<font color=red>".ceil($start/Html_PageNum)."</font>次</b><br/><br>";
        $n=1;
        $pagego=1;
        if($page>0 && $page<$pagejs){
	        $pagejs=$page;
        }
        for($i=$start;$i<=$pagejs;$i++){  
			//获取静态路径
			$Htmllinks = $Htmllink = LinkUrl('topic/lists',$type,$id,$i,'dance',$row['name']);
			//转换成生成路径
			$Htmllink = adminhtml($Htmllinks,'dance');
			//需要替换的标签
			$zdy = array(
				'[topic:cids]'=> $id,
				'[topic:link]'=>LinkUrl('topic/lists','id',$id,$page,'dance'),
				'[topic:hlink]'=>LinkUrl('topic/lists','hits',$id,$page,'dance'),
				'[topic:rlink]'=>LinkUrl('topic/lists','ri',$id,$page,'dance'),
				'[topic:zlink]'=>LinkUrl('topic/lists','zhou',$id,$page,'dance'),
				'[topic:ylink]'=>LinkUrl('topic/lists','yue',$id,$page,'dance'),
				'[topic:slink]'=>LinkUrl('topic/lists','fav',$id,$page,'dance')
			);

			$Mark_Text = $this->Cstpl->plub_list($row,$id,$type,$i,$id,true,'topic.html','topic/lists','','歌曲专辑','歌曲专辑','',$zdy);
			//写入文件
			write_file(FCPATH.$Htmllink,$Mark_Text);

			echo "<a target='_blank' href='".$Htmllinks."'>第 ".$i." 页：".$Htmllinks."<font color=green>生成完毕~!</font></a><br/><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
			ob_flush();flush();
			$n++;

			if(Html_PageNum==$n){
			   $pagego=2;break;
			}
		}
	    if($pagego==2){ //操作系统设置每页数量，分多页生成
			$url = site_url('dance/admin/html/topic_save').$uri.'&nums='.$nums.'&numx='.$numx.'&start='.($i+1);
			exit("</br><b style=\"font-size:14px;\">".vsprintf(L('plub_27'),array(Html_StopTime))."&nbsp;>>>>&nbsp;&nbsp;<a target='_blank' href='".$url."'>".L('plub_28')."</a></b><script>setTimeout('updatenext();',".Html_StopTime."000);function updatenext(){location.href='".$url."';}</script>");
		}else{
			$url=site_url('dance/admin/html/topic_save').$uri.'&nums='.($nums+1);
			$str="<b style=\"font-size:14px;\">".vsprintf(L('plub_27'),array(Html_StopTime))."&nbsp;>>>>&nbsp;&nbsp;<a target='_blank' href='".$url."'>".L('plub_28')."</a></b>";
        }
        //判断生成完毕
		if(($numx+1)>=$len2){ //全部完成
            $url=site_url('dance/admin/html/topic_save').$uri.'&nums='.($nums+1);
			$str="<b style=\"font-size:14px;\"><font color=#0000ff>".L('plub_29')."</font>&nbsp;>>>>&nbsp;&nbsp;<a href='".site_url('dance/admin/html/topic_save').$uri.'&nums='.($nums+1)."'>".L('plub_28')."</a></b>";
		}else{ //当前排序方式完成
            $url=site_url('dance/admin/html/topic_save').$uri.'&nums='.$nums.'&numx='.($numx+1);
			$str='<b style="font-size:14px;">'.vsprintf(L('plub_30'),array($this->gettype($type))).'&nbsp;>>>>&nbsp;&nbsp;<a href="'.site_url('dance/admin/html/topic_save').$uri.'&nums='.$nums.'&numx='.($numx+1).'">'.L('plub_28').'</a></b>';
		}
		echo("</br>".$str."<script>setTimeout('updatenext();',".Html_StopTime."000);function updatenext(){location.href='".$url."';}</script><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>");
	}

    //专辑内容页生成操作
	public function topicshow_save(){
        if($this->huri['topic/show']['check']==0){
			$info['url'] = site_url('dance/admin/html/topic').'?v='.rand(100,999);
        	$info['msg'] = '专辑内容页未开启生成~!';
        	admin_info($info,2);
		}

		$ac = $this->input->get_post('ac',true);
		define('IS_HTML',true);
		if($ac!='pc' && !defined('MOBILE') && config('Mobile_Is')==1){
			define('MOBILE', true);	
		}
		//重新定义模板路径
		$this->load->get_templates();

        $tid = $this->input->get_post('tid',true); //需要生成的专辑ID
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
             $str.=' and id in ('.$tid.')';
		}

        if($datacount==0){
			 $sqlstr = "select id from ".CS_SqlPrefix."dance_topic where yid=0 ".$str;
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
            $info['url'] = site_url('dance/admin/html/topic').'?v='.rand(100,999);
        	$info['msg'] = '所有专辑全部生成完毕~!';
        	admin_info($info,1);
		}

        //公众URI
		$uri='?ac='.$ac.'&tid='.$tid.'&ksid='.$ksid.'&jsid='.$jsid.'&kstime='.$kstime.'&jstime='.$jstime.'&pagesize='.$pagesize.'&pagejs='.$pagejs.'&datacount='.$datacount;

        echo '<link href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><script src="'.base_url().'packs/js/jquery.min.js"></script>';
        echo "<b>正在开始生成专辑播放,分<font color=red>".$pagejs."</font>次生成，当前第<font color=red>".$page."</font>次</b><br/><br/>";

        //SQL查询
        $sql_string="select * from ".CS_SqlPrefix."dance_topic where yid=0 ".$str." order by id desc";
        $sql_string.=' limit '. $pagesize*($page-1) .','. $pagesize;
		$query = $this->db->query($sql_string);

        foreach ($query->result_array() as $row) {
               
			$id=$row['id'];
			//摧毁动态人气字段数组
			unset($row['hits']);
			unset($row['yhits']);
			unset($row['zhits']);
			unset($row['rhits']);

			//装载模板并输出
			$ids['tid']=$id;
			$ids['uid']=$row['uid'];
			$ids['singerid']=$row['singerid'];

			$zdy['[topic:pl]'] = get_pl('dance',$id,1);
			$zdy['[topic:hits]'] = "<script src='".hitslink('hits/dt/hits/'.$id.'/topic','dance')."'></script>";
			$zdy['[topic:yhits]'] = "<script src='".hitslink('hits/dt/yhits/'.$id.'/topic','dance')."'></script>";
			$zdy['[topic:zhits]'] = "<script src='".hitslink('hits/dt/zhits/'.$id.'/topic','dance')."'></script>";
			$zdy['[topic:rhits]'] = "<script src='".hitslink('hits/dt/rhits/'.$id.'/topic','dance')."'></script>";

			//内容分页开始
			$skins = empty($row['skins']) ? 'topic-show.html' : $row['skins'];
			if(!defined('MOBILE')){
	        	$pathfile = VIEWPATH.'pc'.FGF.'skins'.FGF.str_replace('/', FGF, Pc_Skins_Dir).PLUBPATH.FGF.$skins;
	        }else{
	        	$pathfile = VIEWPATH.'mobile'.FGF.'skins'.FGF.str_replace('/', FGF, Mobile_Skins_Dir).PLUBPATH.FGF.$skins;
	        }
			if(!file_exists($pathfile)){
        		admin_info($skins.'模板文件不存在~！');
			}
			$template = file_get_contents($pathfile);
			$psize = (int)str_substr('pagesize="','"',$template);

			$sqlstr="select id from ".CS_SqlPrefix."dance where tid=".$id;
			$tcount = $this->Csdb->get_allnums($sqlstr); //总数量
			$pjs = ceil($tcount/$psize);
			if($pjs<1) $pjs = 1;

			$hitslink = '';
			$sql = "select {field} from ".CS_SqlPrefix."dance where tid=".$id;
			echo "<font style=font-size:10pt;>生成歌曲专辑：<font color=red>".$row['name']."</font></font><br>";
			//生成分页
			for($i=1;$i<=$pjs;$i++){
				//获取静态路径
				$Htmllinks = LinkUrl('topic','show',$id,$i,'dance',$row['name']);
				//转换成生成路径
				$Htmllink = adminhtml($Htmllinks,'dance');
				if($i==1) $hitslink = hitslink('hits/ids/'.$id.'/topic','dance');

				$Mark_Text = $this->Cstpl->plub_list($row,$id,'',$i,$ids,true,$skins,'topic/show','topic',$row['name'],$row['tags'],'',$zdy,$sql,$hitslink);
				//生成
				write_file(FCPATH.$Htmllink,$Mark_Text);
				echo "&nbsp;&nbsp;&nbsp;&nbsp;<font style=font-size:10pt;>第<font color=red>".$i."</font>页，成功：<a href=".$Htmllinks." target=_blank>".$Htmllinks."</a></font><br/><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
				ob_flush();flush();
			}
		}

	    $url=site_url('dance/admin/html/topicshow_save').$uri.'&page='.($page+1);
		$str="<b style=\"font-size:14px;\">暂停".Html_StopTime."秒后继续下一页&nbsp;>>>>&nbsp;&nbsp;<a href='".$url."'>如果您的 浏览器没有跳转，请点击继续...</a></b>";
		echo("</br>".$str."<script>setTimeout('updatenext();',".Html_StopTime."000);function updatenext(){location.href='".$url."';}</script><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>");
	}

    //列表页
	public function type(){
        $this->load->view('html_type.html');
	}

    //列表页生成操作
	public function type_save(){
        if($this->huri['lists']['check']==0){
			admin_info('歌曲分类未开启生成~!');
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
			$fid=($ac=='all')?'id,dance,reco,hits,yhits,zhits,rhits,xhits,shits,dhits,chits,phits':'id';
		}

        //生成全部分类获取全部分类ID
        if($op=='all' && $nums==0){
			$cid=array();
            $query = $this->db->query("SELECT id FROM ".CS_SqlPrefix."dance_list where yid=0 order by xid asc"); 
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
			$info['url'] = site_url('dance/admin/html/type').'?v='.rand(100,999);
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
        	$info['url'] = site_url('dance/admin/html/type').'?v='.rand(100,999);
			$info['msg'] = '所有分类全部生成完毕~!';
			admin_info($info,1);
		}
		$id = (int)$arr[$nums]; //当前分类ID
		$type=$arr2[$numx]; //当前排序

		//获取分类信息
		$row = $this->Csdb->get_row_arr('dance_list','*',$id);
        $tpl = !empty($row['skins']) ? $row['skins'] : 'list.html';
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
				$sqlstr="select id from ".CS_SqlPrefix."dance where cid=".$ids;
			}else{
				$sqlstr="select id from ".CS_SqlPrefix."dance where cid IN (".$ids.")";
			}
			$datacount = $this->Csdb->get_allnums($sqlstr); //总数量
			$pagejs = ceil($datacount/$pagesize);
		}
        if($datacount==0) $pagejs=1;


        echo '<link href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><script src="'.base_url().'packs/js/jquery.min.js"></script>';
        echo '<b style="font-size:14px;">正在开始生成分类<font color=red> '.$row["name"].' </font>按<font color=red> '.$this->gettype($type).' </font>排序的列表,分<font color=red>'.ceil($pagejs/Html_PageNum).'</font>次生成，当前第<font color=red>'.ceil($start/Html_PageNum).'</font>次</b><br/><br>';
        $n=1;
        $pagego=1;
        if($page>0 && $page<$pagejs){
	        $pagejs = $page;
        }
        for($i=$start;$i<=$pagejs;$i++){
			//获取静态路径
			$Htmllinks = LinkUrl('lists',$type,$id,$i,'dance',$row['bname']);
			//转换成生成路径
			$Htmllink = adminhtml($Htmllinks,'dance');

			$arri['cid'] = $ids;
			$arri['fid'] = $row['fid']==0 ? $row['id'] : $row['fid'];
			$arri['sid'] = $arri['fid'];

			$Mark_Text=$this->Cstpl->plub_list($row,$id,$type,$i,$arri,true,$tpl,'lists','dance',$row['name'],$row['name']);
			write_file(FCPATH.$Htmllink,$Mark_Text);

			echo "<font style=font-size:12pt;>第".$i."页：<a target='_blank' href='".$Htmllinks."'>".$Htmllinks."</a>&nbsp;&nbsp;<font color=green>生成完毕~!</font></font><br/><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
			ob_flush();flush();
			$n++;
			if(Html_PageNum==$n){
			   $pagego=2;
			   break;
			}
		}

	    if($pagego==2){ //操作系统设置每页数量，分多页生成
		  $url=site_url('dance/admin/html/type_save').$uri.'&nums='.$nums.'&numx='.$numx.'&start='.($i+1);
	      exit("</br><b style=\"font-size:14px;\">暂停".Html_StopTime."秒后继续下一页>>>>&nbsp;&nbsp;<a target='_blank' href='".$url."'>如果您的浏览器没有跳转，请点击继续...</a></b><script>setTimeout('updatenext();',".Html_StopTime."000);function updatenext(){location.href='".$url."';}</script>");
		}else{  
	          $url=site_url('dance/admin/html/type_save').$uri.'&nums='.($nums+1);
		      $str="<b style=\"font-size:14px;\">暂停".Html_StopTime."秒后继续下一页>>>>&nbsp;&nbsp;<a target='_blank' href='".$url."'>如果您的浏览器没有跳转，请点击继续...</a></b>";
        }

        //判断生成完毕
		if(($numx+1)>=$len2){ //全部完成
            $url=site_url('dance/admin/html/type_save').$uri.'&nums='.($nums+1);
			$str="<b style=\"font-size:14px;\"><font color=#0000ff>所有排序页面全部生成完毕</font>&nbsp;>>>>&nbsp;&nbsp;<a href='".site_url('dance/admin/html/type_save').$uri.'&nums='.($nums+1)."'>如果您的浏览器没有跳转，请点击继续...</a></b>";
		}else{ //当前排序方式完成
            $url=site_url('dance/admin/html/type_save').$uri.'&nums='.$nums.'&numx='.($numx+1);
			$str='<b style=\"font-size:14px;\">按<font color=#0000ff>'.$this->gettype($type).'排序</font>分类生成完毕!&nbsp;>>>>&nbsp;&nbsp;<a href="'.site_url('dance/admin/html/type_save').$uri.'&nums='.$nums.'&numx='.($numx+1).'">如果您的浏览器没有跳转，请点击继续...</a></b>';
		}
		echo("</br>".$str."<script>setTimeout('updatenext();',".Html_StopTime."000);function updatenext(){location.href='".$url."';}</script><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>");
	}

    //播放页
	public function play(){
        $this->load->view('html_play.html');
	}

    //播放页生成操作
	public function play_save(){
        if($this->huri['play']['check']==0){
        	$info['msg'] = '歌曲播放页未开启生成~!';
			$info['url'] = site_url('dance/admin/html/play').'?v='.rand(100,999);
            admin_info($info,2);
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
			$sqlstr="select id from ".CS_SqlPrefix."dance where 1=1 ".$str.$limit;
            $datacount = $this->Csdb->get_allnums($sqlstr); //总数量
	        $pagejs = ceil($datacount/Html_PageNum);
		}
        if($datacount==0) $pagejs=1;

        $pagesize = Html_PageNum;
        if($datacount < $pagesize){
	        $pagesize = $datacount;
        }

        //全部生成完毕
        if($page>$pagejs){
        	$info['msg'] = '所有播放页全部生成完毕~!';
			$info['url'] = site_url('dance/admin/html/play').'?v='.rand(100,999);
            admin_info($info,1);
		}

        //公众URI
		$uri='?ac='.$ac.'&day='.$day.'&cid='.$cid.'&ids='.$ids.'&newid='.$newid.'&ksid='.$ksid.'&jsid='.$jsid.'&kstime='.$kstime.'&jstime='.$jstime.'&pagesize='.$pagesize.'&pagejs='.$pagejs.'&datacount='.$datacount;

        echo '<link href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><script src="'.base_url().'packs/js/jquery.min.js"></script>';
        echo '<b style="font-size:14px;">正在开始生成歌曲播放,分<font color=red>'.$pagejs.'</font>次生成，当前第<font color=red>'.$page.'</font>次</b><br/><br>';
        ob_flush();flush();

        //查询语句
        $sql_string="select * from ".CS_SqlPrefix."dance where 1=1 ".$str." order by id desc";
        $sql_string.=' limit '. $pagesize*($page-1) .','. $pagesize;
		$query = $this->db->query($sql_string);
		//开始生成
        foreach ($query->result_array() as $row) {
			$id=$row['id'];
			//获取静态路径
			$Htmllinks = LinkUrl('play','id',$row['id'],0,'dance',$row['name']);
			//转换成生成路径
			$Htmllink = adminhtml($Htmllinks,'dance');

			//获取当前分类下二级分类ID
			$arr['cid']=getChild($row['cid']);
			$arr['uid']=$row['uid'];
			$arr['did']=$row['id'];
			$arr['singerid']=$row['singerid'];
			$arr['tags']=$row['tags'];

			//标签加超级连接
			$zdytpl['[dance:tags]'] = tagslink($row['tags']);
			//摧毁部分需要超级链接字段数组
			unset($row['tags']);
			unset($row['hits']);
			unset($row['yhits']);
			unset($row['zhits']);
			unset($row['rhits']);
			unset($row['dhits']);
			unset($row['chits']);
			unset($row['shits']);
			unset($row['xhits']);

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
			if(!file_exists($tplfile)){
        		admin_info($skin.'模板文件不存在~！');
			}
			$tplstr = file_get_contents($tplfile);
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

			//动态人气
			$zdytpl['[dance:hits]'] = "<script src='".hitslink('hits/dt/hits/'.$id,'dance')."'></script>";
			$zdytpl['[dance:yhits]'] = "<script src='".hitslink('hits/dt/yhits/'.$id,'dance')."'></script>";
			$zdytpl['[dance:zhits]'] = "<script src='".hitslink('hits/dt/zhits/'.$id,'dance')."'></script>";
			$zdytpl['[dance:rhits]'] = "<script src='".hitslink('hits/dt/rhits/'.$id,'dance')."'></script>";
			$zdytpl['[dance:dhits]'] = "<script src='".hitslink('hits/dt/dhits/'.$id,'dance')."'></script>";
			$zdytpl['[dance:chits]'] = "<script src='".hitslink('hits/dt/chits/'.$id,'dance')."'></script>";
			$zdytpl['[dance:shits]'] = "<script src='".hitslink('hits/dt/shits/'.$id,'dance')."'></script>";
			$zdytpl['[dance:xhits]'] = "<script src='".hitslink('hits/dt/xhits/'.$id,'dance')."'></script>";

			//增加人气地址
			$hitslink = hitslink('hits/ids/'.$id,'dance');

			//装载模板并输出
			$skin = empty($row['skins']) ? 'play.html' : $row['skins'];
			$Mark_Text = $this->Cstpl->plub_show('dance',$row,$arr,true,$skin,$row['name'],$row['name'],'','',$zdytpl,$hitslink);

            //生成
			write_file(FCPATH.$Htmllink,$Mark_Text);
			echo "<font style=font-size:12pt;>生成歌曲播放:<font color=red>".$row['name']."</font>&nbsp;成功：<a href=".$Htmllinks." target=_blank>".$Htmllinks."</a></font><br/>";
			echo "<script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
			ob_flush();flush();
		}
        if(!empty($ids)){
            $url='javascript:history.back();';
		    $str="<font style=font-size:14pt;>全部生成完毕~!&nbsp;>>>>&nbsp;&nbsp;<a href='".$url."'>如果您的浏览器没有跳转，请点击继续...</a></b>";
		}else{ 
	        $url=site_url('dance/admin/html/play_save').$uri.'&page='.($page+1);
		    $str="<b style=font-size:14px;>暂停".Html_StopTime."秒后继续下一页&nbsp;>>>>&nbsp;&nbsp;<a href='".$url."'>如果您的浏览器没有跳转，请点击继续...</a></b>";
		}
		echo("</br>".$str."<script>setTimeout('updatenext();',".Html_StopTime."000);function updatenext(){location.href='".$url."';}</script><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>");
	}

    //下载页
	public function down(){
        $this->load->view('html_down.html');
	}

    //下载页生成操作
	public function down_save(){
        if($this->huri['down']['check']==0){
			$info['url'] = site_url('dance/admin/html/down').'?v='.rand(100,999);
        	$info['msg'] = '歌曲下载页未开启生成~!';
			admin_info($info,2);
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
        	$str.=' and cid in ('.$cid.')';
		}
        if(!empty($ids)){
        	$str.=' and id in ('.$ids.')';
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
			$sqlstr="select id from ".CS_SqlPrefix."dance where 1=1 ".$str.$limit;
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
			$info['msg'] = '歌曲下载页全部生成完毕~!';
			$info['url'] = site_url('dance/admin/html/down').'?v='.rand(100,999);
			admin_info($info,1);
		}

        //公众URI
		$uri='?ac='.$ac.'&day='.$day.'&cid='.$cid.'&ids='.$ids.'&newid='.$newid.'&ksid='.$ksid.'&jsid='.$jsid.'&kstime='.$kstime.'&jstime='.$jstime.'&pagesize='.$pagesize.'&pagejs='.$pagejs.'&datacount='.$datacount;

        echo '<link href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><script src="'.base_url().'packs/js/jquery.min.js"></script><style>b{font-size:14px;}</style>';
		echo "<b>正在开始生成歌曲下载,分<font color=red>".$pagejs."</font>次生成，当前第<font color=red>".$page."</font>次</b><br/><br>";

        $sql_string="select * from ".CS_SqlPrefix."dance where 1=1 ".$str." order by id desc";
        $sql_string.=' limit '. $pagesize*($page-1) .','. $pagesize;
		$query = $this->db->query($sql_string);
		$res_temp = $query->result_array();
        foreach ($res_temp as $row) {
			$id=$row['id'];
			//获取静态路径
			$Htmllinks=LinkUrl('down','id',$row['id'],0,'dance',$row['name']);
			//转换成生成路径
			$Htmllink=adminhtml($Htmllinks,'dance');

			//获取当前分类下二级分类ID
			$arr['cid']=getChild($row['cid']);
			$arr['uid']=$row['uid'];
			$arr['singerid']=$row['singerid'];
			$arr['tags']=$row['tags'];

			//标签加超级连接
			$zdy['[dance:tags]'] = tagslink($row['tags']);
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
			if(defined('MOBILE')){
				$tplfile = VIEWPATH.'mobile'.FGF.'skins'.FGF.Mobile_Skins_Dir.FGF.PLUBPATH.FGF.$skin;
			}else{
				$tplfile = VIEWPATH.'pc'.FGF.'skins'.FGF.Pc_Skins_Dir.FGF.PLUBPATH.FGF.$skin;
			}
			if(!file_exists($tplfile)){
        		admin_info($skin.'模板文件不存在~！');
			}
			$tplstr = file_get_contents($tplfile);
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
			//动态人气
			$zdy['[dance:hits]'] = "<script src='".hitslink('hits/dt/hits/'.$id,'dance')."'></script>";
			$zdy['[dance:yhits]'] = "<script src='".hitslink('hits/dt/yhits/'.$id,'dance')."'></script>";
			$zdy['[dance:zhits]'] = "<script src='".hitslink('hits/dt/zhits/'.$id,'dance')."'></script>";
			$zdy['[dance:rhits]'] = "<script src='".hitslink('hits/dt/rhits/'.$id,'dance')."'></script>";
			$zdy['[dance:dhits]'] = "<script src='".hitslink('hits/dt/dhits/'.$id,'dance')."'></script>";
			$zdy['[dance:chits]'] = "<script src='".hitslink('hits/dt/chits/'.$id,'dance')."'></script>";
			$zdy['[dance:shits]'] = "<script src='".hitslink('hits/dt/shits/'.$id,'dance')."'></script>";
			$zdy['[dance:xhits]'] = "<script src='".hitslink('hits/dt/xhits/'.$id,'dance')."'></script>";
			//摧毁部分需要超级链接字段数组
			unset($row['tags']);
			unset($row['hits']);
			unset($row['yhits']);
			unset($row['zhits']);
			unset($row['rhits']);
			unset($row['dhits']);
			unset($row['chits']);
			unset($row['shits']);
			unset($row['xhits']);
			//装载模板并输出
	        $Mark_Text = $this->Cstpl->plub_show('dance',$row,$arr,true,'down.html',$row['name'],$row['name'],'','',$zdy);

			//生成
			write_file(FCPATH.$Htmllink,$Mark_Text);
			echo "<font style=font-size:12pt;>生成歌曲下载:<font color=red>".$row['name']."</font>&nbsp;成功：<a href=".$Htmllinks." target=_blank>".$Htmllinks."</a></font><br/>";
			echo "<script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
			ob_flush();flush();
		}
        if(!empty($ids)){
            $url='javascript:history.back();';
		    $str="<b>全部生成完毕~!&nbsp;>>>>&nbsp;&nbsp;<a href='".$url."'>如果您的 浏览器没有跳转，请点击继续...</a></b>";
		}else{ 
	        $url=site_url('dance/admin/html/down_save').$uri.'&page='.($page+1);
		    $str="<b>暂停".Html_StopTime."秒后继续下一页&nbsp;>>>>&nbsp;&nbsp;<a href='".$url."'>如果您的 浏览器没有跳转，请点击继续...</a></b>";
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
		        	$Htmllink = Web_Path.'opt/'.$t.'_dance.html';
		        }else{
		        	$Htmllink = Web_Path.Html_Wap_Dir.'/opt/'.$t.'_dance.html';
		        }

				//生成
				write_file(FCPATH.$Htmllink,$Mark_Text);
				echo "<font color=blue>生成自定义页面：<a style=\"color:red\" href=".$Htmllink." target=_blank>".$Htmllink."</a><font color=green>&nbsp;&nbsp;成功！</font></font><br/><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
				ob_flush();flush();
			}
		}
	    $info['url'] = site_url('dance/admin/html/opt');
	    $info['msg'] = '自定义页面全部生成完毕~!';
	    admin_info($info,1);
	}

    //获取排序名称
	public function gettype($fid){
	    $str="ID";
        switch($fid){
			case 'id':$str="ID";break;
			case 'dance':$str=L('plub_53');break;
			case 'reco':$str=L('plub_54');break;
			case 'hits':$str=L('plub_55');break;
			case 'yhits':$str=L('plub_56');break;
			case 'zhits':$str=L('plub_57');break;
			case 'rhits':$str=L('plub_58');break;
			case 'shits':$str=L('plub_59');break;
			case 'xhits':$str=L('plub_60');break;
			case 'yue':$str=L('plub_61');break;
			case 'zhou':$str=L('plub_62');break;
			case 'fav':$str=L('plub_59');break;
			case 'down':$str=L('plub_60');break;
			case 'ri':$str=L('plub_58');break;
			case 'dhits':$str=L('plub_63');break;
			case 'chits':$str=L('plub_64');break;
			case 'ding':$str=L('plub_64');break;
			case 'cai':$str=L('plub_64');break;
		}
		return $str;
	}
}
