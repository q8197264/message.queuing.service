<?php
namespace Cache\Core\Basis;

use Exception;
//use Cache\Core\Contracts\Basis\AppContainer;

/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/9/13
 * Time: 10:48
 */
class Server
{
    protected $app;

    protected $storage;//存储模块

    protected $workspace;//工作空间

    private $deferredEvents = array();

    public function __construct(AppContainer $app, $config, $workspace)
    {
        $this->app       = $app;
        $this->workspace = $workspace = $this->app['config']['app.workspace'][$workspace];

        $this->app->singleton('appmodel', function ($app) use ($workspace, $config) {
            $appmodel = $workspace.'\model\appmodel';
            return new $appmodel($app, $config);
        });
    }

    public function __call($method, $parameters)
    {
        $classfile = $this->workspace.'\\'.$method;
        if (class_exists($classfile)) {

            //the last call method
            return $this->fireDeferredEvent(new $classfile($this->app));
        }

        $closure = function($obj) use ($method, $parameters) {
            if (is_object($obj) && method_exists($obj, $method)) {
                return call_user_func_array(array($obj, $method), $parameters);
            } else {
                throw new Exception('The method '.get_class($obj).'::'.$method.'() is not found');
            }
        };
        $this->pushDeferredEvent($closure);

        return $this;
    }

    //入队
    protected function pushDeferredEvent($closure)
    {
        $this->deferredEvents[] = $closure;
    }

     //出队 （先进先出）
    protected function pullDeferredEvent()
    {
        return array_shift($this->deferredEvents);
    }

    protected function fireDeferredEvent($obj, $res='')
    {
        $closure = $this->pullDeferredEvent();
        if (empty($closure) || !$closure instanceof \Closure) {
            return $res;
        }

        try {
            $res['data'] = call_user_func($closure, $obj);
        } catch(Exception $e) {
            $res['errMsg'] = $e->getMessage();
            return $res;
        }

        return $this->fireDeferredEvent($obj, $res);
    }


}