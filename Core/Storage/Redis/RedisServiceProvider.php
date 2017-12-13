<?php
namespace Cache\Core\Storage\Redis;

use Cache\Core\Basis\ServiceProvider;

/**
 * 获取environment
 * User: Liu xiaoquan
 * Date: 2017/6/17
 * Time: 14:10
 */
class RedisServiceProvider extends ServiceProvider
{
    protected function initialize()
    {
        $this->app['config']['database.redis.master.host'] = 'master_test';
        $this->app['config']['database.redis.slave.host'] = 'slave_test';

        $this->app['config']['database.redis.master.database'] = 0;
        $this->app['config']['database.redis.slave.database'] = 0;
    }

    public function register()
    {
        $this->app->singleton('redis', function() {
            return new \Cache\Core\Storage\Redis\Redis($this->app);
        });
    }

    public function boot()
    {
        $this->initialize();
//        $this->app->make('redis');
    }

}