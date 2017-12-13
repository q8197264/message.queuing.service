<?php
namespace Cache\Core\Exception;
/**
 * 异常处理类
 * User: 刘孝全
 * Date: 2016/7/13
 * Time: 10:55
 */
class CacheException extends \Exception
{
    function __construct( $message='', $code = 0, $previous = NULL )
    {
        parent::__construct( $message, $code, $previous );
        \CY_log::add('['.$this->getFile().']'.'['.$this->getLine().']'.$message);
    }

    /**
     * 获取错误信息，加上错误文件与行数
     * @return string
     */
    function getMsg( $response=NULL )
    {
        if (!empty($response)) {
            $response->SetReturnCode($this->getCode());
            $response->SetErrMsg($this->getMessage());
            $response->SetErrPath($this->getFile().' on line '.$this->getLine());
            $response->SetErrTrace($this->getTraceAsString());
            \CY_log::add(json_encode($response->getSend()));
        }

        return $this->getMessage();
    }
}