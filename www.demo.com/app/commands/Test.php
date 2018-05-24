<?php
/**
 *
 * @author: 洪涛
 * @date: 2017/12/29
 */
namespace app\commands;

class Test
{
    public function a($c, $d)
    {
        echo $c + $d;
    }

    public function b()
    {
        return 'b';
    }
}