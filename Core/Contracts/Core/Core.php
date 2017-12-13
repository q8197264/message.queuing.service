<?php
namespace Cache\Core\Contracts\Core;
/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/6/30
 * Time: 16:49
 */
interface Core
{
    //引导启动
    public function bootstrap();

    //处理请求并响应
    public function handle($request);

    //获得app instance
    //public function getApp();
}