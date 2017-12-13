<?php
namespace Cache\App\demo;

use Cache\App\demo\config\config;

/**
 * demo 查找缓存.
 * User: Liu xiaoquan
 * Date: 2017/9/1
 * Time: 18:24
 */
class get
{
    protected $model;

    public function __construct($app)
    {
        $this->model = $app->make('appmodel')->DB();
    }

    public function getVal($k)
    {
        $key = rKey(config::$key_prefix['demo'], $k);
        $v = $this->model->slave()->get($key);

        return $v;
    }
}