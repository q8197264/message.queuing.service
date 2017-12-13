<?php
namespace Cache\Core\Contracts\Container;

/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/7/21
 * Time: 17:52
 */
interface Container
{
    public function make($abstract, array $parameters=array());
    public function bind($abstract, $concrete = null, $shared = false);
}