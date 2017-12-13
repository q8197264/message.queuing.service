<?php
namespace Cache\Core\Filesystem;

use Cache\Core\Basis\ServiceProvider;

/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/8/11
 * Time: 14:23
 */
class FilesystemServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerNativeFilesystem();

        $this->registerFlysystem();
    }

    protected function registerNativeFilesystem()
    {
        $this->app->singleton('files', function() {
            return new FileSystem;
        });
    }

    protected function registerFlysystem()
    {
        $this->registerManager();

        $this->app->singleton('filesystem.disk',function(){
            return $this->app['filesystem']->disk($this->getDefaultDriver());
        });

        $this->app->singleton('filesystem.cloud',function (){
            return $this->app['filesystem']->disk($this->getCloudDriver());
        });
    }

    protected function registerManager()
    {
        $this->app->singleton('filesystem',function () {
            return new FilesystemManager($this->app);
        });
    }

    protected function getDefaultDriver()
    {
        return $this->app['config']['filesystem.default'];
    }

    /**
     * Get the default cloud based file driver.
     *
     * @return string
     */
    protected function getCloudDriver()
    {
        return $this->app['config']['filesystems.cloud'];
    }

    public function call($callback, array $parameters = array(), $defaultMethod = null)
    {
//        exit('Filesystem end ...');
        return BoundMethod::call($this->app, $callback, $parameters, $defaultMethod);
    }

}