<?php
namespace Cache\Core\Basis\Http;

/**
 * TODO:接口最终结果响应类
 * 最终数据通过处理，再予以返回
 * User: Liu xiaoquan
 * Date: 2017/3/22
 * Time: 15:43
 */
class Response
{
    //错误码 与 对应的文字描述             【需自定义配置】
    private static $errCode = array(
        0=>'success',
        1=>'fail',
    );

    //设置属于正确码的索引                    【可自定义配置】
    // 【区别在于返回的结果中，信息描术的属性为 正确:msg 错误:err_msg】
    private static $rightScope = array(0);


    protected $response = array();

    public static $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        421 => 'Misdirected Request',                                         // RFC7540
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        451 => 'Unavailable For Legal Reasons',                               // RFC7725
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',                                     // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    );

    function __construct( array $errCode=array() )
    {
        if (!empty($errCode)) {
            self::$errCode = $errCode;
        }
    }



    /**
     * 返回最终响应结果
     * @return array|null
     */
    function Send()
    {
        empty($this->response['return_code']) AND $this->SetReturnCode(0);
        $response = $this->response;
        unset($this->response);

        return $response;
    }

    /**
     * 设置状态码：用于判断结果，附带自动匹配相应的错误信息
     * @param $code
     */
    function setReturnCode( $code=0 )
    {
        $this->response['return_code'] = $code;

        if ( !in_array($code,array_keys(self::$errCode))){
            exit("WARNING:the errCode {$code} is no set!");
        }
        if ( in_array( $code, self::$rightScope) ){
            $this->response['msg'] = self::$errCode[$code];
        }else{
            $this->response['err_msg'] = self::$errCode[$code];
        }
    }

    /**
     * 自定义响应结果参数设置
     * @param $key
     * @param $val
     */
    function setParamter( $key, $val )
    {
        if ( 'return_code'!=$key && ('data'!=$key)) {
            $this->response[$key] = $val;
        }
    }

    /**
     * 设置响应结果数组
     * @param $response
     */
    function setData( $response )
    {
        $this->response['data'] = $response;
    }

    /**
     * 错误结果信息或提示
     * @param string $errMsg
     */
    function setErrMsg( $errMsg='' )
    {
        unset($this->response['msg']);
        empty($this->response['return_code']) AND $this->SetReturnCode(1);
        $this->response['Describe'] = $errMsg;
    }

    /**
     * 错误出现的文件，显示所在代码行
     * @param $error
     */
    function setErrPath( $error )
    {
        empty($this->response['return_code']) AND $this->SetReturnCode(1);
        $this->response['err_file'] = $error;
    }

    /**
     * 错误跟踪 (仅跟踪本接口内文件))
     * @param string $str
     */
    function setErrTrace( $str='' )
    {
        $pattern = '/'.preg_quote(__NAMESPACE__,'/').'/';
        $str= implode(array_reverse(preg_grep($pattern,explode('#',$str)),'\n\r'));
        $this->response['err_trace'] = $str;
    }
    function getSend()
    {
        return $this->response;
    }

    //连贯访问方式的防错机制
    function __call( $method, $args ){
        $method = lcfirst($method);
        if (method_exists($this, $method)) {
            $this->$method($args);
        }else{
            exit("WARNING:the {$method} is no exists!");
        }
    }
}