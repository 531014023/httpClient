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
HttpClient::instance()->send('https:://www.baidu.com')->response();
//post
HttpClient::instance()->setPostData(array())->send('url','POST)->response();
//post请求结果解析为json/array
HttpClient::instance()->setPostData(array())->send('url','POST)->parseToJson()->response();
HttpClient::instance()->setPostData(array())->send('url','POST)->parseToArray()->response();
//post json数据
HttpClient::instance()->setPostData(array(),true)->send('url','POST)->response();
//获取错误结果
HttpClient::instance()->send('https:://www.baidu.com')->responseError();
//获取请求信息
HttpClient::instance()->send('https:://www.baidu.com')->getInfo()->response();
//使用代理
HttpClient::instance()->setProxy('ip:port')->send('https:://www.baidu.com')->response();
```
### 使用说明
curl选项设置要在调用send()方法前
返回结果的设置在response()方法前
