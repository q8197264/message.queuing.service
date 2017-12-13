<?php
namespace Cache\Core\Basis\Bootstrap;

use Cache\Core\Contracts\Basis\AppContainer;

/**
 * 注入providers
 * User: Liu xiaoquan
 * Date: 2017/7/26
 * Time: 11:16
 */
class RegisterProviders
{
    public function bootstrap(AppContainer $app)
    {
        $app->registerConfiguredProviders();
    }
}