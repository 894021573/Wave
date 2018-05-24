<?php
/**
 * 视图层父类
 *
 * @author: 洪涛
 * @date: 2018/05/24
 */

namespace core\framework;

class View
{
    private $_viewDirectory; // 视图目录
    private $_viewFile; // 视图文件
    private $_data; // 数据
    private $_cacheDir; // 缓存视图目录
    private $_isOpenCache; // 是否开启视图目缓存
    private $_expireTime; // 缓存文件过期时间

    private $_isPartial = false;

    public function __construct($config)
    {
        $this->_viewDirectory = $config['dir'];
        $this->_isOpenCache = $config['is_open_cache'];
        $this->_cacheDir = $config['cache_dir'];
        $this->_expireTime = $config['expire_time'];

    }

    /**
     * 输出页面
     *
     * @param $viewFile
     * @param array $data
     * @param int $expireTime 当前视图的缓存时间
     * @throws \Exception
     */
    public function render($viewFile, $data = [], $expireTime = null)
    {
        // 缓存文件
        $cacheFile = $this->_cacheDir . $viewFile . '.php';

        // 未开启视图缓存
        $content = '';
        if (!$this->_isOpenCache)
        {
            $content = $this->generateView($viewFile, $data);
        }
        else // 开启视图缓存
        {
            if (isset($expireTime))
            {
                $this->_expireTime = $expireTime;
            }
            if (file_exists($cacheFile) && filemtime($cacheFile) + $this->_expireTime > time()) // 缓存文件存在且未过期
            {
                require_once $cacheFile;
            }
            else
            {
                $content = $this->generateView($viewFile, $data);
                $this->generateCacheView($cacheFile, $content);
            }
        }

        return $content;
    }

    /**
     * 输出页面
     *
     * @param $viewFile
     * @param array $data
     * @param null $expireTime
     */
    public function renderPartial($viewFile, $data = [], $expireTime = null)
    {
        $this->_isPartial = true;
        return $this->render($viewFile, $data, $expireTime);
    }

    /**
     * 生成缓存视图文件
     *
     * @param string $cacheFile 缓存文件名
     * @param string $content 文件内容
     */
    public function generateCacheView($cacheFile, $content)
    {
        $cacheDir = dirname($cacheFile);
        if (!is_dir($cacheDir))
        {
            $this->mkdirs($cacheDir);
        }

        $content = file_put_contents($cacheFile, $content);
        if (!$content)
        {
            throw new \Exception("生成缓存文件（{$cacheFile}）失败");
        }
    }

    /**
     * 生成视图文件
     *
     * @param string $viewFile 视图文件名
     * @param array $data 数据
     * @return string
     * @throws \Exception
     */
    public function generateView($viewFile, $data)
    {
        $this->_viewFile = $viewFile;
        $this->_data = $data;

        $file = $this->_viewDirectory . $this->_viewFile . '.php';

        if (!file_exists($file))
        {
            throw new \Exception("视图文件（{$file}）不存在");
        }

        extract($data);

        ob_start();
        require_once $file;
        $content = ob_get_contents(); // $content变量包含在layout.php中
        ob_end_clean();

        if (!$this->_isPartial)
        {
            ob_start();
            require_once VIEW_DIR . 'layout/layout.php';
            $content = ob_get_contents();
            ob_end_clean();
        }

        return $content;
    }

    /**
     * 页面跳转
     *
     * @param $url
     */
    public function redirect($url)
    {
        header("Location:{$url}");
        exit();
    }

    private function mkdirs($dir, $mode = 0777)
    {
        if(is_dir($dir))
        {
            return TRUE;
        }

        if (!$this->mkdirs(dirname($dir), $mode)) return FALSE;
        return @mkdir($dir, $mode);
    }
}