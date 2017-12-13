<?php
namespace Cache\Core\Container;

use Cache\Core\Exception\CacheException;
use Closure;
use ReflectionClass;
use ReflectionParameter;
use ArrayAccess;
use Cache\Core\Contracts\Container\Container as ContainerContract;

/**
 * 如果类没有依赖任何接口，那么就没有必要将类绑定到容器中了。
 * 容器绑定时，并不需要指定如何构建这些类，因为容器中会通过 PHP 的反射自动解析对象。
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/5/19
 * Time: 15:05
 */
class Container implements ArrayAccess,ContainerContract
{
    protected static $instance;

    protected $bindings = array();
    protected $aliases = array();
    protected $instances = array();

    //AppContainer set alias array
    protected $abstractAliases = array();
    protected $resolved = array();
    protected $contextual = array();
    protected $with = array();//构造参数
    protected $buildStack = array();

    /**
     * All of the registered rebound callbacks.
     *
     * @var array
     */
    protected $reboundCallbacks = [];


    public function bind($abstract, $concrete = null, $shared = false)
    {
        //销毁已存在的实例与别名
        $this->dropStaleInstances($abstract);


        //没有concrete，则默认为abstract, 代表没有需实现的接口
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        //不是匿名函数, 老子便把你打成匿名函数(目标：参数传入; 实现回调时实例化)
        if (! $concrete instanceof Closure) {

            //返回构造后的匿名函数
            $concrete = $this->getClosure($abstract, $concrete);//make | build
        }

        //把变量构造成数组：变量名为key, value为变量值
        $this->bindings[$abstract] = compact('concrete', 'shared');

        //如果解析过此对象，则重新绑定
        if ($this->resolved($abstract)) {
            $this->rebound($abstract);
        }
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string  $abstract
     * @return mixed
     */
    public function make($abstract, array $parameters=array())
    {
        try{
            $obj = $this->resolve($abstract, $parameters);
        } catch (\Exception $e) {
            exit('<p>make error: ['.$e->getLine().']'.$e->getMessage());
        }

        return $obj;
    }

    /**
     * Fire the "rebound" callbacks for the given abstract type.
     *
     * @param  string  $abstract
     * @return void
     */
    protected function rebound($abstract)
    {
        $instance = $this->make($abstract);

        foreach ($this->getReboundCallbacks($abstract) as $callback) {
            call_user_func($callback, $this, $instance);
        }
    }

    /**
     * @param $abstract
     *
     * @return array
     */
    protected function getReboundCallbacks($abstract)
    {
        return isset($this->reboundCallbacks[$abstract]) ? $this->reboundCallbacks[$abstract] : array();
    }

    /**
     * Register a shared binding in the container.
     * 注册一个共享的绑定 单例
     *
     * @param  string|array  $abstract
     * @param  \Closure|string|null  $concrete
     * @return void
     */
    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string  $abstract
     * @param  array  $parameters
     * @return mixed
     */
    protected function resolve($abstract, $parameters = array())
    {
        $abstract = $this->getAlias($abstract);

        $needsContextualBuild = ! empty($parameters) || ! is_null(
                $this->getContextualConcrete($abstract)
            );

        // If an instance of the type is currently being managed as a singleton we'll
        // just return an existing instance instead of instantiating new instances
        // so the developer can keep using the same objects instance every time.
        //判断是否已有单例
        if (isset($this->instances[$abstract]) && ! $needsContextualBuild) {
            return $this->instances[$abstract];
        }

        $this->with[] = $parameters;

        $concrete = $this->getConcrete ($abstract);

        // We're ready to instantiate an instance of the concrete type registered for
        // the binding. This will instantiate the types, as well as resolve any of
        // its "nested" dependencies recursively until all have gotten resolved.
        //判断 是否 可以创建服务实体
        if ($this->isBuildable($concrete, $abstract)) {
            $object = $this->build($concrete);
        } else {
            //不可创建实例
            $object = $this->make($concrete);
        }

        // If we defined any extenders for this type, we'll need to spin through them
        // and apply them to the object being built. This allows for the extension
        // of services, such as changing configuration or decorating the object.
//        foreach ($this->getExtenders($abstract) as $extender) {
//            $object = $extender($object, $this);
//        }

        // If the requested type is registered as a singleton we'll want to cache off
        // the instances in "memory" so we can return it later without creating an
        // entirely new instance of an object on each subsequent request for it.
        //加入单例
        if ($this->isShared($abstract) && ! $needsContextualBuild) {
            $this->instances[$abstract] = $object;
        }

//        $this->fireResolvingCallbacks($abstract, $object);

        // Before returning, we will also set the resolved flag to "true" and pop off
        // the parameter overrides for this build. After those two things are done
        // we will be ready to return back the fully constructed class instance.
        $this->resolved[$abstract] = true;

        array_pop($this->with);

        return $object;
    }

    //根据实例具体名称实例具体对象
    protected function build($concrete)
    {
        // If the concrete type is actually a Closure, we will just execute it and
        // hand back the results of the functions, which allows functions to be
        // used as resolvers for more fine-tuned resolution of these objects.
        if ($concrete instanceof Closure) {
            return $concrete($this, $this->getLastParameterOverride());
        }

        //反射
        $reflector = new ReflectionClass($concrete);

        // If the type is not instantiable, the developer is attempting to resolve
        // an abstract type such as an Interface of Abstract Class and there is
        // no binding registered for the abstractions so we need to bail out.
        //检查类是否可实例化, 排除抽象类abstract和对象接口interface
        if (! $reflector->isInstantiable()) {

            //抛出异常(接口无法实例化)
            throw new \Exception("Can't instantiate this ".$concrete);
        }

        //加入栈
        $this->buildStack[] = $concrete;

        //获取类的构造函数
        $constructor = $reflector->getConstructor();

        // If there are no constructors, that means there are no dependencies then
        // we can just resolve the instances of the objects right away, without
        // resolving any other types or dependencies out of these containers.
        // 若无构造函数，直接实例化并返回
        if (is_null($constructor)) {
            array_pop($this->buildStack);//弹出最后入Repository 栈的数据

            return new $concrete;
        }

        $dependencies = $constructor->getParameters();

        // Once we have all the constructor's parameters we can create each of the
        // dependency instances and then use the reflection instances to make a
        // new instance of this class, injecting the created dependencies in.
        //解析依赖（寻找接口型参数）
        $instances = $this->resolveDependencies($dependencies);

        array_pop($this->buildStack);

        //而类不具有构造函数时,将导致一个 ReflectionException
        return $reflector->newInstanceArgs((array) $instances);
    }

    //通过反射解决参数依赖
    protected function resolveDependencies(array $dependencies)
    {
        $results = array();

        //为构造函数对应的形参赋值
        foreach ($dependencies as $dependency) {

            //传值参数
            if ($this->hasParameterOverride($dependency)) {
                $results[] = $this->getParameterOverride($dependency);
                continue;
            }

            //默认值-无需赋值，保留默认值
            //判断此参数是否是类，是则new
            $results[] = is_null($class = $dependency->getClass())
                ? $this->resolvePrimitive($dependency)
                : $this->resolveClass($dependency);
        }

        return $results;
    }

    /**
     * Resolve a class based dependency from the container.
     *
     * @param  \ReflectionParameter  $parameter
     * @return mixed
     *
     * @throws Exception
     */
    protected function resolveClass(ReflectionParameter $parameter)
    {
        try {
            //回调加载接口
            return $this->make($parameter->getClass()->name);
        } catch (CacheException $e) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }

            throw $e;
        }
    }

    /**
     * Resolve a non-class hinted primitive dependency.
     *
     * @param  \ReflectionParameter  $parameter
     * @return mixed
     *
     * @throws CacheException
     */
    protected function resolvePrimitive(ReflectionParameter $parameter)
    {
        //Router
        if (! is_null($concrete = $this->getContextualConcrete('$'.$parameter->name))) {
            return $concrete instanceof Closure ? $concrete($this) : $concrete;
        }

        //返回此参数(非类)，默认值
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        //没被重写，且没有默认值，抛出异常
        $message = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}";
        throw new CacheException($message);
    }


    /**
     * Determine if the given dependency has a parameter override from make.
     *
     * @param  \ReflectionParameter  $dependency
     * @return bool
     */
    protected function hasParameterOverride($dependency)
    {
        return array_key_exists(
            $dependency->name, $this->getLastParameterOverride()
        );
    }

    //获取需要被重写的指定参数的值
    protected function getParameterOverride($dependency)
    {
        return $this->getLastParameterOverride()[$dependency->name];
    }

    /**
     * Get the last parameter override.
     *
     * @return array
     */
    protected function getLastParameterOverride()
    {
        return isset($this->with) ? end($this->with) : array();
    }

    //是否共享
    protected function isShared($abstract)
    {
        return isset($instances[$abstract]['share']) ? true : false;
    }

    //判断 是否 可以创建服务实体
    protected function isBuildable($concrete, $abstract)
    {
        return $concrete===$abstract || $concrete instanceof Closure;
    }

    protected function getConcrete($abstract)
    {
        //Return concrete if the abstract has already contextual
        if (! is_null($concrete = $this->getContextualConcrete($abstract))) {
            return $concrete;
        }

        //Return abstract when No binded
        if (empty($this->bindings[$abstract])) {
            return $abstract;
        }

        //Return binded concrete
        return $this->bindings[$abstract]['concrete'];
    }

    /**
     * Add a contextual binding to the container.
     *
     * @param  string  $concrete
     * @param  string  $abstract
     * @param  \Closure|string  $implementation
     * @return void
     */
    public function addContextualBinding($concrete, $abstract, $implementation)
    {
        $this->contextual[$concrete][$this->getAlias($abstract)] = $implementation;
    }

    /**
     * Get the contextual concrete binding for the given abstract.
     *
     * @param  string  $abstract
     * @return string|null
     */
    protected function getContextualConcrete($abstract)
    {
        if (! is_null($binding = $this->findInContextualBindings($abstract))) {
            return $binding;
        }

        // Next we need to see if a contextual binding might be bound under an alias of the
        // given abstract type. So, we will need to check if any aliases exist with this
        // type and then spin through them and check for contextual bindings on these.
        if (empty($this->abstractAliases[$abstract])) {
            return;
        }

        foreach ($this->abstractAliases[$abstract] as $alias) {

//            return $this->bindings[];
            if (! is_null($binding = $this->findInContextualBindings($alias))) {
                return $binding;
            }
        }
    }

    /**
     * Find the concrete binding for the given abstract in the contextual binding array.
     *
     * @param  string  $abstract
     * @return string|null
     */
    protected function findInContextualBindings($abstract)
    {
        if (isset($this->contextual[end($this->buildStack)][$abstract])) {
            return $this->contextual[end($this->buildStack)][$abstract];
        }
    }

    /**
     * Get the Closure to be used when building a type.
     * 临时创建一个没有指定名称的函数
     *
     * @param  string  $abstract
     * @param  string  $concrete
     * @return \Closure
     */
    protected function getClosure($abstract, $concrete)
    {
        //函名函数的形参是变
        return function ($container, array $parameters = array()) use ($abstract, $concrete) {
            if ($abstract == $concrete) {

                //如果相同（自已注入自已，代表注入参数就是类名本身，所以无需参数）
                return $container->build($concrete);
            }

            return $container->make($concrete, $parameters);
        };
    }


    /**
     * Define a contextual binding.
     *
     * @param  string  $concrete
     * @return \ContextualBindingBuilder
     */
    public function when($concrete)
    {
//        return new ContextualBindingBuilder($this, $this->getAlias($concrete));
    }

    public function hasMethodBinding()
    {
//        echo '<p>'.__METHOD__.'</p>';
    }


    /**
     * Determine if the given abstract type has been resolved.
     *
     * @param  string  $abstract
     * @return bool
     */
    public function resolved($abstract)
    {
        //判断别名
        if ($this->isAlias($abstract)) {
            $abstract = $this->getAlias($abstract);
        }

        return isset($this->resolved[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * Register an existing instance as shared in the container.
     *
     * @param  string  $abstract
     * @param  mixed   $instance
     * @return void
     */
    public function instance($abstract, $instance)
    {
        $this->removeAbstractAlias($abstract);

        $isBound = $this->bound($abstract);

        unset($this->aliases[$abstract]);

        // We'll check to determine if this type has been bound before, and if it has
        // we will fire the rebound callbacks registered with the container and it
        // can be updated with consuming classes that have gotten resolved here.
        $this->instances[$abstract] = $instance;

        if ($isBound) {
            $this->rebound($abstract);
        }
    }

    /**
     * Set the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Set the shared instance of the container.
     *
     * @param  Container|null  $container
     * @return static
     */
    public static function setInstance(ContainerContract $container = null)
    {
        return static::$instance = $container;
    }

    /**
     * Remove registered alias and related instance
     *
     */
    protected function removeAbstractAlias($absearch)
    {
        if (!isset($this->aliases[$absearch])) {
            return;
        }

        foreach ($this->abstractAliases as $abstract => $aliases) {
            foreach ($aliases as $index => $alias) {
                if ($alias == $absearch) {
                    unset($this->abstractAliases[$abstract][$index]);
                }
            }
        }
    }

    /**
     * Determine if the given abstract type has been bound.
     *
     * @param  string  $abstract
     * @return bool
     */
    public function bound($abstract)
    {
        return isset($this->bindings[$abstract]) ||
        isset($this->instances[$abstract]) ||
        $this->isAlias($abstract);
    }
    /**
     * Flush the container of all bindings and resolved instances.
     *
     * @return void
     */
    public function flush()
    {
        $this->aliases = [];
        $this->resolved = [];
        $this->bindings = [];
        $this->instances = [];
        $this->abstractAliases = [];
    }

    /**
     * Drop all of the stale instances and aliases.
     *
     * @param  string  $abstract
     * @return void
     */
    protected function dropStaleInstances($abstract)
    {
        unset($this->instances[$abstract], $this->aliases[$abstract]);
    }


    protected function isAlias($abstract)
    {
        return isset($this->aliases[$abstract]);
    }

    /**
     * Alias a type to a different name.
     *
     * @param  string  $abstract
     * @param  string  $alias
     * @return void
     */
    public function alias($abstract, $alias)
    {
        $this->aliases[$alias] = $abstract;
        $this->abstractAliases[$abstract][] = $alias;
    }

    /**
     * Get the alias for an abstract if available.
     *
     * @param  string  $abstract
     * @return string
     *
     * @throws \LogicException
     */
    public function getAlias($abstract)
    {
        if (! isset($this->aliases[$abstract])) {
            return $abstract;
        }

        if ($this->aliases[$abstract] === $abstract) {
            throw new CacheException("[{$abstract}] is aliased to itself.");
        }

        return $this->getAlias($this->aliases[$abstract]);
    }

    //提取别名
    protected function extractAlias(array $definition)
    {
        return array(key($definition), current($definition));
    }


    /* ++++++++++++++++++ 数组接口访问 +++++++++++++++++++++++*/
    public function offsetExists($key)
    {
        return $this->bound($key);
    }

    public function offsetGet($key)
    {
        return $this->make($key);
    }

    public function offsetSet($key, $value)
    {
        $this->bind($key, $value instanceOf Closure ? $value : function () use ($value) {
            return $value;
        });
    }

    public function offsetUnset($key){
        unset($this->bindings[$key], $this->instances[$key], $this->resolved[$key]);
    }

    public function __get($key)
    {
        return $this[$key];
    }

    public function __set($key, $value)
    {
        $this[$key] = $value;
    }
}