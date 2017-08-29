<?php if ( ! defined('CSCMS')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-08
 */
class News extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	    $this->load->model('Csuser');
        $this->Csuser->User_Login();
		$this->load->helper('string');
	}

    //已审核
	public function index($cid=0,$page=1){
	    $cid=intval($cid); //分类ID
	    $page=intval($page); //分页
		//模板
		$tpl='news.html';
		//URL地址
	    $url='news/index/'.$cid;
        $sqlstr = "select {field} from ".CS_SqlPrefix."news where uid=".$_SESSION['cscms__id'];
        if($cid>0){
			$cids = getChild($cid);
			if(is_numeric($cids)){
            	$sqlstr.= " and cid=".$cids;
			}else{
            	$sqlstr.= " and cid in(".$cids.")";
			}
		}
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title='我的文章 - 会员中心';
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];

		$zdy['[news:cid]'] = $cid;
        $this->Cstpl->user_list($row,$url,$page,$tpl,$title,$cid,$sqlstr,$ids,false,'user',$zdy);
	}

    //待审核
	public function verify($cid=0,$page=1){
	    $cid=intval($cid); //分类ID
	    $page=intval($page); //分页
		//模板
		$tpl='verify.html';
		//URL地址
	    $url='news/verify/'.$cid;
        $sqlstr = "select {field} from ".CS_SqlPrefix."news_verify where uid=".$_SESSION['cscms__id'];
        if($cid>0){
			$cids = getChild($cid);
			if(is_numeric($cids)){
            	$sqlstr.= " and cid=".$cids;
			}else{
            	$sqlstr.= " and cid in(".$cids.")";
			}
		}
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title='待审文章 - 会员中心';
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];

		$zdy['[news:cid]'] = $cid;
        $this->Cstpl->user_list($row,$url,$page,$tpl,$title,$cid,$sqlstr,$ids,false,'user',$zdy);
	}

	//分享文章
	public function add(){
		//模板
		$tpl='add.html';
		//URL地址
	    $url='news/add';
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];

		//检测发表权限
		$rowz=$this->Csdb->get_row('userzu','aid,sid',$row['zid']);
		if(!$rowz || $rowz->aid==0){
             msg_url('您所在会员组没有权限发表文章~!','javascript:history.back();');
		}
		
		//装载模板
		$title='文章投稿 - 会员中心';
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];

		$zdy['[user:token]'] = get_token('news_token');
		$zdy['[user:newssave]'] = spacelink('news,save','news');

        $this->Cstpl->user_list($row,$url,1,$tpl,$title,'','',$ids,false,'user',$zdy);
	}

	//上传文章保存
	public function save(){
		$token=$this->input->post('token', TRUE);
		if(!get_token('news_token',1,$token)) msg_url('非法提交~!','javascript:history.back();');
		//检测发表权限
		$zuid=getzd('user','zid',$_SESSION['cscms__id']);
		$rowu=$this->Csdb->get_row('userzu','aid,sid',$zuid);
		if(!$rowu || $rowu->aid==0){
             msg_url('您所在会员组没有权限发表文章~!','javascript:history.back();');
		}
		//检测发表数据是否需要审核
		$table=($rowu->sid==1)?'news':'news_verify';
		//选填字段
		$news['cion']=intval($this->input->post('cion'));
		$news['pic']=$this->input->post('pic', TRUE, TRUE);
		$news['tags']=$this->input->post('tags', TRUE, TRUE);
		$news['info']=$this->input->post('info', TRUE, TRUE);
		$news['uid']=$_SESSION['cscms__id'];
		$news['addtime']=time();
        //必填字段
		$news['name']=$this->input->post('name', TRUE, TRUE);
		$news['cid']=intval($this->input->post('cid'));
		$news['content']=remove_xss($this->input->post('content'));
        //检测必须字段
		if($news['cid']==0) msg_url('请选择文章分类~!','javascript:history.back();');
		if(empty($news['name'])) msg_url('文章名称不能为空~!','javascript:history.back();');
		if(empty($news['content'])) msg_url('文章内容不能为空~!','javascript:history.back();');
        //截取概述
		$news['info'] = sub_str(str_checkhtml($news['content']),120);
        //增加到数据库
        $did=$this->Csdb->get_insert($table,$news);
		if(intval($did)==0){
			 msg_url('文章发布失败，请稍候再试~!','javascript:history.back();');
		}
        //摧毁token
        get_token('news_token',2);
		//增加动态
	    $dt['dir'] = 'news';
	    $dt['uid'] = $_SESSION['cscms__id'];
	    $dt['did'] = $did;
	    $dt['yid'] = ($rowu->sid==1)?0:1;
	    $dt['title'] = '发布了文章';
	    $dt['name'] = $news['name'];
	    $dt['link'] = linkurl('show','id',$did,1,'news');
	    $dt['addtime'] = time();
        $this->Csdb->get_insert('dt',$dt);
		//如果免审核，则给会员增加相应金币、积分
		if($table=='news'){
		     $addhits=getzd('user','addhits',$_SESSION['cscms__id']);
			 if($addhits<User_Nums_Add){
                 $this->db->query("update ".CS_SqlPrefix."user set cion=cion+".User_Cion_Add.",jinyan=jinyan+".User_Jinyan_Add.",addhits=addhits+1 where id=".$_SESSION['cscms__id']."");
			 }
			 msg_url('恭喜您，文章发布成功~!',spacelink('news','news'));
		}else{
			 msg_url('恭喜您，文章发布成功,请等待管理员审核~!',spacelink('news/verify','news'));
		}
	}
}

