<?php
/**
 * 服务层父类
 *
 * @author: 洪涛
 * @date: 2017/8/30
 */

namespace core\framework;

use core\Wave;

class Service
{
    /**
     * 输出固定格式的JSON数据
     *
     * @param $result
     * @return string
     */
    public function jsonR($result)
    {
        list($code, $msg, $data) = $result;

        /**
         * @var Response $response
         */
        $response = Wave::make('response');

        return $response->jsonR($code, $msg, $data);
    }

    /**
     * 输出任意形式的JSON数据
     *
     * @param $result
     * @return string
     */
    public function json($result)
    {
        /**
         * @var Response $response
         */
        $response = Wave::make('response');

        return $response->json($result);
    }
}