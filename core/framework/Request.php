<?php
/**
 * Request类
 *
 * @author: 洪涛
 * @date: 2018/05/24
 */

namespace core\framework;

use core\Wave;

class Request
{
    private $_group; // 控制器组名
    private $_fullController; // 带命名空间的控制器名
    private $_controller; // 控制器名
    private $_shortController; // 不带Controller后缀的控制器名
    private $_action; // 方法名

    private $_controllerNamespace; // 控制器命名空间

    private $_requestMethod; // 请求方法

    public function __construct()
    {

    }

    /**
     * @return mixed
     */
    public function getShortController()
    {
        $this->_shortController = str_replace('Controller', '', $this->_controller);
        return $this->_shortController;
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->_group;
    }

    /**
     * @return mixed
     */
    public function getFullController()
    {
        return $this->_fullController;
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * @return mixed
     */
    public function getRequestMethod()
    {
        return $this->_requestMethod;
    }

    public function isGet()
    {
        return $this->getRequestMethod() == 'GET';
    }

    public function isPost()
    {
        return $this->getRequestMethod() == 'POST';
    }

    /**
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        $gets = $_GET;
        $gets = $this->filterParams($gets);
        if (!isset($key)) {
            return $gets;
        }

        return isset($gets[$key]) ? $gets[$key] : (isset($default) ? $default : null);
    }

    /**
     * @return mixed
     */
    public function post($key = null, $default = null)
    {
        $posts = $_POST;
        $posts = $this->filterParams($posts);
        if (!isset($key)) {
            return $posts;
        }

        return isset($posts[$key]) ? $posts[$key] : (isset($default) ? $default : null);
    }

    /**
     * 参数过滤
     *
     * @param array $params
     * @return array
     */
    private function filterParams(array &$params)
    {

        foreach ($params as $k => $item) {
            if (is_array($params[$k])) {
                $this->filterParams($params[$k]);
            } else {
                $params[$k] = addslashes(htmlentities($item));
            }
        }
        return $params;
    }

    // 合并GET参数
    public function mergeGet($vars)
    {
        $_GET = array_merge($vars, $_GET);
    }

    // 处理路由handler
    public function processHandler($requestMethod, $handler)
    {
        $this->_requestMethod = $requestMethod;

        // 检查是否为可执行的匿名函数
        if ($handler instanceof \Closure && is_callable($handler)) {
            $handler();
            return;
        }

        // 检查路由
        if (!strpos($handler, '@')) {
            throw new \Exception("不合法的路由（{$handler}）");
        }

        // 提示控制器和方法
        list($controller, $action) = explode('@', $handler);

        $this->_controllerNamespace = CONTROLLER_NAMESPACE;
        if (strpos($controller, '/')) {
            list($group, $controller) = explode('/', $controller);
            $this->_group = $group;
            $this->_controllerNamespace .= CONTROLLER_NAMESPACE . '\\' . $this->_group;
        }
        $this->_controller = $controller;
        $this->_action = $action;

        if (!empty($this->_group)) {
            $controllerFile = CONTROLLER_DIR . $this->_group . '/' . $this->_controller . '.php';
        } else {
            $controllerFile = CONTROLLER_DIR . $this->_controller . '.php';
        }

        if (!file_exists($controllerFile)) {
            throw new \Exception("控制器文件（{$controllerFile}）不存在");
        }

        $this->_fullController = $this->_controllerNamespace . '\\' . $this->_controller;

        $reflectionClass = new \ReflectionClass($this->_fullController);

        if (!$reflectionClass->hasMethod($this->_action)) {
            throw new \Exception("方法（{$this->_action}）不存在");
        }

        $reflectMethod = new \ReflectionMethod($this->_fullController, $this->_action);
        if (!$reflectMethod->isPublic()) {
            throw new \Exception("（{$this->_fullController}）中的方法（{$this->_action}）必须是公有的");
        }

        $controllerObject = $reflectionClass->newInstance();

        if ($reflectionClass->hasMethod('proxy')) // 用代理模式
        {
            $proxyReflectMethod = new \ReflectionMethod($this->_fullController, 'proxy');
            $result = $proxyReflectMethod->invokeArgs($controllerObject, [$this->_fullController, $this->_action]);
        }
        else
        {
            $result = $reflectMethod->invokeArgs($controllerObject, [$this]);
        }

        /**
         * @var Response $response
         */
        $response = Wave::make('response');

        echo $response->show($result);
    }

    public function getSession($key = null, $default = '')
    {
        $this->startSession();

        if(!isset($key)){
            return $_SESSION;
        }

        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return $default;
    }

    public function setSession($key, $value)
    {
        $this->startSession();
        $_SESSION[$key] = $value;
    }

    public function removeSession($key)
    {
        $this->startSession();

        unset($_SESSION[$key]);
    }

    private function startSession()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
    }
}