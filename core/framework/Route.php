<?php
/**
 * 路由类
 *
 * @author: 洪涛
 * @date: 2018/05/24
 */

namespace core\framework;

use core\base\debug\Debug;
use core\Wave;

class Route
{
    // 自动路由:域名 + [index.php] + [组名] + 控制器名 + 方法名 + ?a=1&b=2
    public function autoRun()
    {
        if (($pos = strpos($_SERVER['PATH_INFO'], '?')) !== false) {
            $pathInfo = substr($_SERVER['PATH_INFO'], 0, $pos);
        } else {
            $pathInfo = $_SERVER['PATH_INFO'];
        }

        $routes = explode('/', trim($pathInfo, '/'));

        $count = count($routes);
        if ($count == 2) {
            list($controller, $action) = $routes;
            $controller = ucfirst($controller) . 'Controller';
            $handler = $controller . '@' . $action;
        } elseif ($count > 2) {
            $controller = $routes[$count - 2] . 'Controller';
            $action = $routes[$count - 1];
            $handler = $controller . '@' . $action;

            unset($routes[count($routes) - 1]);
            unset($routes[$count - 2]);

            $temp = implode($routes, '/');
            $handler = $temp . '/' . $handler;
        } else {
            throw new \Exception('路由' . current($routes) . '不正确,信息太少了');
        }

        $httpMethod = $_SERVER['REQUEST_METHOD'];

        $this->processHandler($httpMethod, $handler,[]);
    }

    // 手动路由
    public function run($routeFile)
    {
        $route = new \core\base\route\Route();

        if (!file_exists($routeFile)) {
            throw new \Exception('app目录下route.php文件不存在');
        }

        require $routeFile;

        $route->run();

        if (!empty($error = $route->getError())) {
            throw new \Exception($error);
        }

        $this->processHandler($route->getRequestMethod(), $route->getMatchResult(),$route->getParams());
    }

    private function processHandler($method, $matchResult, $params = [])
    {
        /**
         * @var Request $request
         */
        $request = Wave::make('request');
        $request->mergeGet($params);
        $request->processHandler($method, $matchResult);

        // 把请求信息加入到调试面板
        $this->addDebug($request);
    }

    private function addDebug(Request $request)
    {
        $messages[] = '请求控制器：' . $request->getFullController();
        $messages[] = '请求方法：' . $request->getAction();
        $messages[] = 'GET参数：' . urldecode(http_build_query($request->get()));
        $messages[] = 'POST参数：' . urldecode(http_build_query($request->post()));
        /**
         * @var Debug $debug
         */
        $debug = Wave::make('debug');
        $debug->addDebug('Request', str_replace("\r\n", '<br>', $messages));
    }
}