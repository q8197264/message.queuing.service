<?php
namespace Cache\App\queue;

/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/9/15
 * Time: 18:10
 */
class push
{
    public function __construct($app)
    {
        $this->appmodel = $app->make('appmodel');
    }

    public function setExchange($exc='', $extype=null, $exflags=null)
    {
        $this->appmodel->setExchange($exc, $extype, $exflags);
        return $this;
    }
    public function addQueue($qkey = '', $routekey = null, $queueflags = null)
    {
        $this->appmodel->addQueue($qkey, $routekey, $queueflags);

        return $this;
    }

    public function __call($method, array $args)
    {
        if (in_array($method, array('send','rpc'))) {
            return call_user_func_array(array($this->appmodel, $method), $args);
        }
        die('The method no exists');
    }
}