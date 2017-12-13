<?php
/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/10/11
 * Time: 12:54
 */
header("Content-type:text/html;charset=utf-8");
require_once "vendor/autoload.php";

use Hprose\InvokeSettings;
use Hprose\ResultMode;

$client = new \Hprose\Http\Client('http://instance01-q8197264457938.codeanyapp.com:8080', false);
echo '<pre>';
var_dump($client->func1("你好", new InvokeSettings(array('mode' => ResultMode::Normal))));
var_dump($client->func2(2, new InvokeSettings(array('mode' => ResultMode::Normal))));