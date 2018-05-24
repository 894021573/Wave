<?php
/**
 * 控制器层父类
 *
 * @author: 洪涛
 * @date: 2018/05/24
 */

namespace core\framework;

use core\Wave;

class Controller
{
    protected $_viewPath;
    protected $_is_csrf = true; // 是否开启CSRF验证

    /**
     * @var Request $_request
     */
    private $_request = null;

    /**
     * @var View $request
     */
    private $_view = null;

    public function __construct()
    {
        $this->_request = Wave::make('request');
        $this->_view = Wave::make('view');

        if ($this->_request->isPost() && $this->_is_csrf) {
            if (empty($this->_request->post(CSRF_TOKEN)) || $this->_request->getSession(CSRF_TOKEN) != $this->_request->post(CSRF_TOKEN)) {
                throw new \Exception('CSRF验证失败！');
            }
        }

        if (!IS_CLI) {
            if (empty($this->_viewPath)) {
                $group = $this->_request->getGroup();
                $shortController = lcfirst($this->_request->getShortController());
                $this->_viewPath = $group . '/' . $shortController . '/';
            }
        }
    }

    /**
     * 渲染页面,受layout.php影响
     *
     * @param string $viewFile
     * @param array $data
     * @return string
     */
    public function render($viewFile = '', $data = [])
    {
        if (empty($viewFile)) {
            $viewFile = $this->_request->getAction();
        }

        return $this->_view->render($this->_viewPath . $viewFile, $data);
    }

    /**
     * 渲染页面,不受layout.php影响
     *
     * @param string $viewFile
     * @param array $data
     * @return string
     */
    public function renderPartial($viewFile = '', $data = [])
    {
        if (empty($viewFile)) {
            $viewFile = $this->_request->getAction();
        }

        return $this->_view->renderPartial($this->_viewPath . $viewFile, $data);
    }

    /**
     * 获取GET参数
     *
     * @param null $key
     * @param null $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return $this->_request->get($key, $default);
    }

    /**
     * 获取POST参数
     *
     * @param null $key
     * @param null $default
     * @return mixed
     */
    public function post($key = null, $default = null)
    {
        return $this->_request->post($key, $default);
    }
}