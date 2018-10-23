<?php
/**
 * Copyright (C) qiaodata.com 2018 All rights reserved
 * @author luojianglai
 * @date   2018/10/22 19:59
 */

namespace app\common\library;


class Tool
{
    public static function getIP()
    {
        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else {
                if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                    $realip = $_SERVER["HTTP_CLIENT_IP"];
                } else {
                    $realip = $_SERVER["REMOTE_ADDR"];
                }
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            } else {
                if (getenv("HTTP_CLIENT_IP")) {
                    $realip = getenv("HTTP_CLIENT_IP");
                } else {
                    $realip = getenv("REMOTE_ADDR");
                }
            }
        }
        return $realip;
    }
}