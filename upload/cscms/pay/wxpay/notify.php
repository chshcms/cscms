<?php

require_once CSPATH."pay/wxpay/lib/WxPay.Api.php";
require_once CSPATH."pay/wxpay/lib/WxPay.Notify.php";
require_once CSPATH."pay/wxpay/log.php";

class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
		    //支付成功，处理程序逻辑
		    $out_trade_no=$result["out_trade_no"];
			if(!empty($out_trade_no)){
	 	        $CI = &get_instance();
				//获取订单记录
				$row=$CI->Csdb->get_row('pay','*',$out_trade_no,'dingdan');
			    if($row && $row->pid!=1){
					//增加金钱
					$CI->db->query("update ".CS_SqlPrefix."user set rmb=rmb+".$row->rmb." where id=".$row->uid."");
					//改变状态
					$CI->db->query("update ".CS_SqlPrefix."pay set pid=1 where id=".$row->id."");
					//发送通知
					$add['uida']=$row->uid;
					$add['uidb']=0;
					$add['name']='在线充值成功通知';
					$add['neir']='恭喜您，您刚使用支付宝成功充值'.$row->rmb.'元，订单号：'.$row->dingdan.'，感谢您的支持~~';
					$add['addtime']=time();
					$CI->Csdb->get_insert('msg',$add);
				}
			}
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		Log::DEBUG("call back:" . json_encode($data));
		$notfiyOutput = array();
		
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
		return true;
	}
}


