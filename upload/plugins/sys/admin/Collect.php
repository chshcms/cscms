<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2008-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-11-13
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Collect extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csadmin');
		$this->lang->load('admin_collect');
        $this->Csadmin->Admin_Login();
        $this->load->library('caiji');
	}

    //规则列表
	public function index(){
	    $page = intval($this->input->get('page'));
	    $dir = $this->input->get('dir',true,true);
        if($page==0) $page=1;

        if($dir){
             $sql_string = "SELECT id,name,dir,addtime FROM ".CS_SqlPrefix."caiji where dir='".$dir."' order by id desc";
		}else{
             $sql_string = "SELECT id,name,dir,addtime FROM ".CS_SqlPrefix."caiji order by id desc";
		}
        $count_sql = str_replace('id,name,dir,addtime','count(*) as count',$sql_string);
        $query = $this->db->query($count_sql)->result_array();
        $total = $query[0]['count'];

        $base_url = site_url('collect/index').'?dir='.$dir;
        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        $totalPages = ($totalPages>0)?$totalPages:1;
        $page = ($page>$totalPages)?$totalPages:$page;
        $data['nums'] = $total;
        if($total<$per_page){
              $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);

        $data['collect'] = $query->result();
        $base_url = site_url('collect/index').'?dir='.$dir.'&page=';
        $data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('collect.html',$data);
	}

    //新增
	public function add(){
        $id = intval($this->input->get_post('id'));
        $page = intval($this->input->get('page'));
		$html = $this->input->post('html');
        $data['page']=$page;
        $data['id']=$id;
		$data['savelink']=site_url('collect/add').'?id='.$id;

		if($id>0){
            $row = $this->db->query("SELECT * FROM ".CS_SqlPrefix."caiji where id='".$id."'")->row();
            if(!$row){
                exit(L('plub_01'));//记录不存在
            }
			//自动补充网站地址
            $this->caiji->weburl($row->url);
		}

        if($page==0 || $page==1){
            if($id==0){
                $data['name']="";
                $data['url']="";
                $data['code']="utf-8";
                $data['dir']="";
                $data['cjurl']="";
                $data['ksid']=1;
                $data['jsid']=1;
                $data['cfid']=0;
                $data['picid']=0;
                $data['dxid']=0;
                $data['rkid']=0;
			}else{
                $data['name']=$row->name;
                $data['url']=$row->url;
                $data['code']=$row->code;
                $data['dir']=$row->dir;
                $data['cjurl']=$row->cjurl;
                $data['ksid']=$row->ksid;
                $data['jsid']=$row->jsid;
                $data['cfid']=$row->cfid;
                $data['picid']=$row->picid;
                $data['dxid']=$row->dxid;
                $data['rkid']=$row->rkid;
			}
        }elseif($page==2){
            $datas['name']=$this->input->post('name',true);
            $datas['url']=$this->input->post('url',true);
            $datas['code']=$this->input->post('code',true);
            $datas['dir']=$this->input->post('dir',true);
            $datas['cjurl']=$this->input->post('cjurl',true);
            $datas['ksid']=intval($this->input->post('ksid'));
            $datas['jsid']=intval($this->input->post('jsid'));
            $datas['cfid']=intval($this->input->post('cfid'));
            $datas['picid']=intval($this->input->post('picid'));
            $datas['dxid']=intval($this->input->post('dxid'));
            $datas['rkid']=intval($this->input->post('rkid'));
            if($datas['ksid']==0) $datas['ksid']=1;
            if($datas['jsid']==0) $datas['jsid']=1;
            if(empty($datas['code'])) $datas['code']='utf-8';
            //图片版块转为pic_type
            if($datas['dir']=='pic') $datas['dir']='pic_type';
            if(empty($datas['name']) || empty($datas['url']) || empty($datas['dir']) || empty($datas['cjurl'])){
               getjson(L('plub_02'));//名称、地址、板块、采集地址都不能为空
            }
            $cjurl =str_replace('{$id}',$datas['ksid'],str_decode($datas['cjurl']));
            $data['cjurl'] = $cjurl;
            $data['code'] = $datas['code'];
            $neir=$this->caiji->str($cjurl,$datas['code']);
            if(empty($neir)) getjson(L('plub_03'));//获取列表页出错！

            //写入数据库
            if($id==0){  //新增
                $row = $this->db->query("SELECT id FROM ".CS_SqlPrefix."caiji where cjurl='".$cjurl."'")->row();
                if(!$row){
                    $data['id']=$this->Csdb->get_insert('caiji',$datas);
                }else{
                    $data['id']=$row->id;
                }
                $data['listks']='';
                $data['listjs']='';
            }else{  //修改
                $this->Csdb->get_update('caiji',$id,$datas);
                $data['listks']=$row->listks;
                $data['listjs']=$row->listjs;
            }
            $data['savelink'] = site_url('collect/add').'?id='.$data['id'];
            $info['url'] = site_url('collect/add2').'?var='.cs_base64_encode(arraystring($data));
            $info['tips'] = 1;
            $info['time'] = 300;
            getjson($info,0);
        }elseif($page==3){

            $datas['listks']=$this->input->post('listks');
            $datas['listjs']=$this->input->post('listjs');

            $data['code'] = $this->input->post('code',true);
            $data['cjurl'] = $this->input->post('cjurl',true);

            if(empty($datas['listks']) || empty($datas['listjs'])){
                getjson(L('plub_04'));//列表开始代码和结束代码都不能为空
            }

            $neir=$this->caiji->getstr($html,$datas['listks'],$datas['listjs']);
            if(empty($neir)) getjson(L('plub_05'));//获取列表内容出错！
            $data['listks'] = $datas['listks'];
            $data['listjs'] = $datas['listjs'];
            //修改数据库
            $this->Csdb->get_update('caiji',$id,$datas);

            $info['url'] = site_url('collect/add2').'?var='.cs_base64_encode(arraystring($data));
            $info['tips'] = 1;
            $info['time'] = 300;
            getjson($info,0);
        }elseif($page==4){

            $datas['linkks']=$this->input->post('linkks');
            $datas['linkjs']=$this->input->post('linkjs');
            $datas['picmode']=intval($this->input->post('picmode'));
            $datas['picks']=$this->input->post('picks');
            $datas['picjs']=$this->input->post('picjs');

            $data['code'] = $this->input->post('code',true);
            $data['cjurl'] = $this->input->post('cjurl',true);
            $data['linkks'] = $datas['linkks'];
            $data['linkjs'] = $datas['linkjs'];
            $data['listks'] = $this->input->post('listks');
            $data['listjs'] = $this->input->post('listjs');
            if(empty($datas['linkks']) || empty($datas['linkjs'])){
               getjson(L('plub_06'));//链接开始代码和链接结束代码都不能为空
            }

            if($datas['picmode']==1 && (empty($datas['picks']) || empty($datas['picjs']))){
                getjson(L('plub_07'));//图片开始代码和图片结束代码都不能为空
            }
            $links = $this->caiji->getarr($datas['linkks'],$datas['linkjs'],$html);
            if(empty($links)) getjson(L('plub_08'));//获取连接地址出错！
            //修改数据库
            $this->Csdb->get_update('caiji',$id,$datas);
            $info['url'] = site_url('collect/add2').'?var='.cs_base64_encode(arraystring($data));
            $info['tips'] = 1;
            $info['time'] = 300;
            getjson($info,0);

        }elseif($page==5){

            $html=$this->input->post('html');
            $datas['strth']=$this->input->post('strth');
            if($row->picmode==2){
                $datas['picks']=$this->input->post('picks');
                $datas['picjs']=$this->input->post('picjs');
                $data['pic']=$this->caiji->getstr($html,$datas['picks'],$datas['picjs']);
            }
            //修改数据库
            $this->Csdb->get_update('caiji',$id,$datas);

            //获取标题和正文
            //$data['name']=$this->caiji->rep($this->caiji->getstr($html,$datas['nameks'],$datas['namejs']),$datas['strth']);
            //获取图片
            if($row->picmode==1){
                $cjurl =str_replace('{$id}',$row->ksid,str_decode($row->cjurl));
                $phtml=$this->caiji->str($cjurl,$row->code);
                $pstr=$this->caiji->getstr($phtml,$row->listks,$row->listjs);
                $pics=$this->caiji->getarr($row->picks,$row->picjs,$pstr);
                $data['pic']=!empty($pics)?$pics[0]:'';
            }

            //判断自定义标记规则
            $zdy=$this->input->post('zdy');
            $ids='';
            foreach($zdy['id'] as  $i=>$v) {
                $add['name']=$zdy['name'][$i];
                $add['zd']=$zdy['zd'][$i];
                $add['ks']=$zdy['ks'][$i];
                $add['js']=$zdy['js'][$i];
                $add['fid']=$zdy['fid'][$i];
                $add['fname']=$zdy['fname'][$i];
                $add['cid']=$row->id;
                if(isset($zdy['htmlid'][$i])){
                    $add['htmlid']=$zdy['htmlid'][$i];
                }else{
                    $add['htmlid']=0;
                }

                if(intval($zdy['id'][$i])==0){ //新增
                    $rows=$this->db->query("SELECT id FROM ".CS_SqlPrefix."cjannex where zd='".$add['zd']."' and cid=".$add['cid']."")->row(); 
                	if(!$rows){
                        $id=$this->Csdb->get_insert('cjannex',$add);
                        $ids.=' and id!='.$id.'';
                	}else{
                        $ids.=' and id!='.$rows->id.'';
                	}
                }else{  //修改
                    $this->Csdb->get_update('cjannex',$zdy['id'][$i],$add);
                	$ids.=' and id!='.$zdy['id'][$i].'';
                }
                $data['zdy']['name'][]=$add['name'];
                $data['zdy']['zd'][]=$add['zd'];
                if($add['fid']==1){
                    //相册采集专用
                    if($add['zd']=='cscms_pic_url'){
                        $zdyneir=$this->caiji->getarr($add['ks'],$add['js'],$html);
                    //视频多组播放地址、下载地址
                    }elseif($row->dir=='vod' && ($add['zd']=='purl' || $add['zd']=='durl')){
                        $zdyneir=$this->caiji->getarr($add['ks'],$add['js'],$html,0);
                    }else{
                        $zdyneir=$this->caiji->rep($this->caiji->getstr($html,$add['ks'],$add['js']),$datas['strth']);
                    }
                    //判断是否清除HTML代码
                    if($add['htmlid']==1){
                        $data['zdy']['text'][]=str_checkhtml($zdyneir);
                    }else{
                        $data['zdy']['text'][]=$zdyneir;
                    }
            	}else{
                    $data['zdy']['text'][]=$add['fname'];
            	}
            }
            //删除自定义标记规则
            $this->db->query("delete from ".CS_SqlPrefix."cjannex where cid=".$id." ".$ids."");
            $data['picmode']=$row->picmode;
            $info['url'] = site_url('collect/add2').'?var='.cs_base64_encode(arraystring($data));
            $info['tips'] = 1;
            $info['time'] = 300;
            getjson($info,0);
		}
        $this->load->view('collect_edit.html',$data);
	}
    public function add2(){
        $var = $this->input->get_post('var');
        $data = unarraystring(cs_base64_decode($var));
        $row = $this->db->query("SELECT * FROM ".CS_SqlPrefix."caiji where id='".$data['id']."'")->row();
        $this->caiji->weburl($row->url);
        if($data['page'] == 2){
            $neir = $this->caiji->str($data['cjurl'],$data['code']);
            $data['html'] = $neir;
        }
        if($data['page'] == 3){
            $neir1 = $this->caiji->str($data['cjurl'],$data['code']);
            $neir2 = $this->caiji->getstr($neir1,$data['listks'],$data['listjs']);
            $data['html'] = $neir2;
            $data['picmode'] = $row->picmode;
            $data['picks'] = $row->picks;
            $data['picjs'] = $row->picjs;
            $data['linkks'] = $row->linkks;
            $data['linkjs'] = $row->linkjs;
        }
        if($data['page'] == 4){
            $neir1 = $this->caiji->str($data['cjurl'],$data['code']);
            $neir2 = $this->caiji->getstr($neir1,$data['listks'],$data['listjs']);
            $links = $this->caiji->getarr($data['linkks'],$data['linkjs'],$neir2);
            $data['html']=$this->caiji->str($links[0],$row->code);
            $data['links'] = $links;
            $data['nameks']=$row->nameks;
            $data['namejs']=$row->namejs;
            $data['strth']=$row->strth;
            $data['picmode']=$row->picmode;
            $data['picks']=$row->picks;
            $data['picjs']=$row->picjs;
            $data['dir']=$row->dir;
        }
        $this->load->view('collect_edit'.$data['page'].'.html',$data);
    }
    //采集
    public function caiji1($sign=0){
        $ac = $this->input->get('ac',true);
        $id = intval($this->input->get('id'));
        $xid = intval($this->input->get('xid'));
        $page = intval($this->input->get('page'));
        $okid = intval($this->input->get('okid'));
        $insid = intval($this->input->get('insid'));
        if($page==0) $page=1;
        if($xid==0) $xid=1;

        if($id==0) getjson(L('plub_09'));//ID不能为空
        $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix."caiji where id='".$id."'")->row(); 
        if(!$row) getjson(L('plub_01'));//记录不存在
        $this->caiji->weburl($row->url);

        //总页数
        $pagejs=$row->jsid-$row->ksid;
        if($pagejs<1) $pagejs = 1;

        //倒顺采集
        if($row->dxid==1){
            if($xid==1) $xid=$row->jsid;
            $cjurl =str_replace('{$id}',$xid,str_decode($row->cjurl));
            $data['ids']=$xid;
            $xid=($xid-1);
        }else{
            if($xid==1) $xid=$row->ksid;
            $cjurl =str_replace('{$id}',$xid,str_decode($row->cjurl));
            $data['ids']=$xid;
            $xid=($xid+1);
        }

        //读取列表文件
        $Content=trim($this->caiji->str($cjurl,$row->code));
        if(empty($Content)) getjson(L('plub_11'));//获取列表连接内容出错！

        //赋予网站主路径
        $this->caiji->weburl($row->url);

        //获取列表开始-结束
        $Liststr = $this->caiji->getstr($Content,$row->listks,$row->listjs);
        if(empty($Liststr)) getjson(L('plub_12'));//截取列表内容出错！

        //获取到连接地址 返回数组
        $LinkArr= $this->caiji->getarr($row->linkks,$row->linkjs,$Liststr);
        if(empty($LinkArr)) getjson(L('plub_13'));//截取内容连接地址出错！

        //列表页采集图片
        $pic='';
        if($row->picmode==1){
           //获取到图片地址 返回数组
           $PicArr = $this->caiji->getarr($row->picks,$row->picjs,$Liststr);
           $pic = $PicArr[$okid];
           if(!empty($pic) && substr($pic,0,7)!='http://') $pic=$row->url.$pic;
        }
        $data['pic']=$pic;

        //当前页内容总数
        $LinkCount = count($LinkArr);
        if($page>($pagejs+1)){ //采集完毕
            $info['url'] = site_url('collect').'?v='.rand(1000,9999);
            getjson($info,0);
        }
        //采集内容页开始
        $data['err'] = '';
        $data['pagejs'] = ($pagejs+1);
        $data['page'] = $page;
        $data['okid'] = $okid;
        $data['xid'] = $xid;
        $data['oknum'] = ($okid+1);
        $data['names'] = $row->name;
        $data['linkcount'] = $LinkCount;
        $data['title'] = '';
        $data['insid'] = $insid;
        if($sign==1){
            $data['id']=$row->id;
            $data['ac']=$ac;
            $this->load->view('collect_caiji.html',$data);
        }else{
            $neirurl=(substr($LinkArr[$okid],0,7)!='http://')?$row->url.$LinkArr[$okid]:$LinkArr[$okid];
            //读取内容文件
            $cjzt=0;
            $DanceContent=$this->caiji->str($neirurl,$row->code);
            if(empty($DanceContent)) {
                $temurl[0] = $neirurl;
                getjson(L('plub_15',$temurl));//获取内容页'.$neirurl.'出错，没有获取到内容，采集失败！
            }else{
                $query = $this->db->query("SELECT * FROM ".CS_SqlPrefix."cjannex where cid=".$id." order by id desc")->row_array();
                if(empty($query)){
                    getjson(L('plub_14'));//请先添加采集的字段
                }else{
                    getjson('',0);
                }
            }
        } 
    }
	public function caiji(){
        $ac = $this->input->get('ac',true);
        $id = intval($this->input->get('id'));
        $xid = intval($this->input->get('xid'));
        $page = intval($this->input->get('page'));
        $okid = intval($this->input->get('okid'));
        $insid = intval($this->input->get('insid'));
		if($page==0) $page=1;
		if($xid==0) $xid=1;

		if($id==0) $data['err'] = L('plub_09');//ID不能为空
		$row=$this->db->query("SELECT * FROM ".CS_SqlPrefix."caiji where id='".$id."'")->row(); 
		if(!$row) $data['err'] = L('plub_01');//记录不存在

        //总页数
        $pagejs=$row->jsid-$row->ksid;
		if($pagejs<1) $pagejs = 1;

        //倒顺采集
        if($row->dxid==1){
            if($xid==1) $xid=$row->jsid;
            $cjurl =str_replace('{$id}',$xid,str_decode($row->cjurl));
            $data['ids']=$xid;
            $xid=($xid-1);
        }else{
            if($xid==1) $xid=$row->ksid;
            $cjurl =str_replace('{$id}',$xid,str_decode($row->cjurl));
            $data['ids']=$xid;
            $xid=($xid+1);
        }

		//读取列表文件
		$Content=trim($this->caiji->str($cjurl,$row->code));
		if(empty($Content)) $data['err'] = L('plub_11');//获取列表连接内容出错！

		//赋予网站主路径
        $this->caiji->weburl($row->url);

		//获取列表开始-结束
        $Liststr = $this->caiji->getstr($Content,$row->listks,$row->listjs);
		if(empty($Liststr)) $data['err'] = L('plub_12');//截取列表内容出错！

		//获取到连接地址 返回数组
		$LinkArr= $this->caiji->getarr($row->linkks,$row->linkjs,$Liststr);
		if(empty($LinkArr)) $data['err'] = L('plub_13');//截取内容连接地址出错！

        //列表页采集图片
        $pic='';
        if($row->picmode==1){
		   //获取到图片地址 返回数组
           $PicArr=$this->caiji->getarr($row->picks,$row->picjs,$Liststr);
           $pic=$PicArr[$okid];
           if(!empty($pic) && substr($pic,0,7)!='http://') $pic=$row->url.$pic;
        }
		$data['pic']=$pic;

		//当前页内容总数
        $LinkCount = count($LinkArr);
        if($page>($pagejs+1)){ //采集完毕
            $info['title'] = 'over';
            $info['url'] = site_url('collect').'?v='.rand(1000,9999);
			getjson($info,0);
        }

        //采集内容页开始
        $data['err'] = '';
		$data['pagejs'] = ($pagejs+1);
		$data['page'] = $page;
		$data['okid'] = $okid;
		$data['xid'] = $xid;
		$data['oknum'] = ($okid+1);
		$data['names'] = $row->name;
		$data['linkcount'] = $LinkCount;
		$data['title'] = '';

        $neirurl=(substr($LinkArr[$okid],0,7)!='http://')?$row->url.$LinkArr[$okid]:$LinkArr[$okid];
		//读取内容文件
		$cjzt=0;
        $DanceContent=$this->caiji->str($neirurl,$row->code);
        if(empty($DanceContent)) {
            $temurl[0] = $neirurl;
            getjson(L('plub_15',$temurl));//获取内容页'.$neirurl.'出错，没有获取到内容，采集失败！
        }else{
            //标题
            $name = $this->caiji->getstr($DanceContent,$row->nameks,$row->namejs); 
            $data['name'] = str_checkhtml($this->caiji->rep($name,$row->strth));
            //图片
            if($row->picmode==2){
                $data['pic'] =  $this->caiji->getstr($DanceContent,$row->picks,$row->picjs); 
            }else{
                $data['pic'] = str_checkhtml($pic);
            }
            if(1){
                //获取自定义
                $query=$this->db->query("SELECT * FROM ".CS_SqlPrefix."cjannex where cid=".$id." order by id desc");
                foreach ($query->result() as $rowz) {
                    //相册采集专用
                    if($rowz->zd=='cscms_pic_url'){
                        $zdy = $this->caiji->getarr($rowz->ks,$rowz->js,$DanceContent);
                    //视频多组播放地址、下载地址
                    }elseif($row->dir=='vod' && ($rowz->zd=='purl' || $rowz->zd=='durl')){
                        $zdy = $this->caiji->getarr($rowz->ks,$rowz->js,$DanceContent,0);
                        $zdy = str_replace("\r\n","-cscms-",$zdy);
                    }else{
                        $zdy = $this->caiji->getstr($DanceContent,$rowz->ks,$rowz->js);
                    }
                    $data['zdy']['name'][] = $rowz->name;
                    $data['zdy']['zd'][] = $rowz->zd;
                    $data['zdy']['fname'][] = $rowz->fname;
                    if($rowz->fid==0){
                        $data['zdy']['text'][] = $rowz->fname;
                    }else{
                        $zdyneir=$this->caiji->rep($zdy,$row->strth);
                       //判断是否清除HTML代码
                        if($rowz->htmlid==1){
                            $data['zdy']['text'][]=str_checkhtml($zdyneir);
                        }else{
                            $data['zdy']['text'][] = $zdyneir;
                        }
                    }
                }
                if($ac=='ceshi'){  //测试不入库
                    $data['title'] = L('plub_16');//测试采集，不入库~！
                    $insid ++;
                }else{
                    $cjzt=1;
                    //---------------------------入库---------------------------------------
                    $add['name']=$neirurl;
                    $add['pic']=$data['pic'];
                    $add['dir']=$row->dir;
                    $add['zdy']=(!empty($data['zdy']))?arraystring($data['zdy']):'';
                    $add['addtime']=time();
                    //------------------------判断保存图片到本地----------------------------------
                    if($row->picid==1){
                     if(!empty($data['pic']) && substr($data['pic'],0,7)=='http://'){
                          $picdata = @file_get_contents($data['pic']); // 读文件内容
                          $picfolder =FCPATH."attachment/vod/";
                          $picname=date('Ymd')."/".date('Ymd').time().mt_rand(1,100).".jpg";
                          if(!empty($picdata)){
                                if(write_file($picfolder.$picname, $picdata)){
                    		        $add['pic']=$picname;
                    		    }
                            }
                        }
                    }

                             //------------------------判断入未审核库----------------------------------
                    if($row->rkid==0){ //临时库
                        $add['cfid']=$row->cfid;
                        //判断相同数据
                    	$rows=$this->db->query("SELECT id FROM ".CS_SqlPrefix."cjdata where name='".$neirurl."' and dir='".$row->dir."'")->row();
                    	if($rows){
                                if($row->cfid==0){  //不入库
                                     $data['title']=L('plub_17');//数据已经存在，不入库~！
                                }elseif($row->cfid==1){  //新增
                                     $this->Csdb->get_insert('cjdata',$add);
                                     $data['title']=L('plub_18');//入库成功~！
                                     $insid++;
                                }elseif($row->cfid==2){  //覆盖
                                     $this->Csdb->get_update('cjdata',$rows->id,$add);
                                     $data['title']= L('plub_19');//数据存在，自能覆盖成功~！
                                     $insid++;
                                }
                    	}else{
                                $this->Csdb->get_insert('cjdata',$add);
                                $data['title']=L('plub_18');//入库成功~！
                                $insid++;
                    	}

                    }else{ //入板块主数据库
                        //判断板块数据表是否存在
                        if ($this->db->table_exists(CS_SqlPrefix.$row->dir)){
                    	    $strs='';
                            unset($add['dir']); 
                            unset($add['zdy']);
                    		//判断名称字段
                    	    if(!$this->db->field_exists('name', CS_SqlPrefix.$row->dir)){
                                 unset($add['name']); 
                    		}else{
                    	         $strs.="and name='".addslashes($add['name'])."' ";
                    		}
                    		//判断图片字段
                    	    if(!$this->db->field_exists('pic', CS_SqlPrefix.$row->dir)){
                                 unset($add['pic']); 
                    		}else{
                    	         $strs.="and pic='".addslashes($add['pic'])."' ";
                    		}
                    		//判断时间字段
                    	    if(!$this->db->field_exists('addtime', CS_SqlPrefix.$row->dir)){
                                 unset($add['addtime']); 
                    		}
                            //自定义规则
                    		$zdy_pic_data=$zdy_vod_play=$zdy_vod_down='';
                    		$zdy=$data['zdy'];
                    		if(!empty($zdy)){
                                 for ($i=0; $i < count($zdy['zd']); $i++) {
                    				 //多组图片采集专用
                    				 if($zdy['zd'][$i]=='cscms_pic_url'){
                                         $zdy_pic_data=$zdy['text'][$i];
                    				 //多组视频播放地址采集专用
                    				 }elseif($row->dir=='vod' && $zdy['zd'][$i]=='purl'){
                                         $zdy_vod_play['url']=$zdy['text'][$i];
                                         $zdy_vod_play['laiy']=$zdy['fname'][$i];
                    				 //多组视频下载地址采集专用
                    				 }elseif($row->dir=='vod' && $zdy['zd'][$i]=='durl'){
                                         $zdy_vod_down['url']=$zdy['text'][$i];
                                         $zdy_vod_down['laiy']=$zdy['fname'][$i];
                    				 }else{
                                         $add[$zdy['zd'][$i]]=$zdy['text'][$i];
                    				     if(strlen($zdy['text'][$i])<200) $strs.="and ".$zdy['zd'][$i]."='".addslashes($zdy['text'][$i])."' ";
                    				 }
                    			 }
                    		}
                    		//多组视频播放地址采集专用
                    		if(!empty($zdy_vod_play)){
                    			$purl=array();
                    			$purl_arr=explode("-cscms-",$zdy_vod_play['url']);
                    			for ($i=0; $i < count($purl_arr); $i++) {
                    				$ii=$i+1;
                    				if($ii<10) $ii='0'.$ii;
                                    $purl[]=L('plub_20').$ii.L('plub_21').'$'.$purl_arr[$i].'$'.$zdy_vod_play['laiy'];
                    			}
                    			$add['purl']=implode("\n",$purl);
                    		}
                    		//多组视频下载地址采集专用
                    		if(!empty($zdy_vod_down)){
                    			$durl=array();
                    			$durl_arr=explode("-cscms-",$zdy_vod_down['url']);
                    			for ($i=0; $i < count($durl_arr); $i++) {
                    				$ii=$i+1;
                    				if($ii<10) $ii='0'.$ii;
                                    $purl[]=L('plub_20').$ii.L('plub_21').'$'.$durl_arr[$i].'$'.$zdy_vod_down['laiy'];
                    			}
                    			$add['durl']=implode("\n",$durl);
                    		}
                     		$strs=substr($strs,3);
                     		$rowk=$this->db->query("SELECT id FROM ".CS_SqlPrefix.$row->dir." where ".$strs."")->row();
                     		if($rowk){
                    	 		 if($row->cfid==2){ //覆盖
                    	        		$data['title'] = L('plub_17');//数据已经存在，不入库~！
                               		    $this->Csdb->get_update($row->dir,$rowk->id,$add);
                    			 }elseif($row->cfid==1){ //新增
                    		    		$this->Csdb->get_insert($row->dir,$add);
                    	        		$data['title'] = L('plub_18');//入库成功~！
                                        $insid++;
                    	  		 }else{
                    	        		$data['title'] = L('plub_17');//数据已经存在，不入库~！
                                        $insid++;
                    	  		 }
                     		}else{
                          		$ids=$this->Csdb->get_insert($row->dir,$add);
                    			//多组图片采集专用
                    			if(!empty($zdy_pic_data)){
                    				 foreach ($zdy_pic_data as $pic) {
                    					 $rowp=$this->db->query("SELECT id FROM ".CS_SqlPrefix."pic where pic='".$pic."' and sid=".$ids."")->row();
                    					 if(!$rowp){
                                             $add_p['pic']=$pic;
                                             $add_p['sid']=$ids;
                    					     $add_p['cid']=(int)$zdy['zd']['cid'];
                    					     $add_p['uid']=(int)$zdy['zd']['uid'];
                                             $add_p['addtime']=time();
                                             $this->Csdb->get_insert('pic',$add_p);
                    					 }
                    				 }
                    			}
                    	  		$data['title'] = L('plub_18');//入库成功~！
                                $insid++;
                     		}
                        }
                    }
                    //修改采集时间
                    $cjtdata['addtime']=time();
                    $this->Csdb->get_update('caiji',$row->id,$cjtdata);
                }
			}
		}
		if($ac!='ceshi'){  //写入采集记录,测试不入库
            $zhwrow = $this->db->query("select id from ".CS_SqlPrefix."cjlist where dir='".$row->dir."' and url='".$neirurl."'")->row();
            if(!$zhwrow){
                $cjdata['name']=$data['name'];
                $cjdata['names']=$row->name;
                $cjdata['dir']=$row->dir;
                $cjdata['url']=$neirurl;
                $cjdata['zid']=$cjzt;
                $this->Csdb->get_insert('cjlist',$cjdata);
            }
		}
        $data['id']=$row->id;
        $data['ac']=$ac;
        $data['insid'] = $insid;
        //$data = str_checkhtml($data);
        //print_r($data);exit;
        getjson($data,0);
        //$this->load->view('collect_caiji.html',$data);
	}

    //规则导出
	public function daochu(){
        $id=intval($this->input->get('id'));
		if($id==0) exit(L('plub_22'));//参数错误
		$row=$this->db->query("SELECT * FROM ".CS_SqlPrefix."caiji where id='".$id."'")->row(); 
		if(!$row) exit(L('plub_01'));//记录不存在

        $query = $this->db->query("SHOW FULL FIELDS FROM ".CS_SqlPrefix."caiji");
		$rule='';
		foreach ($query->result_array() as $rowz) {
			   if($rowz['Field']!='id' && $rowz['Field']!='addtime'){
		           $rule.="<cscms_".$rowz['Field'].">".str_replace("\r\n","###换行###",$row->$rowz['Field'])."</cscms_".$rowz['Field'].">\r\n";
			   }
		}
		//获取自定义规则
		$query=$this->db->query("SELECT * FROM ".CS_SqlPrefix."cjannex where cid=".$id." order by id desc"); 
        foreach ($query->result() as $rows) {
		       $rule.="<cscms_zdy><name>".$rows->name."</name><ks>".str_replace("\r\n","###换行###",$rows->ks)."</ks><js>".str_replace("\r\n","###换行###",$rows->js)."</js><fid>".$rows->fid."</fid><htmlid>".$rows->htmlid."</htmlid><zd>".$rows->zd."</zd><fname>".str_replace("\r\n","###换行###",$rows->fname)."</fname></cscms_zdy>\r\n";
		}
		$rule.="---------cscms规则结束----------";
        $rule=str_replace("\r\n---------cscms规则结束----------","",$rule);
        $this->load->helper('download');
        force_download($row->name.".txt", $rule);
	}

    //导入规则
	public function daoru(){
        $this->load->view('collect_daoru.html');
	}

    //导入规则入库
	public function daoru_save(){            
 	        $sid = intval($this->input->post('sid'));
 	        $neirs = $this->input->post('neir');
            $filename = $this->input->post('filename');
            if($sid==2){  //上传规则
                $path = FCPATH.'attachment/other/';
                $Filename = $path.$filename;
                $neirs=@file_get_contents($Filename);
            }

            if(empty($neirs)) getjson(L('plub_23'));//内容不能为空！

	        $regArr = explode("\n",$neirs);
	        for($i=0;$i<count($regArr);$i++){
                $ziduan = $this->caiji->getstr($regArr[$i],'<cscms_','>');
				if($ziduan!='zdy'){ //自定义规则
                     $neir=$this->caiji->getstr($regArr[$i],"<cscms_".$ziduan.">","</cscms_".$ziduan.">");
                    if(!empty($ziduan)){
                           $add[$ziduan]=str_replace("###换行###","\r\n",$neir);
					}
                }
            }

            if(empty($add)) getjson(L('plub_24'));//规则不正确！
            $id=$this->Csdb->get_insert('caiji',$add);

            //自定义规则
            $zdy=$this->caiji->getarr('<cscms_zdy>','</cscms_zdy>',$neirs,2);
			if(!empty($zdy)){
				$add2='';
				$zdy=explode("\n",$zdy);
                foreach ($zdy as $zdystr) {
                       $add2['name']=$this->caiji->getstr($zdystr,'<name>','</name>');
                       $add2['ks']=str_replace("###换行###","\r\n",$this->caiji->getstr($zdystr,'<ks>','</ks>'));
                       $add2['js']=str_replace("###换行###","\r\n",$this->caiji->getstr($zdystr,'<js>','</js>'));
                       $add2['fid']=$this->caiji->getstr($zdystr,'<fid>','</fid>');
                       $add2['htmlid']=$this->caiji->getstr($zdystr,'<htmlid>','</htmlid>');
                       $add2['zd']=$this->caiji->getstr($zdystr,'<zd>','</zd>');
                       $add2['fname']=str_replace("###换行###","\r\n",$this->caiji->getstr($zdystr,'<fname>','</fname>'));
                       $add2['cid']=$id;
			           if(!empty($add2)) $this->Csdb->get_insert('cjannex',$add2); //增加自定义
				}
			}
            if($sid==2){ //如果是上传你的则删除文件
                @unlink($Filename);
			}
            $info['url'] = site_url('collect/index').'?v='.rand(1000,9999);
            $info['parent'] = 1;
            getjson($info,0);
	}   

    //删除规则
	public function del(){
        $id = $this->input->get_post('id',true);
		$this->Csdb->get_del('caiji',$id);
		$this->Csdb->get_del('cjannex',$id,'cid');
        $info['url'] = site_url('collect').'?v='.rand(1000,9999);
		if(is_numeric($id)){
			getjson('',0);
		}else{
			getjson($info,0);
		}
	}

    //历史记录
	public function lists(){
        $page = intval($this->input->get('page'));
        $zid = intval($this->input->get_post('zid'));
        $dir = $this->input->get_post('dir',true);
        if($page==0) $page=1;

        $sql_string = "SELECT * FROM ".CS_SqlPrefix."cjlist where 1=1";
        if(!empty($dir)){
             $sql_string.=" and dir='".$dir."'";
		}
        if($zid>0){
            $sql_string.=" and zid=".($zid-1)."";
		}
        $sql_string.=" order by id desc";
        $count_sql = str_replace('*','count(*) as count',$sql_string);
        $query = $this->db->query($count_sql)->result_array();
        $total = $query[0]['count'];

        $base_url = site_url('collect/lists').'?dir='.$dir.'&zid='.$zid.'&page=';
        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        $totalPages = ($totalPages>0)?$totalPages:1;
        $page = ($page>$totalPages)?$totalPages:$page;
        $data['nums'] = $total;
        if($total<$per_page){
            $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);

        $data['collect'] = $query->result();
        $data['zid'] = $zid;
        $data['dir'] = $dir;
        $data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('collect_lists.html',$data);
	}

    //删除历史记录
	public function lists_del(){
        $ac = $this->input->get_post('ac',true);
        $id = $this->input->get_post('id',true);
		if($ac=='all'){ //全部
			$this->db->query("delete from ".CS_SqlPrefix."cjlist");
		}else{
            $this->Csdb->get_del('cjlist',$id);
		}
        $info['url'] = site_url('collect/lists').'?v='.rand(1000,9999);
        $info['msg'] = L('plub_25');//恭喜你，操作成功！
        getjson($info,0);
	}

    //临时库记录
	public function ruku(){
        $page = intval($this->input->get('page'));
        $zid = intval($this->input->get_post('zid'));
        $dir = $this->input->get_post('dir',true);
        if($page==0) $page=1;
        $sql_string = "SELECT * FROM ".CS_SqlPrefix."cjdata where 1=1";
        if(!empty($dir)){
            $sql_string.=" and dir='".$dir."'";
		}
        if($zid>0){
            $sql_string.=" and zid=".($zid-1)."";
		}
        $sql_string.=" order by id desc";
        $count_sql = str_replace('*','count(*) as count',$sql_string);
        $query = $this->db->query($count_sql)->result_array();
        $total = $query[0]['count'];

        $per_page = 15; 
        $totalPages = ceil($total / $per_page); // 总页数
        $data['nums'] = $total;
        $totalPages = ($totalPages>0)?$totalPages:1;
        $page = ($page>$totalPages)?$totalPages:$page;
        if($total<$per_page){
              $per_page=$total;
        }
        $sql_string.=' limit '. $per_page*($page-1) .','. $per_page;
        $query = $this->db->query($sql_string);

        $data['collect'] = $query->result();
        $data['zid'] = $zid;
        $data['dir'] = $dir;
        $base_url = site_url('collect/ruku').'?dir='.$dir.'&zid='.$zid.'&page=';
        $data['page_data'] = page_data($total,$page,$totalPages);
        $data['page_list'] = admin_page($base_url,$page,$totalPages); //获取分页类
        $this->load->view('collect_ruku.html',$data);
	}

    //查看临时库记录
	public function look(){
        $id   = intval($this->input->get('id'));
        $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix."cjdata where id=".$id."")->row(); 
		if(!$row) admin_msg(L('plub_01'),'javascript:history.back();','no');  //记录不存在
        $data['row']=$row;
        $this->load->view('collect_look.html',$data);
	}

    //入库临时库
	public function ruku_add(){
        $ac = $this->input->get('ac',true);
        $id = $this->input->get_post('id',true);

		$data['fail'] = array();
        if($ac=='no'){  //入库全部未入库数据
            $query = $this->db->query("SELECT * FROM ".CS_SqlPrefix."cjdata where zid=0 order by id desc");
            if($query->num_rows()==0){
                $info['url'] = site_url('collect/ruku').'?v='.rand(1000,9999);
                $info['msg'] = L('plub_26');//恭喜您，全部入库完成~!
                getjson($info,0);
            }
			foreach ($query->result() as $row) {
			        //判断板块数据表是否存在
                    $sign = 0;
                    if ($row && $this->db->table_exists(CS_SqlPrefix.$row->dir)){
						     $strs='';
						     //判断标题字段
			                 if($this->db->field_exists('name', CS_SqlPrefix.$row->dir)){
								 $add['name']=$row->name;
								 $strs.="and name='".addslashes($row->name)."' ";
							 }
						     //判断图片字段
			                 if($this->db->field_exists('pic', CS_SqlPrefix.$row->dir)){
								 $add['pic']=$row->pic;
								 $strs.="and pic='".addslashes($row->pic)."' ";
							 }
						     //判断时间字段
			                 if($this->db->field_exists('addtime', CS_SqlPrefix.$row->dir)){
								 $add['addtime']=time();
							 }
						     //判断自定义字段
							 $zdy_pic_data=$zdy_vod_play=$zdy_vod_down='';
			                 if(!empty($row->zdy)){
                                    $zdy=unarraystring($row->zdy);
	                                for ($i=0; $i < count($zdy['name']); $i++) {
											 //多组图片采集专用
											 if($zdy['zd'][$i]=='cscms_pic_url'){
                                                 $zdy_pic_data=$zdy['text'][$i];
											 //多组视频播放地址采集专用
											 }elseif($row->dir=='vod' && $zdy['zd'][$i]=='purl'){
                                                 $zdy_vod_play['url']=$zdy['text'][$i];
                                                 $zdy_vod_play['laiy']=$zdy['fname'][$i];
											 //多组视频下载地址采集专用
											 }elseif($row->dir=='vod' && $zdy['zd'][$i]=='durl'){
                                                 $zdy_vod_down['url']=$zdy['text'][$i];
                                                 $zdy_vod_down['laiy']=$zdy['fname'][$i];
											 }else{
                                                 $add[$zdy['zd'][$i]]=$zdy['text'][$i];
											     if(strlen($zdy['text'][$i])<200) $strs.="and ".$zdy['zd'][$i]."='".addslashes($zdy['text'][$i])."' ";
											 }
	                                } 
			                 }
							 //多组视频播放地址采集专用
							 if(!empty($zdy_vod_play)){
									$purl=array();
									$purl_arr=explode("-cscms-",$zdy_vod_play['url']);
									for ($i=0; $i < count($purl_arr); $i++) {
										$ii=$i+1;
										if($ii<10) $ii='0'.$ii;
                                        $purl[]=L('plub_20').$ii.L('plub_21').'$'.$purl_arr[$i].'$'.$zdy_vod_play['laiy'];
									}
									$add['purl']=implode("\n",$purl);
							 }
							 //多组视频下载地址采集专用
							 if(!empty($zdy_vod_down)){
									$durl=array();
									$durl_arr=explode("-cscms-",$zdy_vod_down['url']);
									for ($i=0; $i < count($durl_arr); $i++) {
										$ii=$i+1;
										if($ii<10) $ii='0'.$ii;
                                        $purl[]=L('plub_20').$ii.L('plub_21').'$'.$durl_arr[$i].'$'.$zdy_vod_down['laiy'];
									}
									$add['durl']=implode("\n",$durl);
							 }
							 $strs=substr($strs,3);
							 $rowk=$this->db->query("SELECT id FROM ".CS_SqlPrefix.$row->dir." where ".$strs."")->row();
							 if($rowk){
								  if($row->cfid==2){ //覆盖
					                    $this->Csdb->get_update($row->dir,$rowk->id,$add);
								  }elseif($row->cfid==1){ //新增
									    $this->Csdb->get_insert($row->dir,$add);
								  }else{
								        $sign = 1;
								  }
							 }else{
			                      $ids=$this->Csdb->get_insert($row->dir,$add);
								  //多组图片采集专用
								  if(!empty($zdy_pic_data)){
										foreach ($zdy_pic_data as $pic) {
											$rowp=$this->db->query("SELECT id FROM ".CS_SqlPrefix."pic where pic='".$pic."' and sid=".$ids."")->row();
											if(!$rowp){
                                                  $add_p['pic']=$pic;
                                                  $add_p['sid']=$ids;
												  $add_p['cid']=(int)$zdy['zd']['cid'];
												  $add_p['uid']=(int)$zdy['zd']['uid'];
                                                  $add_p['addtime']=time();
                                                  $this->Csdb->get_insert('pic',$add_p);
											}
										}
								  }
							 }
					         //修改入库状态
					         $edit['zid']=1;
					         $this->Csdb->get_update('cjdata',$row->id,$edit);
                             if($sign==1){
                                array_push($data['fail'], $row->id);
                             }
					}
            }
            if(empty($data['fail'])){
                $info['msg'] = L('plub_27');//恭喜你，全部入库成功
            }else{
                $tempfail[0] = implode(",",$data['fail']);
                $info['msg'] = L('plub_28',$tempfail);//编号为 * 的数据入库失败
                $info['alert'] = 1;
            }
            $info['url'] = site_url('collect/ruku').'?v='.rand(1000,9999);
            getjson($info,0);
		}else{  //入库所选
            if(empty($id)) getjson(L('plub_29')); //未选择数据
            if(is_array($id)){ //多记录
                foreach ($id as $ids) {
                    $sign = 0;
                    $row=$this->db->query("SELECT * FROM ".CS_SqlPrefix."cjdata where id=".intval($ids)." and zid=0")->row();
			          //判断板块数据表是否存在
                    if ($row && $this->db->table_exists(CS_SqlPrefix.$row->dir)){
                        $strs='';
                        //判断标题字段
                        if($this->db->field_exists('name', CS_SqlPrefix.$row->dir)){
                             $add['name']=$row->name;
                             $strs.="and name='".addslashes($row->name)."' ";
                        }
                        //判断图片字段
                        if($this->db->field_exists('pic', CS_SqlPrefix.$row->dir)){
                             $add['pic']=$row->pic;
                             $strs.="and pic='".addslashes($row->pic)."' ";
                        }
                        //判断时间字段
                        if($this->db->field_exists('addtime', CS_SqlPrefix.$row->dir)){
                            $add['addtime']=time();
                        }
					    //判断自定义字段
						$zdy_pic_data=$zdy_vod_play=$zdy_vod_down='';
                        if(!empty($row->zdy)){
                            $zdy=unarraystring($row->zdy);
                            for ($i=0; $i < count($zdy['name']); $i++) {
                        	    //多组图片采集专用
                                if($zdy['zd'][$i]=='cscms_pic_url'){
                                    $zdy_pic_data=$zdy['text'][$i];
                                //多组视频播放地址采集专用
                                }elseif($row->dir=='vod' && $zdy['zd'][$i]=='purl'){
                                    $zdy_vod_play['url']=$zdy['text'][$i];
                                    $zdy_vod_play['laiy']=$zdy['fname'][$i];
                                //多组视频下载地址采集专用
                                }elseif($row->dir=='vod' && $zdy['zd'][$i]=='durl'){
                                    $zdy_vod_down['url']=$zdy['text'][$i];
                                    $zdy_vod_down['laiy']=$zdy['fname'][$i];
                                }else{
                                    $add[$zdy['zd'][$i]]=$zdy['text'][$i];
                                    if(strlen($zdy['text'][$i])<200) $strs.="and ".$zdy['zd'][$i]."='".addslashes($zdy['text'][$i])."' ";
                                }
                            } 
                        }
                        //多组视频播放地址采集专用
                        if(!empty($zdy_vod_play)){
                        	$purl=array();
                        	$purl_arr=explode("-cscms-",$zdy_vod_play['url']);
                        	for ($i=0; $i < count($purl_arr); $i++) {
                        		$ii=$i+1;
                        		if($ii<10) $ii='0'.$ii;
                                $purl[]=L('plub_20').$ii.L('plub_21').'$'.$purl_arr[$i].'$'.$zdy_vod_play['laiy'];
                        	}
                        	$add['purl']=implode("\n",$purl);
                        }
                        //多组视频下载地址采集专用
                        if(!empty($zdy_vod_down)){
                        	$durl=array();
                        	$durl_arr=explode("-cscms-",$zdy_vod_down['url']);
                        	for ($i=0; $i < count($durl_arr); $i++) {
                        		$ii=$i+1;
                        		if($ii<10) $ii='0'.$ii;
                                $purl[]=L('plub_20').$ii.L('plub_21').'$'.$durl_arr[$i].'$'.$zdy_vod_down['laiy'];
                        	}
                        	$add['durl']=implode("\n",$durl);
                        }
                        $strs=substr($strs,3);
                        $rowk=$this->db->query("SELECT id FROM ".CS_SqlPrefix.$row->dir." where ".$strs."")->row();
                        if($rowk){
                            if($row->cfid==2){ //覆盖
                                $this->Csdb->get_update($row->dir,$rowk->id,$add);
                            }elseif($row->cfid==1){ //新增
                                $this->Csdb->get_insert($row->dir,$add);
                            }else{
                            $sign = 1;
                            }
                        }else{
                            $ids=$this->Csdb->get_insert($row->dir,$add);
                            //多组图片采集专用
                            if(!empty($zdy_pic_data)){
								foreach ($zdy_pic_data as $pic) {
									$rowp=$this->db->query("SELECT id FROM ".CS_SqlPrefix."pic where pic='".$pic."' and sid=".$ids."")->row();
									if(!$rowp){
                                          $add_p['pic']=$pic;
                                          $add_p['sid']=$ids;
										  $add_p['cid']=(int)$zdy['zd']['cid'];
										  $add_p['uid']=(int)$zdy['zd']['uid'];
                                          $add_p['addtime']=time();
                                          $this->Csdb->get_insert('pic',$add_p);
									}
								}
                            }
                        }
                        //修改入库状态
                        $edit['zid']=1;
                        $this->Csdb->get_update('cjdata',$row->id,$edit);
                        if($sign==1){
                            array_push($data['fail'], $row->id);
                        }
                    }
			    }
            }
            if(empty($data['fail'])){
                $info['msg'] = L('plub_27');//恭喜你，全部入库成功
            }else{
                $tempfail[0] = implode(",",$data['fail']);
                $info['msg'] = L('plub_28',$tempfail);//编号为 * 的数据入库失败
                $info['alert'] = 1;
            }
            $info['url'] = site_url('collect/ruku').'?v='.rand(1000,9999);  //操作成功
            getjson($info,0);
        }
    }

    //删除临时库记录
	public function ruku_del(){
        $ac = $this->input->get_post('ac',true);
        $id = $this->input->get_post('id',true);
		if($ac=='all'){//全部
            $this->db->query("delete from ".CS_SqlPrefix."cjdata");
		}elseif($ac=='yes'){ //已经入库
            $this->Csdb->get_del('cjdata',1,'zid');
		}else{
            $this->Csdb->get_del('cjdata',$id);
		}
        $info['url'] = site_url('collect/ruku').'?v='.rand(1000,9999);
        $info['msg'] = L('plub_25');//恭喜你，操作成功
        getjson($info,0);
	}
}
