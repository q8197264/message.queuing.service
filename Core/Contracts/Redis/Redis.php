<?php
namespace Cache\Core\Contracts\Redis;

use Closure;
/**
 * redis 类型约束
 * User: Liu xiaoquan
 * Date: 2017/6/23
 * Time: 14:43
 */
interface Redis
{
    //key
    public function get($key);

    public function set($key, $value);

    //Hash
    public function hget($key);

    public function hmset($key, array $fields);

    public function hgetall($key);

    public function increment($key, $value=1);

    public function decrement($key, $value=1);

    //list
    public function put($list, $value, $minutes);

    public function pull($list);
}