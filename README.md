这是一个简单的http请求库,主要适用于一般简单访问api的使用,如果需求较为复杂建议使用原生curl或其它成熟的库
## 安装
```
composer require du-yong/http-client
```
## 使用
```php
require_once "../vendor/autoload.php";
use httpClient\HttpClient;

//简单使用
//get
echo HttpClient::get("https://www.baidu.com/")->getBody();
//post
$response = HttpClient::post("http://qzshop.93dd.top/test",['code'=>1,'data'=>'test']);
print_r($response->getBody());
//post请求结果解析为json/array
print_r($response->parseJson->getBody());
print_r($response->parseArray->getBody());
//post json数据
HttpClient::postJson("http://www.baidu.com",['code'=>1,'data'=>'test']);
//获取错误结果
print_r($response->getError());
print_r($response->getErrno());
//获取响应的请求头
print_r($response->getHeader());
//使用代理
HttpClient::instance()->setProxy('ip:port')->send('https:://www.baidu.com');
//上传文件
HttpClient::postFile("http://www.baidu.com",['code'=>1,'file'=>'/usr/local/test.png']);
//使用session
$session = HttpClient::session();
$response = $session::post("http://www.baidu.com",[
    'username'=>'username',
    'password'=>'password'
]);
echo "header: ";
print_r($response->getHeader());
echo "body: ";
print_r($response->getBody());
```
## 完整使用
```php
//get
HttpClient::instance()->send('https:://www.baidu.com');
//post
HttpClient::instance()->setPostData(array())->send('url','POST);
//post请求结果解析为json/array
HttpClient::instance()->setPostData(array())->send('url','POST)->parseJson()->getBody();
HttpClient::instance()->setPostData(array())->send('url','POST)->parseArray()->getBody();
//post json数据
HttpClient::instance()->setPostData(array(),true)->send('url','POST);
//获取错误结果
HttpClient::instance()->send('https:://www.baidu.com')->getError();
//获取响应的请求头
HttpClient::instance()->send('https:://www.baidu.com')->getHeader();
//使用代理
HttpClient::instance()->setProxy('ip:port')->send('https:://www.baidu.com');
//上传文件
HttpClient::instance()->setPostData(array('file'=>new \CURLFile($path)))->setUpload()->send('url','POST);
HttpClient::instance()->setPostData(array('file'=>'@'.$path))->setUpload()->send('url','POST);
```
### 使用说明
简单版只是内部封装了常用的完整版，如果需要设置代理这类的操作就需要使用完成版调用
curl选项设置要在调用send()方法前
返回结果的设置在response()方法前

更多用法请参见源码
