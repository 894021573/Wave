<?php
/**
 *
 * @author: 洪涛
 * @date: 2018/05/24
 */
namespace core\base\command;


class Command
{
    public function __construct($file, $name, $params)
    {
        $commands = require_once $file;

        if (!isset($commands[$name])) {
            throw new \Exception("could not find sign {$name} in line.php, please check!");
        }

        $line = $commands[$name];
        if (!empty($params)) {
            $line['params'] = $params;
        }

        call_user_func_array([(new $line['class']), $line['method']], $line['params']);
    }
}