<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-10
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Alipay extends Cscms_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Csuser');
		$this->lang->load('pay');
	}

    //请求支付
	public function index($id=0){
        $this->Csuser->User_Login();
	    $id=(int)$id; //订单ID
        if($id==0)  msg_url(L('pay_01'),spacelink('pay'));
        $row=$this->Csdb->get_row('pay','*',$id);
		if(!$row || $row->uid!=$_SESSION['cscms__id']){
            msg_url(L('pay_02'),spacelink('pay'));
		}

		if(CS_Alipay_JK==1){ //双功能
			require_once(CSPATH."pay/alipay_trade/alipay.config.php");
			require_once(CSPATH."pay/alipay_trade/lib/alipay_submit.class.php");
			$payment_type = "1";
			$notify_url = get_link("pay/alipay/notify_url");
			$return_url = get_link("pay/alipay/return_url");
			$seller_email = CS_Alipay_Name;
			$out_trade_no = $row->dingdan;
			$subject = L('pay_03',array($_SESSION['cscms__name']));
			$price = $row->rmb;
			$quantity = "1";
			$logistics_fee = "0.00";
			$logistics_type = "EXPRESS";
			$logistics_payment = "SELLER_PAY";
			$body = $subject;
			$show_url = '';
			$receive_name = '';
			$receive_address = '';
			$receive_zip = '';
			$receive_phone = '';
			$receive_mobile = '';
			//构造要请求的参数数组，无需改动
			$parameter = array(
				"service" => "trade_create_by_buyer",
				"partner" => trim($alipay_config['partner']),
				"payment_type"	=> $payment_type,
				"notify_url"	=> $notify_url,
				"return_url"	=> $return_url,
				"seller_email"	=> $seller_email,
				"out_trade_no"	=> $out_trade_no,
				"subject"	=> $subject,
				"price"	=> $price,
				"quantity"	=> $quantity,
				"logistics_fee"	=> $logistics_fee,
				"logistics_type"	=> $logistics_type,
				"logistics_payment"	=> $logistics_payment,
				"body"	=> $body,
				"show_url"	=> $show_url,
				"receive_name"	=> $receive_name,
				"receive_address"	=> $receive_address,
				"receive_zip"	=> $receive_zip,
				"receive_phone"	=> $receive_phone,
				"receive_mobile"	=> $receive_mobile,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
			);
			//建立请求
			$alipaySubmit = new AlipaySubmit($alipay_config);
			$html_text = $alipaySubmit->buildRequestForm($parameter,"get", L('pay_04'));
			echo $html_text;
		}else{ //即时到账
			require_once(CSPATH."pay/alipay_direct/alipay.config.php");
			require_once(CSPATH."pay/alipay_direct/lib/alipay_submit.class.php");
			$payment_type = "1";
			$notify_url = get_link("pay/alipay/notify_url");
			$return_url = get_link("pay/alipay/return_url");
			$seller_email = CS_Alipay_Name;
			$out_trade_no = $row->dingdan;
			$subject = L('pay_03',array($_SESSION['cscms__name']));
			$total_fee = $row->rmb;
			$body = $subject;
			$show_url = '';
			$anti_phishing_key = '';
			$exter_invoke_ip = '';
			//构造要请求的参数数组，无需改动
			$parameter = array(
				"service" => "create_direct_pay_by_user",
				"partner" => trim($alipay_config['partner']),
				"payment_type"	=> $payment_type,
				"notify_url"	=> $notify_url,
				"return_url"	=> $return_url,
				"seller_email"	=> $seller_email,
				"out_trade_no"	=> $out_trade_no,
				"subject"	=> $subject,
				"total_fee"	=> $total_fee,
				"body"	=> $body,
				"show_url"	=> $show_url,
				"anti_phishing_key"	=> $anti_phishing_key,
				"exter_invoke_ip"	=> $exter_invoke_ip,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
			);
			//建立请求
			$alipaySubmit = new AlipaySubmit($alipay_config);
			$html_text = $alipaySubmit->buildRequestForm($parameter,"get", L('pay_04'));
			echo $html_text;
		}
	}

	//同步返回
	public function return_url(){
        $this->Csuser->User_Login();
        if(CS_Alipay_JK==1){ //双功能
			 require_once(CSPATH."pay/alipay_trade/alipay.config.php");
			 require_once(CSPATH."pay/alipay_trade/lib/alipay_notify.class.php");
			 //计算得出通知验证结果
			 $alipayNotify = new AlipayNotify($alipay_config);
			 $verify_result = $alipayNotify->verifyReturn();
			 if($verify_result) {//验证成功
				//商户订单号
				$out_trade_no = $this->input->get('out_trade_no',true,true);
				//支付宝交易号
				$trade_no = $this->input->get('trade_no',true,true);
				//交易状态
				$trade_status = $this->input->get('trade_status',true,true);
				if($trade_status == 'WAIT_SELLER_SEND_GOODS') {  //付款成功，没有发货
                    msg_url(L('pay_10').$out_trade_no,spacelink('pay'));
				}elseif($trade_status == 'TRADE_FINISHED') {  //交易完成
                    msg_url(L('pay_07').$out_trade_no,spacelink('pay'));
			    }else{
                    echo L('pay_08').$trade_status;
				}
			 }else {
				msg_url(L('pay_09'),spacelink('pay'));
			 }

		}else{ //即时到账

			require_once(CSPATH."pay/alipay_direct/alipay.config.php");
			require_once(CSPATH."pay/alipay_direct/lib/alipay_notify.class.php");
			$alipayNotify = new AlipayNotify($alipay_config);
			$verify_result = $alipayNotify->verifyReturn();
			if($verify_result) {//验证成功
				//商户订单号
				$out_trade_no = $this->input->get('out_trade_no',true,true);
				//支付宝交易号
				$trade_no = $this->input->get('trade_no',true,true);
				//交易状态
				$trade_status = $this->input->get('trade_status',true,true);
				if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
					msg_url(L('pay_07').$out_trade_no,spacelink('pay'));
				}else{
					echo L('pay_08').$trade_status;
				}
			}else {
				msg_url(L('pay_09'),spacelink('pay'));
			}
		}
	}

	//异步返回
	public function notify_url(){
        if(CS_Alipay_JK==1){ //双功能
			require_once(CSPATH."pay/alipay_trade/alipay.config.php");
			require_once(CSPATH."pay/alipay_trade/lib/alipay_notify.class.php");
			//计算得出通知验证结果
			$alipayNotify = new AlipayNotify($alipay_config);
			$verify_result = $alipayNotify->verifyNotify();
			if($verify_result) {//验证成功
				//商户订单号
				$out_trade_no = $this->input->post('out_trade_no',true,true);
				//支付宝交易号
				$trade_no = $this->input->post('trade_no',true,true);
				//交易状态
				$trade_status = $this->input->post('trade_status',true,true);
				//获取订单记录
				$row=$this->Csdb->get_row('pay','*',$out_trade_no,'dingdan');
				if($trade_status == 'WAIT_BUYER_PAY') {  //产生了交易记录，但没有付款
				    echo "success";
				}elseif($trade_status == 'WAIT_SELLER_SEND_GOODS') {  //等待发货
				    if($row){
						$this->db->query("update ".CS_SqlPrefix."pay set pid=2 where id=".$row->id."");
						//发送通知
						$add['uida']=$row->uid;
						$add['uidb']=0;
						$add['name']=L('pay_13');
						$add['neir']=L('pay_14',array($row->rmb,$out_trade_no));
						$add['addtime']=time();
						$this->Csdb->get_insert('msg',$add);
					}
				    echo "success";
				}elseif($trade_status == 'WAIT_BUYER_CONFIRM_GOODS') {  //已经发货
				    if($row){
						$this->db->query("update ".CS_SqlPrefix."pay set pid=3 where id=".$row->id."");
						//发送通知
						$add['uida']=$row->uid;
						$add['uidb']=0;
						$add['name']=L('pay_15');
						$add['neir']=L('pay_16',array($out_trade_no));
						$add['addtime']=time();
						$this->Csdb->get_insert('msg',$add);
					}
				    echo "success";
				}elseif($trade_status == 'TRADE_FINISHED') {  //确定收货
				    if($row && $row->pid!=1){
						//增加金钱
						$this->db->query("update ".CS_SqlPrefix."user set rmb=rmb+".$row->rmb." where id=".$row->uid."");
						//改变状态
						$this->db->query("update ".CS_SqlPrefix."pay set pid=1 where id=".$row->id."");
						//发送通知
						$add['uida']=$row->uid;
						$add['uidb']=0;
						$add['name']=L('pay_11');
						$add['neir']=L('pay_12',array($row->rmb,$out_trade_no));
						$add['addtime']=time();
						$this->Csdb->get_insert('msg',$add);
					}
				    echo "success";
				}else{
				    echo "success";
				}
			}else {
				echo "fail";
			}

		}else{ //即时到账

			 require_once(CSPATH."pay/alipay_direct/alipay.config.php");
			 require_once(CSPATH."pay/alipay_direct/lib/alipay_notify.class.php");
             $alipayNotify = new AlipayNotify($alipay_config);
             $verify_result = $alipayNotify->verifyNotify();
             if($verify_result) {//验证成功
				//商户订单号
				$out_trade_no = $this->input->post('out_trade_no',true,true);
				//支付宝交易号
				$trade_no = $this->input->post('trade_no',true,true);
				//交易状态
				$trade_status = $this->input->post('trade_status',true,true);
				//获取订单记录
				$row=$this->Csdb->get_row('pay','*',$out_trade_no,'dingdan');
				if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
				    if($row && $row->pid!=1){
						//增加金钱
						$this->db->query("update ".CS_SqlPrefix."user set rmb=rmb+".$row->rmb." where id=".$row->uid."");
						//改变状态
						$this->db->query("update ".CS_SqlPrefix."pay set pid=1 where id=".$row->id."");
						//发送通知
						$add['uida']=$row->uid;
						$add['uidb']=0;
						$add['name']=L('pay_11');
						$add['neir']=L('pay_12',array($row->rmb,$out_trade_no));
						$add['addtime']=time();
						$this->Csdb->get_insert('msg',$add);
					}
				    echo "success";
				}else{
				    echo "success";
				}
			 }else {
                echo "fail";
			 }
		}
	}
}
