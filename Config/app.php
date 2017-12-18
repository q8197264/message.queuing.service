<?php

return array(
    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => array(

        //Cache Framework Service Providers...
        //Cache\Auth\AuthServiceProvider::class,

        //Application Service Providers...
        Cache\Core\Filesystem\FilesystemServiceProvider::class,
//        Cache\App\Providers\AppServiceProvider::class,
//        Cache\App\Providers\AuthServiceProvider::class,
//        Cache\App\Providers\EventServiceProvider::class,
//        Cache\Core\Router\RouteServiceProvider::class,
        Cache\Core\Storage\Redis\RedisServiceProvider::class,
    ),

    /*
    |--------------------------------------------------------------------------
    | project director
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */
    'workspace' => array(
        'demo' => 'Cache\App\demo',
        'queue' => 'AmqpCall',
    ),

);
