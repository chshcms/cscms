<?php 
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2015-04-10
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cspay extends Cscms_Controller {

    function __construct(){
        parent::__construct();
        $this->load->model('Csuser');
        $this->lang->load('pay');
    }

    //请求支付
    public function index($id=0,$sid=1)
    {
        $this->Csuser->User_Login();
        $id=(int)$id; //订单ID
        $sid=(int)$sid; //支付方式
        if($id==0)  msg_url(L('pay_01'),spacelink('pay'));
        $row=$this->Csdb->get_row('pay','*',$id);
        if(!$row || $row->uid!=$_SESSION['cscms__id']){
            msg_url(L('pay_02'),spacelink('pay'));
        }
        $pay_url       = 'http://api.pay.chshcms.com/pay';/* CSPAY支付请求地址 */
        $pay_sid       = $sid;  /* 交易方式，业务代码，1为支付宝，2为微信支付，3为网银 */
        $pay_mode      = '';
        $body          = L('pay_03',array($_SESSION['cscms__name']));    /* 商品名 */  
        $out_trade_no  = $row->dingdan; /* 商户订单号*/               
        $total_fee     = $row->rmb; /* 总金额 */
        $partner       = CS_Cspay_ID; /* 商户号 */ 
        $key           = CS_Cspay_Key;  /* 商户加密key */      
        $return_url    = get_link('pay/cspay/return_url');/* 同步返回地址 */
        $notify_url    = get_link('pay/cspay/notify_url');/* 异步返回地址 */
        $remark        = '';  /* 自定义字段 */

        /* 数字签名 */
        $sign_arr = array(
            'pay_sid'      => $pay_sid,
            'out_trade_no' => $out_trade_no,
            'total_fee'    => (float)$total_fee,
            'partner'      => $partner,
            'return_url'   => $return_url,
            'notify_url'   => $notify_url
        );
        $sign = $this->md5_sign($sign_arr,$key);

        /* 交易参数 */
        $parameter = $sign_arr;
        $parameter['charset'] = 'utf-8'; 
        $parameter['body'] = urlencode($body); 
        $parameter['remark'] = $remark; 
        $parameter['sign'] = $sign; 
        $button  = '<form name="CsPayForm" method="post" style="text-align:left;" action="' . $pay_url . '" style="margin:0px;padding:0px" >';
        foreach ($parameter AS $key=>$val)
        {
            $button  .= "<input type='hidden' name='$key' value='$val'/>";
        }
        $formstr = $button . '</form><script>document.CsPayForm.submit();</script>';
        echo $formstr;
    }

    //同步返回
    public function return_url()
    {
        $this->Csuser->User_Login();
          /*取返回参数*/
        $cspay_id      = intval($this->input->get('cspay_id',TRUE));        //CSPAY交易号
        $cspay_pid     = intval($this->input->get('cspay_pid',TRUE));       //CSPAY交易结果
        $pay_sid         = intval($this->input->get('pay_sid',TRUE));         //交易方式，业务代码
        $out_trade_no  = $this->input->get('out_trade_no',TRUE,TRUE);    //定单号
        $total_fee       = $this->input->get('total_fee',TRUE,TRUE);       //金额
        $remark            = $this->input->get('remark',TRUE,TRUE);          //自定义字段
        $sign          = $this->input->get('sign',TRUE,TRUE);            //加密数字签名
        $partner           = CS_Cspay_ID;                       
        $key                 = CS_Cspay_Key;                
        $return_url      = get_link('pay/cspay/return_url');            
        $notify_url      = get_link('pay/cspay/notify_url');            
        
        /* 检查数字签名是否正确 */
        $sign_arr = array(
            'pay_sid'      => $pay_sid,
            'cspay_id'     => $cspay_id,
            'cspay_pid'    => $cspay_pid,
            'out_trade_no' => $out_trade_no,
            'total_fee'    => $total_fee,
            'return_url'   => $return_url,
            'notify_url'   => $notify_url
        );
        $sign_md5 = $this->md5_sign($sign_arr,$key);

        //支付状态验证
        if ($sign_md5 == $sign && $cspay_pid==1){  //验证支付成功
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
        } else {  //验证支付失败
            msg_url(L('pay_09'),spacelink('pay'));
        }
    }

    //异步返回
    public function notify_url()
    {
        /*取返回参数*/
        $cspay_id        = intval($this->input->post('cspay_id',TRUE));        //CSPAY交易号
        $cspay_pid       = intval($this->input->post('cspay_pid',TRUE));       //CSPAY交易结果
        $pay_sid         = intval($this->input->post('pay_sid',TRUE));         //交易方式，业务代码
        $out_trade_no    = $this->input->post('out_trade_no',TRUE,TRUE);    //定单号
        $total_fee       = $this->input->post('total_fee',TRUE,TRUE);       //金额
        $remark          = $this->input->post('remark',TRUE,TRUE);          //自定义字段
        $sign            = $this->input->post('sign',TRUE,TRUE);            //加密数字签名
        $partner         = CS_Cspay_ID;                     
        $key             = CS_Cspay_Key;                
        $return_url      = get_link('pay/cspay/return_url');            
        $notify_url      = get_link('pay/cspay/notify_url');            
        
        /* 检查数字签名是否正确 */
        $sign_arr = array(
            'pay_sid'      => $pay_sid,
            'cspay_id'     => $cspay_id,
            'cspay_pid'    => $cspay_pid,
            'out_trade_no' => $out_trade_no,
            'total_fee'    => $total_fee,
            'return_url'   => $return_url,
            'notify_url'   => $notify_url
        );
        $sign_md5 = $this->md5_sign($sign_arr,$key);

        if ($sign_md5 == $sign && $cspay_pid==1){
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
            echo "YES";
        }else{
            echo "NO";
        }
    }

    //生成签名
    public function md5_sign($arr,$skey) {
        $arr_filter = array();
        foreach($arr as $key=>$val){
            if($key == "sign" || $val == "") continue;
            $arr_filter[$key] = $arr[$key];
        }
        //对数组排序
        ksort($arr_filter);
        reset($arr_filter);
        $arg  = "";
        foreach($arr_filter as $key=>$val){
            $arg.=$key."=".urlencode($val)."&";
        }
        $arg = substr($arg,0,count($arg)-2);
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        //MD5加密
        $sign =  strtoupper(md5($arg.$skey));
        return $sign;
    }
}
