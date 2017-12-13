<?php
namespace Cache\Common;
/**
 * Http 方法
 * User: Liu xiao quan
 * Date: 2016/6/1
 * Time: 16:43
 */
class Http
{
    /**
     * 判断请求来自PC还是Mobile
     * @return bool     手机:true PC:false
     */
    static function IsMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA']))
        {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT']))
        {
            $clientkeywords = array ('nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            );
            $mobilekeywords=array('Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser',
                'UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris',
                'NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod'
            );

            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
            {
                return true;
            }

            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            $useragent_keywords=preg_match('|.∗?|',$_SERVER['HTTP_USER_AGENT'],$matches)>0?$matches[0]:'';
            if (preg_match("/(" . implode('|', str_replace('/','\/',$mobilekeywords)) . ")/i", $useragent_keywords))
            {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT']))
        {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Curl版本       兼容POST|GET
     * 使用方法：
     * $post_string = "app=request&version=beta";
     * request_by_curl('http://facebook.cn/restServer.php',$post_string);
     */
    static function http_curl( $url='', $post=array(), $timeout=5, $ip='' ){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ( !empty($post)){
            curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
        if (!empty($ip)){
            curl_setopt($ch,CURLOPT_HTTPPROXYTUNNEL,0);
            curl_setopt($ch, CURLOPT_PROXY,$ip);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT,  $timeout);
        $ex = curl_exec($ch);
        if ( curl_errno($ch) )
        {
            $ex = 'Curl error:'.curl_error($ch);
        }
        curl_close($ch);

        return $ex;
    }

    /**
     * Socket版本 只支持POST
     * 使用方法：
     * $post_string = "app=socket&version=beta";
     * request_by_socket('facebook.cn','/restServer.php',$post_string);
     */
    static function http_socket($remote_server,$remote_path,$post_string,$port = 80,$timeout = 30){
        $socket = fsockopen($remote_server,$port,$errno,$errstr,$timeout);
        if (!$socket) die("$errstr($errno)");

        fwrite($socket,"POST $remote_path HTTP/1.0\r\n");
        fwrite($socket,"User-Agent: Socket Example\r\n");
        fwrite($socket,"HOST: $remote_server\r\n");
        fwrite($socket,"Content-type: application/x-www-form-urlencoded\r\n");
        fwrite($socket,"Content-length: ".(strlen($post_string)+8)."\r\n");
        fwrite($socket,"Accept:*/*\r\n");
        fwrite($socket,"\r\n");
        fwrite($socket,"mypost=$post_string\r\n");
        fwrite($socket,"\r\n");

        $header = "";
        while ($str = trim(fgets($socket,4096))) {
            $header.=$str;
        }

        $data = "";
        while (!feof($socket)) {
            $data .= fgets($socket,4096);
        }

        return $data;
    }

    /**
     * 其它版本  只支持POST
     * 使用方法：
     * $post_string = "app=request&version=beta";
     * request_by_other('http://facebook.cn/restServer.php',$post_string);
     */
    static function http_other($remote_server,$post_string){
        $context = array(
            'http'=>array(
                'method'=>'POST',
                'header'=>'Content-type: application/x-www-form-urlencoded'."\r\n".
                    'User-Agent : Jimmy\'s POST Example beta'."\r\n".
                    'Content-length: '.(strlen($post_string)+8),
                'content'=>$post_string
            )
        );
        $stream_context = stream_context_create($context);
        $data = file_get_contents($remote_server,FALSE,$stream_context);
        return $data;
    }
}