<?php

use app\common\services\UserService;

class UserController extends ApiBaseController
{

    public function initialize()
    {
        parent::initialize();
        $this->userService = new UserService();
    }


}