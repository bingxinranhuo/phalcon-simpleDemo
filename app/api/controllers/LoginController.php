<?php

use app\common\services\UserService;

class LoginController extends ApiBaseController
{
    protected $userService;
    /**
     *
     * @author luojianglai@qiaodata.com
     * @date   2018/10/23 9:19
     */
    public function initialize()
    {
        parent::initialize();
        $this->userService = new UserService();
    }

    /**
     * 手机号+短信验证码登录
     * @throws
     * @author luojianglai@qiaodata.com
     * @date   2018/4/28 15:10
     */
    public function doAction()
    {
//        if (!$this->request->isPost()) {
//            $this->ajaxReturn(1, '非法操作');
//        }

//        $partner = $this->request->getPost('account', 'string');
//        $mobile = $this->request->getPost('mobile', 'string');
//        $password = $this->request->getPost('pwd');
//        $uid = $this->userService->passwordLogin($partner, $password, $mobile);
        $this->ajaxReturn(0, 'success', ['token' => '12321dsfr#$342134fsafea']);
    }
}