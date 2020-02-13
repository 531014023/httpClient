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
    private $response;
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
    public function setUpload($upload)
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
            $this->header = array(
                'Content-Type: application/x-www-form-urlencoded',
                'Content-Length: ' . strlen($post_str)
            );
        }else{
            $post_str = json_encode($postData);
            $this->header = array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($post_str)
            );
        }
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
        $this->header = $header;
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
            $this->header = array_map(function ($item)use ($con_type){
                if(strpos($item,'Content-Type:') !== false){
                    return 'Content-Type: '.$con_type;
                }
                return $item;
            },$this->header);
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
            $this->header = array_map(function ($item)use ($user_agent){
                if(strpos($item,'User-Agent:') !== false){
                    return 'User-Agent: '.$user_agent;
                }
                return $item;
            },$this->header);
        }
        return $this;
    }
    public function setFollow($follow){
        $this->follow = $follow;
        return $this;
    }
    /**
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
        $this->response = curl_exec($this->ch);
        return $this;
    }
    public function parseToJson(){
        $this->response = json_decode($this->response);
        return $this;
    }
    public function parseToArray(){
        $this->response = json_decode($this->response,true);
        return $this;
    }
    private function closeCurl(){
        curl_close($this->ch);
        return $this;
    }
    public function responseError(){
        $error = curl_error($this->ch);
        $errorno = curl_errno($this->ch);
        $this->closeCurl();
        return compact('error','errorno');
    }
    public function response(){
        if(empty($this->response)){
            $this->response = curl_error($this->ch);
        }
        $this->closeCurl();
        return $this->response;
    }
    public function getInfo(){
        $this->response = curl_getinfo($this->ch);
        return $this;
    }
    public function getFollowUrl(){
        $this->response = curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);
        return $this;
    }
}