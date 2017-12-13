<?php
/**
 * 加载环境配置
 * User: Liu xiaoquan
 * Date: 2017/6/17
 * Time: 13:35
 */
$env = file(dirname(dirname(dirname(__DIR__))).'/env.ini');
foreach ($env as $k=>$v) {
    if (strpos($v,'#')===0) {
        continue;
    }
    list($key, $value) = array_map('trim',explode('=',$v));
    putenv("$key=$value");
}

if (!function_exists('env')) {
    function env($key, $value=null)
    {
        if (isset($value)) {
            return putenv("$key=$value");
        } else {
            return getenv($key);
        }
    }
}

//获取键：实现变量后
if (!function_exists('rkey')) {
    function rkey($subject, $replacement)
    {
        preg_match_all('/\{\$[a-zA-Z0-9_]+\}/', $subject, $values);
        $res = str_replace($values[0], $replacement, $subject);

        return $res;
    }
}
