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
    public function addQueue($qkey='', $routekey, $queueflags)
    {
        $this->appmodel->addQueue($qkey, $routekey, $queueflags);

        return $this;
    }

    public function send($data = '', $routeingkey = null)
    {
        $data = is_string($data) ? array($data) : $data;
        return $this->appmodel->send($data, $routeingkey);
    }
}