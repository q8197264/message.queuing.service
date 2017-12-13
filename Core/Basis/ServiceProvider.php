<?php
namespace Cache\Core\Basis;

use Cache\Core\Router\BoundMethod;

/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/6/23
 * Time: 10:16
 */
abstract class ServiceProvider
{
    protected $app;
    
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public function __construct(AppContainer $app)
    {
        $this->app = $app;
    }



    //初始化启动当前定制服务
    public function bootstrap()
    {
        //起动所有配置的服务
        echo __CLASS__.": 启动定制服务...";

//        $this->AppContainer->boot();
//        $this->AppContainer->make(\Cache\App\OAuth2\Controller\OAuth2::class);

    }

    //启动当前服务
    public function bootProvider()
    {
        echo __METHOD__;
    }

    //the service attach depend classes
    public function provides()
    {
        return array();
    }

    /**
     * Determine if the provider is deferred.
     *
     * @return bool
     */
    public function isDeferred()
    {
        return $this->defer;
    }

    public function when()
    {}


    /**
     * Call the given Closure / class@method and inject its dependencies.
     *
     * @param  callable|string  $callback
     * @param  array  $parameters
     * @param  string|null  $defaultMethod
     * @return mixed
     */
    public function call($callback, array $parameters = array(), $defaultMethod = null)
    {
        return BoundMethod::call($this->app, $callback, $parameters, $defaultMethod);
    }

    //改别名

}