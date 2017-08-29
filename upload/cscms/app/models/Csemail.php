<?php
/**
 * @Cscms 4.x open source management system
 * @copyright 2009-2013 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2013-06-12
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Csemail extends CI_Model
{
    function __construct (){
	       parent:: __construct ();
	       $config['crlf']          = "\r\n";
           $config['newline']       = "\r\n";
           $config['charset']       = 'utf-8';
           $config['protocol']      = 'smtp';
           $config['smtp_timeout']  = 5;
           $config['wordwrap']      = TRUE;
           $config['mailtype']      = 'html';
           $config['smtp_host']=CS_Smtphost; 
           $config['smtp_port']=CS_Smtpport;
           $config['smtp_user']=CS_Smtpuser;
           $config['smtp_pass']=CS_Smtppass;
           $this->load->library('email', $config);
    }

    //发送EMAIL
    function send($tomail,$title,$neir) {
		   if(CS_Smtpmode==0) return FALSE;
           $this->email->from(CS_Smtpmail, CS_Smtpname);
           $this->email->to(trim($tomail)); 
           $this->email->subject($title);
           $this->email->message($neir); 
		   if ( ! $this->email->send()){
                //echo $this->email->print_debugger();  //返回信息
                return FALSE;
		   }else{
                return TRUE;
		   }
    }
}

