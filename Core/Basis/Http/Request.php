<?php
namespace Cache\Core\Basis\Http;

use Closure;

/**
 * parse a http request
 * User: Liu xiaoquan
 * Date: 2017/6/27
 * Time: 16:38
 */
class Request
{

    protected static $urlPath;
    protected static $query=array();
    protected static $class;
    protected static $method;
    protected static $params=array();


    /**
     * 运行
     */
    public static function capture()
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            return null;
        }
        //例：http://component.yaofang.cn/allpay/cy_allpay.php?m=pay&out_trade_no=123223421&total_fee=1&pay_id=1
        self::parseUrl();

        //经过url重写：目录模式
        if (strpos(self::$urlPath,'.php')===false){
            self::getRedirectQuery();
        }else{
            //未经过url重写：参数模式
            //self::getOriginUriQuery();
        }

        self::getMethodParameters();

        //执行请求
        //self::handle();

        return new Data(self::$class, self::$method, self::$params);
    }


    public function getHttpHost(){}

    /**
     * 获取请求方法参数
     */
    private static function getMethodParameters()
    {
        self::deleteReuestMethod($_REQUEST, static::$method);
        self::deleteReuestMethod($_GET, static::$method);
        if (empty(static::$class)) {
//            trigger_error('url error : The class file is not exists!',E_USER_ERROR);
        }
    }



    //url解析
    private static function parseUrl()
    {

        $url = parse_url($_SERVER['REQUEST_URI']);
        count($url)>1 AND parse_str(end($url),self::$query);
        self::$urlPath = trim($url['path'],'/');
    }

    //经过url重写：目录模式
    protected static function getRedirectQuery()
    {
        //http://component.yaofang.cn/allpay/cy_allpay/pay?out_trade_no=123223421&total_fee=1&pay_id=1&getPayUrl=1
        $ex = explode('/',self::$urlPath);
        array_shift($ex);

        //此处解析路由

        array_shift($ex);


        self::$class = array_shift($ex);


        self::$method = array_shift($ex);

        $_params=array();
        array_walk(self::$query,function($v,$k)use(&$_params){
            if(!preg_match('/[\/\\\]/',$k)) {
                $_params[$k]=$v;
            }
        });
        self::$params = empty($_params)?( empty($ex)?array():(count($ex)>0?$ex:array_shift($ex)) ):array_merge($ex,$_params);
    }



    //未经过url重写：参数模式
    protected static function getOriginUriQuery()
    {
        self::$class = ltrim(rtrim(strrchr(self::$urlPath,'/'),'.php'),'/');
        self::$method = array_shift(self::$query);
        self::$params=self::$query;
    }


    /**
     * 从请求中获取出受访问的方法 (...xx.com?m={$method}) ，并把它从请求中剔除
     * @param array $array
     * @param string $m
     */
    private static function deleteReuestMethod( &$array=array(), $m='' )
    {
        list($k,$v)=each($array);
        reset($array);
        if ($k=='m' && $v==$m){
            array_shift($array);
        }
    }





}