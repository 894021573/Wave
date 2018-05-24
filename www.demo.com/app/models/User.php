<?php
namespace app\models;

use core\framework\Model;

class User extends Model
{
    protected $_tableName = 'user';

    public function getName()
    {
        return $this->one();
    }
}