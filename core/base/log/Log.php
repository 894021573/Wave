<?php
/**
 * 日志类
 *
 * @author: 洪涛
 * @date: 2018/05/24
 */

namespace core\base\log;

class Log
{
    private $_logDir;

    const TYPE_NORMAL = 'normal';  //类型 - 通用
    const TYPE_USER = 'user';    //类型 - 用户
    const TYPE_CLIENT = 'client';  //类型 - 终端
    const TYPE_ADMIN = 'admin';   //类型 - 管理员
    const TYPE_DANGER = 'danger';  //类型 - 高危

    public function __construct($config)
    {
        $this->_logDir = $config['dir'] . date('Y-m-d') . '/';
    }

    /**
     * 写入日志
     *
     * @param $filePath
     * @param $content
     * @throws \Exception
     */
    public function write($content, $type = self::TYPE_NORMAL)
    {
        if (!is_dir($this->_logDir)) {
            $this->mkDirs($this->_logDir);
        }

        $filePath = $this->_logDir . '/' . $type . '.log';

        return file_put_contents($filePath, $content, FILE_APPEND);
    }

    // 读取日志
    public function read()
    {

    }

    private function mkDirs($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;
        if (!$this->mkDirs(dirname($dir), $mode)) return FALSE;
        return @mkdir($dir, $mode);
    }
}