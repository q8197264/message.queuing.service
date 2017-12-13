<?php
namespace Cache\Bootstrap;

$app = new \Cache\Core\Basis\AppContainer(
    realpath(dirname(__DIR__))
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

//注册核心载服务
$app->singleton(
    \Cache\Core\Contracts\Core\Core::class,
    \Cache\App\AppCore::class
);

//注册控制台
//$app->singleton(
//    Illuminate\Contracts\Console\Kernel::class,
//    App\Console\Kernel::class
//);

//注册异常
//$app->singleton(
//    Illuminate\Contracts\Debug\ExceptionHandler::class,
//    App\Exceptions\Handler::class
//);

return $app;