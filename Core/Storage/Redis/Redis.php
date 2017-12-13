<?php
namespace Cache\Core\Storage\Redis;

use Cache\Core\Contracts\Redis\Redis as RedisContract;
/**
 * redis 数据库驱动
 * 负责 操作数据库的 CURD
 * User: Liu xiao quan
 * Date: 2016/7/13
 * Time: 11:21
 */
class Redis implements RedisContract
{

    private static $db;

    /*
     * [
     *  'master'=>['table'=>,'database'=>],
     *  'slave'=>['table'=>,'database'=>]
     * ]
     */
    function __construct($app)
    {
        extract($t=$app['config']['database.redis']);
        $this->master = $master;
        $this->slave = $slave;
    }

    //数据库驱动连接 DAO 层
    protected function db()
    {
        if ( empty(self::$db) ) {
            require_once(env('COMPONENT_ROOT').'/DAO/DAO.php');
            self::$db = new \DAO;
        }

        return self::$db;
    }

    public function master()
    {
        return $this->db()->redis($this->master['host'], $this->master['database']);
    }

    public function slave()
    {
        return $this->db()->redis($this->slave['host'], $this->slave['database']);
    }


    public function get($key)
    {
        return $this->slave()->get($key);
    }

    public function set($key, $value)
    {
        return $this->master()->set($key, $value);
    }

    public function hget($key)
    {
        return $this->slave()->hget($key);
    }

    public function hmset($key, array $list)
    {
        return $this->slave()->hmset($key, $list);
    }

    public function hgetall($key) {
        return $this->slave()->hgetall($key);
    }

    public function put($list, $value, $minutes){}

    public function pull($list){}

    public function increment($key, $value=1){}

    public function decrement($key, $value=1){}
}