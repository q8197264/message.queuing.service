<?php
namespace Cache\Core\Basis;

use Cache\Core\Filesystem\Filesystem;
use Cache\Core\Contracts\Basis\AppContainer as AppContainerContract;

/**
 * register services provides.
 *
 * @package Cache\Core\Basis
 */
class ProviderRepository
{
    protected $app;
    protected $file;
    protected $servicesCachePath;

    /**
     * Initialize parameters
     *
     * @param AppContainerContract $app             应用容器
     * @param                      $file            文件
     * @param                      $manifestPath    密钥清单 路径
     */
    public function __construct(AppContainerContract $app,Filesystem $file, $servicesCachePath)
    {
        $this->app = $app;
        $this->files = $file;
        $this->servicesCachePath = $servicesCachePath;
    }

    /**
     * Register the application services.
     * @param array $provides
     */
    public function load(array $providers)
    {
        $services = $this->loadServices();

        if ($this->shouldRecompile($services, $providers)) {
            $services = $this->compileManifest($providers);
        }

        // Next, we will register events to load the providers for each of the events
        // that it has requested. This allows the service provider to defer itself
        // while still getting automatically loaded when a certain event occurs.
        foreach ($services['when'] as $provider => $events) {
//            $this->registerLoadEvents($provider, $events);
        }

        // We will go ahead and register all of the eagerly loaded providers with the
        // application so their services can be registered with the application as
        // a provided service. Then we will set the deferred service list on it.
        foreach ($services['eager'] as $provider) {
            $this->app->register($provider);
        }

        $this->app->addDeferredServices($services['deferred']);
    }

    protected function loadServices()
    {
        if ($this->files->exists($this->servicesCachePath)) {
            $services = $this->files->getRequire($this->servicesCachePath);
            if ($services) {
                return array_merge(array('when' => array()), $services);
            }
        }
    }

    /**
     * Determine if the manifest should be compiled.
     *
     * @param  array  $manifest
     * @param  array  $providers
     * @return bool
     */
    public function shouldRecompile($manifest, $providers)
    {
        return is_null($manifest) || $manifest['providers'] != $providers;
    }

    /**
     * Compile the application service manifest file.
     *
     * @param  array  $providers
     * @return array
     */
    protected function compileManifest($providers)
    {
        // The service manifest should contain a list of all of the providers for
        // the application so we can compare it on each request to the service
        // and determine if the manifest should be recompiled or is current.
        $services = $this->freshServices($providers);

        foreach ($providers as $provider) {

            $instance = new $provider($this->app);

            // When recompiling the service manifest, we will spin through each of the
            // providers and check if it's a deferred provider or not. If so we'll
            // add it's provided services to the manifest and note the provider.
            if ($instance->isDeferred()) {
                foreach ($instance->provides() as $service) {
                    $services['deferred'][$service] = $provider;
                }

                $services['when'][$provider] = $instance->when();
            }else {
                // If the service providers are not deferred, we will simply add it to an
                // array of eagerly loaded providers that will get registered on every
                // request to this application instead of "lazy" loading every time.
                $services['eager'][] = $provider;
            }
        }

        return $this->writeManifest($services);
    }


    /**
     * Create a fresh service manifest data structure.
     *
     * @param  array  $providers
     * @return array
     */
    protected function freshServices(array $providers)
    {
        return array('providers' => $providers, 'eager' => array(), 'deferred' => array());
    }


    /**
     * Write the service manifest file to disk.
     *
     * @param  array  $manifest
     * @return array
     *
     * @throws \Exception
     */
    public function writeManifest($manifest)
    {
        if (! is_writable(dirname($this->servicesCachePath))) {
            throw new \Exception('The bootstrap/cache directory must be present and writable.');
        }

        $this->files->put(
            $this->servicesCachePath, '<?php return '.var_export($manifest, true).';'
        );

        return array_merge(array('when' => array()), $manifest);
    }

    /**
     * Register the load events for the given provider.
     *
     * @param  string  $provider
     * @param  array  $events
     * @return void
     */
    protected function registerLoadEvents($provider, array $events)
    {
        if (count($events) < 1) {
            return;
        }

        $this->app->make('events')->listen($events, function () use ($provider) {
            $this->app->register($provider);
        });
    }
}