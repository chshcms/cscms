<?php if ( ! defined('IS_ADMIN')) exit('No direct script access allowed');
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2014 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-12-08
 */
class Saomiao extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
        $this->load->helper('file');
	    $this->load->model('Csadmin');
		$this->load->library('mp3file');
        $this->Csadmin->Admin_Login();
        require_once CSCMS.'sys/Cs_FtpSm.php';
	}

    //本地扫描
	public function index(){
        $data['hz']="mp3|wma|mp4|flv|avi";
        $this->load->view('saomiao.html',$data);
	}

    //获取目录内容
	public function yps(){
        $dir = $this->input->get_post('dir');
        $path = $this->input->get_post('path');
        $hz = $this->input->get_post('hz');
        if(empty($path)) $path="/";

        $dir = str_replace("\\","/",$dir);			
        $dir = str_replace("//","/",$dir);

        $path = str_replace("\\","/",$path);
        $path = str_replace("//","/",$path);

        $hz = str_replace("php","",$hz);
        $hz = str_replace("||","|",$hz);

        if(empty($dir)){
            $paths = ".".$path;
        }else{
            $paths = $dir.$path;
        }
        $paths = str_replace("//","/",$paths);
        $showarr = get_dir_file_info(get_bm($paths,'utf-8','gbk'), $top_level_only = TRUE);

        $data['path'] = $path;
        $data['dir'] = $dir;
        $data['hz'] = $hz;
        $data['paths'] = $paths;

        //检查扩展名
		$ext_arr = explode("|", $hz);
        $dirs=$list = array();
	    if($showarr) {
		    foreach ($showarr as $t) {
				$t['name'] = get_bm($t['name']);
			    if (is_dir($t['server_path'])) {
			        $dirs[] = array(
				       'name' => $t['name'],
				       'date' => date('Y-m-d H:i:s',$t['date']),
				       'size' => '--',
				       'icon' => Web_Path.'packs/admin/images/ext/dir.gif',
				       'link' => site_url('dance/admin/saomiao/yps')."?dir=".$dir."&hz=".$hz."&path=".$path."/".$t['name'],
				       'rklink' => site_url('dance/admin/saomiao/ruku')."?dir=".$dir."&id=".$path."/".$t['name']."&hz=".$hz,
			        );
			    } else {
					$exts = strtolower(trim(strrchr($t['name'], '.'), '.'));
        			if (in_array($exts,$ext_arr) !== false) {
						$times = date('Y-m-d H:i:s',$t['date']);
					    $list[] = array(
						    'name' => $t['name'],
						    'ext' => get_extpic($exts),
					        'date' => (date('Y-m-d',$t['date'])==date('Y-m-d'))?'<font color=red>'.$times.'<font>':$times,
					        'size' => formatsize($t['size']),
						    'icon' => Web_Path.'packs/admin/images/ext/'.get_extpic($exts).'.gif',
						    'rklink' => site_url('dance/admin/saomiao/ruku')."?dir=".$dir."&id=".$path."/".$t['name'],
					    );
					}
			    }
		    }
	    }
		$data['dirs'] = $dirs;
		$data['show'] = $list;
        $this->load->view('saomiao_look.html',$data);
	}

	//批量入库
	public function ruku(){
        $dir = $this->input->get_post('dir');
        $path = $this->input->get_post('path');
        $hz = $this->input->get_post('hz');
        $ids = $this->input->get_post('id');
        if(empty($path)) $path="/";
		if(empty($ids)) exit('<span style="color:red;">抱歉，请选择要入库的数据~!</span><span style="color:#009688;">&nbsp;&nbsp;2秒后返回......</span><script>setTimeout(function(){location.href = history.back();},2000);</script>');

        $dir=str_replace("\\","/",$dir);			
        $dir=str_replace("//","/",$dir);

        $path=str_replace("\\","/",$path);
        $path=str_replace("//","/",$path);

        $hz=str_replace("php","",$hz);
        $hz=str_replace("||","|",$hz);

        if(empty($dir)){
                $paths=".".$path;
        }else{
                $paths=$dir.$path;
        }
        $paths=str_replace("//","/",$paths);
        
		$files='';
		if(is_array($ids)){ //全选入库
            foreach ($ids as $t) {
                $file=$paths.'/'.$t;
                $files.=str_replace("//","/",$file)."\r\n";
		    }
		    $files.='##cscms##';
		    $files=str_replace("\r\n##cscms##","",$files);
		}else{ //单文件入库
            $files=$paths.str_replace("//","/",$ids);
			$files=str_replace("//","/",$files);
		}
        $data['path']=$path;
        $data['dir']=$dir;
        $data['hz']=$hz;
		$data['files']=$files;
        $this->load->view('saomiao_ruku.html',$data);
	}

	//确定入库
	public function save(){
        $dir = $this->input->post('dir');
        $path = $this->input->post('path');
        $hz = $this->input->post('hz');
        $playhz = $this->input->post('playhz');
        $files = $this->input->post('files');
        $cid = intval($this->input->post('cid',true));
        $user = $this->input->post('user',true);
        $singer = $this->input->post('singer',true);

		if(empty($files)) exit('<span style="color:red;">抱歉，请选择要入库的数据~!</span><span style="color:#009688;">&nbsp;&nbsp;2秒后返回......</span><script>setTimeout(function(){location.href = history.back();},2000);</script>');
		if($cid==0) exit('<span style="color:red;">抱歉，请选择要入库的分类~!</span><span style="color:#009688;">&nbsp;&nbsp;2秒后返回......</span><script>setTimeout(function(){location.href = history.back();},2000);</script>');

        $dir=str_replace("\\","/",$dir);			
        $dir=str_replace("//","/",$dir);

        $path=str_replace("\\","/",$path);
        $path=str_replace("//","/",$path);

        $hz=str_replace("php","",$hz);
        $hz=str_replace("||","|",$hz);

        //入库开始
        $data['cid']=$cid;
        $data['tid']=intval($this->input->post('tid'));
        $data['fid']=intval($this->input->post('fid'));
        $data['reco']=intval($this->input->post('reco'));
        $data['uid']=intval(getzd('user','id',$user,'name'));
        $data['lrc']='';
        $data['text']='';
        $data['cion']=intval($this->input->post('cion'));
        $data['vip']=intval($this->input->post('vip'));
        $data['level']=intval($this->input->post('level'));
        $data['tags']=$this->input->post('tags',true);
        $data['zc']=$this->input->post('zc',true);
        $data['zq']=$this->input->post('zq',true);
        $data['bq']=$this->input->post('bq',true);
        $data['hy']=$this->input->post('hy',true);
        $data['singerid']=intval(getzd('singer','id',$singer,'name'));
        $data['skins']=$this->input->post('skins',true);
        $data['addtime']=time();

        echo '<LINK href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><script src="'.base_url().'packs/js/jquery.min.js"></script>';

		$filearr=explode("\r\n",$files);
        for($i=0;$i<count($filearr);$i++){
			$file = get_bm($filearr[$i],'utf-8','gbk');
			$file = str_replace("//","/",$file);
			if(substr($file,0,2) == "./"){
			    $file = substr($file,1);
				$dir = $_SERVER['DOCUMENT_ROOT'];
			    $file = $dir.$file;
			}
			if(is_dir($file)){  //文件夹
			    $strs = $this->dirtofiles($file,$hz);
				if(!empty($strs)){
			        foreach ($strs as $value) {  
			            if(!empty($value)){
			                $dance = addslashes(get_bm($value));
							$dance = str_replace($dir,"",$dance);
							$exts = trim(strrchr($dance, '.'), '.');
							$name = end(explode("/",$dance));
			                $data['name'] = str_replace('.'.$exts,'',$name);
							$data['purl'] = $dance;
			                $data['durl'] = $dance;
			                //判断视听后缀
			                if(!empty($playhz)){
			                	$data['purl'] = str_replace('.'.$exts,'.'.$playhz,$data['purl']);
			                }

							//判断数据是否存在
							$row=$this->db->query("SELECT id FROM ".CS_SqlPrefix."dance where durl='".$data['durl']."'")->row();
							if($row){

			                    echo "<br>&nbsp;&nbsp;<font style=font-size:10pt;>&nbsp;&nbsp;".$dance."<font color=red>&nbsp;已经存在,入库失败...</font></font><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";

							}else{

							     $data['dx']=formatsize(filesize($value));
							     $info=$this->djinfo($value);
							     if($info){
								     $data['dx']=$info['dx'];
								     $data['yz']=$info['yz'];
								     $data['sc']=$info['sc'];
							     }

			                     $this->Csdb->get_insert('dance',$data);
			                     echo "<br>&nbsp;&nbsp;<font style=font-size:10pt;>&nbsp;&nbsp;".$dance."<font color=#009688;>&nbsp;操作成功,入库完成...</font></font><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
			                     flush();ob_flush();
							}
						}
					}
				}
			}else{  //文件

			    if(!empty($file)){

		            $dance = addslashes(get_bm($file));
					$dance = str_replace($dir,"",$dance);
					$exts = trim(strrchr($dance, '.'), '.');
					$name = end(explode("/",$dance));
		            $data['name'] = str_replace('.'.$exts,'',$name);
					$data['purl'] = $dance;
		            $data['durl'] = $dance;
		            //判断视听后缀
		            if(!empty($playhz)){
		            	$data['purl'] = str_replace('.'.$exts,'.'.$playhz,$data['purl']);
		            }

					//判断数据是否存在
					$row=$this->db->query("SELECT id FROM ".CS_SqlPrefix."dance where durl='".$data['durl']."'")->row();
					if($row){
		                  echo "<br>&nbsp;&nbsp;<font style=font-size:10pt;>&nbsp;&nbsp;".$dance."<font color=red>&nbsp;已经存在,入库失败...</font></font><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";

					}else{
						$data['dx']=formatsize(filesize($file));
					    $info=$this->djinfo($file);
					    if($info){
							   $data['dx']=$info['dx'];
							   $data['yz']=$info['yz'];
							   $data['sc']=$info['sc'];
					      }

		                  $this->Csdb->get_insert('dance',$data);
		                  echo "<br>&nbsp;&nbsp;<font style=font-size:10pt;>&nbsp;&nbsp;".$dance."<font color=#009688;>&nbsp;操作成功,入库完成...</font></font><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
					}
					flush();ob_flush();
				}
			}
		}
        die("<br>&nbsp;&nbsp;&nbsp;&nbsp;<font style=color:red;font-size:14px;><b>操作完毕,3秒后返回...</b><br></font><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;setTimeout('ReadGo();',3000);function ReadGo(){window.location.href ='javascript:history.go(-2);'}</script>");
	}

    //FTP扫描
	public function ftp(){
	    if(FTP_Sm_Server=='' || FTP_Sm_Name==''){
            $this->load->view('saomiao_ftp_config.html');
		}else{
            $this->load->view('saomiao_ftp.html');
        }
	}

    //FTP扫描配置
	public function ftp_config(){
        $this->load->view('saomiao_ftp_config.html');
	}

    //FTP扫描配置保存
	public function ftpsave(){
	    $FTP_Sm_Server = $this->input->post('FTP_Sm_Server', TRUE);
	    $FTP_Sm_Port = intval($this->input->post('FTP_Sm_Port', TRUE));
	    $FTP_Sm_Name = $this->input->post('FTP_Sm_Name', TRUE);
	    $FTP_Sm_Pass = $this->input->post('FTP_Sm_Pass', TRUE);
	    $FTP_Sm_Ive = $this->input->post('FTP_Sm_Ive', TRUE);

        if($FTP_Sm_Port==0)   $FTP_Sm_Port=21;
		$ypass=substr(FTP_Sm_Pass,0,3).'*****'.substr(FTP_Sm_Pass,-3);
		if($ypass==$FTP_Sm_Pass) $FTP_Sm_Pass=FTP_Sm_Pass;

        $strs="<?php"."\r\n";
        $strs.="define('FTP_Sm_Server','".$FTP_Sm_Server."');  //远程FTP服务器IP   \r\n";
        $strs.="define('FTP_Sm_Port','".$FTP_Sm_Port."');  //远程FTP端口  \r\n";
        $strs.="define('FTP_Sm_Name','".$FTP_Sm_Name."');  //远程FTP帐号  \r\n";
        $strs.="define('FTP_Sm_Pass','".$FTP_Sm_Pass."');  //远程FTP密码  \r\n";
        $strs.="define('FTP_Sm_Ive',".$FTP_Sm_Ive.");  //是否使用被动模式";

        //写文件
        if (!write_file(CSCMS.'sys/Cs_FtpSm.php', $strs)){
            getjson('抱歉，保存失败~!');
        }else{
            $info['url'] = site_url('dance/admin/saomiao/ftp');
            getjson($info,0);
        }
        
	}

	//FTP扫描入库
	public function ftpruku(){
        $hz = $this->input->post('hz');
        $path = $this->input->post('path');
        $playhz = $this->input->post('playhz');
        $files = $this->input->post('files');
        $cid = intval($this->input->post('cid',true));
        $user = $this->input->post('user',true);
        $singer = $this->input->post('singer',true);

		if(empty($path)) exit('<span style="color:red;">抱歉，请填写FTP入库目录~!</span><span style="color:#009688;">&nbsp;&nbsp;2秒后返回......</span><script>setTimeout(function(){location.href = history.back();},2000);</script>');
		if($cid==0) exit('<span style="color:red;">抱歉，请选择要入库的分类~!</span><span style="color:#009688;">&nbsp;&nbsp;2秒后返回......</span><script>setTimeout(function(){location.href = history.back();},2000);</script>');

        if(empty($path)) $path="/";
        if(substr($path,0,1)!="/") $path="/".$path;
        if(substr($path,-1)!="/") $path=$path."/";

        $path=str_replace("\\","/",$path);
        $path=str_replace("//","/",$path);
        $paths=".".$path;

        //入库开始
        $data['cid']=$cid;
        $data['fid']=intval($this->input->post('fid'));
        $data['tid']=intval($this->input->post('tid'));
        $data['reco']=intval($this->input->post('reco'));
        $data['uid']=intval(getzd('user','id',$user,'name'));
        $data['lrc']='';
        $data['text']='';
        $data['cion']=intval($this->input->post('cion'));
        $data['vip']=intval($this->input->post('vip'));
        $data['level']=intval($this->input->post('level'));
        $data['tags']=$this->input->post('tags',true);
        $data['zc']=$this->input->post('zc',true);
        $data['zq']=$this->input->post('zq',true);
        $data['bq']=$this->input->post('bq',true);
        $data['hy']=$this->input->post('hy',true);
        $data['singerid']=intval(getzd('singer','id',$singer,'name'));
        $data['skins']=$this->input->post('skins',true);
        $data['addtime']=time();

        echo '<LINK href="'.base_url().'packs/admin/css/style.css" type="text/css" rel="stylesheet"><script src="'.base_url().'packs/js/jquery.min.js"></script>';

	    $this->load->library('ftp');
	    if ($this->ftp->connect(array(
			'port' => FTP_Sm_Port,
			'debug' => FALSE,
			'passive' => FTP_Sm_Ive,
			'hostname' => FTP_Sm_Server,
			'username' => FTP_Sm_Name,
			'password' => FTP_Sm_Pass,
	    ))) { // 连接ftp成功
            $arrs = $this->ftp->list_files($paths);
			$this->ftp->close();
	    }else{
            exit('<span style="color:red;">抱歉，FTP连接失败~!</span><span style="color:#009688;">&nbsp;&nbsp;2秒后返回......</span><script>setTimeout(function(){location.href = history.back();},2000);</script>');
		}

        $ext_arr = explode("|", $hz);

        if(!empty($arrs)){

            $i=0;
            foreach ($arrs as $file) {  
                    $dance = addslashes(get_bm($file));
			        if(strpos($dance,$path) === FALSE){
                        $dance=$path.$dance;
					}elseif(substr($dance,0,1)=='.'){
					    $dance=	substr($dance,1);
					}
					$exts = strtolower(trim(strrchr($dance, '.'), '.'));

					if (in_array($exts,$ext_arr) !== false) {

							$name=explode("/",$dance);
                            $data['name']=str_replace('.'.$exts,'',$name[count($name)-1]);
							$data['purl']=(!empty($playhz))?str_replace('.'.$exts,'.'.$playhz,$dance):$dance;
                            $data['durl']=$dance;

							//判断数据是否存在
							$row=$this->db->query("SELECT id FROM ".CS_SqlPrefix."dance where durl='".$data['durl']."'")->row();
							if($row){
                                echo "<br>&nbsp;&nbsp;<font style=font-size:10pt;>&nbsp;&nbsp;".$file."<font color=red>&nbsp;已经存在,入库失败...</font></font><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
							}else{
                                $this->Csdb->get_insert('dance',$data);
                                echo "<br>&nbsp;&nbsp;<font style=font-size:10pt;>&nbsp;&nbsp;".$file."<font color=#009688;>&nbsp;操作成功,入库完成...</font></font><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;</script>";
							}
                            flush();ob_flush();usleep(100000);
					 }
			  }
		}
        die("<br>&nbsp;&nbsp;&nbsp;&nbsp;<font style=color:red;font-size:14px;><b>操作完毕,3秒后返回...</b><br></font><script>document.getElementsByTagName('BODY')[0].scrollTop=document.getElementsByTagName('BODY')[0].scrollHeight;setTimeout('ReadGo();',3000);function ReadGo(){window.location.href ='javascript:history.go(-2);'}</script>");
	}

    //获取歌曲属性
	public function djinfo($dir){
        if(!file_exists($dir)) return false;
		$music = $this->mp3file->get_metadata($dir);
        if(!empty($music['Filesize']) && !empty($music['Bitrate']) && !empty($music['Length mm:ss'])){
      	    return array("dx"=>formatsize($music['Filesize']),"yz"=>$music['Bitrate']." Kbps","sc"=>$music['Length mm:ss']);
   	    }else{
      		return false;
   	    }
	}

	//文件夹文件归替
	public function dirtofiles($dir,$hz='mp3'){ 
        $showarr = get_dir_file_info($dir, $top_level_only = TRUE);
        $ext_arr = explode('|', $hz);
		if($showarr) {
			$files = array();
		    foreach ($showarr as $t) {
			    if (is_dir($t['server_path'])) {
			        $files2 = $this->dirtofiles($t['server_path'],$hz);
			        $files = array_merge($files,$files2);
			    } else {
			    	$filename = get_bm($t['server_path']);
			    	$exts = strtolower(trim(strrchr($filename, '.'), '.'));
        			if (in_array($exts,$ext_arr) !== false) {
			    		$files[] = $filename;
			    	}
			    }
			}
		}
		return $files;
	}
}

