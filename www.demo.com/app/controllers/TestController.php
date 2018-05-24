<?php
/**
 *
 * @author: 洪涛
 * @date: 2018/1/12
 */
namespace app\controllers;

use app\tests\AllTest;

class TestController
{
    public function index()
    {
        new AllTest();
    }
}