<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-10
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tenpay extends Cscms_Controller {

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
			require_once(CSPATH."pay/tenpay/RequestHandler.class.php");
        	$partner = CS_Tenpay_ID;
        	$key = CS_Tenpay_Key;
        	$return_url = get_link('pay/tenpay/return_url');
       		$notify_url = get_link('pay/tenpay/notify_url');

        	/* 创建支付请求对象 */
        	$reqHandler = new RequestHandler();
        	$reqHandler->init();
        	$reqHandler->setKey($key);
       	    $reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");
        	$reqHandler->setParameter("partner", $partner);
        	$reqHandler->setParameter("out_trade_no", $row->dingdan);
        	$reqHandler->setParameter("total_fee", floor($row->rmb)."00");  //总金额
        	$reqHandler->setParameter("return_url",  $return_url);
        	$reqHandler->setParameter("notify_url", $notify_url);
        	$reqHandler->setParameter("body", L('pay_03',array($_SESSION['cscms__name'])));
        	$reqHandler->setParameter("bank_type", "DEFAULT");  	  //银行类型，默认为财付通
        	//用户ip
        	$reqHandler->setParameter("spbill_create_ip", getip());//客户端IP
        	$reqHandler->setParameter("fee_type", "1");               //币种
        	$reqHandler->setParameter("subject",L('pay_03',array($_SESSION['cscms__name'])));  //商品名称
        	//系统可选参数
        	$reqHandler->setParameter("sign_type", "MD5");  	 	  //签名方式，默认为MD5，可选RSA
        	$reqHandler->setParameter("service_version", "1.0"); 	  //接口版本号
        	$reqHandler->setParameter("input_charset", "utf-8");   	  //字符集
        	$reqHandler->setParameter("sign_key_index", "1");    	  //密钥序号
        	//业务可选参数
        	$reqHandler->setParameter("attach", "");             	  //附件数据，原样返回就可以了
        	$reqHandler->setParameter("product_fee", "");        	  //商品费用
        	$reqHandler->setParameter("transport_fee", "0");      	  //物流费用
        	$reqHandler->setParameter("time_start", date("YmdHis"));  //订单生成时间
        	$reqHandler->setParameter("time_expire", "");             //订单失效时间
        	$reqHandler->setParameter("buyer_id", "");                //买方财付通帐号
        	$reqHandler->setParameter("goods_tag", "");               //商品标记
        	$reqHandler->setParameter("trade_mode","1");              //交易模式 1.即时到帐模式，2.中介担保模式，3.后台选择
        	$reqHandler->setParameter("transport_desc","");              //物流说明
        	$reqHandler->setParameter("trans_type","1");              //交易类型
        	$reqHandler->setParameter("agentid","");                  //平台ID
        	$reqHandler->setParameter("agent_type","");               //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
        	$reqHandler->setParameter("seller_id","");                //卖家的商户号
       	    //请求的URL
        	$reqUrl = $reqHandler->getRequestURL();
        	$debugInfo = $reqHandler->getDebugInfo();
        	echo '<form action="'.$reqHandler->getGateUrl().'" name="form1" method="post">';
        	$params = $reqHandler->getAllParameters();
        	foreach($params as $k => $v) {
	        	echo "<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\" />\n";
        	}
        	echo '<script language="javascript">document.form1.submit();</script></form>';
	}

	//同步返回
	public function return_url()
	{
            $this->Csuser->User_Login();
	        $partner = CS_Tenpay_ID;
            $key = CS_Tenpay_Key;
            require_once (CSPATH."pay/tenpay/ResponseHandler.class.php");
            $resHandler = new ResponseHandler();
            $resHandler->setKey($key);
	        //通知id
	        $notify_id = $this->input->get('notify_id',TRUE,TRUE); 
	        //商户订单号
	        $out_trade_no = $this->input->get('out_trade_no',TRUE,TRUE); 
	        //财付通订单号
	        $transaction_id = $this->input->get('transaction_id',TRUE,TRUE); 
	        //如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
	        $discount = $this->input->get('discount',TRUE,TRUE); 
	        //支付结果
	        $trade_statess = $_GET['trade_state']; 
	        //交易模式,1即时到账
	        $trade_mode = $this->input->get('trade_mode',TRUE,TRUE); 
            //判断签名
            if($resHandler->isTenpaySign()) {
	            if("1" == $trade_mode ) {
		            if( "0" == $trade_statess){ 
                        msg_url(L('pay_07').$out_trade_no,spacelink('pay'));
					} else {
			            msg_url(L('pay_09'),spacelink('pay'));
					}
				}elseif( "2" == $trade_mode  ) {
		            if( "0" == $trade_statess) {
						msg_url(L('pay_19'),spacelink('pay'));
		            } else {
			            msg_url(L('pay_09'),spacelink('pay'));
		            }
	            }
            } else {
				msg_url(L('pay_09'),spacelink('pay'));
            }
	}

	//异步返回
	public function notify_url()
	{

	    $partner = CS_Tenpay_ID;
        $key = CS_Tenpay_Key;
        require (CSPATH."pay/tenpay/ResponseHandler.class.php");
        require (CSPATH."pay/tenpay/RequestHandler.class.php");
        require (CSPATH."pay/tenpay/client/ClientResponseHandler.class.php");
        require (CSPATH."pay/tenpay/client/TenpayHttpClient.class.php");
	    $resHandler = new ResponseHandler();
	    $resHandler->setKey($key);
	    if($resHandler->isTenpaySign()) {
		     $notify_id = $resHandler->getParameter("notify_id");
		     $queryReq = new RequestHandler();
		     $queryReq->init();
		     $queryReq->setKey($key);
		     $queryReq->setGateUrl("https://gw.tenpay.com/gateway/simpleverifynotifyid.xml");
	         $queryReq->setParameter("partner", $partner);
		     $queryReq->setParameter("notify_id", $notify_id);
		     $httpClient = new TenpayHttpClient();
		     $httpClient->setTimeOut(5);
		     $httpClient->setReqContent($queryReq->getRequestURL());
		     if($httpClient->call()) {
			     $queryRes = new ClientResponseHandler();
			     $queryRes->setContent($httpClient->getResContent());
			     $queryRes->setKey($key);
		         if($resHandler->getParameter("trade_mode") == "1"){ //判断签名及结果（即时到帐）
		                if($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0" && $resHandler->getParameter("trade_state") == "0") {
				             $out_trade_no = $resHandler->getParameter("out_trade_no");
				             $transaction_id = $resHandler->getParameter("transaction_id");
				             $total_fee = $resHandler->getParameter("total_fee");
				             $discount = $resHandler->getParameter("discount");
							 //获取订单记录
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
				             echo "success";
				
						} else {
			                 echo "fail";
						}

		        }elseif ($resHandler->getParameter("trade_mode") == "2"){ //判断签名及结果（中介担保）

		                if($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0" ) {
				            $out_trade_no = $resHandler->getParameter("out_trade_no");
				            $transaction_id = $resHandler->getParameter("transaction_id");
				            $total_fee = $resHandler->getParameter("total_fee");
				            $discount = $resHandler->getParameter("discount");
							$row=$this->Csdb->get_row('pay','*',$out_trade_no,'dingdan');
				            switch ($resHandler->getParameter("trade_state")) {
						            case "0":	//付款成功
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
							            break;
						            case "1":	//交易创建
							            break;
						            case "2":	//收获地址填写完毕
							            break;
						            case "4":	//卖家发货成功
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
							            break;
						            case "5":	//买家收货确认，交易成功
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
							            break;
						            case "6":	//交易关闭，未完成超时关闭
							            break;
						            case "7":	//修改交易价格成功
							            break;
						            case "8":	//买家发起退款
							            break;
						            case "9":	//退款成功
							            break;
						            case "10":	//退款关闭			
							            break;
						            default:
							            break;
							}
				            echo "success";
						} else{
			 	            echo "fail";
						}
		        }
			 }else{
	             //通信失败
		         echo "fail";
			 }
		} else {
             echo "fail";
		}
	}
}
