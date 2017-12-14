<?php
namespace Cache\App\queue;

/**
 * Class pull
 * @package Cache\App\queue
 */
class pull
{

    public function __construct($app)
    {
        $this->appmodel = $app->make('appmodel');
    }

    public function setExchange($exc='', $extype='', $exflags=null)
    {
        $this->appmodel->setExchange($exc, $extype, $exflags);

        return $this;
    }

    public function addQueue($qkey='', $routekey='', $queueflags=null)
    {
        $this->appmodel->addQueue($qkey, $routekey, $queueflags);

        return $this;
    }

    public function consume($func)
    {
        $this->appmodel->consume($func);
    }
}