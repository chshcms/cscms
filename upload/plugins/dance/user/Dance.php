<?php if ( ! defined('CSCMS')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-08
 */
class Dance extends Cscms_Controller {

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
		$tpl='dance.html';
		//URL地址
	    $url='dance/index/'.$cid;
        $sqlstr = "select {field} from ".CS_SqlPrefix."dance where uid=".$_SESSION['cscms__id'];
        if($cid>0){
			$cids = getChild($cid);
            $sqlstr .= " and cid in(".$cids.")";
		}
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title='我的歌曲 - 会员中心';
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];
		$zdy['[dance:cid]'] = $cid;
        $this->Cstpl->user_list($row,$url,$page,$tpl,$title,$cid,$sqlstr,$ids,false,'user',$zdy);
	}

    //待审核
	public function verify($cid=0,$page=1){
	    $cid=intval($cid); //分类ID
	    $page=intval($page); //分页
		//模板
		$tpl='verify.html';
		//URL地址
	    $url='dance/verify/'.$cid;
        $sqlstr = "select {field} from ".CS_SqlPrefix."dance_verify where uid=".$_SESSION['cscms__id'];
        if($cid>0){
			$cids=getChild($cid);
            $sqlstr.= " and cid in(".$cids.")";
		}
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title='待审歌曲 - 会员中心';
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];

		$zdy['[dance:cid]'] = $cid;
        $this->Cstpl->user_list($row,$url,$page,$tpl,$title,$cid,$sqlstr,$ids,false,'user',$zdy);
	}

	//上传歌曲
	public function add(){
		//模板
		$tpl='add.html';
		//URL地址
	    $url='dance/add';
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];

		//检测发表权限
		$rowz=$this->Csdb->get_row('userzu','aid,sid',$row['zid']);
		if(!$rowz || $rowz->aid==0){
             msg_url('您所在会员组没有权限发表歌曲~!','javascript:history.back();');
		}
		
		//装载模板
		$title='上传歌曲 - 会员中心';
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];

		$zdy['[user:token]'] = get_token('dance_token');
		$zdy['[user:dancesave]'] = spacelink('dance,save','dance');
        $this->Cstpl->user_list($row,$url,1,$tpl,$title,'','',$ids,false,'user',$zdy);
	}

	//上传歌曲保存
	public function save(){
		$token=$this->input->post('token', TRUE);
		if(!get_token('dance_token',1,$token)) msg_url('非法提交~!','javascript:history.back();');

		//检测发表权限
		$zuid=getzd('user','zid',$_SESSION['cscms__id']);
		$rowu=$this->Csdb->get_row('userzu','aid,sid',$zuid);
		if(!$rowu || $rowu->aid==0){
             msg_url('您所在会员组没有权限发表歌曲~!','javascript:history.back();');
		}
		//检测发表数据是否需要审核
		$table = $rowu->sid==1 ? 'dance' : 'dance_verify';

        //必填字段
		$music['name']=$this->input->post('name', TRUE);
		$music['cid']=intval($this->input->post('cid'));
		$music['purl']=$this->input->post('purl', TRUE, TRUE);

        //检测必须字段
		if($music['cid']==0) msg_url('请选择歌曲分类~!','javascript:history.back();');
		if(empty($music['name'])) msg_url('歌曲名称不能为空~!','javascript:history.back();');
		if(empty($music['purl'])) msg_url('歌曲地址不能为空~!','javascript:history.back();');

		//选填字段
		$music['tid']=intval($this->input->post('tid'));
		$music['cion']=intval($this->input->post('cion'));
		$music['text']=remove_xss(str_replace("\r\n","<br>",$_POST['text']));
		$music['lrc']=$this->input->post('lrc', TRUE, TRUE);
		$music['pic']=$this->input->post('pic', TRUE, TRUE);
		$music['tags']=$this->input->post('tags', TRUE, TRUE);
		$music['zc']=$this->input->post('zc', TRUE, TRUE);
		$music['zq']=$this->input->post('zq', TRUE, TRUE);
		$music['bq']=$this->input->post('bq', TRUE, TRUE);
		$music['hy']=$this->input->post('hy', TRUE, TRUE);
		$music['durl']=$music['purl'];
		$music['uid']=$_SESSION['cscms__id'];
		$music['addtime']=time();

		$singer=$this->input->post('singer', TRUE, TRUE);
		//判断歌手是否存在
		if(!empty($singer) && $this->db->table_exists(CS_SqlPrefix.'singer')){
		     $row=$this->Csdb->get_row('singer','id',$singer,'name');
			 if($row){
                   $music['singerid']=$row->id;
			 }
		}
		//获取大小、音质、时长
		if(substr($music['purl'],0,7)!='http://' && UP_Mode==1){
			 if(UP_Pan==''){
                  $filename=FCPATH.$music['purl'];
			 }else{
                  $filename=UP_Pan.$music['purl'];
			 }
			 if(file_exists($filename)){
	             $this->load->library('mp3file');
		         $arr = $this->mp3file->get_metadata($filename);
				 $music['dx']=!empty($arr['Filesize'])?formatsize($arr['Filesize']):'';
	             $music['yz']=!empty($arr['Bitrate'])?$arr['Bitrate'].' Kbps':'';
	             $music['sc']=!empty($arr['Length mm:ss'])?$arr['Length mm:ss']:'';
             }
		}
        //增加到数据库
        $did=$this->Csdb->get_insert($table,$music);
		if(intval($did)==0){
			 msg_url('歌曲发布失败，请稍候再试~!','javascript:history.back();');
		}

        //摧毁token
        get_token('dance_token',2);

		//增加动态
	    $dt['dir'] = 'dance';
	    $dt['uid'] = $_SESSION['cscms__id'];
	    $dt['did'] = $did;
	    $dt['yid'] = $table == 'dance' ? 0 : 1;
	    $dt['title'] = '发布了歌曲';
	    $dt['name'] = $music['name'];
	    $dt['link'] = linkurl('play','id',$did,1,'dance');
	    $dt['addtime'] = time();
        $this->Csdb->get_insert('dt',$dt);

		//如果免审核，则给会员增加相应金币、积分
		if($table=='dance'){
		     $addhits=getzd('user','addhits',$_SESSION['cscms__id']);
			 if($addhits<User_Nums_Add){
                 $this->db->query("update ".CS_SqlPrefix."user set cion=cion+".User_Cion_Add.",jinyan=jinyan+".User_Jinyan_Add.",addhits=addhits+1 where id=".$_SESSION['cscms__id']."");
			 }
			 msg_url('恭喜您，歌曲发布成功~!',spacelink('dance','dance'));
		}else{
			 msg_url('恭喜您，歌曲发布成功,请等待管理员审核~!',spacelink('dance/verify','dance'));
		}
	}

	//选择歌手列表
	public function singer($page=1){
		if(!$this->db->table_exists(CS_SqlPrefix.'singer')){
			exit('歌手板块不存在~!');
		}
	    $page=intval($page); //分页
		//模板
		$tpl='singer.html';
		//URL地址
	    $url='dance/singer';
        $sqlstr = "SELECT * FROM ".CS_SqlPrefix."singer where uid=".$_SESSION['cscms__id'];
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title='选择歌手 - 会员中心';
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];
		$zdy['[singer:searchlink]'] = site_url('dance/user/dance/search');
		$zdy['[singer:no]'] = '您的名下没有歌手记录，可以搜索全站歌手！';
        $this->Cstpl->user_list($row,$url,$page,$tpl,$title,'',$sqlstr,$ids,false,'user',$zdy);
	}

	//歌手搜索列表
	public function search(){
		if(!$this->db->table_exists(CS_SqlPrefix.'singer')){
			exit('歌手板块不存在~!');
		}
	    $page = (int)$this->input->get_post('page');
	    $page = ($page==0)?1:$page;
	    if(empty($key)) $key=$this->input->get_post('key',true);
		//模板
		$tpl='singer.html';
		//URL地址
	    $url='dance/search?key='.$key.'&page=';
        $sqlstr = "SELECT * FROM ".CS_SqlPrefix."singer where name like '%".$key."%'";
		//当前会员
	    $row=$this->Csdb->get_row_arr('user','*',$_SESSION['cscms__id']);
		if(empty($row['nichen'])) $row['nichen']=$row['name'];
		//装载模板
		$title='选择歌手 - 会员中心';
		$ids['uid']=$_SESSION['cscms__id'];
		$ids['uida']=$_SESSION['cscms__id'];
		$zdy['[singer:searchlink]'] = site_url('dance/user/dance/search');
		$zdy['[singer:no]'] = '未查询到歌手记录！';
        $this->Cstpl->user_list($row,$url,$page,$tpl,$title,'',$sqlstr,$ids,false,'user',$zdy);
	}
}
