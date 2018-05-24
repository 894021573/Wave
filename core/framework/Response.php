<?php
/**
 * Created by PhpStorm.
 * User: ht
 * @date: 2018/05/24
 */

namespace core\framework;

class Response
{
    private $_contentType = self::HTML;

    const HTML = 'html';
    const JSON = 'json';

    const SUCCESS = 1;
    const UNKNOWN_ERROR = 2;
    const SERVICE_TEMPORARILY_UNAVAILABLE = 3; //后端服务暂时不可用
    const INVALID_PARAMETER = 100; //参数无效或缺失
    const UPDATE_FAILED = 1001;
    const DELETE_FAILED = 1002;
    const ADD_FAILED = 1003;
    const OPERATE_FAILED = 1004;

    private static $messages = [
        1 => 'Success',//成功
        2 => 'Unknown error',//未知错误
        3 => 'Service temporarily unavailable',//后端服务暂时不可用
        1001 => 'Update Failed',//修改失败
        1002 => 'Delete Failed',//删除失败
        1003 => 'Add Failed',//添加失败
        1004 => 'Operate Failed', //操作失败
    ];

    public function jsonR($code, $msg = '', $data = [])
    {
        $this->_contentType = self::JSON;

        if (empty($code)) {
            throw new \Exception('code 不能为空');
        }

        if (empty($msg) && isset(self::$messages[$code])) {
            $msg = self::$messages[$code];
        }
        return json_encode(['code' => $code, 'msg' => $msg, 'data' => $data]);
    }

    public function json($result)
    {
        $this->_contentType = self::JSON;

        return json_encode($result);
    }

    public function getContentType()
    {
        return $this->_contentType ? $this->_contentType : self::HTML;
    }

    public function show($result)
    {
        switch ($this->_contentType) {
            case self::HTML:
                header('Content-type: text/html;charset=utf-8');
                break;
            case self::JSON:
                header('Content-type: application/json;charset=utf-8');
                break;
            default:
                header('Content-type: text/html;charset=utf-8');
        }

        return $result;
    }
}