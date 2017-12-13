<?php
namespace Cache\Core\Router;

use Closure;
use Cache\Core\Contracts\Basis\AppContainer;
use Cache\Core\Contracts\Router\Router as RouterContract;

/**
 * url路由 配置类
 * User: 刘孝全
 * Date: 2016/6/29
 * Time: 16:41
 */
class Router implements RouterContract
{
    protected static $class;
    protected static $method;

    protected static $parameters = array();
    protected $AppContainer = null;

    protected $routes;

    /**
     * 运行
     */
    public function __construct(AppContainer $app)
    {
        $this->app = $app;
        $this->app->bind('resque', function($class)use($app){
            $s = $app['config']['app.options.resque'].'\\get';
            return new $s($app);
        });
    }

    //TODO
    public function send($request)
    {
        echo 5;
        static::$method = $request->getMethod();
        static::$parameters = $request->getParameters();



//        var_dump($this->app->make('resque',array('get')));


//         new \Cache\Core\Basis\Http\response($contents);
        echo $request->getClass().'->'.static::$method;
        exit('<p>xxx');
        //此处请路由出马
        //$this->class = $this->AppContainer->make($request->getClass())->boot();
        return call_user_func_array(array($this->app->make('resque'),$this->method),$this->parameters);
        //return call_user_func_array(array(new \Cache($this->AppContainer),$this->method),$this->parameters);
    }

    private static function handle()
    {
        // The class name is require using namespace. so the oauth2 should be instantiated before that
        if (is_callable(array(static::$class, static::$method))) {

            //参数排序
            $access = new \ReflectionClass(static::$class);

            $args = $access->getMethod(static::$method)->getParameters();
            $args = array_flip(array_map(create_function('$v','return $v->name;'),$args));
            if ( !empty($args) && !empty(static::$params) ){
                if( $inter = array_intersect_key($args,static::$params)) {
                    static::$params = array_merge($args,static::$params);
                }
            }
        }else{
            if (is_object(static::$class) && !in_array('__call', get_class_methods(self::$class))) {
                trigger_error('Router error >> '.get_class(static::$class).'::'.static::$method.'() is not exists!',E_USER_ERROR);
                exit;
            }
        }
    }

//    public function then()
//    {
//        call_user_func_array(array($this->class,$this->method),$this->parameters);
//    }

    public function map(){
        $this->mapCacheRoutes();
    }

    protected function mapCacheRoutes()
    {
        //load route set
        //Router::middleware('Cache')->namespace($this->namespace)->group("../../../Config/Routes.php");
    }

    protected function middleware()
    {}

    //parse route
    public static function get($uri, $callback)
    {
        //the $uri resolved for the controller and method
        var_dump(explode('/',$uri));

        if ($callback instanceof Closure) {
            self::$urlPath = $callback();
        }

        $class = stristr($uri,'/',true);
//        $parameters=$args;
    }


    /**
     * Get the underlying route collection.
     *
     * @return RouteCollection
     */
    public function getRoutes()
    {
        exit('todo: '.__METHOD__);
        return $this->routes;
    }

/*
    public static function group(array $map, $callback)
    {
        list($group, $alias) = each($map);
        switch($group) {
            case 'middleware':
                $this->middleware = $alias;
                $this->AppContainer->register($alias);
                break;
            case 'namespace':
                $this->redirect = 'Cache\App\\'.$alias;
                break;
            case 'domain':
                $this->redirect = $alias;
                break;
            case 'prefix':
                $this->prefix = $alias.'/'.$this->redirect;
                break;
        }


    }
*/

    public function __call($method, $parameters)
    {
        return call_user_func_array(
            array($this->AppContainer->make(Router::class), $method), $parameters
        );
    }
}