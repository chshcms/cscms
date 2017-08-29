<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-10
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ybpay extends Cscms_Controller {

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
        require_once(CSPATH."pay/yeepay/yeepayCommon.php");
        $p2_Order = $row->dingdan;
        $p3_Amt	= $row->rmb;
        $p4_Cur	= "CNY";
        $p5_Pid	= L('pay_03',array($_SESSION['cscms__name']));
        $p6_Pcat = '';
        $p7_Pdesc = $p5_Pid;
        $p8_Url	= get_link("pay/ybpay/return_url");												
        $pa_MP = '';
        $pd_FrpId = '';
        $pr_NeedResponse = "1";
        $hmac = getReqHmacString($p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse);

  		$button  = '<form name="CsPayForm" method="post" style="text-align:left;" action="'.$reqURL_onLine.'" style="margin:0px;padding:0px" >';
  		$button  .= "<input type='hidden' name='p0_Cmd' value='".$p0_Cmd."'/>";
  		$button  .= "<input type='hidden' name='p1_MerId' value='".$p1_MerId."'/>";
  		$button  .= "<input type='hidden' name='p2_Order' value='".$p2_Order."'/>";
  		$button  .= "<input type='hidden' name='p3_Amt' value='".$p3_Amt."'/>";
  		$button  .= "<input type='hidden' name='p4_Cur' value='".$p4_Cur."'/>";
  		$button  .= "<input type='hidden' name='p5_Pid' value='".$p5_Pid."'/>";
  		$button  .= "<input type='hidden' name='p6_Pcat' value='".$p6_Pcat."'/>";
  		$button  .= "<input type='hidden' name='p7_Pdesc' value='".$p7_Pdesc."'/>";
  		$button  .= "<input type='hidden' name='p8_Url' value='".$p8_Url."'/>";
  		$button  .= "<input type='hidden' name='p9_SAF' value='".$p9_SAF."'/>";
  		$button  .= "<input type='hidden' name='pa_MP' value='".$pa_MP."'/>";
  		$button  .= "<input type='hidden' name='pd_FrpId' value='".$pd_FrpId."'/>";
  		$button  .= "<input type='hidden' name='pr_NeedResponse' value='".$pr_NeedResponse."'/>";
  		$button  .= "<input type='hidden' name='hmac' value='".$hmac."'/>";
  		$formstr = $button . '</form><script>document.CsPayForm.submit();</script>';
  		echo $formstr;
	}

	//支付返回
	public function return_url()
	{
        require_once(CSPATH."pay/yeepay/yeepayCommon.php");
        $return = getCallBackValue($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);
        $bRet = CheckHmac($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);
        if($bRet){
            if($r1_Code=="1"){
	             if($r9_BType=="1"){
		              $out_trade_no = $this->input->get_post('r6_Order',true,true);
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
                      msg_url(L('pay_07').$out_trade_no,spacelink('pay'));
				 }elseif($r9_BType=="2"){
                      msg_url(L('pay_09'),spacelink('pay'));
				 }
			}else{
                 msg_url(L('pay_09'),spacelink('pay'));
			}
		}
	}
}
