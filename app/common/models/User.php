<?php

namespace app\common\models;

class User extends BaseModel
{
    /**
     * @var
     */
    public $id;
    /**
     * @var
     */
    public $account;
    /**
     * @var
     */
    public $password;
    /**
     * @var
     */
    public $status;
    /**
     * @var
     */
    public $create_time;
    /**
     * @var
     */
    public $update_time;


    public function getSource()
    {
        return "user";
    }

    /**
     * Allows to query a set of records that match the specified conditions
     * @param mixed $parameters
     * @return UserLog[]|UserLog|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     * @param mixed $parameters
     * @return UserLog|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
}