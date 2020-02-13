<?php
/**
 * Created by PhpStorm.
 * User: dy
 * Date: 2020/2/13
 * Time: 17:33
 */
namespace httpClient;
class Response
{
    private $status_code;
    private $body;
    private $error;
    private $errno;
    private $header;
    public function __construct($ch,$body)
    {
        $this->body = $body;
        $this->header = curl_getinfo($ch);
        $this->status_code = $this->header['http_code'];
        $this->error = curl_error($ch);
        $this->errno = curl_errno($ch);
    }
    public function parseToJson(){
        $this->body = json_decode($this->body);
        return $this;
    }
    public function parseToArray(){
        $this->body = json_decode($this->body,true);
        return $this;
    }
    public function __isset($name)
    {
        return isset($this->$name);
    }
    public function __get($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }
    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }
    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }
    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
    /**
     * @return int
     */
    public function getErrno()
    {
        return $this->errno;
    }
    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }
}