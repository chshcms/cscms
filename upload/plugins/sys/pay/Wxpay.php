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
		
        require_once CSPATH."pay/wxpay/lib/WxPay.Api.php";
        require_once CSPATH."pay/wxpay/WxPay.NativePay.php";
        require_once CSPATH."pay/wxpay/log.php";
        $notify = new NativePay();
        $input = new WxPayUnifiedOrder();
        $input->SetBody($body);  //订单名称
        $input->SetAttach($body); //订单介绍
        $input->SetOut_trade_no($dingdan); //订单号
        $input->SetTotal_fee($total_fee*100); //订单价格，单位 分
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag($body); //详细介绍
        $input->SetNotify_url(get_link("pay/wxpay/notify_url")); //回调地址
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id($dingdan); //二维码唯一号
        $result = $notify->GetPayUrl($input);
		//print_r($result);exit;
		if(!isset($result["code_url"])){
			msg_url('获取二维码失败，请稍后再试~!',spacelink('pay'));
		}

		$data['url'] = $result["code_url"];
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
        require_once CSPATH."pay/wxpay/notify.php";

        //初始化日志
        //$logHandler= new CLogFileHandler(FCPATH."caches/logs/wxpay/".date('Y-m-d').'.log');
        //$log = Log::Init($logHandler, 15);

        $notify = new PayNotifyCallBack();
        $notify->Handle(false);
	}
}
