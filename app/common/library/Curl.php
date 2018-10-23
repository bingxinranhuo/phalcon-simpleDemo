<?php

namespace app\common\library;

use phalcon\Di;

/**
 * Copyright (C) qiaodata.com 2018 All rights reserved
 * @author luojianglai@qiaodata.com
 * @date   2018/9/27 9:30
 */
class Curl
{
    public static $timeOut = 5;
    public static $httpVersion = '1.1';
    private static $httpResponseHeader;
    private static $httpResponseBody;
    private static $defaultHeaders = [
        'Pragma' => "no-cache",
        'Cache-Control' => "no-cache",
        'Connection' => "close"
    ];

    /**
     * http get 请求
     * @param string $url
     * @param null   $headers
     * @return mixed
     * @author luojianglai@qiaodata.com
     * @date   2018/9/27 9:28
     */
    public static function get($url, $headers = null)
    {
        return self::action('get', $url, $headers);
    }

    /**
     * http post 请求
     * @param string $url
     * @param array  $data
     * @param null   $headers
     * @return mixed
     * @author luojianglai@qiaodata.com
     * @date   2018/9/27 9:28
     */
    public static function post($url, $data = [], $headers = null)
    {
        return self::action('post', $url, $headers, $data);
    }

    public static function action($action, $url, $headers = null, $data = null)
    {
        $headers = array_merge(self::$defaultHeaders, (array)$headers);
        $setHeaders = [];
        foreach ((array)$headers as $k => $v) {
            $setHeaders[] .= $k . ': ' . $v;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$timeOut);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_TCP_NODELAY, true);
        if ($data) {
            if (is_string($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }

        if ($setHeaders) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $setHeaders);
        }
        curl_setopt($ch, CURLOPT_HTTP_VERSION, self::$httpVersion);
        curl_setopt($ch, CURLOPT_POST, $action == 'post' ? true : false);
        $httpResponseBody = curl_exec($ch);
        $httpResponseHeader = curl_getinfo($ch);
        curl_close($ch);
        self::setResponseBody($httpResponseBody);
        self::setResponseHeader($httpResponseHeader);
        if ($httpResponseHeader['http_code'] != 200) {
            return false;
        } else {
            return $httpResponseBody;
        }
    }

    /**
     * 获取返回内容
     * @author luojianglai@qiaodata.com
     * @date   2018/9/30 11:31
     */
    public static function getResponseBody()
    {
        return self::$httpResponseBody;
    }

    /**
     * 获取相应头
     * @author luojianglai@qiaodata.com
     * @date   2018/9/30 11:31
     */
    public static function getResponseHeader()
    {
        return self::$httpResponseHeader;
    }

    /**
     * 设置返回内容
     * @author luojianglai@qiaodata.com
     * @date   2018/9/30 11:30
     */
    private static function setResponseBody($httpResponseBody)
    {
        self::$httpResponseBody = $httpResponseBody;
    }

    /**
     * 设置响应头
     * @author luojianglai@qiaodata.com
     * @date   2018/9/30 11:30
     */
    private static function setResponseHeader($httpResponseHeader)
    {
        self::$httpResponseHeader = $httpResponseHeader;
    }
}
