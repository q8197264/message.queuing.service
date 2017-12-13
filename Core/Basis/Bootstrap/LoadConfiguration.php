<?php
namespace Cache\Core\Basis\Bootstrap;

use Cache\Core\Config\Repository;
use Cache\Core\Config\Finder;
use Cache\Core\Contracts\Basis\AppContainer;
use Cache\Core\Contracts\Config\Repository as RepositoryContract;

/**
 * 加载配置
 * Class LoadConfiguration
 * @package Cache\Core\Basis\Bootstrap
 */
class LoadConfiguration
{
    /**
     * Bootstrap the application for HTTP requests.
     *
     * @return void
     */
    public function bootstrap(AppContainer $app)
    {
        $items = array();

        // First we will see if we have a cache configuration file. If we do, we'll load
        // the configuration items from that file so that it is very quick. Otherwise
        // we will need to spin through every configuration file and load them all.
        if (file_exists($cached = $app->getCachedConfigPath())) {
            $items = require $cached;

            $loadedFromCache = true;
        }

        // Next we will spin through all of the configuration files in the configuration
        // directory and load each one into the repository. This will make all of the
        // options available to the developer for use in various parts of this app.
        $app->instance('config', $config = new Repository($items));

        if (! isset($loadedFromCache)) {
            $this->loadConfigurationFiles($app, $config);
        }
        //var_dump($app['config']->get('database.redis'));
        // Finally, we will set the application's environment based on the configuration
        // values that were loaded. We will pass a callback which will be used to get
        // the environment in a web context where an "--env" switch is not present.
//        $app->detectEnvironment(function () use ($config) {
//            return $config->get('app.env', 'production');
//        });

        //date_default_timezone_set($config->get('app.timezone', 'UTC'));

       // mb_internal_encoding('UTF-8');
    }

    /**
     * Load the configuration items from all of the files.
     *
     * @param  AppContainer  $app
     * @param  Repository  $repository
     * @return void
     * @throws \Exception
     */
    protected function loadConfigurationFiles(AppContainer $app, RepositoryContract $repository)
    {

        $files = $this->getConfigurationFiles($app);

        if (! isset($files['app'])) {
            throw new \Exception('Unable to load the "app" configuration file.');
        }

        foreach ($files as $key => $path) {
            $repository->set($key, require $path);
        }
    }

    /**
     * Get all of the configuration files for the application.
     *
     * @param  Application  $app
     * @return array
     */
    protected function getConfigurationFiles(AppContainer $app)
    {
        $files = array();

        $configPath = realpath($app->configPath());

        foreach(Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $files[stristr(basename($file),'.php',true)] = $file;
        }

        return $files;
    }


}