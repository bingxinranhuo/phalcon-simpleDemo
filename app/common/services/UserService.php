<?php

namespace app\common\services;

use phalcon\Di;
use app\common\models\User;
use app\common\models\UserLog;
use app\common\library\Verify;
use app\common\library\Tool;
use app\common\library\Crypt;

/**
 * Copyright (C) qiaodata.com 2018 All rights reserved
 * @author luojianglai@qiaodata.com
 * @date   2018/5/18 18:35
 */
class UserService extends BaseService
{
    private $userModel;
    private $userLogModel;
    private static $lastLoginTime;
    const LOGIN_EXPIRES = 3600 * 24; //登录 过期时间

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->userLogModel = new UserLog();
    }


    /**
     * 密码登录
     * @param string $account          用户名
     * @param string $password         密码
     * @param string $rid              滑动验证码id
     * @param int    $rememberPassword 记住密码时间
     * @throws \Exception
     * @author luojianglai@qiaodata.com
     * @date   2018/5/21 18:07
     */
    public function passwordLogin($account, $password, $mobile)
    {
        if (empty($account)) {
            throw new \Exception('用户名不能为空');
        }
        if (empty($password)) {
            throw new \Exception('密码不能为空');
        }
        if (empty($mobile)) {
            throw new \Exception('手机号不能为空');
        }

        $passwordKey = 'password:' . $account;
        $passwordCache = self::$redis->get($passwordKey);
        $passwordCache = json_decode($passwordCache, JSON_OBJECT_AS_ARRAY);
        if ($passwordCache && $passwordCache['errNum'] >= 5) {
            throw new \Exception('密码错误超过5次,请使用其他方式登录');
        }

        $arr = [
            'conditions' => 'account = :account: and mobile=:mobile:',
            'bind' => ['account' => $account, 'mobile' => $mobile],
        ];

        $userInfo = User::findFirst($arr);
        if ($userInfo) {
            if ($userInfo->status == 1) {
                throw new \Exception('该账号已被禁用，请联系客服处理！');
            }
        } else {
            throw new \Exception('账号不存在，请先注册账号！');
        }

        if (empty($userInfo->password)) {
            throw new \Exception('该账号还未设置密码,请用短信验证码登录！');
        }


        if (md5($password) != $userInfo->password) {
            //密码错误次数
            $errCount = empty($passwordCache['errNum']) ? 1 : ++$passwordCache['errNum'];
            $cacheData = ['errNum' => $errCount];
            $expiresTime = strtotime(date('Y-m-d') . '+1day') - time();
            self::$redis->set($passwordKey, json_encode($cacheData), $expiresTime);
            throw new \Exception('密码错误');
        }
        self::$lastLoginTime = time();
        $userInfo->login_time = self::$lastLoginTime;
        $userInfo->save();
        $userInfo = $userInfo->toArray();
        //删除密码错误次数
        self::$redis->del($passwordKey);
        self::setUserSession($userInfo['id'], $userInfo);
        self::setToken($userInfo);
        $this->addLoginLog($userInfo['id']);
        return $userInfo['id'];
    }

    /**
     * 添加登录记录
     * @param int $uid
     * @author luojianglai@qiaodata.com
     * @date   2018/8/23 16:03
     */
    public function addLoginLog($uid)
    {
        $this->userLogModel->user_id = $uid;
        $this->userLogModel->ip = ip2long(Tool::getIP());
        $this->userLogModel->create();
    }

    /**
     * 判断用户是否存在
     * @param int $uid 用户id
     * @return bool 存在true
     * @author luojianglai@qiaodata.com
     * @date   2018/5/16 19:56
     */
    public function isExistByUid($uid)
    {
        $rel = User::findFirst($uid);
        if ($rel) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 格式用户数据
     * @param $data
     * @author luojianglai@qiaodata.com
     * @date   2018/7/20 14:36
     */
    private static function formatData($data)
    {
        if (is_object($data)) {
            $data = $data->toArray();
        }
        return $data;
    }


    /**
     * 用户添加session
     * @param int   $uid
     * @param array $data 没有数据会通过uid
     * @return bool
     * @author luojianglai@qiaodata.com
     * @date   2018/8/14 16:46
     */
    public static function setUserSession($uid, $data = [])
    {
        if (!$uid) {
            return false;
        }
        $key = 'session_' . $uid;
        //没有值自己获取
        if (!$data) {
            $data = self::getUserForSession($uid);
        } else {
            $data = self::formatData($data);
        }
        $redis = Di::getDefault()->get('redis');
        $redis->set($key, json_encode($data), self::LOGIN_EXPIRES);
    }

    /**
     * 获取用户session
     * @param int $uid
     * @return bool|mixed
     * @author luojianglai@qiaodata.com
     * @date   2018/8/14 16:46
     */
    public static function getUserSession($uid)
    {
        if (!$uid) {
            return false;
        }
        $key = 'session_' . $uid;
        $redis = Di::getDefault()->get('redis');
        $user = $redis->get($key);
        return json_decode($user, JSON_OBJECT_AS_ARRAY);
    }

    /**
     * 删除用户session
     * @param int  $uid
     * @param bool $reset false只删除不创建,true删除后重新创建session
     * @return bool
     * @author luojianglai@qiaodata.com
     * @date   2018/8/15 17:06
     */
    public static function delUserSession($uid, $reset = false)
    {
        if (!$uid) {
            return false;
        }
        $key = 'session_' . $uid;
        if ($reset) {
            self::setUserSession($uid);
        } else {
            $redis = Di::getDefault()->get('redis');
            $redis->del($key);
        }
        return true;
    }

    /**
     * 获取用户session所需信息
     * @param int $uid
     * @return array
     * @author luojianglai@qiaodata.com
     * @date   2018/8/14 16:55
     */
    public static function getUserForSession($uid)
    {
        if (!$uid) {
            return [];
        }
        $arr = [
            'conditions' => 'id = :uid:',
            'bind' => ['uid' => $uid],
        ];
        $user = User::findFirst($arr);
        if ($user) {
            $user->toArray();
            $user = self::formatData($user);
            return $user;
        } else {
            return [];
        }
    }

    /**
     * 加密session
     * @param string $uid
     * @return string
     * @author luojianglai@qiaodata.com
     * @date   2018/8/15 17:49
     */
    public static function packSession($uid)
    {
        $arr = [
            'uid' => $uid,
            'lastTime' => self::$lastLoginTime,
            'expires' => time() + self::LOGIN_EXPIRES,
        ];
        $str = json_encode($arr);
        return base64_encode(self::_mask(Crypt::encrypt($str)));
    }

    /**
     * 解密session
     * @param string $session
     * @author luojianglai@qiaodata.com
     * @date   2018/8/15 10:09
     */
    public static function unpackSession($session)
    {
        $session = self::_mask(base64_decode($session));
        $session = Crypt::decrypt($session);
        $arr = json_decode($session, JSON_OBJECT_AS_ARRAY);
        if (empty($arr) || !isset($arr['uid']) || !isset($arr['lastTime'])) {
            return false;
        }
        return $arr;
    }

    /**
     * 数据掩码
     * @param string $str
     * @return string
     * @author luojianglai@qiaodata.com
     * @date   2018/8/15 10:16
     */
    private static function _mask($str)
    {
        $mask = 'qi3ao4b9da7d9b2ta146l8j8lb4d691';
        $maskMd5 = md5($mask, true);
        $len = strlen($str);

        $result = '';
        $i = 0;
        while ($i < $len) {
            $j = 0;
            while ($i < $len && $j < 16) {
                $result .= $str[$i] ^ $maskMd5[$j];
                $i++;
                $j++;
            }
        }
        return $result;
    }

    /**
     * 退出登录
     * @param int $uid
     * @author luojianglai@qiaodata.com
     * @date   2018/8/15 17:33
     */
    public static function logout($session)
    {
        setcookie('sid', null, 0, '/');
        $userInfo = self::unpackSession($session);
        if ($userInfo) {
            self::delUserSession($userInfo['uid']);
        }
    }

    /**
     * 设置cookie
     * @author luojianglai@qiaodata.com
     * @date   2018/8/16 18:00
     */
    public static function setToken($user)
    {
        //登录id
        return UserService::packSession($user['id']);
    }

    /**
     * 禁用账号
     * @param $uid
     * @author luojianglai@qiaodata.com
     * @date   2018/8/22 15:51
     */
    public static function disableUser($uid)
    {
        $params = [
            'conditions' => 'id=:id:',
            'bind' => ['id' => $uid],
        ];
        $user = User::findFirst($params);
        $user->status = 1;
        $user->save();
        UserService::logout($uid);
    }


}