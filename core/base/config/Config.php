<?php
/**
 * 配置类
 *
 * @author: 洪涛
 * @author: 洪涛
 * @date: 2018/05/24
 */

namespace core\base\config;

class Config
{
    private $_configs = [];

    public function __construct()
    {
       $list = scandir(CONFIG_DIR);
       foreach ($list as $item){
           if($item == '.' || $item == '..'){
               continue;
           }
           $this->load(CONFIG_DIR . $item);
       }
    }

    /**
     * 载入配置文件
     *
     * @param $configFile
     * @return array
     * @throws \Exception
     */
    private function load($configFile)
    {
        if (!file_exists($configFile))
        {
            throw new \Exception("配置文件[{$configFile}]不存在");
        }

        $baseFile = basename($configFile);

        $index = strpos($baseFile, '.');
        if ($index !== false)
        {
            $k = substr($baseFile, 0, $index);
        }
        else
        {
            $k = $baseFile;
        }

        $this->_configs[$k] = require_once $configFile;

        return $this->_configs;
    }

    /**
     * 获取配置项
     *
     * @param string $key
     * @return array|mixed
     */
    public function get($key = '')
    {
        if (empty($key))
        {
            return $this->_configs;
        }

        if (($index = strpos($key, '.')) !== false)
        {
            $keys = explode('.', $key);
            return $this->getConfig($keys);
        }

        return $this->_configs[$key];
    }

    /**
     * 根据规则获取配置项
     *
     * @param $keys
     * @return array|mixed
     */
    private function getConfig($keys)
    {
        $currentConfig = $this->_configs;
        foreach ($keys as $k)
        {
            if(!isset($currentConfig[$k]))
            {
                var_dump($k);
                var_dump($currentConfig);
            }
            $currentConfig = $currentConfig[$k];
        }
        return $currentConfig;
    }
}