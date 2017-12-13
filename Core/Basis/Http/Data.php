<?php
namespace Cache\Core\Basis\Http;

/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/7/11
 * Time: 16:23
 */
class Data
{
    public $parameters = array();

    public $class;

    public $method;

    public function __construct($class, $method, array $parameters)
    {
        $args = compact('class','method','parameters');
        foreach ($args as $k=>$v) {
            $this->$k = $v;
        }
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getParameters()
    {
        return $this->parameters;
    }
}