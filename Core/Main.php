<?php
/**
 *
 * User: Liu xiaoquan
 * Date: 2017/5/19
 * Time: 14:50
 */
namespace Cache;

//自定义配置
date_default_timezone_set('PRC') OR ini_set('date.timezone','Asia/Shanghai');
ini_get('display_errors') OR ini_set('display_errors', '1');//错误显示

//load environment
require_once('Basis/Bootstrap/Environment.php');

env('COMPONENT_ROOT', str_replace('\\','/',dirname(dirname(dirname(__DIR__))).'/component.yaofang.cn'));

//加载日志类
if ( is_file(env('COMPONENT_ROOT').'/log/cy_log.php') ){
    require_once(env('COMPONENT_ROOT').'/log/cy_log.php');
}

//加载公共方法类
include(dirname( __DIR__ ) . '/Core/Common/Utils.php');
include(dirname( __DIR__ ) . '/Core/Common/Http.php');

/////////////////////////////  CORE \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
// autoload
require_once(dirname( __DIR__ ) . '/Core/AutoLoader.php');
\Cache\Core\AutoLoader::Register();
\Cache\Core\AutoLoader::Register(dirname(__DIR__).'/App/queue/lib/');

// container
$app = require_once(dirname( __DIR__ ) . '/Bootstrap/AppContainer.php');

//启动核心服务
$core = $app->make(\Cache\Core\Contracts\Core\Core::class);

$response = $core->handle(
    $request = \Cache\Core\Basis\Http\Request::capture()
);
//$response->send();

return $response;