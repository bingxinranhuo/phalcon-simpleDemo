<?php

namespace app\common\library;

/**
 * 加解密
 * Copyright (C) qiaodata.com 2018 All rights reserved
 * @author luojianglai
 * @date   2018/8/2 11:55
 */
class Crypt
{
    private static $key = '+Cm0/dhmcYl7Gko/r+V+pP63RVpeSBX+yn9TB6eAsWo=';//32位
    private static $iv = 'cR/27pU5TnPAKuNtvV0jdA==';//16位

    /**
     * 加密
     * @param string $data 需要加密的数据
     * @return string
     * @author luojianglai@qiaodata.com
     * @date   2018/8/2 11:50
     */
    public static function encrypt($str)
    {
        $encrypted = openssl_encrypt($str, 'aes-256-cbc', base64_decode(self::$key), OPENSSL_RAW_DATA, base64_decode(self::$iv));
        return self::base64ToUrl($encrypted);
    }

    /**
     * 解密
     * @param string $str 待解密数据
     * @return string
     * @author luojianglai@qiaodata.com
     * @date   2018/8/2 12:00
     */
    public static function decrypt($str)
    {
        $str = self::urlToBase64($str);
        return openssl_decrypt($str, 'aes-256-cbc', base64_decode(self::$key), OPENSSL_RAW_DATA, base64_decode(self::$iv));
    }

    /**
     * base64 转url
     * @param $string
     * @return mixed|string
     * @author luojianglai@qiaodata.com
     * @date   2018/8/2 13:48
     */
    private static function base64ToUrl($string)
    {
        $data = base64_encode($string);
        $data = str_replace(['+', '/', '='], ['-', '_', ''], $data);
        return $data;
    }

    /**
     * url 转base64
     * @param $string
     * @return bool|string
     * @author luojianglai@qiaodata.com
     * @date   2018/8/2 14:08
     */
    private static function urlToBase64($string)
    {
        $data = str_replace(['-', '_'], ['+', '/'], $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    /**
     * 批量加密
     * @param string $data 需要加密的数据
     * @param bool   $mode true 返回:[原数据=>加密结果],fasle只返回加密结果[加密结果]
     * @return string
     * @author luojianglai@qiaodata.com
     * @date   2018/8/2 14:10
     */
    public static function encryptBatch($data, $mode = false)
    {
        $reult = [];
        foreach ($data as $v) {
            if ($mode) {
                $reult[$v] = self::encrypt($v);
            } else {
                $reult[] = self::encrypt($v);
            }
        }
        return $reult;
    }

    /**
     * 批量解密
     * @param array $data 需要加密的数据
     * @param bool   $mode true 返回:[加密=>解密结果],fasle只返回加密结果[解密结果]
     * @return array
     * @author luojianglai@qiaodata.com
     * @date   2018/8/2 14:12
     */
    public static function decryptBatch($data, $mode = false)
    {
        $reult = [];
        foreach ($data as $v) {
            if ($mode) {
                $reult[$v] = self::decrypt($v);
            } else {
                $reult[] = self::decrypt($v);
            }
        }
        return $reult;
    }
}