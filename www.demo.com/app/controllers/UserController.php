<?php
/**
 *
 * @author: 洪涛
 * @date: 2018/1/5
 */

namespace app\controllers;

use app\models\User;
use app\services\UserService;
use app\validators\UserValidator;
use core\framework\Controller;

class UserController extends Controller
{
    protected $_is_csrf = false;

    public function aaa()
    {
        $gets = $this->get();
        var_dump($gets);

        $posts = $this->post();
        var_dump($posts);

        return $this->renderPartial('index');
    }

    public function bbb()
    {
        $s = new UserService();
        echo $s->getName();
    }

    public function ccc()
    {
        $m = new User();
        $r = $m->getName();
        var_dump($r);
    }

    public function ddd()
    {
        return 3;
    }

    public function eee()
    {
        $data = ['name' => '111','age' => 'a','range' => 20,'address' => 'a','list' => 8,'my' => 'ht'];
        $v = new UserValidator($data,'login');

        var_dump($v->getErrors());
    }
}