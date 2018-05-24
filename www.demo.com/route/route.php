<?php
/**
 * Created by PhpStorm.
 * User: ht
 * Date: 2017/10/20
 * Time: 19:56
 */

use core\base\route\Route;

if (!isset($route) || !$route instanceof Route) {
    throw new Exception('非法的Route实例');
}

$route->addRoutes(function () use ($route) {

    $route->add(['GET'], 'test/index', 'TestController@index');



    $route->add(['GET','POST'], 'user/{id:\d+}', 'UserController@aaa');
    $route->add(['GET'], 'bbb/{id:\d+}', 'UserController@bbb');
    $route->add(['GET'], 'ccc/', 'UserController@ccc');
    $route->add(['GET'], 'eee/', 'UserController@eee');
});

