<?php if ( ! defined('CSCMS')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-08
 */
class Singer extends Cscms_Controller {

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
		$tpl='singer.html';
		//URL地址
	    $url='singer/index/'.$cid;
        $sqlstr = "select {field} from ".CS_SqlPrefix."singer where uid=".$_SESSION['cscms__id'];
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
		$title='我的歌手 - 会员中心';
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];
		$zdy['[singer:cid]'] = $cid;
        $this->Cstpl->user_list($row,$url,$page,$tpl,$title,$cid,$sqlstr,$ids,false,'user',$zdy);
	}

    //待审核
	public function verify($cid=0,$page=1){
	    $cid=intval($cid); //分类ID
	    $page=intval($page); //分页
		//模板
		$tpl='verify.html';
		//URL地址
	    $url='singer/verify/'.$cid;
        $sqlstr = "select {field} from ".CS_SqlPrefix."singer_verify where uid=".$_SESSION['cscms__id'];
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
		$title='待审歌手 - 会员中心';
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];

		$zdy['[singer:cid]'] = $cid;
        $this->Cstpl->user_list($row,$url,$page,$tpl,$title,$cid,$sqlstr,$ids,false,'user',$zdy);
	}

	//新增歌手
	public function add(){
		$id = (int)$this->input->get('id',true);
		$yid = (int)$this->input->get('yid',true);
		//模板
		$tpl='add.html';
		//URL地址
	    $url='singer/add';
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];

		//检测发表权限
		$rowz=$this->Csdb->get_row('userzu','aid,sid',$row['zid']);
		if(!$rowz || $rowz->aid==0){
             msg_url('您所在会员组没有权限发表歌手~!','javascript:history.back();');
		}
		$zdy['[singer:id]'] = $id;
		if($id==0){
			$title='新增歌手 - 会员中心';
			$zdy['[singer:name]'] = '';
			$zdy['[singer:bname]'] = '';
			$zdy['[singer:cid]'] = 0;
			$zdy['[singer:pic]'] = '';
			$zdy['[singer:height]'] = '';
			$zdy['[singer:weight]'] = '';
			$zdy['[singer:sex]'] = '';
			$zdy['[singer:sr]'] = '';
			$zdy['[singer:nat]'] = '';
			$zdy['[singer:yuyan]'] = '';
			$zdy['[singer:xingzuo]'] = '';
			$zdy['[singer:city]'] = '';
			$zdy['[singer:tags]'] = '';
			$zdy['[singer:content]'] = '';
		}else{
			$table = ($yid==1)?'singer_verify':'singer';
			$singer = $this->Csdb->get_row($table,'*',$id);
			if(!$singer || $singer->uid!=$_SESSION['cscms__id']) msg_url('歌手不存在或者没有操作权限~!','javascript:history.back();');
			//自定义字段
			$row['cscms_field'] = $singer;
			$title = '编辑歌手 - 会员中心';
			$zdy['[singer:name]'] = $singer->name;
			$zdy['[singer:bname]'] = $singer->bname;
			$zdy['[singer:cid]'] = $singer->cid;
			$zdy['[singer:pic]'] = $singer->pic;
			$zdy['[singer:height]'] = $singer->height;
			$zdy['[singer:weight]'] = $singer->weight;
			$zdy['[singer:sex]'] = $singer->sex;
			$zdy['[singer:sr]'] = $singer->sr;
			$zdy['[singer:nat]'] = $singer->nat;
			$zdy['[singer:yuyan]'] = $singer->yuyan;
			$zdy['[singer:xingzuo]'] = $singer->xingzuo;
			$zdy['[singer:city]'] = $singer->city;
			$zdy['[singer:tags]'] = $singer->tags;
			$zdy['[singer:content]'] = $singer->content;
		}
		//装载模板
		$ids['uid']=$_SESSION['cscms__id'];

		$zdy['[user:token]'] = get_token('singer_token');
		$zdy['[user:singersave]'] = spacelink('singer,save','singer');
		$zdy['[singer:yid]'] = $yid;
        $this->Cstpl->user_list($row,$url,1,$tpl,$title,'','',$ids,false,'user',$zdy);
	}

	//上传歌手保存
	public function save(){
		$token=$this->input->post('token', TRUE);
		$yid = $this->input->post('yid',true);
		if(!get_token('singer_token',1,$token)) msg_url('非法提交~!','javascript:history.back();');

		//检测发表权限
		$zuid=getzd('user','zid',$_SESSION['cscms__id']);
		$rowu=$this->Csdb->get_row('userzu','aid,sid',$zuid);
		if(!$rowu || $rowu->aid==0){
            msg_url('您所在会员组没有权限操作歌手~!','javascript:history.back();');
		}
		//检测发表数据是否需要审核
		if($rowu->sid==1 && $yid==0){
			$table = 'singer';
		}else{
			$table = 'singer_verify';
		}

        //必填字段
		$add['name'] = $this->input->post('name', TRUE);
		$add['cid'] = (int)$this->input->post('cid',TRUE);
		$add['pic'] = $this->input->post('pic', TRUE);

        //检测必须字段
		if($add['cid']==0) msg_url('请选择歌手分类~!','javascript:history.back();');
		if(empty($add['name'])) msg_url('歌手名称不能为空~!','javascript:history.back();');
		if(empty($add['pic'])) msg_url('歌曲图片不能为空~!','javascript:history.back();');
		
		//选填字段
		$add['bname'] = $this->input->post('bname',true);
		$add['height'] = $this->input->post('height');
		$add['weight'] = $this->input->post('weight');
		$add['sex'] = $this->input->post('sex', true);
		$add['sr'] = $this->input->post('sr', TRUE);
		$add['nat'] = $this->input->post('nat', TRUE, TRUE);
		$add['yuyan'] = $this->input->post('yuyan', TRUE, TRUE);
		$add['xingzuo'] = $this->input->post('xingzuo', TRUE, TRUE);
		$add['city'] = $this->input->post('city', TRUE, TRUE);
		$add['tags'] = $this->input->post('tags',TRUE);
		$add['addtime'] = time();
		$add['content'] = remove_xss(str_replace("\r\n","<br>",$_POST['content']));

		$id = (int)$this->input->post('id',true);
		//摧毁token
		get_token('dance_token',2);
		if($id==0){
			$singer = $this->db->query("select * from ".CS_SqlPrefix."singer where name='".$add['name']."'")->row();
			//判断歌手是否存在
			if(!$singer){
				$add['uid'] = $_SESSION['cscms__id'];
				$res = $this->Csdb->get_insert($table,$add);
			}else{
				if($singer->uid>0){
					msg_url('抱歉，该歌手已存在！如果该歌手是贵公司名下艺人，请与管理员联系',spacelink('singer','singer'));
				}else{
					$add['uid'] = $_SESSION['cscms__id'];
					$res = $this->Csdb->get_update('singer',$singer->id,$add);
				}
			}
			if($res){
				msg_url('恭喜您，操作成功',spacelink('singer','singer'));
			}else{
				msg_url('抱歉，网络异常，请稍后重试','javascript:history.back()');
			}
		}else{
			$singer = $this->db->query("select * from ".CS_SqlPrefix.$table." where id=".$id)->row();
			if($singer->uid!=$_SESSION['cscms__id']){
				msg_url('抱歉，操作非法','javascript:history.back();');
			}else{
				$res = $this->Csdb->get_update($table,$singer->id,$add);
				$method = $yid==1?'singer/verify':'singer';
				msg_url('恭喜您，操作成功',spacelink($method,'singer'));
			}
		}
	}
	//删除歌手
	public function del(){
		$id = (int)$this->input->post('id',true);
		$yid = (int)$this->input->post('yid',true);
		if($id<1) getjson('抱歉，参数错误');
		$table = ($yid==1)?'singer_verify':'singer';
		$singer = $this->Csdb->get_row_arr($table,'*',$id);
		if($singer && $singer['uid']==$_SESSION['cscms__id']){
			if($yid==1){
				unset($singer['id']);
				$singer['hid'] = 1;
				$this->db->insert('singer_hui',$singer);
				$this->db->delete('singer_verify',array('id'=>$id));
			}else{
				$this->db->update('singer',array('uid'=>0),array('id'=>$id));
			}
			getjson('',0);
		}else{
			getjson('非法操作~！');
		}
	}
}

