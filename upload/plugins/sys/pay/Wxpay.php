<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-10
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Wxpay extends Cscms_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('Csuser');
		$this->lang->load('pay');
	}

    //请求支付
	public function index($id=0)
	{
        $this->Csuser->User_Login();
	    $id=(int)$id; //订单ID
        if($id==0)  msg_url(L('pay_01'),spacelink('pay'));
        $row=$this->Csdb->get_row('pay','*',$id);
		if(!$row || $row->uid!=$_SESSION['cscms__id']){
            msg_url(L('pay_02'),spacelink('pay'));
		}
		$body = L('pay_03',array($_SESSION['cscms__name']));
		$dingdan = $row->dingdan;
		$total_fee = $row->rmb;

        require_once CSPATH."pay/wxpay/wxpays.php";
        $pay = new Wxpays();
        $code_url = $pay->get_ma($dingdan,$total_fee,$body);

		if(!isset($code_url)){
			msg_url('获取二维码失败，请稍后再试~!',spacelink('pay'));
		}

		$data['url'] = $code_url;
		$data['dingdan'] = $dingdan;
		$data['rmb'] = $total_fee;
		$data['yblink'] = get_link("pay/wxpay/init/".$row->id);
		$this->load->get_templates('common');
		$this->load->view('wxpay.html',$data);
	}

	//查询订单状态
	public function init($id=0){
		$id = (int)$id;
		$msg = 'no';
		if($id>0){
			$row=$this->Csdb->get_row('pay','pid',$id);
			if($row && $row->pid==1){
				$msg = 'ok';
			}
		}
		$data['msg'] = $msg;
		$data['url'] = spacelink('pay/lists');
		getjson($data,0,1);
	}

	//异步返回
	public function notify_url(){
		//微信回调类
        require_once CSPATH."pay/wxpay/wxpays.php";
        $pay = new Wxpays();
        $out_trade_no = $pay->is_sign();
        if($out_trade_no){

        	echo "success";

        	$row=$this->Csdb->get_row('pay','*',$out_trade_no,'dingdan');
            if($row && $row->pid!=1){
                //增加金钱
                $this->db->query("update ".CS_SqlPrefix."user set rmb=rmb+".$row->rmb." where id=".$row->uid."");
                //改变状态
                $this->db->query("update ".CS_SqlPrefix."pay set pid=1 where id=".$row->id."");
                //发送通知
                $add['uida']=$row->uid;
                $add['uidb']=0;
                $add['name']=L('pay_11');
                $add['neir']=L('pay_17',array($row->rmb,$out_trade_no));
                $add['addtime']=time();
                $this->Csdb->get_insert('msg',$add);
            }

        }else{
        	echo 'fail';
        }
	}
}

