<?php
namespace Cache\Core\Model;

use Cache\Core\Contracts\Basis\AppContainer;

/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/9/4
 * Time: 16:38
 */
abstract class Model
{
    protected $app;
    protected $storage;

    public function __construct(AppContainer $app, $config)
    {
        $this->app      = $app;
        $this->storage  = empty($config['storage']) ? '' : $config['storage'];
        unset($config['storage']);
        $config   = array_filter($config);

        $this->init($app, $config);

        
    }

    //初始化配置
    abstract protected function init(AppContainer $app, array $config);

    //存储引擎
    protected function DB()
    {
        if (isset($this->app[$this->storage])) {
            return $this->app->make($this->storage);
        }
        return new \StdClass();
    }

}