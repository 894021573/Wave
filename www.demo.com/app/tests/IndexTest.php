<?php
/**
 *
 * @author: 洪涛
 * @date: 2017/12/15
 */
namespace app\tests;

use app\controllers\UserController;
use core\base\unit_test\UnitTest;

class IndexTest extends UnitTest
{
    public function testTitle()
    {
        $u = new UserController();
        $this->assertEqual($u->ddd(),123);
    }
}