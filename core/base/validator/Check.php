<?php
/**
 *
 * @author: 洪涛
 * @date: 2018/05/24
 */

namespace core\base\validator;

class Check
{
    public function checkRequired($value)
    {
        return isset($value) && trim($value) !== '' ? true : false;
    }

    public function checkInt($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT);
    }

    public function checkEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    public function checkURL($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL);
    }

    public function checkIP($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP);
    }

    public function checkFloat($value)
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT);
    }

    public function checkBoolean($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function checkRegexp($value, $regexp)
    {
        return filter_var($value, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => $regexp]]);
    }

    public function checkRange($value,$range)
    {
        if(!is_array($range) || (is_array($range) && count($range) !=2))
        {
            throw new \Exception('range should be an array contains two elements');
        }
        list($min,$max) = $range;
        return filter_var($value, FILTER_VALIDATE_INT,['options' => ['min_range' => $min,'max_range' => $max]]);
    }

    public function checkLength($value,$length)
    {
        if(!is_array($length) || (is_array($length) && count($length) !=2))
        {
            throw new \Exception('length should be an array contains two elements');
        }
        list($min,$max) = $length;
        $len = strlen($value);
        return $len >= $min && $len <= $max ? true : false;
    }

    public function checkList($value,$list)
    {
        if(!is_array($list))
        {
            throw new \Exception('list should be an array');
        }
        return in_array($value,$list) ? true : false;
    }
}