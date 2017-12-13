<?php
namespace Cache\Core\Contracts\Config;


/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/7/20
 * Time: 18:49
 */
interface Repository
{
    public function has($key);

    public function set($key, $value);

    public function get($key);
}