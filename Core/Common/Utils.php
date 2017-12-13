<?php
namespace Cache\App\Utils;

/**
 * 公用工具函数
 *
 *  应用场景：常放一些共用的验证，字符处理,签名算法等函数
 *
 * User: Liu xiaoquan
 * Date: 2017/4/17
 * Time: 11:19
 */
/**
 * 签名字符串
 * @param $prestr 需要签名的字符串
 * @param $key 私钥
 * return 签名结果
 */
function md5Sign($prestr='', $key='')
{
    $prestr = $prestr . $key;

    return md5($prestr);
}

/**
 * 验证签名
 * @param $prestr 需要签名的字符串
 * @param $sign 签名结果
 * @param $key 私钥
 * return 签名结果
 */
function md5Verify($prestr='', $sign='', $key='')
{
    $prestr = $prestr . $key;
    $mysgin = md5($prestr);

    if($mysgin == $sign) {
        return true;
    } else {
        return false;
    }
}