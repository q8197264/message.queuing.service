<?php
namespace Cache\Core;
/**
 * 自动加载类的函数
 * User: 刘孝全
 * Date: 2016/6/29
 * Time: 17:00
 */
class AutoLoader
{
    private $dir;

    function __construct( $dir=NULL )
    {
        $this->dir = isset($dir) ? substr($dir,0,-1) : dirname(dirname(__FILE__));
    }

    static function Register( $dir=NULL )
    {
        ini_set('unserialize_callback_func', 'spl_autoload_call');
        spl_autoload_register(array(new self($dir),'autoload'),true);
    }

    function autoload( $className )
    {
//        if ( 0!==strpos($className, strstr(__NAMESPACE__,'\\',true))) {
//            return;
//        }
        if (is_file($file = str_replace('\\','/',$this->dir.strstr(trim($className,'\\'),'\\')).'.php')) {
            require($file);
        }
    }
}