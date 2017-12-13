resque 消息队列服务

## 使用说明 ##
### 入队 ###
```php
<?php
//入队列url  :queue=队列名称
$url = 'http://message.queuing.service/resque/queue/enqueue?queue=default';

//入队列参数
$post = array(
    'callback_url'=>'http://xxx.yaofang.cn/xxx/xxx',//出队处理接口 （队列出队时通过curl 访问此url）
    'callback_ip'=>'192.168.10.146:80', //callback_url接口所在服务器的ip （必填，内部访问）
    'callback_args'=>array('out_trade_no','5202143') //欲向callback_url接口传递的参数
);

echo $ex = http_curl( $url, $post, '127.0.0.1:80' );?>
```



### 出队 ###
```php
自已实现业务逻辑，必须保证接口可访问
http://xxx.yaofang.cn/xxx/xxx 可访问接口 （须自已实现）
此接口返回参数必须为 array('return_code'=>0)形式的数组
return_code=0; 代表接口执行成功

```



### 入队测试（须配置host） ###
入队url：http://message.queuing.service/resque/queue/add_test
出队url：http://message.queuing.service/resque/queue/callback_test