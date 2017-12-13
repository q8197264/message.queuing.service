<?php
namespace Cache\App\demo;

/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/9/1
 * Time: 18:25
 */
class del
{
    protected $app;

    function __construct($app)
    {
        $this->app = $app;
    }

    public function delDemo()
    {
        return 'del';
    }
}