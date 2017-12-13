<?php
namespace Cache\Core\Router;

use Cache\Core\Basis\ServiceProvider;

/**
 * Router initialize
 * User: Liu xiaoquan
 * Date: 2017/7/14
 * Time: 14:54
 */
class RoutingServiceProvider extends ServiceProvider
{
    protected $namespace = 'App';

    public function register()
    {
        echo '<p>Register routeServiceProvider: parse uri ...</p>';

        $this->registerRouter();

        $this->registerUrlGenerator();

        $this->registerRedirector();

        $this->registerPsrRequest();

        $this->registerPsrResponse();

        $this->registerResponseFactory();

        echo '<P>Register end ...</P>';
    }

    //
    protected function registerRouter()
    {
        $this->app->singleton('router', function($app){
            return new \Cache\Core\Router\Router($app);
        });
    }

    protected function registerUrlGenerator(){}

    protected function registerRedirector(){}

    protected function registerPsrRequest(){}

    protected function registerPsrResponse(){}

    protected function registerResponseFactory(){}


    public function boot()
    {
        echo '<p>Boot routeServiceProvide ... </p>';

        $this->app->booted(function () {
            $this->app['router']->getRoutes()->refreshNameLookups();
            $this->app['router']->getRoutes()->refreshActionLookups();
        });
    }

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
}