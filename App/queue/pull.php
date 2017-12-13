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

    public function setExchange($exc, $extype, $exflags)
    {
        if (empty($exc) || empty($extype) || empty($exflags)) {
            trigger_error('exchange args is null', E_USER_ERROR);
        }
        $this->appmodel->setExchange($exc, $extype, $exflags);

        return $this;
    }

    public function addQueue($qkey, $routekey, $queueflags)
    {
        if (empty($qkey)) {
            trigger_error('queue name args is null', E_USER_ERROR);
        }
        $this->appmodel->addQueue($qkey, $routekey, $queueflags);

        return $this;
    }

    public function consume($func)
    {
        $this->appmodel->consume($func);
    }
}