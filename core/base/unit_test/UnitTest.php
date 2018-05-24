<?php
/**
 * 基于assert函数写的简单的单元测试类
 * @date: 2018/05/24
 */

namespace core\base\unit_test;

class UnitTest
{
    private $_line;
    private $_file;
    private $_funcName;
    private $_args;
    private $_express;

    private $_passNum = 0;
    private $_failNum = 0;
    private $_failArray = []; // 错误信息数组

    public function __construct()
    {
        assert_options(ASSERT_ACTIVE, true);
        assert_options(ASSERT_BAIL, false); // 断言失败后继续执行
        assert_options(ASSERT_WARNING, false);
    }

    public function assertEqual($first, $second)
    {
        $backtrace = debug_backtrace();
        $this->backtrace($backtrace);

        $r = assert('$first == $second');
        $this->addPassOrFail($r);
    }

    public function assertStrictEqual($first, $second)
    {
        $backtrace = debug_backtrace();
        $this->backtrace($backtrace);

        $r = assert('$first === $second');
        $this->addPassOrFail($r);
    }

    public function assertNotEqual($first, $second)
    {
        $backtrace = debug_backtrace();
        $this->backtrace($backtrace);

        $r = assert('$first != $second');
        $this->addPassOrFail($r);
    }

    public function assertNotStrictEqual($first, $second)
    {
        $backtrace = debug_backtrace();
        $this->backtrace($backtrace);

        $r = assert('$first !== $second');
        $this->addPassOrFail($r);
    }

    public function assertNull($first)
    {
        $backtrace = debug_backtrace();
        $this->backtrace($backtrace);

        $r = assert('is_null($first)');
        $this->addPassOrFail($r);
    }

    public function assertNotNull($first)
    {
        $backtrace = debug_backtrace();
        $this->backtrace($backtrace);

        $r = assert('!is_null($first)');
        $this->addPassOrFail($r);
    }

    public function assertTrue($first)
    {
        $backtrace = debug_backtrace();
        $this->backtrace($backtrace);

        $r = assert('$first === true');
        $this->addPassOrFail($r);
    }

    public function assertNotTrue($first)
    {
        $backtrace = debug_backtrace();
        $this->backtrace($backtrace);

        $r = assert('$first !== true');
        $this->addPassOrFail($r);
    }

    public function assertFalse($first)
    {
        $backtrace = debug_backtrace();
        $this->backtrace($backtrace);

        $r = assert('$first === false');
        $this->addPassOrFail($r);
    }

    public function assertNotFalse($first)
    {
        $backtrace = debug_backtrace();
        $this->backtrace($backtrace);

        $r = assert('$first !== false');
        $this->addPassOrFail($r);
    }

    private function addPassOrFail($r)
    {
        if($r)
        {
            $this->_passNum++;
        }else
        {
            $this->_failNum++;
            $basename = basename($this->_file);
            $this->_failArray[] = "{$basename} -> {$this->_express} -> [{$this->_file} line {$this->_line}]";
        }
    }

    private function backtrace($backtrace)
    {
        $this->_line = $backtrace[0]['line'];
        $this->_funcName = $backtrace[0]['function'];
        $this->_file = $backtrace[0]['file'];
        $this->_args = implode(',', $backtrace[0]['args']);

        $this->_express = "{$this->_funcName}({$this->_args})";
    }

    // 输出测试结果
    public function getResult()
    {
        $allNum = $this->_passNum + $this->_failNum;

        $failMessage = [];
        $message = '';
        if($allNum >0)
        {
            $color = $this->_failNum == 0 ? 'green' : 'red';

            foreach ($this->_failArray as $fail) {
                $failMessage[] = <<<EOF
<div style="padding: 8px; margin:0 15px 0;"><span style="color:red">Fail:</span>{$fail}</div>
EOF;
            }

            $message = <<<EOF
<div style="padding: 8px; margin:6px 15px 0; background-color: {$color}; color: white;">{$allNum} test cases complete:<strong>{$this->_passNum}</strong> passes, <strong>{$this->_failNum}</strong> fails at [{$this->_file}]</div>
EOF;
        }

        return ['message' => $message, 'fail' => $failMessage];
    }
}