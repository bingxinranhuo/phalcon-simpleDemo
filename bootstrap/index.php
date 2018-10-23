<?php
define('DEBUG', true);
define('RUN_MODEL', 'dev'); //影响config路径下的配置文件载入,dev开发模式,test测试模式,, product生产模式,
define('ROOT_PATH', dirname(__DIR__) . '/');
define('COMMON_PATH', ROOT_PATH . 'common/');
define('CONF_PATH', ROOT_PATH . 'config/');
define('TMP_PATH', '/home/work/logs/cc/');
define('LOG_PATH', TMP_PATH . 'log/');
define('UPLOAD_PATH', TMP_PATH . 'upload/');
define('PUBLIC_PATH', ROOT_PATH . 'public/');
define('VENDOR_PATH', ROOT_PATH . 'vendor/');
define('IMAGE_PATH', ROOT_PATH . 'public/img/');

define('VERSION', '1.0.0');//版本号
if (DEBUG) {
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 'Off');
    error_reporting(0);
}
//载入依赖
require ROOT_PATH . 'bootstrap/loader.php';
//路由
require ROOT_PATH . 'bootstrap/router.php';
try {
    $application = new Phalcon\Mvc\Application($di);
    echo $application->handle()->getContent();
} catch (\Exception $e) {
    $content = ' url：' . $_SERVER['REQUEST_URI']
        . 'file:' . $e->getFile()
        . ' line:' . $e->getLine()
        . ' message:' . $e->getMessage();

    $di->getShared('logger')->error($content);
    header('Content-Type:application/json; charset=utf-8');
    if (DEBUG) {
        echo($content);
    } else {
        echo(json_encode(['err' => 1, 'msg' => $e->getMessage(), 'data' => []], JSON_ERROR_NONE));
    }
}
