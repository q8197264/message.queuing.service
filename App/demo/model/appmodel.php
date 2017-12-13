<?php
namespace Cache\App\demo\model;

use Cache\App\demo\config\config;
use Cache\Core\Model\Model;

/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/9/4
 * Time: 16:38
 */
class appmodel extends Model
{
    protected $controllers = array(
        \Cache\App\demo\get::class,
        \Cache\App\demo\set::class,
        \Cache\App\demo\del::class,
    );

    protected function setConfig()
    {
        $this->app['config']['database.redis.master.host']     = config::$init['host']['master'];
        $this->app['config']['database.redis.slave.host']      = config::$init['host']['slave'];
        $this->app['config']['database.redis.master.database'] = config::$init['database'];
        $this->app['config']['database.redis.slave.database']  = config::$init['database'];
    }
}