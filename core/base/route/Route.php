<?php
/**
 *
 * @author: 洪涛
 * @date: 2018/05/24
 */

namespace core\base\route;

class Route
{
    private $_routes = []; // 路由数组
    private $_requestUri;
    private $_requestMethod;

    private $_params = []; // 参数数组

    private $_matchResult; // 匹配结果

    private $_error; // 错误信息
    private $_currentNumber = 0; // 当前路由编号

    private $_isMatchAnyMethod = false; // 是否匹配任意方式

    private $_nodes = []; // 用于优化匹配的路由节点数组

    private $_isReadRouteCache = false; // 是否从缓存文件中读取路由

    private $_routeCacheFile; // 路由缓存文件

    public function __construct()
    {
        $this->_requestUri = '';
        if (!empty($_SERVER['REQUEST_URI'])) {
            $this->_requestUri = trim($_SERVER['REQUEST_URI'], '/');
        }

        $this->_requestMethod = $_SERVER['REQUEST_METHOD'];

        $this->_routeCacheFile = __DIR__ . '/route_nodes.php';
    }

    /**
     * 添加路由
     *
     * @param array $methods
     * @param $route
     * @param $handler
     * @param array $keys
     */
    public function add(array $methods, $route, $handler)
    {
        $this->_currentNumber += 1;

        $route = trim($route, '/');
        $this->_routes[] = ['no' => $this->_currentNumber, 'methods' => $methods, 'route' => $route, 'handler' => $handler];

        // 按照一定规则存入路由节点中，以提高匹配效率
        $routes = explode('/', $route);
        $tempString = '';
        foreach ($routes as $item) {
            if ($this->isVariable($item)) {
                break;
            }

            $tempString .= $item . '-';
        }

        $tempString = trim($tempString, '-');

        $this->_nodes[$tempString][] = ['no' => $this->_currentNumber, 'methods' => $methods, 'route' => $route, 'handler' => $handler];
    }

    /**
     * 匹配任意请求方式
     *
     * @param $route
     * @param $handler
     */
    public function any($route, $handler)
    {
        $this->_isMatchAnyMethod = true;
        $this->add([], $route, $handler);
    }

    public function addRoutes(\Closure $addRoute)
    {
        if (!$this->_isReadRouteCache) {
            $addRoute();
        }
    }

    /**
     * 处理
     */
    public function run()
    {
        if ($this->_isReadRouteCache) {
            $this->_nodes = $this->getRouteCache();
        } else {
            $this->setRouteCache();
        }

        // 按照一定规则解析请求URI，用于和nodes数组匹配
        $routes = explode('/', $this->_requestUri);
        $tempString = '';
        $tempArray = [];
        foreach ($routes as $item) {
            $tempString .= $item . '-';
            $tempArray[] = trim($tempString, '-');
        }

        foreach ($tempArray as $tempString) {
            if (isset($this->_nodes[$tempString])) {
                foreach ($this->_nodes[$tempString] as $k => $route) {
                    if ($this->match($route)) {
                        break;
                    }
                }
            }
        }

//        foreach ($this->_routes as $k => $route) {
//            if($this->match($route)){
//                break;
//            }
//        }
    }

    private function match($route)
    {
        $isMatchRequestMethod = $this->matchMethod($route['methods']); // 再匹配请求方法
        if (!$isMatchRequestMethod) {
            return false;
        }

        $isMatchRoute = $this->matchRoute($route['route'], $this->_requestUri); // 先匹配路由
        if (!$isMatchRoute) {
            return false;
        }

        $this->_matchResult = $route['handler']; // 取得匹配结果

        return true;
    }

    private function getRouteCache()
    {
        if (file_exists($this->_routeCacheFile)) {
            return include $this->_routeCacheFile;
        } else {
            throw new \Exception("路由缓存文件{$this->_routeCacheFile}不存在");
        }
    }

    private function setRouteCache()
    {
        $tempNodes = [];
        foreach ($this->_nodes as $k => $item) {
            foreach ($item as $val) {
                if (!$val['handler'] instanceof \Closure) { // 闭包函数不存入节点
                    $tempNodes[$k][] = $val;
                }
            }
        }
        $this->_nodes = $tempNodes;
        file_put_contents($this->_routeCacheFile, '<?php return ' . var_export($this->_nodes, true) . ';');
    }

    private function matchMethod($methods)
    {
        if (!in_array(strtoupper($this->_requestMethod), $methods) && !$this->_isMatchAnyMethod) {
            $this->_error = $this->setError('请求方式不匹配');
            return false;
        }
        return true;
    }

    private function matchRoute($route, $requestUri)
    {
        $routes = explode('/', $route);
        $requestUri = explode('/', $requestUri);

        if (count($routes) != count($requestUri)) {
            $this->_error = $this->setError('两边结构数目不对等');
            return false;
        }

        $isMatch = true;
        for ($i = 0; $i < count($routes); $i++) {
            $isMatch = $this->matchPart($routes[$i], $requestUri[$i], $i);
            if (!$isMatch) {
                break;
            }
        }

        return $isMatch;
    }

    private function matchPart($routePart, $requestUriPart, $i)
    {
        // 全等匹配
        if ($routePart === $requestUriPart) {
            return true;
        }

        $i = $i + 1;
        if (!$this->isVariable($routePart)) {
            $this->_error = $this->setError("第{$i}部分不是可识别的{xxx}语法");
            return false;
        }

        $routePart = str_replace(['{', '}'], '', $routePart);
        if (strpos($routePart, ':')) {
            list($paramName, $regularExpression) = explode(":", $routePart);
        } else {
            $regularExpression = $routePart;
        }

        // 正则匹配
        $isMatch = preg_match("/^{$regularExpression}$/i", $requestUriPart, $matches);
        if (!$isMatch) {
            $i = $i + 1;
            $this->_error = $this->setError("第{$i}部分不匹配：{$regularExpression} not match {$requestUriPart}");
            return false;
        }

        // 保存参数
        if (!empty($paramName)) {
            $this->_params[$paramName] = current($matches);
        } else {
            $this->_params[] = current($matches);
        }

        return true;
    }

    /**
     * 是否为可变的字符串
     *
     * @param $string
     * @return bool
     */
    private function isVariable($string)
    {
        if (strpos($string, '{') !== 0 && strpos($string, '}') !== strlen($string) - 1) {
            return false;
        }

        return true;
    }

    /**
     * 获取匹配的参数数组
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    public function getError()
    {
        if (empty($this->getMatchResult())) {
            return $this->_error;
        } else {
            return '';
        }
    }

    /**
     * 获取匹配的结果
     *
     * @return mixed
     */
    public function getMatchResult()
    {
        $result = $this->_matchResult;

        return $result;
    }

    /**
     * 获取请求方法
     *
     * @return mixed
     */
    public function getRequestMethod()
    {
        return $this->_requestMethod;
    }

    private function setError($msg)
    {
        $this->_error = '第' . $this->_currentNumber . '条路由=>' . $msg;
        return $this->_error;
    }

    public function getNodes()
    {
        return $this->_nodes;
    }
}

