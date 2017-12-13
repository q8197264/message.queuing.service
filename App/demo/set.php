<?php
namespace Cache\App\demo;

use Cache\App\demo\config\config;

/**
 * demo 设置缓存.
 * User: Liu xiaoquan
 * Date: 2017/9/1
 * Time: 18:24
 */
class set
{
    public function __construct($app)
    {
        $this->model = $app->make('appmodel')->DB();
    }

    public function setVal($k, $v=null)
    {
        $key = rkey(config::$key_prefix['demo'], $k);
        return $this->model->master()->set($key, $v);
    }
}