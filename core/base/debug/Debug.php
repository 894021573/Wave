<?php

/**
 * 调试面板类
 *
 * @author: 洪涛
 * @date: 2018/05/24
 */

namespace core\base\debug;


class Debug
{
    private $_debug_messages = [];
    private $_panels = [];
    private $_isOpen = true;
    private static $_instance;

    /**
     * 单例模式
     *
     * @return Debug
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self))
        {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * @param mixed $isOpen
     */
    public function setIsOpen($isOpen)
    {
        $this->_isOpen = $isOpen;
    }

    /**
     * 加入调试面板
     *
     * @param $panel
     * @param $message
     */
    public function addDebug($panel, $message)
    {
        if ($this->_isOpen)
        {
            if (!in_array($panel, $this->_panels))
            {
                $this->_panels[] = $panel;
            }

            if(is_array($message))
            {
                $this->_debug_messages[$panel] = $message;
            }else
            {
                $this->_debug_messages[$panel][] = $message;
            }
        }
    }

    /**
     * 输出调试面板
     */
    public function showDebug()
    {
        if (!empty($this->_debug_messages))
        {
            require_once 'debug_template.php';
        }
    }
}