<?php	 
@session_start(); 
require_once '../../../cscms/lib/Cs_Config.php';
require_once '../../../cscms/lib/Cs_Kpan.php';
require_once 'kuaipan.class.php';
$oauth_token = isset ( $_REQUEST ['oauth_token'] ) ? $_REQUEST ['oauth_token'] : '';
$oauth_verifier = isset ( $_REQUEST ['oauth_verifier'] ) ? $_REQUEST ['oauth_verifier'] : '';
$filename = isset ( $_REQUEST ['filename'] ) ? $_REQUEST ['filename'] : '';

$kp = new Kuaipan ( CS_Kp_Ck, CS_Kp_Cs );

if(!empty($filename)){
     $access_token_arr['oauth_token']=CS_Kp_Acc;
     $access_token_arr['oauth_token_secret']=CS_Kp_Acc_Key;
     $params = array (
            'root' => 'app_folder',
            'path' => $filename 
     );
     $filename = dirname ( __FILE__ ) . '/' . $file_name;
     $ret = $kp->api ( 'fileops/download_file', '', $params, 'GET', $filename ,$access_token_arr);
     if (false === $ret) {
        $ret = $kp->getError ();
     } else {
        header("Location: ".$ret."");
		exit;
     }

}elseif(empty($oauth_token)){
     $authorization_uri = $kp->getAuthorizationUri ( 'http://'.Web_Url.Web_Path.'packs/uploads/kdisk/acc.php');
     if (false === $authorization_uri) {
        echo 'request token error' . '<br />';
        echo nl2br ( var_export ( $kp->getError () ) );
        exit ();
     } else {
        header ( 'Location:' . $authorization_uri );
		exit;
	 }
}else{
     $access_token = $kp->getAccessToken ( $oauth_token, $oauth_verifier );
     if (false == $access_token) {
        echo 'access token error' . '<br />';
        echo nl2br ( var_export ( $kp->getError () ) );
        exit ();
     } else {
        echo '以下信息有效时间为一年,请填写到后台配置<br>';
        echo 'access_token:   '.$access_token['oauth_token'].'<br>';
		echo 'access_token_secret:   '.$access_token['oauth_token_secret'];
		echo '<script>parent.att_acc(\''.$access_token['oauth_token'].'||'.$access_token['oauth_token_secret'].'\');</script>';
     }
}
