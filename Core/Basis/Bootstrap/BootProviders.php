<?php
/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/7/26
 * Time: 11:12
 */
namespace Cache\Core\Basis\Bootstrap;

use Cache\Core\Contracts\Basis\AppContainer;

/**
 * 启动 所有providers
 * Class BootProviders
 * @package Cache\Core\Basis\Bootstrap
 */
class BootProviders
{
    public function bootstrap(AppContainer $app){
        $app->boot();
    }
}