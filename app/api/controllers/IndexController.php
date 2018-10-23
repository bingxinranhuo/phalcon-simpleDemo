<?php

/**
 * Copyright (C) qiaodata.com 2018 All rights reserved
 * @author luojianglai@qiaodata.com
 * @date   2018/10/23 10:12
 */
class IndexController extends ApiBaseController
{
    public function initialize()
    {
        parent::initialize();
    }

    public function indexAction()
    {
        echo 'index';
        die;
    }


}