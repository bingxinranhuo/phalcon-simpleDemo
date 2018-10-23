<?php

namespace app\common\services;

use phalcon\Di;


/**
 * Class BaseService
 * @package app\common\services
 */
class BaseService
{
    protected static $db;
    protected static $redis;
    protected static $config;

    public function __construct()
    {
        self::$db = Di::getDefault()->getShared('db');
        self::$redis = Di::getDefault()->get('redis');
        self::$config = Di::getDefault()->get('config');
    }
}
