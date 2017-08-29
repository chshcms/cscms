<?php if ( ! defined('CSCMS')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-08
 */
class Vod extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Cstpl');
	    $this->load->model('Csuser');
        $this->Csuser->User_Login();
		$this->load->helper('vod');
		$this->load->helper('string');
	}

    //已审核
	public function index($cid=0,$page=1){
	    $cid=intval($cid); //分类ID
	    $page=intval($page); //分页
		//模板
		$tpl='vod.html';
		//URL地址
	    $url='vod/index/'.$cid;
        $sqlstr = "select {field} from ".CS_SqlPrefix."vod where uid=".$_SESSION['cscms__id'];
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
		$title='我的视频 - 会员中心';
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];

		$zdy['[vod:cid]'] = $cid;
        $this->Cstpl->user_list($row,$url,$page,$tpl,$title,'',$sqlstr,$ids,false,'user',$zdy);
	}

    //待审核
	public function verify($cid=0,$page=1){
	    $cid=intval($cid); //分类ID
	    $page=intval($page); //分页
		//模板
		$tpl='verify.html';
		//URL地址
	    $url='vod/verify/'.$cid;
        $sqlstr = "select {field} from ".CS_SqlPrefix."vod_verify where uid=".$_SESSION['cscms__id'];
        if($cid>0){
			$cids=getChild($cid);
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
		$title='待审视频 - 会员中心';
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];

		$zdy['[vod:cid]'] = $cid;
        $this->Cstpl->user_list($row,$url,$page,$tpl,$title,'',$sqlstr,$ids,false,'user',$zdy);
	}

	//上传视频
	public function add(){
		//模板
		$tpl='add.html';
		//URL地址
	    $url='vod/add';
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];

		//检测发表权限
		$rowz=$this->Csdb->get_row('userzu','aid,sid',$row['zid']);
		if(!$rowz || $rowz->aid==0){
             msg_url('您所在会员组没有权限发表视频~!','javascript:history.back();');
		}
		
		//装载模板
		$title='上传视频 - 会员中心';
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];

		$zdy['[user:token]'] = get_token('vod_token');
		$zdy['[user:vodsave]'] = spacelink('vod,save','vod');
        $this->Cstpl->user_list($row,$url,1,$tpl,$title,'','',$ids,false,'user',$zdy);
	}

	//上传视频保存
	public function save(){
		$token=$this->input->post('token', TRUE);
		if(!get_token('vod_token',1,$token)) msg_url('非法提交~!','javascript:history.back();');

		//检测发表权限
		$zuid=getzd('user','zid',$_SESSION['cscms__id']);
		$rowu=$this->Csdb->get_row('userzu','aid,sid',$zuid);
		if(!$rowu || $rowu->aid==0){
             msg_url('您所在会员组没有权限发表视频~!','javascript:history.back();');
		}

		//检测发表数据是否需要审核
		$table = $rowu->sid==1 ? 'vod' : 'vod_verify';

		//选填字段
		$vod['cion']=intval($this->input->post('cion'));
		$vod['dcion']=intval($this->input->post('dcion'));
		$vod['text']=remove_xss(str_replace("\r\n","<br>",$_POST['text']));
		$vod['pic']=$this->input->post('pic', TRUE, TRUE);
		$vod['tags']=$this->input->post('tags', TRUE, TRUE);
		$vod['daoyan']=$this->input->post('daoyan', TRUE, TRUE);
		$vod['zhuyan']=$this->input->post('zhuyan', TRUE, TRUE);
		$vod['yuyan']=$this->input->post('yuyan', TRUE, TRUE);
		$vod['diqu']=$this->input->post('diqu', TRUE, TRUE);
		$vod['year']=$this->input->post('year', TRUE, TRUE);
		$vod['info']=$this->input->post('info', TRUE, TRUE);
		$vod['uid']=$_SESSION['cscms__id'];
		$vod['addtime']=time();
		$down=$this->input->post('down', TRUE, TRUE);
		$durl=$this->input->post('durl', TRUE, TRUE);

        //必填字段
		$vod['name']=$this->input->post('name', TRUE, TRUE);
		$vod['cid']=intval($this->input->post('cid'));
		$play=$this->input->post('play', TRUE, TRUE);
		$purl=$this->input->post('purl', TRUE, TRUE);

        //检测必须字段
		if($vod['cid']==0) msg_url('请选择视频分类~!','javascript:history.back();');
		if(empty($vod['name'])) msg_url('视频名称不能为空~!','javascript:history.back();');
		if(empty($play)) msg_url('视频播放来源不能为空~!','javascript:history.back();');
		if(empty($purl)) msg_url('视频播放地址不能为空~!','javascript:history.back();');

		//播放地址组合
		if($play!='flv' && $play!='media' && $play!='swf'){
		    if(substr($purl,0,7)!='http://') msg_url('视频播放地址不正确~!','javascript:history.back();');
		    $arr=caiji($purl,1);
			$form=$arr['laiy'];
			$purl=$arr['url'];
			if(empty($vod['pic'])) $vod['pic']=$arr['pic'];
			$vod['purl']='第01集$'.$purl.'$'.$form;
		}else{
			$vod['purl']='第01集$'.$purl.'$'.$play;
		}

		//下载地址组合
		if(!empty($down) && !empty($durl)){
		    $vod['durl']='第01集$'.$durl.'$'.$down;
		}

		$singer=$this->input->post('singer', TRUE, TRUE);
		//判断歌手是否存在
		if(!empty($singer) && $this->db->table_exists(CS_SqlPrefix.'singer')){
		    $row=$this->Csdb->get_row('singer','id',$singer,'name');
			if($row){
                $vod['singerid']=$row->id;
			}
		}

        //增加到数据库
        $did=$this->Csdb->get_insert($table,$vod);
		if(intval($did)==0){
			msg_url('视频发布失败，请稍候再试~!','javascript:history.back();');
		}

        //摧毁token
        get_token('vod_token',2);

		//增加动态
	    $dt['dir'] = 'vod';
	    $dt['uid'] = $_SESSION['cscms__id'];
	    $dt['did'] = $did;
	    $dt['yid'] = $table == 'vod' ? 0 : 1;
	    $dt['title'] = '发布了视频';
	    $dt['name'] = $vod['name'];
	    $dt['link'] = linkurl('show','id',$did,1,'vod');
	    $dt['addtime'] = time();
        $this->Csdb->get_insert('dt',$dt);

		//如果免审核，则给会员增加相应金币、积分
		if($table == 'vod'){
		     $addhits=getzd('user','addhits',$_SESSION['cscms__id']);
			 if($addhits<User_Nums_Add){
                $this->db->query("update ".CS_SqlPrefix."user set cion=cion+".User_Cion_Add.",jinyan=jinyan+".User_Jinyan_Add.",addhits=addhits+1 where id=".$_SESSION['cscms__id']."");
			 }
			 msg_url('恭喜您，视频发布成功~!',spacelink('vod','vod'));
		}else{
			 msg_url('恭喜您，视频发布成功,请等待管理员审核~!',spacelink('vod/verify','vod'));
		}
	}
}