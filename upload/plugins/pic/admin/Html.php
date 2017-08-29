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

	    $Mark_Text = $this->Cstpl->plub_index('pic','index.html',true);
		write_file(FCPATH.$index_url,$Mark_Text);

	    $info['msg'] = '恭喜你，板块主页已生成';
		$info['time'] = 2000;
	    $info['url'] = site_url('pic/admin/html').'?v='.rand(1000,1999);
		getjson($info,0);
	}

    //列表页
	public function type(){
        $this->load->view('html_type.html');
	}

    //列表页生成操作
	public function type_save(){
        if($this->huri['lists']['check']==0){
			$info['url'] = site_url('pic/admin/html/type').'?v='.rand(100,999);
        	$info['msg'] = '相册分类未开启生成~!';
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
			$fid=($op=='all')?'id,news,reco,hits,yhits,zhits,rhits,xhits,shits':'id';
		}

        //生成全部分类获取全部分类ID
        if($op=='all' && $nums==0){
			$cid=array();
			$query = $this->db->query("SELECT id FROM ".CS_SqlPrefix."pic_list where yid=0 order by xid asc"); 
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
			$info['url'] = site_url('pic/admin/html/type').'?v='.rand(100,999);
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
			$info['url'] = site_url('pic/admin/html/type').'?v='.rand(100,999);
            admin_info($info,1);
		}

		$id=(int)$arr[$nums]; //当前分类ID
		$type=$arr2[$numx]; //当前排序

		//获取分类信息
		$row = $this->Csdb->get_row_arr('pic_list','*',$id);
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
				$sqlstr="select id from ".CS_SqlPrefix."pic_type where cid=".$ids;
			}else{
				$sqlstr="select id from ".CS_SqlPrefix."pic_type where cid IN (".$ids.")";
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
			$Htmllinks = LinkUrl('lists',$type,$id,$i,'pic',$row['bname']);
			//转换成生成路径
			$Htmllink = adminhtml($Htmllinks,'pic');

			$arri['cid'] = $ids;
			$arri['fid'] = $row['fid']==0 ? $row['id'] : $row['fid'];

			$Mark_Text=$this->Cstpl->plub_list($row,$id,$type,$i,$arri,true,$tpl,'lists','pic',$row['name'],$row['name']);
			write_file(FCPATH.$Htmllink,$Mark_Text);

			echo "<font color=blue >第".$i."页：</font><a target='_blank' href='".$Htmllinks."'>".$Htmllinks."</a><font color=green>&nbsp;&nbsp;生成完毕!</font><br/><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
			$n++;
			ob_flush();flush();usleep(100000);
			if(Html_PageNum==$n){
			   $pagego=2;break;
			}
		}

		if($pagego==2){ //操作系统设置每页数量，分多页生成
			$url=site_url('pic/admin/html/type_save').$uri.'&nums='.$nums.'&numx='.$numx.'&start='.($i+1);
		    exit("</br><b  style=\"font-size:14px;\">暂停".Html_StopTime."秒后继续下一页&nbsp;>>>>&nbsp;&nbsp;<a target='_blank' href='".$url."'>如果您的 浏览器没有跳转，请点击继续...</a></b><script>setTimeout('updatenext();',".Html_StopTime."000);function updatenext(){location.href='".$url."';}</script>");
		}else{  
	        $url=site_url('pic/admin/html/type_save').$uri.'&nums='.($nums+1);
		    $str="<b  style=\"font-size:14px;\">暂停".Html_StopTime."秒后继续&nbsp;>>>>&nbsp;&nbsp;<a target='_blank' href='".$url."'>如果您的 浏览器没有跳转，请点击继续...</a></b>";
        }
        //判断生成完毕
		if(($numx+1)>=$len2){ //全部完成
            $url=site_url('pic/admin/html/type_save').$uri.'&nums='.($nums+1);
			$str="<b  style=\"font-size:14px;\"><font color=#0000ff>所有排序页面全部生成完毕!</font>&nbsp;>>>>&nbsp;&nbsp;<a href='".site_url('pic/admin/html/type_save').$uri.'&nums='.($nums+1)."'>如果您的 浏览器没有跳转，请点击继续...</a></b>";
		}else{ //当前排序方式完成
            $url=site_url('pic/admin/html/type_save').$uri.'&nums='.$nums.'&numx='.($numx+1);
			$str='<b  style=\"font-size:14px;\">按<font color=#0000ff>'.$this->gettype($type).'排序</font>分类生成完毕!&nbsp;>>>>&nbsp;&nbsp;<a href="'.site_url('pic/admin/html/type_save').$uri.'&nums='.$nums.'&numx='.($numx+1).'">如果您的 浏览器没有跳转，请点击继续...</a></b>';
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
			$info['msg'] = '相册内容页未开启生成~!';
			$info['url'] = site_url('pic/admin/html/show').'?v='.rand(100,999);
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
			 $sqlstr = "select id from ".CS_SqlPrefix."pic_type where 1=1 ".$str.$limit;
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
			$info['url'] = site_url('pic/admin/html/show').'?v='.rand(100,999);
            admin_info($info,1);
		}
        //公众URI
		$uri='?ac='.$ac.'&day='.$day.'&cid='.$cid.'&ids='.$ids.'&newid='.$newid.'&ksid='.$ksid.'&jsid='.$jsid.'&kstime='.$kstime.'&jstime='.$jstime.'&pagesize='.$pagesize.'&pagejs='.$pagejs.'&datacount='.$datacount;

        echo '<link href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><script src="'.base_url().'packs/js/jquery.min.js"></script>';
		echo '<b style="font-size:14px;">正在开始生成相册内容,分<font color=red>'.$pagejs.'</font>次生成，当前第<font color=red>'.$page.'</font>次</b><br/></br>';

        $sql_string="select * from ".CS_SqlPrefix."pic_type where 1=1 ".$str." order by id desc";
        $sql_string.=' limit '. $pagesize*($page-1) .','. $pagesize;
		$query = $this->db->query($sql_string);
        foreach ($query->result_array() as $row) {
               
			$id = $row['id'];
			//获取静态路径
			$Htmllinks = LinkUrl('show','id',$row['id'],0,'pic');
			//转换成生成路径
			$Htmllink = adminhtml($Htmllinks,'pic');

			//获取当前分类下二级分类ID
			$arr['cid'] = getChild($row['cid']);
			$arr['uid'] = $row['uid'];
			$arr['tags'] = $row['tags'];
			$arr['sid'] = $row['id'];

			//标签加超级连接
			$zdy['[pic:tags]'] = tagslink($row['tags']);
			unset($row['tags']);

			//默认模板
			$skin = empty($row['skins']) ? 'show.html' : $row['skins'];
			if(defined('MOBILE') && config('Mobile_Is')==1){
				$tplfile = VIEWPATH.'mobile'.FGF.'skins'.FGF.Mobile_Skins_Dir.FGF.PLUBPATH.FGF.$skin;
			}else{
				$tplfile = VIEWPATH.'pc'.FGF.'skins'.FGF.Pc_Skins_Dir.FGF.PLUBPATH.FGF.$skin;
			}
			$tplstr = file_exists($tplfile) ? file_get_contents($tplfile) : '';

			$zdy['[pic:pl]'] = get_pl('pic',$id);
			$zdy['[pic:link]'] = LinkUrl('show','id',$row['id'],1,'pic');
			$zdy['[pic:classlink]'] = LinkUrl('lists','id',$row['cid'],1,'pic');
			$zdy['[pic:classname]'] = getzd('pic_list','name',$row['cid']);

			//获取当前相册总数
			$pcount=$this->db->query("Select id from ".CS_SqlPrefix."pic where sid=".$id." ")->num_rows();
			$zdy['[pic:count]'] = $pcount;
			//第一张图片
			$rowp=$this->db->query("Select pic,content from ".CS_SqlPrefix."pic where sid=".$id." order by id desc limit 1")->row();
			$pics = $rowp ? $rowp->pic : '';
			$content = $rowp ? $rowp->content : '';
			$zdy['[pic:url]'] = piclink('pic',$pics);
			$zdy['[pic:content]'] = $content;

			//获取上下张
			if(strpos($tplstr,'[pic:slink]') !== false || strpos($tplstr,'[pic:sname]') !== false || strpos($tplstr,'[pic:spic]') !== false){
				$rowd=$this->db->query("Select id,cid,pic,name from ".CS_SqlPrefix."pic_type where id<".$id." order by id desc limit 1")->row();
				if($rowd){
					$zdy['[pic:slink]'] = LinkUrl('show','id',$rowd->id,1,'pic');
					$zdy['[pic:spic]'] = piclink('pic',$rowd->pic);
					$zdy['[pic:sid]'] = $rowd->id;
					$zdy['[pic:sname]'] = $rowd->name;
				}else{
					$zdy['[pic:slink]'] = '###';
					$zdy['[pic:spic]'] = piclink('pic','');
					$zdy['[pic:sid]'] = 0;
					$zdy['[pic:sname]'] = '没有了';
				}
			}
			if(strpos($tplstr,'[pic:xlink]') !== false || strpos($tplstr,'[pic:xname]') !== false || strpos($tplstr,'[pic:xpic]') !== false){
				$rowd=$this->db->query("Select id,cid,pic,name from ".CS_SqlPrefix."pic_type where id>".$id." order by id asc limit 1")->row();
				if($rowd){
					$zdy['[pic:xlink]'] = LinkUrl('show','id',$rowd->id,1,'pic');
					$zdy['[pic:xpic]'] = piclink('pic',$rowd->pic);
					$zdy['[pic:xid]'] = $rowd->id;
					$zdy['[pic:xname]'] = $rowd->name;
				}else{
					$zdy['[pic:xlink]'] = '###';
					$zdy['[pic:xpic]'] = piclink('pic','');
					$zdy['[pic:xid]'] = 0;
					$zdy['[pic:xname]'] = '没有了';
				}
			}
			//动态人气
			$zdy['[pic:hits]'] = "<script src='".hitslink('hits/dt/hits/'.$id,'pic')."'></script>";
			$zdy['[pic:yhits]'] = "<script src='".hitslink('hits/dt/yhits/'.$id,'pic')."'></script>";
			$zdy['[pic:zhits]'] = "<script src='".hitslink('hits/dt/zhits/'.$id,'pic')."'></script>";
			$zdy['[pic:rhits]'] = "<script src='".hitslink('hits/dt/rhits/'.$id,'pic')."'></script>";
			$zdy['[pic:dhits]'] = "<script src='".hitslink('hits/dt/dhits/'.$id,'pic')."'></script>";
			$zdy['[pic:chits]'] = "<script src='".hitslink('hits/dt/chits/'.$id,'pic')."'></script>";

			//增加人气链接
			$hitslink = hitslink('hits/ids/'.$id,'pic');
			//摧毁部分需要超级链接字段数组
			unset($row['tags']);
			unset($row['hits']);
			unset($row['yhits']);
			unset($row['zhits']);
			unset($row['rhits']);
			unset($row['dhits']);
			unset($row['chits']);
			unset($row['content']);
			//装载模板并输出
			$Mark_Text=$this->Cstpl->plub_show('pic',$row,$arr,true,$skin,$row['name'],$row['name'],'','',$zdy,$hitslink);
			//生成
			write_file(FCPATH.$Htmllink,$Mark_Text);
			echo "<font color=blue font-size=12px>生成相册:<a href=".$Htmllinks." target=_blank><font color=red>".$row['name'].$Htmllinks."</a></font><font color=green>&nbsp;&nbsp;成功！</font><br/><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
			ob_flush();flush();

		}
        if(!empty($ids)){
            $url='javascript:history.back();';
		    $str="<b style=\"font-size:14px;\">全部生成完毕&nbsp;>>>>&nbsp;&nbsp;<a href='".$url."'>如果您的 浏览器没有跳转，请点击继续...</a></b>";
		}else{ 
	        $url=site_url('pic/admin/html/show_save').$uri.'&page='.($page+1);
		    $str="<b style=\"font-size:14px;\">暂停".Html_StopTime."秒后继续&nbsp;>>>>&nbsp;&nbsp;<a href='".$url."'>如果您的 浏览器没有跳转，请点击继续...</a></b>";
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
		        	$Htmllink = Web_Path.'opt/'.$t.'_pic.html';
		        }else{
		        	$Htmllink = Web_Path.Html_Wap_Dir.'/opt/'.$t.'_pic.html';
		        }

				//生成
				write_file(FCPATH.$Htmllink,$Mark_Text);
				echo "<font color=blue>生成自定义页面：<a style=\"color:red\" href=".$Htmllink." target=_blank>".$Htmllink."</a><font color=green>&nbsp;&nbsp;成功！</font></font><br/><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
				ob_flush();flush();
			}
		}
	    $info['url'] = site_url('pic/admin/html/opt');
	    $info['msg'] = '自定义页面全部生成完毕~!';
	    admin_info($info,1);
	}

    //获取排序名称
	public function gettype($fid){
	    $str="ID";
        switch($fid){
			case 'id':$str="ID";break;
			case 'pic':$str="更新时间";break;
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
