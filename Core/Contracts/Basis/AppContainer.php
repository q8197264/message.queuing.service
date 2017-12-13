<?php
namespace Cache\Core\Contracts\Basis;

use Cache\Core\Contracts\Container\Container;
/**
 * AppContainer Contract
 * User: Administrator
 * Date: 2017/7/29
 * Time: 14:59
 */
interface AppContainer extends Container
{
    public function basePath();

    public function register($provider, $options = array(), $force = false);

    //register the application's service provides.
    public function registerConfiguredProviders();

    //Boot the application's service providers.
    public function boot();

}