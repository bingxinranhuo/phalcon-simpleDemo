<?php
/*
 * 命令行入口文件
 */
//判断是否是命令行运行
if (strcmp('cli', php_sapi_name()) !== 0) {
    echo "运行错误(:";
    exit();
}

use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Cli\Console as ConsoleApp;
use Phalcon\Loader;
use Phalcon\Config\Adapter\Php as ConfigPhp;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Logger\Adapter\File as FileAdapter;

/**
 * 读取配置文件
 */

define('DEBUG', true);
define('RUN_MODEL', 'dev');//dev开发模式,test测试模式,, product生产模式,影响配置文件载入
define('ROOT_PATH', dirname(__DIR__) . '/');
define('COMMON_PATH', ROOT_PATH.'common/');
define('CONF_PATH', ROOT_PATH . 'config/');
define('TMP_PATH', '/home/work/logs/bi/');
define('LOG_PATH', TMP_PATH . 'log/');

define('VENDOR_PATH', ROOT_PATH.'vendor/');
define('IMAGE_PATH', ROOT_PATH.'public/images/');
$config = new ConfigPhp(CONF_PATH.'config.php');
// Using the CLI factory default services container
$di = new CliDI();
if (DEBUG) {
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 'Off');
    error_reporting(0);
}
//日志
$di->setShared('logger', function () {
    $date = date('Ymd');
    $logger = new FileAdapter(LOG_PATH . "{$date}.log");
    return $logger;
});

/**
 * Auto-loader configuration
 * Register an autoloader
 * register namespace
 */
$loader = new Loader();
$loader->registerDirs(array(
    ROOT_PATH . 'app/tasks/',
    ROOT_PATH.'app/common/models',
    ROOT_PATH.'app/common/services',
    ROOT_PATH.'app/common/library',
))->register();
 $loader->registerNamespaces(array(
     'app\common\models'=>ROOT_PATH.'app/common/models',
     'app\common\services'=>ROOT_PATH.'app/common/services',
     'app\common\library'=>ROOT_PATH.'app/common/library',
 ));

// Create a console application
$console = new ConsoleApp();
$console->setDI($di);

/**
 * Process the console arguments
 */
$arguments = [];
foreach ($argv as $k => $arg) {
    if ($k === 1) {
        $arguments["task"] = $arg;
    } elseif ($k === 2) {
        $arguments["action"] = $arg;
    } elseif ($k >= 3) {
        $arguments["params"][] = $arg;
    }
}

try {
    /**
     * Set the database service
     * 数据库配置
     */
    $di['db'] = function () use ($config, $di) {
        //新建一个事件管理器
        $eventsManager = new \Phalcon\Events\Manager();

        //从di中获取共享的profiler实例
        $profiler = $di->getProfiler();

        //监听所有的db事件
        $eventsManager->attach('db', function ($event, $connection) use ($profiler) {
            //一条语句查询之前事件，profiler开始记录sql语句
            if ($event->getType() == 'beforeQuery') {
                $profiler->startProfile($connection->getSQLStatement());
            }
            //一条语句查询结束，结束本次记录，记录结果会保存在profiler对象中
            if ($event->getType() == 'afterQuery') {
                $profiler->stopProfile();
            }
        });

        //将事件管理器绑定到db实例中
        $connection = new DbAdapter($config->pdatabase->toArray());
        $connection->setEventsManager($eventsManager);
        return $connection;
    };

    $di->set('profiler', function () {
        return new\Phalcon\Db\Profiler();
    }, true);

    $di->set("modelsManager", function () {
        $modelsManager = new Manager();
        return $modelsManager;
    });

    /**
     * 配置
     */
    $di->set("config", function () use ($config) {
        return $config;
    });

    $di->setShared('console', $console);
    // Handle incoming arguments
    $console->handle($arguments);
} catch (\Phalcon\Exception $e) {
    //记录错误日志
    $error_info = '系统错误为:' . $e->getMessage() . PHP_EOL;
    $log_path = LOG_PATH . date('Ymd') . ".log";
    error_log($error_info, 3, $log_path);
    echo $e->getMessage();
}

