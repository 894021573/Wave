<?php
/**
 *
 * @author: 洪涛
 * @date: 2017/12/15
 */

namespace core\base\unit_test;

class RunTest
{
    private $_reflection = [];

    public function add($testObj)
    {
        $this->_reflection[serialize($testObj)] = new \ReflectionClass($testObj);
    }

    public function run()
    {
        $result = [];
        foreach ($this->_reflection as $k => $v)
        {
            /**
             * @var $v \ReflectionClass
             */
            $methods = $v->getMethods();

            /**
             * @var $obj UnitTest
             */
            $obj = unserialize($k);
            foreach ($methods as $method)
            {
                if(strpos($method->name,'test') !== false)
                {
                    $method->invoke($obj);
                }
            }

            $result[] = $obj->getResult();
        }

        // 输出测试结果
        $this->show($result);
    }

    public function show($result)
    {
        // 先输出失败的，再输出成功的
        $message = [];
        $fail = [];

        foreach ($result as $item)
        {
            $message[] = $item['message'];
            if(!empty($item['fail']))
            {
                $fail[] = $item['fail'];
            }
        }

        foreach ($fail as $item)
        {
            foreach ($item as $v)
            {
                echo $v;
            }
        }

        foreach ($message as $item)
        {
            echo $item;
        }
    }
}