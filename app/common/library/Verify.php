<?php
/**
 * Copyright (C) qiaodata.com 2018 All rights reserved
 * @author luojianglai
 * @date 2018/4/27 15:41
 */

namespace app\common\library;

/**
 * 验证类
 */
class Verify
{
    /**
     * 是否为空值
     */
    public static function isEmpty($str)
    {
        $str = trim($str);
        return empty($str) ? true : false;
    }
    
    /**
     * 数字验证
     * param:$flag : int是否是整数，float是否是浮点型
     */
    public static function isNum($str, $flag = 'float')
    {
        if (self::isEmpty($str)) {
            return false;
        }
        if (strtolower($flag) == 'int') {
            return ((string)(int)$str === (string)$str) ? true : false;
        } else {
            return ((string)(float)$str === (string)$str) ? true : false;
        }
    }
    
    /**
     * 邮箱验证
     */
    public static function isEmail($str)
    {
        if (self::isEmpty($str)) {
            return false;
        }
        return preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/", $str) ? true : false;
    }
    
    //手机号码验证
    public static function isMobile($str)
    {
        $exp = "/^1[3|4|5|6|7|8|9][0-9]{9}$/";
        if (preg_match($exp, $str)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 身份证验证
     * @param $str
     * @return bool
     * @author luojianglai@qiaodata.com
     * @date 2018/5/25 18:38
     */
    public static function isIdCard($str)
    {
        $exp = "/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|[xX])$/";
        if (preg_match($exp, $str)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * URL验证，纯网址格式，不支持IP验证
     */
    public static function isUrl($str)
    {
        if (self::isEmpty($str)) {
            return false;
        }
        return preg_match('#(http|https|ftp|ftps)://([w-]+.)+[w-]+(/[w-./?%&=]*)?#i', $str) ? true : false;
    }
    
    public static function isIp($ip)
    {
        return preg_match('/^[0-9]{1-3}\.[0-9]{1-3}\.[0-9]{1-3}\.[0-9]{1-3}$/', $ip);
    }
    /**
     * 验证中文
     * @param:string $str 要匹配的字符串
     * @param:$charset 编码（默认utf-8,支持gb2312）
     */
    public static function isChinese($str, $charset = 'utf-8')
    {
        if (self::isEmpty($str)) {
            return false;
        }
        $match = (strtolower($charset) == 'gb2312') ? "/^[" . chr(0xa1) . "-" . chr(0xff) . "]+$/"
            : "/^[x{4e00}-x{9fa5}]+$/u";
        return preg_match($match, $str) ? true : false;
    }
    
    /**
     * UTF-8验证
     */
    public static function isUtf8($str)
    {
        if (self::isEmpty($str)) {
            return false;
        }
        return (preg_match("/^([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){1}/", $str)
            == true || preg_match("/([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){1}$/",
                $str)
            == true || preg_match("/([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){2,}/",
                $str)
            == true) ? true : false;
    }
    
    /**
     * 验证长度
     * @param: string $str
     * @param: int $type(方式，默认min <= $str <= max)
     * @param: int $min,最小值;$max,最大值;
     * @param: string $charset 字符
     */
    public static function length($str, $type = 3, $min = 0, $max = 0, $charset = 'utf-8')
    {
        if (self::isEmpty($str)) {
            return false;
        }
        $len = mb_strlen($str, $charset);
        switch ($type) {
            case 1: //只匹配最小值
                return ($len >= $min) ? true : false;
                break;
            case 2: //只匹配最大值
                return ($max >= $len) ? true : false;
                break;
            default: //min <= $str <= max
                return (($min <= $len) && ($len <= $max)) ? true : false;
        }
    }
    
    /**
     * 验证密码
     * @param string $value
     * @param int $length
     * @return boolean
     */
    public static function isPWD($value, $minLen = 6, $maxLen = 16)
    {
        $match = '/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{' . $minLen . ',' . $maxLen . '}$/';
        $v = trim($value);
        if (empty($v)) {
            return false;
        }
        return preg_match($match, $v);
    }

    /**
     * 验证密码,必须包含大小写字母和数字的组合，不能使用特殊字符
     * @param string $value
     * @param int $length
     * @return boolean
     */
    public static function isPWD1($value, $minLen = 6, $maxLen = 16)
    {
        $match = '/^(?![0-9]+$)(?![a-z]+$)(?![A-Z]+$)[0-9A-Za-z]{' . $minLen . ',' . $maxLen . '}$/';
        $v = trim($value);
        if (empty($v)) {
            return false;
        }
        return preg_match($match, $v);
    }

    /**
     * 验证密码,字母数字特殊字符,至少两种组合
     * @param string $value
     * @param int $length
     * @return boolean
     */
    public static function isPWD2($value, $minLen = 6, $maxLen = 16)
    {
        $match = '/^(?![0-9]+$)(?![a-z]+$)(?![A-Z]+$)(?![!@$^&,\.#%\'\+\*\-:;^_`]+$)[!@$^&,\.#%\'\+\*\-:;^_`0-9A-Za-z]{' . $minLen . ',' . $maxLen . '}$/';
        $v = trim($value);
        if (empty($v)) {
            return false;
        }
        return preg_match($match, $v);
    }

    /**
     * 是否是md5
     * @param string $string
     * @return false|int
     * @author luojianglai@qiaodata.com
     * @date 2018/5/30 18:17
     */
    public static function isMd5($string) {
        return preg_match("/^[a-z0-9]{32}$/", $string);
    }

    /**
     * 验证用户名
     * @param string $value
     * @param int $length
     * @return boolean
     */
    public static function isName($value, $minLen = 2, $maxLen = 16, $charset = 'ALL')
    {
        if (self::isEmpty($value)) {
            return false;
        }

        switch ($charset) {
            case 'EN':
                $match = '/^[_\w\d]{' . $minLen . ',' . $maxLen . '}$/iu';
                break;
            case 'CN':
                $match = '/^[_\x{4e00}-\x{9fa5}\d]{' . $minLen . ',' . $maxLen . '}$/iu';
                break;
            case 'ENCN':
                $match = '/^[\w\x{4e00}-\x{9fa5}]{' . $minLen . ',' . $maxLen . '}$/iu';
                break;
            default:
                $match = '/^[_\w\d\x{4e00}-\x{9fa5}]{' . $minLen . ',' . $maxLen . '}$/iu';
        }
        return preg_match($match, $value);
    }

    /**
     * 验证文件名
     * @param string $value
     * @param int $length
     * @return boolean
     */
    public static function isFilename($value, $minLen = 6, $maxLen = 16)
    {
        $match = '/^[_\w\d\x{4e00}-\x{9fa5}]{' . $minLen . ',' . $maxLen . '}$/iu';
        $v = trim($value);
        if (empty($v)) {
            return false;
        }
        return preg_match($match, $v);
    }


    /**
     * 匹配日期
     * @param string $value
     */
    public static function checkDate($str)
    {
        $dateArr = explode("-", $str);
        if (is_numeric($dateArr[0]) && is_numeric($dateArr[1]) && is_numeric($dateArr[2])) {
            if (($dateArr[0] >= 1000 && $dateArr[0] <= 10000) && ($dateArr[1] >= 0 && $dateArr[1] <= 12) && ($dateArr[2] >= 0 && $dateArr[2] <= 31)) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }
    
    /**
     * 匹配时间
     * @param string $value
     */
    public static function checkTime($str)
    {
        $timeArr = explode(":", $str);
        if (is_numeric($timeArr[0]) && is_numeric($timeArr[1]) && is_numeric($timeArr[2])) {
            if (($timeArr[0] >= 0 && $timeArr[0] <= 23) && ($timeArr[1] >= 0 && $timeArr[1] <= 59) && ($timeArr[2] >= 0 && $timeArr[2] <= 59)) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }
}
