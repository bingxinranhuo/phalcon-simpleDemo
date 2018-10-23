<?php

use Phalcon\Mvc\Controller;
use app\common\services\UserService;

/**
 * Copyright (C) qiaodata.com 2018 All rights reserved
 * @author luojianglai@qiaodata.com
 * @date   2018/10/23 10:12
 */
class ApiBaseController extends Controller
{
    protected $page = 1;
    protected $pageSize = 20;
    /**
     * @var array 当前用户数据
     */
    protected $user;
    /**
     * @var string 当前登录url
     */
    protected $url;
    /**
     * @var array 参数
     */
    protected $params;

    /**
     * 监听方法
     */
    public function beforeExecuteRoute($dispatcher)
    {
        $this->controller = $dispatcher->getControllerName();
        $this->action = $dispatcher->getActionName();
    }

    public function initialize()
    {
//        if (!$this->checkLogin()) {
//            $this->ajaxReturn();
//        }
    }

    /**
     * 检测登陆
     * @author luojianglai@qiaodata.com
     * @date   2018/10/22 9:31
     */
    private function checkLogin()
    {

    }

    public function afterExecuteRoute($dispatcher)
    {

    }

    /**
     * Ajax方式返回数据到客户端
     * @param int    $err
     * @param string $msg
     * @param array  $res
     * @author luojianglai@qiaodata.com
     * @date   2018/10/22 19:24
     */
    protected function ajaxReturn($err = 0, $msg = 'success', $res = [])
    {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode(['err' => $err, 'msg' => $msg, 'res' => $res], JSON_ERROR_NONE | JSON_UNESCAPED_UNICODE));
    }
}
