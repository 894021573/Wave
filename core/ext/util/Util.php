<?php
/**
 *
 * @author: 洪涛
 * @date: 2017/10/12
 */
namespace core\ext\util;

use core\framework\Request;
use core\Wave;

class Util
{

    public static function generateCSRF()
    {
        $chars = [
            'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
            'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
            '0','1','2','3','4','5','6','7','8','9',
            '=','-','_'
        ];

        shuffle($chars);

        $csrf = implode('',$chars);
        /**
         * @var Request $request
         */
        $request = Wave::make('request');
        $request->setSession('csrf_token',$csrf);
        return $csrf;
    }
}