<?php
/**
 *
 * @author: 洪涛
 * @date: 2017/8/30 ‘’
 */

namespace app\validators;

use core\base\validator\Validator;

class UserValidator extends Validator
{
    public function rules()
    {
        return
            [
//                [['name', 'age'], self::REQUIRED, 'message' => '自定义提示', 'on' => 'login'],
                [['name', 'age'], self::REQUIRED, 'message' => '自定义提示'],
                [['age'], self::INT, 'message' => '{tag} must be int'],
                ['range',self::RANGE,[0,10],'message' => '最大值{max},最小值{min},标签{tag}'],
                ['address',self::LENGTH,[2,5]],
                ['list',self::IN,[1,2,3,4,5,6,7],'on'=>'login'],
//                ['name', self::LENGTH, 6],
//                ['x', self::EMAIL],
//                ['xx', self::URL],
//                ['xxx', self::IP],
//                ['xxxx', self::FLOAT],
//                ['xxxxx', self::BOOLEAN],
//                ['xxxxxx', self::REGEXP, '/\d+/'], // 正则验证
                ['my', self::CALLBACK, 'myFunc'], // 自定义函数验证
            ];
    }

    public function myFunc($tag,$value)
    {
        if($value == 'ht')
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}