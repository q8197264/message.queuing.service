<?php
namespace Cache\App\demo\config;

class config
{
    //redis connect 配置项
    static $init = array(
        'host'=>array(
            'master'    => 'master_test',
            'slave'     => 'slave_test',
        ),

        'database'  => 0,
    );

    //key 前缀
    static $key_prefix = array(
        'demo'=>'demo:{$domain}',
        'test'=>'test:{$uid}',
        //...
    );
}