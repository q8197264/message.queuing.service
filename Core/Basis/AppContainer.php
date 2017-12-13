<?php
namespace Cache\Core\Basis;

use Closure;
use Cache\Core\Filesystem\Filesystem;
use Cache\Core\Basis\ServiceProvider;
use Cache\Core\Container\Container;
use Cache\Core\Contracts\Basis\AppContainer as AppContainerContacts;
use Cache\Core\Router\RoutingServiceProvider;

/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/6/27
 * Time: 16:15
 */
class AppContainer extends Container implements AppContainerContacts
{
    /**
     * The base path for the Laravel installation.
     * @var string
     */
    protected $basePath;

    protected $hasBeenBootstraped = false;

    /**
     * All of the registered service providers.
     * @var array
     */
    protected $serviceProviders = array();

    /**
     * The names of the loaded service providers.
     * @var array
     */
    protected $loadedProviders = array();

    /**
     * The deferred services and their providers.
     *
     * @var array
     */
    protected $deferredServices = array();

    protected $bootingCallbacks = array();
    
    /**
     * determine if the application has booted
     * @var bool
     */
    protected $booted = false;

    /**
     * The array of booted callback
     * @var array
     */
    protected $bootedCallbacks = array();

    /**
     *
     * @param null $basePath  the application's base path
     */
    public function __construct($basePath = null)
    {
        if ($basePath) {
            //set all config file path
            $this->setBasePath($basePath);
        }

        //register AppContainer and container
        $this->registerBaseBindings();

        //register base service
        $this->registerBaseServiceProviders();

        //register service alias
        $this->registerCoreContainerAliases();
    }

    public function make($abstract, array $parameters=array())
    {
        $abstract = $this->getAlias($abstract);

        if (isset($this->deferredServices[$abstract])) {
            $this->loadDeferredProvider($abstract);
        }

        return parent::make($abstract, $parameters);
    }

    /**
     * Set the base path for the application.
     *
     * @param  string  $basePath
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');
        $this->bindPathsInContainer();

        return $this;
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        static::setInstance($this);

        $this->instance('app', $this);

        $this->instance(Container::class, $this);
    }

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        //        $this->register(new EventServiceProvider($this));

        //        $this->register(new LogServiceProvider($this));

//        $this->register(new RoutingServiceProvider($this));
    }

    /**
     * Bind all of the application paths in the container for load every lib config and subsequent calls.
     *
     * @return void
     */
    protected function bindPathsInContainer()
    {
        $this->instance('path', $this->path());
        $this->instance('path.base', $this->basePath());
        $this->instance('path.config', $this->configPath());
//        $this->instance('path.storage', $this->storagePath());
        $this->instance('path.database', $this->databasePath());
        $this->instance('path.bootstrap', $this->bootstrapPath());
    }

    public function path($path = '')
    {
        return $this->basePath.'/app'.($path ? '/'.$path : $path);
    }

    public function basePath($path='')
    {
        return $this->basePath.($path ? '/'.$path : $path);
    }

    /**
     * Get the path to the application configuration files.
     *
     * @param string $path Optionally, a path to append to the config path
     * @return string
     */
    public function configPath($path = '')
    {
        return $this->basePath.'/config'.($path ? '/'.$path : $path);
    }

//    public function storagePath()
//    {
//        return $this->storagePath ?: $this->basePath.'/storage';
//    }

    public function databasePath($path='')
    {
        return $this->basePath.'/database'.($path ? '/'.$path : $path);
    }

    /**
     * Register a service provider with the application.
     *
     * @param  ServiceProvider|string  $provider
     * @param  array  $options
     * @param  bool   $force
     * @return ServiceProvider
     */
    public function register($provider, $options = array(), $force = false)
    {
        if (($registered = $this->getProvider($provider)) && ! $force) {
            return $registered;
        }

        // If the given "provider" is a string, we will resolve it, passing in the
        // application instance automatically for the developer. This is simply
        // a more convenient way of specifying your service provider classes.
        if (is_string($provider)) {
            $provider = $this->resolveProvider($provider);
        }

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        $this->markAsRegistered($provider);

        // If the application has already booted, we will call this boot method on
        // the provider class so it has an opportunity to do its boot logic and
        // will be ready for any usage by this developer's application logic.
        if ($this->booted) {
            $this->bootProvider($provider);
        }

        return $provider;
    }

    /**
     * Register a new "booted" listener.
     *
     * @param  mixed  $callback
     * @return void
     */
    public function booted($callback)
    {

        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted()) {
            $this->fireAppCallbacks(array($callback));
        }
    }

    /**
     * Load the provider for a deferred service.
     *
     * @param  string  $service
     * @return void
     */
    public function loadDeferredProvider($service)
    {
        if (! isset($this->deferredServices[$service])) {
            return;
        }

        $provider = $this->deferredServices[$service];

        // If the service provider has not already been loaded and registered we can
        // register it with the application and remove the service from this list
        // of deferred services, since it will already be loaded on subsequent.
        if (! isset($this->loadedProviders[$provider])) {
            $this->registerDeferredProvider($provider, $service);
        }
    }

    /**
     * Register a deferred provider and service.
     *
     * @param  string  $provider
     * @param  string  $service
     * @return void
     */
    public function registerDeferredProvider($provider, $service = null)
    {
        // Once the provider that provides the deferred service has been registered we
        // will remove it from our local list of the deferred services with related
        // providers so that this container does not try to resolve it out again.
        if ($service) {
            unset($this->deferredServices[$service]);
        }

        $this->register($instance = new $provider($this));

        if (! $this->booted) {
            $this->booting(function () use ($instance) {
                $this->bootProvider($instance);
            });
        }
    }


    /**
     * Register a new boot listener.
     *
     * @param  mixed  $callback
     * @return void
     */
    public function booting($callback)
    {
        $this->bootingCallbacks[] = $callback;
    }
    /**
     * Add an array of services to the application's deferred services.
     *
     * @param  array  $services
     * @return void
     */
    public function addDeferredServices(array $services)
    {
        $this->deferredServices = array_merge($this->deferredServices, $services);
    }
    /**
     * Get the application's deferred services.
     *
     * @return array
     */
    public function getDeferredServices()
    {
        return $this->deferredServices;
    }

    /**
     * Set the application's deferred services.
     *
     * @param  array  $services
     * @return void
     */
    public function setDeferredServices(array $services)
    {
        $this->deferredServices = $services;
    }

    /**
     * Determine if the given abstract type has been bound.
     *
     * (Overriding Container::bound)
     *
     * @param  string  $abstract
     * @return bool
     */
    public function bound($abstract)
    {
        return isset($this->deferredServices[$abstract]) || parent::bound($abstract);
    }

    /**
     * Determine if the given service is a deferred service.
     *
     * @param  string  $service
     * @return bool
     */
    public function isDeferredService($service)
    {
        return isset($this->deferredServices[$service]);
    }

    /**
     * Resolve a service provider instance from the class name.
     *
     * @param  string  $provider
     * @return ServiceProvider
     */
    public function resolveProvider($provider)
    {
        return new $provider($this);
    }

    /**
     * Mark the given provider as registered.
     *
     * @param  ServiceProvider  $provider
     * @return void
     */
    protected function markAsRegistered($provider)
    {
        $this->serviceProviders[] = $provider;

        $this->loadedProviders[get_class($provider)] = true;
    }

    /**
     * Get the registered service provider instance if it exists.
     *
     * @param  ServiceProvider|string  $provider
     * @return ServiceProvider|null
     */
    public function getProvider($provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return self::first($this->serviceProviders, function ($value) use ($name) {
            return $value instanceof $name;
        });
    }

    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param  array  $array
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public static function first($array, callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return self::value($default);
            }

            foreach ($array as $item) {
                return $item;
            }
        }

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $value, $key)) {
                return $value;
            }
        }

        return self::value($default);
    }

    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }


    /**
     * Run bootstrap
     * 顺序启动各类的服务配置provides(引导程序, 随路由运行)
     *      含: EnvironmentVariables
     *          Configuration
     *          RegisterProvides (注册配置服务)
     *          bootProvides     (启动配置服务)
     *
     * @param array $bootstrappers   [EnvironmentVariables, Configuration, RegisterProvides, BootProvides].
     *
     */
    public function bootstrap(array $bootstrappers=array())
    {
        $this->hasBeenBootstrapped = true;

        //启动所有注册服务
        foreach ($bootstrappers as $abstract) {
            $this->make($abstract)->bootstrap($this);
        }
    }

    /**
     * Boot the application's service providers.
     * (启动配置服务)
     *
     * @return void
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        // Once the application has booted we will also fire some "booted" callbacks
        // for any listeners that need to do work after this initial booting gets
        // finished. This is useful when ordering the boot-up processes we run.
        $this->fireAppCallbacks($this->bootingCallbacks);

        array_walk($this->serviceProviders, function ($p) {
            $this->bootProvider($p);
        });

        $this->booted = true;

        $this->fireAppCallbacks($this->bootedCallbacks);
    }

    /**
     * Call the booting callbacks for the application.
     *
     * @param  array  $callbacks
     * @return void
     */
    protected function fireAppCallbacks(array $callbacks)
    {
        foreach ($callbacks as $callback) {
            call_user_func($callback, $this);
        }
    }

    /**
     * Boot the given service provider.
     * (启动单个指定服务)
     *
     * @param ServiceProvider  $provider
     *
     * @return mixed
     */
    protected function bootProvider(ServiceProvider $provider)
    {
        if (method_exists($provider, 'boot')) {
            return $provider->call(array($provider, 'boot'));
        }
    }

    /**
     * 引导服务是否已启动?
     * @return mixed
     */
    public function hasBeenBootstraped()
    {
        return $this->hasBeenBootstraped;
    }


    /**
     * Determine if the application has booted.
     *
     * @return bool
     */
    public function isBooted()
    {
        return $this->booted;
    }

    /**
     * Register all of the configured providers.
     *
     * @return void
     */
    public function registerConfiguredProviders()
    {
        (new ProviderRepository($this, new Filesystem, $this->getCachedServicesPath()))
            ->load($this->config['app.providers']);
    }

    //缓存配置功能未使用
    /**
     * Get the path to the cached services.php file.
     *
     * @return string
     */
    public function getCachedServicesPath()
    {
        return $this->bootstrapPath().'/cache/services.php';
    }

    /**
     * Get the path to the configuration cache file.
     *
     * @return string
     */
    public function getCachedConfigPath()
    {
        return $this->bootstrapPath().'/Cache/Config.php';
    }


    /*---------------------------------
     | Get the path to app configuration files
     -------------------------------------
     * @param string $path
     *
     * @return string
     */
    public function bootstrapPath($path = '')
    {
        return $this->basePath.'/Bootstrap'.($path ? '/'.$path : $path);
    }

    /**
     * Register the core class aliases in the container.
     *
     * @return void
     */
    public function registerCoreContainerAliases()
    {
        $aliases = array(
            'app'        => array( \Cache\Core\Basis\AppContainer::class, \Cache\Core\Contracts\Basis\AppContainer::class ),
            'config'     => array( \Cache\Core\Config\Repository::class, \Cache\Core\Contracts\Config\Repository::class ),
            'files'      => array( \Cache\Core\Filesystem\Filesystem::class ),
            'filesystem' => array( \Cache\Core\Filesystem\FilesystemManager::class, \Cache\Core\Contracts\Filesystem\Filesystem::class ),

//            'router'     => array( \Cache\Core\Router\RouteServiceProvider::class, \Cache\Core\Contracts\Router\Router::class ),
        );
        foreach ($aliases as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }

}