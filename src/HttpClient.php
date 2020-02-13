<?php
namespace httpClient;

use Exception;

class HttpClient{
    private static $instance;
    private $ch;
    private $url;
    private $method = 'GET';
    private $cookie;
    private $cookie_file;
    private $postData;
    private $timeout = 10;
    private $header;
    private $proxyAuth = CURLAUTH_BASIC;
    private $proxy = null;
    private $upload;
    private $ssl = false;
    private $showHead = false;
    private $follow = true;
    private $body;
    private $userAgent = [
        'web'=>[
            'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36'
        ],
        'mobile'=>[
            'Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Mobile Safari/537.36'
        ]
    ];
    private $contentType = [
        'form'=>'application/x-www-form-urlencoded',
        'json'=>'application/json',
        'text'=>'text/plain',
        'js'=>'application/javascript',
        'html'=>'text/html',
        'xml'=>'application/xml'
    ];

    private function __construct(){

    }

    /**
     * @param mixed $ssl
     * @return HttpClient
     */
    public function setSsl($ssl)
    {
        $this->ssl = $ssl;
        return $this;
    }
    /**
     * @param mixed $showHead
     * @return HttpClient
     */
    public function setShowHead($showHead)
    {
        $this->showHead = $showHead;
        return $this;
    }
    /**
     * @param mixed $upload
     * @return HttpClient
     */
    public function setUpload($upload = true)
    {
        $this->upload = $upload;
        return $this;
    }
    /**
     * @param mixed $proxy
     * @return HttpClient
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
        return $this;
    }
    /**
     * @param $proxyAuth
     * @return HttpClient
     */
    public function setProxyAuth($proxyAuth)
    {
        $this->proxyAuth = $proxyAuth;
        return $this;
    }
    private function __clone(){}

    public static function instance($force = false){
        if(empty(self::$instance) || $force){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function setUrl($url){
        $this->url = $url;
        return $this;
    }
    public function setMethod($method){
        $this->method = strtoupper($method);
        return $this;
    }
    public function setPostData($postData,$isJson = false){
        $post_str = '';
        if(!$isJson) {
            if (is_array($postData)) {
                $post_str = http_build_query($postData);
            }
            if (is_string($postData)) {
                $post_str = urlencode($postData);
            }
            $this->setHeader(array(
                'Content-Type: application/x-www-form-urlencoded',
                'Content-Length: ' . strlen($post_str)
            ));
        }else{
            $post_str = json_encode($postData);
            $this->setHeader(array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($post_str)
            ));
        }
        $this->setAjax();
        $this->postData = $post_str;
        return $this;
    }
    public function setCookie($cookie){
        $this->cookie = $cookie;
        return $this;
    }
    public function setCookieFile($cookie_file){
        $this->cookie_file = $cookie_file;
        return $this;
    }
    public function setTimeOut($timeout){
        $this->timeout = $timeout;
        return $this;
    }
    public function setHeader($header){
        if(empty($this->header)) {
            $this->header = $header;
        }else{
            $this->header = array_merge($this->header,$header);
        }
        return $this;
    }
    public function setAjax(){
        if(empty($this->header)){
            $this->header = [
                'X-Requested-With: XMLHttpRequest'
            ];
        }else{
            if($key = array_search('X-Requested-With: XMLHttpRequest',$this->header) === false){
                $this->header[] = 'X-Requested-With: XMLHttpRequest';
            }
        }
        return $this;
    }
    /**
     * @param string $content_type ['form','json','text','html','js','xml']
     * @return $this
     */
    public function setContentType($content_type){
        $con_type = $this->contentType[$content_type];
        if(empty($this->header)){
            $this->header = [
                'Content-Type: '.$con_type
            ];
        }else{
            $is_content_type = false;
            foreach ($this->header as $k=>$header){
                if(strpos($header,'Content-Type:') !== false){
                    $this->header[$k] = 'Content-Type: '.$con_type;
                    $is_content_type = true;
                }
            }
            if(!$is_content_type){
                $this->header[] = 'Content-Type: '.$con_type;
            }
        }
        return $this;
    }
    public function setUserAgent($ua){
        $user_agent = $this->userAgent[$ua][array_rand($this->userAgent[$ua])];
        if(empty($this->header)){
            $this->header = [
                'User-Agent: '.$user_agent
            ];
        }else{
            $is_ua = false;
            foreach ($this->header as $k=>$header){
                if(strpos($header,'Content-Type:') !== false){
                    $this->header[$k] = 'Content-Type: '.$user_agent;
                    $is_ua = true;
                }
            }
            if(!$is_ua){
                $this->header[] = 'Content-Type: '.$user_agent;
            }
        }
        return $this;
    }
    public function setFollow($follow){
        $this->follow = $follow;
        return $this;
    }
    /**
     * @param null $url
     * @param null $method
     * @return Response
     * @throws Exception
     */
    public function send($url = null, $method = null){
        $this->ch = curl_init();
        if(empty($this->url) && empty($url)){
            throw new Exception('url not empty');
        }
        curl_setopt($this->ch,CURLOPT_URL,$url ? $url : $this->url);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method ? strtoupper($method) : $this->method);
        if(($this->method === 'POST' || strtoupper($method) === 'POST') && $this->postData){
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->postData);
            if($this->upload){
                if (class_exists('\CURLFile')) {
                    //$postData = array('file' => new \CURLFile(realpath($path)));//>=5.5
                    curl_setopt($this->ch, CURLOPT_SAFE_UPLOAD, true);
                } else {
                    //$postData = array('file' => '@' . realpath($path));//<=5.5
                    if (defined('CURLOPT_SAFE_UPLOAD')) {
                        curl_setopt($this->ch, CURLOPT_SAFE_UPLOAD, false);
                    }
                }
            }
        }
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, $this->follow);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $this->ssl);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->ssl);
        curl_setopt($this->ch, CURLOPT_HEADER,$this->showHead);
        if($this->proxy !== null){
            curl_setopt($this->ch, CURLOPT_HTTPPROXYTUNNEL, true);
            curl_setopt($this->ch, CURLOPT_PROXYAUTH, $this->proxyAuth); //代理认证模式
            curl_setopt($this->ch, CURLOPT_PROXY, $this->proxy); //代理服务器地址
        }
        if($this->cookie_file && $this->cookie){
            throw new Exception('cookie and cookie_file only one');
        }
        if($this->cookie) {
            curl_setopt($this->ch, CURLOPT_COOKIE, $this->cookie);
        }
        if($this->cookie_file) {
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookie_file);
            curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        }
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
        if($this->header) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->header);
        }
        $this->body = curl_exec($this->ch);
        return $this->response();
    }
    /**
     * @param $url
     * @param array $header
     * @return HttpClient|Response
     * @throws Exception
     */
    public static function get($url,$header = []){
        self::$instance = self::instance();
        if($header){
            self::$instance->setHeader($header);
        }
        return self::$instance->setUrl($url)->send();
    }
    /**
     * @param $url
     * @param array $data
     * @param array $header
     * @return HttpClient|Response
     * @throws Exception
     */
    public static function post($url,$data = [],$header = []){
        self::$instance = self::instance();
        if($header){
            self::$instance->setHeader($header);
        }
        if($data){
            self::$instance->setPostData($data);
        }
        return self::$instance->setMethod('POST')->setUrl($url)->send();
    }
    /**
     * @param $url
     * @param array $data
     * @param array $header
     * @return HttpClient|Response
     * @throws Exception
     */
    public static function postJson($url,$data = [],$header = []){
        self::$instance = self::instance();
        if($header){
            self::$instance->setHeader($header);
        }
        if($data){
            self::$instance->setPostData($data,true);
        }
        return self::$instance->setMethod('POST')->setUrl($url)->send();
    }
    /**
     * @param $url
     * @param $file_data
     * @param array $header
     * @return HttpClient|Response
     * @throws Exception
     */
    public static function postFile($url,$file_data,$header = []){
        self::$instance = self::instance();
        if($header){
            self::$instance->setHeader($header);
        }
        $file_data = array_map(function ($file_path){
            if(!is_file($file_path)){
                return $file_path;
            }
            if(class_exists("\CURLFile")){
                return new \CURLFile($file_path);
            }else{
                return "@".$file_path;
            }
        },$file_data);
        return self::$instance->setUrl($url)->setMethod('POST')->setUpload()->setPostData($file_data)->send();
    }
    public static function session($path = ''){
        self::$instance = self::instance();
        if($path){
            self::$instance->setCookieFile($path);
        }else{
            self::$instance->setCookieFile(tmpfile());
        }
        return self::$instance;
    }
    private function closeCurl(){
        curl_close($this->ch);
        return $this;
    }
    private function response(){
        $response = new Response($this->ch,$this->body);
        $this->closeCurl();
        return $response;
    }
}