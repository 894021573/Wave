<?php
/**
 *
 * @author: 洪涛
 * @date: 2017/12/15
 */

namespace app\tests;

use core\base\unit_test\RunTest;

class AllTest
{
    public function __construct()
    {
        $runTest = new RunTest();

        // add test class start

        $runTest->add(new IndexTest());

        // add test class end

        $runTest->run();
    }
}