<?php
namespace Cache\Core\Basis;

use Cache\Core\Contracts\Router\Router;
use Cache\Core\Contracts\Basis\AppContainer as AppContainerContract;
use Cache\Core\Contracts\Core\Core as CoreContract;

/**
 * 绑定容器 并 运行路由
 *  注册顺序：
 *      0.  Core (request, response)
 *      1.  AppContainer (BasePath, registerBaseServiceProviders, set aliases)
 *      2.  Core (bootstrappers) --|config
 *                                 |providers
 *                                 |boot
 *  启动顺序：
 *      0. Core 依赖参数类 (AppContainer、router)
 *      1. 启动Bootstrap 目录
 *
 *
 * User: Liu xiaoquan
 * Date: 2017/6/30
 * Time: 15:13
 */
class Core implements CoreContract
{
    /**
     * The application implementation.
     *
     * @var AppContrainer
     */
    protected $app;

    /**
     * The router instance.
     *
     * @var Router
     */
    protected $router;


    //The bootstrap classes for the application
    protected $bootstrappers = array(
//        \Cache\Core\Basis\Bootstrap\LoadEnvironmentVariables::class,//环境变量
        \Cache\Core\Basis\Bootstrap\LoadConfiguration::class,//配置参数文件
        \Cache\Core\Basis\Bootstrap\RegisterProviders::class,//注册配置服务
        \Cache\Core\Basis\Bootstrap\BootProviders::class,//启动配置服务
    );

    /**
     * 使用容器运行此类，使构造参数通过容器实例化参数依赖
     *  结果：实例容器(继承最初值)，实例路由
     * @param AppContainer $app
     * @param Router       $router
     */
    public function __construct(AppContainerContract $app)
    {
//        $this->router = $router;
        $this->app = $app;

        //注册中间服务（在启动路由前）
//        $router->middlewareGroup();
    }

    /**
     * file include
     *
     * @param       $method    $method || $class
     * @param array $parameters
     *
     * @return $this|mixed
     */
//    function __call($method, array $parameters=array())
//    {
//        exit($method);
//        //Step two
//        if (isset(self::$callback)) {
//            $abstract = '\Cache\App\\'.$method.'\Provide';
//            return call_user_func(self::$callback, $abstract);
//        }
//
//        //Step one
//        self::$callback = function($abstract) use ($method, $parameters) {
//            return call_user_func_array(array($this->app->make($abstract), $method),$parameters);
//        };
//        return $this;
//    }


    //http main lanuche
    public function handle($request)
    {
        try{
            $response = $this->sendRequestThroughRouter($request);
        }catch(\Exception $e){
            echo $e->getMessage();
        }

        return $response;
    }

    /**
     * Send the given request through the middleware / router.
     *
     * @param  Request  $request
     * @return Response
     */
    protected function sendRequestThroughRouter($request)
    {
        $this->app->instance('request', $request);

//        Facade::clearResolvedInstance('request');

        //Run application
        $this->bootstrap();

        if ($this->isHttp() && false) {

            //        return (new Pipeline($this->app))
            //            ->send($request)
            //            ->through($this->app->shouldSkipMiddleware() ? [] : $this->middleware)
            //            ->then($this->dispatchToRouter());

            return (new \Cache\Core\Router\Router($this->app))->send($request);
        }

        //CI框架 直接调用
        return $this->sendRequestThroughObject();
    }

    protected function isHttp()
    {
        return isset($_SERVER['SERVER_NAME']) AND ($_SERVER['SERVER_NAME'] == 'component.yaofang.cn');
    }

    /**
     * 本机直接调用文件直接访问
     */
    public function sendRequestThroughObject()
    {
        $app = $this->app;
        $closure = function (array $config=array(), $workspace='queue') use ($app)
        {
            $class = new \Cache\Core\Basis\Server($app, $config, $workspace);

            return $class;
        };

        return $closure;
    }

    /**
     * bootstrap the application
     */
    public function bootstrap()
    {
        //启动引导程序服务
        if (!$this->app->hasBeenBootstraped()) {
            $this->app->bootstrap($this->bootstrappers());
        }
    }

    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function bootstrappers()
    {
        return $this->bootstrappers;
    }
}