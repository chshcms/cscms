<?php
class Kuaipan {

    const VERSION = '1.0';
    /**
     * 获取授权需要知道的几个uri
     */
    public $authorize_uris = array (
            'request_token' => 'https://openapi.kuaipan.cn/open/requestToken',
            'access_token' => 'https://openapi.kuaipan.cn/open/accessToken',
            'authorization' => 'https://www.kuaipan.cn/api.php?ac=open&op=authorise&oauth_token=%s'
    );

    /**
     * api对应的url， 话说api地址应该有个规律啊。。。不然也不用这么焦灼了。。。
     *
     * @var array
     */
    public $api_uri = array (
            'account_info' => 'http://openapi.kuaipan.cn/1/account_info',
            'metadata' => 'http://openapi.kuaipan.cn/1/metadata',
            'shares' => 'http://openapi.kuaipan.cn/1/shares',
            'fileops/create_folder' => 'http://openapi.kuaipan.cn/1/fileops/create_folder',
            'fileops/delete' => 'http://openapi.kuaipan.cn/1/fileops/delete',
            'fileops/move' => 'http://openapi.kuaipan.cn/1/fileops/move',
            'fileops/copy' => 'http://openapi.kuaipan.cn/1/fileops/copy',
            'fileops/upload_locate' => 'http://api-content.dfs.kuaipan.cn/1/fileops/upload_locate',
            'fileops/download_file' => 'http://api-content.dfs.kuaipan.cn/1/fileops/download_file',
            'thumbnail' => 'http://conv.kuaipan.cn/1/fileops/thumbnail',
            'fileops/documentView' => 'http://conv.kuaipan.cn/1/fileops/documentView',
    );

    /**
     * Default options for curl.
     */
    protected $default_curl_opts = array (
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:10.0.2) Gecko/20100101 Firefox/10.0.2',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            //CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
    );

    private $consumer_key = null;
    private $consumer_secret = null;
    private $format = 'array';
    private $http_code = '';
    private $error_msg = '';

    /**
     * session 中存储最终oauth_token及oauth_token_secret的key
     */
    const SKEY_ACCESS_TOKEN = 's_access_token';
    const SKEY_ACCESS_SECRET = 's_access_secret';
    const SKEY_REQUEST_TOKEN = 's_request_token';
    const SKEY_REQUEST_TOKEN_SECRET = 's_request_token_secret';


    /**
     * 构造函数
     * @param string $consumer_key
     * @param string $consumer_secret
     */
    public function __construct($consumer_key = '', $consumer_secret = '') {
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
    }

    /**
     * 获取当前设置的结果返回格式
     * @return string
     */
    public function getFormat() {
        return $this->format;
    }

    /**
     * 设置返回的结果格式:json/array
     *
     * @param string $format
     */
    public function setFormat($format = '') {
        $allow_formats = array ( 'json', 'array' );
        if (!empty($format) && in_array ( $format, $allow_formats )) {
            $this->format = $format;
        }
        return $this;
    }

    /**
     * call api
     * @param string $api
     * @param string $path
     * @param array $params
     * @param string $http_method
     * @param string $file_path
     * @return multitype:string
     */
    public function api($api, $path = '', $params = array (), $http_method = 'GET', $file_path = '', $oauth_token_arr = '') {

		if (empty ( $oauth_token_arr )){
              $token = $this->getAccessToken();
		}else{
              $token = $oauth_token_arr;
              $_SESSION [self::SKEY_REQUEST_TOKEN] = $token ['oauth_token'];
              $_SESSION [self::SKEY_REQUEST_TOKEN_SECRET] = $token ['oauth_token_secret'];
              $_SESSION [self::SKEY_ACCESS_TOKEN] = $token ['oauth_token'];
              $_SESSION [self::SKEY_ACCESS_SECRET] = $token ['oauth_token_secret'];
		}

        if (empty ( $token ['oauth_token'] ) || empty ( $token ['oauth_token_secret'] )) {
            exit ( 'An oauth_token and oauth_token_secret is required' );
        }
        if (empty ( $params ['oauth_token'] )) {
            $params ['oauth_token'] = $token ['oauth_token'];
        }
        $http_method = strtoupper($http_method);
        $params = $this->prepareParams ( $params );
        
        if (strcasecmp ( substr ( $api, 0, 4 ), 'http' ) == 0) {
            $api_uri = $api;
        } else {
            $api_uri = isset ( $this->api_uri [trim ( $api, '\\/' )] ) ? $this->api_uri [trim ( $api, '\\/' )] : '';
        }
        
        if (empty ( $api_uri )) {
            throw new Exception('Api not exists or api uri not define.');
        }
        if (! empty ( $path )) {
            $api_uri = rtrim ( $api_uri, '/' ) . '/' . trim ( $path, '\\/' );
        }
        $full_uri = $this->finalUri($api_uri, $params, $http_method, $_SESSION[self::SKEY_ACCESS_SECRET]);
		if($api=='fileops/download_file'){  //下载
            return $full_uri;
		}
        // $uri, $http_method = 'GET', $file_path = '', $header = array ()
        $response = $this->request ( $full_uri, $http_method, $file_path );
        if (false !== $response) {
            if ($this->format == 'array') {
                $ret = json_decode ( $response, true );
            } else {
                $ret = $response;
            }
        } else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * 使用curl发送request
     * @param string $uri
     * @param string $http_method
     * @param string $file_path  如果需要发送文件，这个参数是文件地址
     * @param string $header  额外的头信息
     * @throws Exception
     */
    protected function request($uri, $http_method = 'GET', $file_path = '', $header = array ()) {
        //init
        $this->http_code = '';
        $this->error_msg = '';
        $uri_parts = parse_url ( $uri );
        $has_content_type = $has_cache_control = $has_connection = $has_keep_alive = false;
        if (!empty($header)) {
            foreach ( $header as $h ) {
                if (strncasecmp ( $h, 'Content-Type:', 13 ) == 0) {
                    $has_content_type = true;
                }
                if (strncasecmp ( $h, 'Cache-Control:', 14 ) == 0) {
                    $has_cache_control = true;
                }
                if (strncasecmp ( $h, 'Connection:', 11 ) == 0) {
                    $has_connection = true;
                }
                if (strncasecmp ( $h, 'Keep-Alive:', 11 ) == 0) {
                    $has_keep_alive = true;
                }
            }
        }
        ! $has_cache_control && $header [] = "Cache-Control: no-cache";
        ! $has_connection && $header [] = "Connection: keep-alive";
        ! $has_keep_alive && $header [] = "Keep-Alive: 300";
        
        $ch = curl_init ($uri);
        $curl_opts = $this->default_curl_opts;
        if (! empty ( $file_path )) {
            if (preg_match ( '/[^a-z0-9\-_.]/i', basename($file_path) )) {
                throw new Exception ( sprintf ( 'Security check: Illegal character in filename "%s".', $file_path ) );
            }
            if ($http_method == 'POST') {//upload file
                //check file
                if (! file_exists ( $file_path )) {
                    throw new Exception ( sprintf ( 'File not exists: "%s".', $file_path ) );
                }
                if (! filesize ( $file_path )) {
                    throw new Exception ( sprintf ( 'File size read error: "%s".', $file_path ) );
                }
                $curl_opts [CURLOPT_POST] = true;
                $curl_opts [CURLOPT_POSTFIELDS] = array ('file' => '@' . $file_path);
            } else { // download file
                // set cookies path
                $cookie_file = tempnam ( sys_get_temp_dir (), 'kp_phpsdk_cookie_' );
                $curl_opts [CURLOPT_COOKIEFILE] = $cookie_file;
                $curl_opts [CURLOPT_COOKIEJAR] = $cookie_file;
                //resource handle for save  file
                $fp = fopen ( $file_path, 'wb' );
                $curl_opts [CURLOPT_FILE] = $fp;
            }
        } else {
            // a 'normal' request, no body to be send
            if ($http_method == 'POST') {
                if (! $has_content_type) {
                    $header [] = 'Content-Type: application/x-www-form-urlencoded';
                    $has_content_type = true;
                }
                $curl_opts [CURLOPT_POST] = true;
                !empty($uri_parts['query']) && $curl_opts [CURLOPT_POSTFIELDS] = $uri_parts['query'];
            }
        }
        //set headers
        $curl_opts[CURLOPT_HTTPHEADER] = $header;
        curl_setopt_array ( $ch, $curl_opts );
        $response = curl_exec ( $ch );
        if ($response === false) {
            $error = curl_error ( $ch );
            curl_close ( $ch );
            isset($fp) && fclose($fp);
            throw new Exception ( 'CURL error: ' . $error );
        }
        unset ( $header, $uri, $http_method, $uri_parts, $file_path );
        if (! empty ( $response )) {
            $code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
            if ($code != 200) {
                $this->http_code = $code;
                $this->error_msg = $response;
                $ret = false;
            } else { // parse result
                $ret = $response;
            }
        }
        curl_close ( $ch );
        isset($fp) && fclose($fp);
        return $ret;
    }
    
    /**
     * 获取一个授权url，用户打开次授权窗口进行授权操作
     * 
     * @param string $callback_uri
     * @return boolean|string
     */
    public function getAuthorizationUri($callback_uri = 'oob') {
        $params = array (
                'oauth_callback' => $callback_uri 
        );
        $uri = $this->finalUri ( $this->authorize_uris ['request_token'], $params, 'GET' );
        $response = $this->request ( $uri, 'GET' );
        $token = json_decode ( $response, true );
        if (empty ( $token ['oauth_token'] ) || empty ( $token ['oauth_token_secret'] )) {
            return false;
        } else {
            $_SESSION [self::SKEY_REQUEST_TOKEN] = $token ['oauth_token'];
            $_SESSION [self::SKEY_REQUEST_TOKEN_SECRET] = $token ['oauth_token_secret'];
            return sprintf ( $this->authorize_uris ['authorization'], $token ['oauth_token'] );
        }
    }

    /**
     * 获取access token
     * 
     * @param string $oauth_token  用户授权以后，callback获取到的oauth_token
     * @param string $oauth_verifier  用户授权以后，callback获取到的oauth_verifier
     */
    public function getAccessToken($oauth_token = '', $oauth_verifier = '') {
        $ret = false;
        if (! empty ( $oauth_token ) && ! empty ( $oauth_verifier )) {
            $params = array (
                    'oauth_token' => $oauth_token,
                    'oauth_verifier' => $oauth_verifier
            );
            $uri = $this->finalUri ( $this->authorize_uris ['access_token'], $params, 'GET', $_SESSION [self::SKEY_REQUEST_TOKEN_SECRET] );
            $response = $this->request ( $uri, 'GET' );
            $token = json_decode ( $response, true );
            if (! empty ( $token ['oauth_token'] ) && ! empty ( $token ['oauth_token_secret'] )) {
                $_SESSION [self::SKEY_ACCESS_TOKEN] = $token ['oauth_token'];
                $_SESSION [self::SKEY_ACCESS_SECRET] = $token ['oauth_token_secret'];
                unset($_SESSION[self::SKEY_REQUEST_TOKEN], $_SESSION[self::SKEY_REQUEST_TOKEN_SECRET]);
                if ($this->format == 'array') {
                    $ret = $token;
                } else {
                    $ret = $response;
                }
            }
        } else if (empty ( $_SESSION ['oauth_token'] ) && empty ( $_SESSION ['oauth_token_secret'] )) {
            $ret = array (
                    'oauth_token' => $_SESSION [self::SKEY_ACCESS_TOKEN],
                    'oauth_token_secret' => $_SESSION [self::SKEY_ACCESS_SECRET] 
            );
        }
        return $ret;
    }

    /**
     * 返回请求的错误信息：http code, error msg.
     *
     * @return multitype:string
     */
    public function getError() {
        return array (
                'http_code' => $this->http_code,
                'response' => $this->error_msg,
        );
    }

    /**
     * 生成最终的requeset uri
     * @param string $request_uri
     * @param array $params
     * @param string $secret
     */
    private function finalUri($request_uri, $params = array (), $http_method = 'GET', $token_secret = '') {
        $full_uri = $request_uri;
        $params = $this->prepareParams($params);
        $params['oauth_signature'] = self::signature($request_uri, $http_method, $params, $this->consumer_secret, $token_secret);
        if (strpos ( $request_uri, '?' )) {
            $full_uri .= '&' . http_build_query($params);
        } else {
            $full_uri .= '?' . http_build_query($params);
        }
        return $full_uri;
    }


    /**
     * request之前，对部分参数进行初始化处理
     *
     * @param array $params
     */
    private function prepareParams($params = array ()) {
        empty($params['oauth_consumer_key'])  && $params['oauth_consumer_key'] = $this->consumer_key;
        empty($params['oauth_signature_method'])  && $params['oauth_signature_method'] = 'HMAC-SHA1';
        empty($params['oauth_timestamp'])  && $params['oauth_timestamp'] = time();
        empty($params['oauth_nonce'])  && $params['oauth_nonce'] = uniqid('');
        empty($params['oauth_version'])  && $params['oauth_version'] = '1.0';
        return $params;
    }

    /**
     * 生成请求需要的signature
     *
     * @param string $uri
     * @param string $http_method
     * @param array $params
     * @param string $secret
     */
    public static function signature($uri, $http_method, $params = array (), $consumer_secret = '', $token_secret = '') {
        $base_string = self::baseString ( $uri, $http_method, $params );
        $key = self::urlencode($consumer_secret) . '&'. self::urlencode($token_secret);
        $sign = base64_encode ( hash_hmac ( "sha1", $base_string, $key, true));
        return $sign;
    }

    /**
	 * 获取签名需要的basestring
	 *
	 * @return string
	 */
	private static function baseString ($request_uri = '', $http_method = '', $params = array ()) {
	    $uri_parts = parse_url($request_uri);
	    $base_uri = $uri_parts ['scheme'] . '://' . $uri_parts ['user'] . (! empty ( $uri_parts ['pass'] ) ? ':' : '') . $uri_parts ['pass'] . (! empty ( $uri_parts ['user'] ) ? '@' : '') . $uri_parts ['host'];
        if ($uri_parts ['port'] && $uri_parts ['port'] != self::defaultPortForScheme ( $uri_parts ['scheme'] )) {
            $base_uri .= ':' . $uri_parts ['port'];
        }
        if (! empty ( $uri_parts ['path'] )) {
            $base_uri .= $uri_parts ['path'];
        }
        if (! empty ( $uri_parts ['query'] ) && is_array($params)) {
            parse_str ( $uri_parts ['query'], $params );
        }
		$sig 	= array();
		$sig[]	= strtoupper($http_method);
		$sig[]	= $base_uri;
		if (!empty($params)) {
		    ksort ( $params );
		    $normalized = array();
		    foreach ( $params as $key => $value ) {
                if ($key != 'oauth_signature') {
                    if (is_array ( $value )) {
                        $value_sort = $value;
                        sort ( $value_sort );
                        foreach ( $value_sort as $v ) {
                            $normalized [] = self::urlencode($key) . '=' . self::urlencode($v);
                        }
                    } else {
                        $normalized [] = self::urlencode($key) . '=' . self::urlencode($value);
                    }
                }
            }
		    $sig[]	= implode('&', $normalized);
		}
		return implode('&', array_map(array('Kuaipan', 'urlencode'), $sig));
	}

	/**
	 * 获取协议默认的端口号
	 *
	 * @param string scheme
	 * @return int
	 */
	protected static function defaultPortForScheme($scheme) {
        switch ($scheme) {
            case 'http' :
                return 80;
            case 'https' :
                return 443;
            default :
                throw new Exception ( 'Unsupported scheme type, expected http or https, got "' . $scheme . '"' );
                break;
        }
    }

    /**
     * Encode a string according to the RFC3986
     *
     * @param  string
     * @return string
     */
    public static function urlencode($s) {
        if ($s === false) {
            return $s;
        } else {
            return str_replace ( '%7E', '~', rawurlencode ( $s ) );
        }
    }

}


