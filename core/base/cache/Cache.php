<?php
/**
 * 缓存类
 *
 * @author: 洪涛
 * @date: 2018/05/24
 */

namespace core\base\cache;

class Cache implements CacheImplement
{
    private $_driver;
    private $_caches;

    /**
     *
     * @param array $config 缓存配置
     * @param string $forceDriver 指定缓存驱动
     * @throws \Exception
     */
    public function __construct($driver,$driverConfig, $forceDriver = '')
    {
        if (!empty($forceDriver))
        {
            $this->_driver = $forceDriver;
        } else
        {
            $this->_driver = $driver;
        }

        switch ($this->_driver)
        {
            case 'redis':
                $this->_caches[$this->_driver] = new RedisCache($driverConfig);
                break;
            default:
                throw new \Exception('未找到对应的驱动类' . $this->_driver);
        }
    }

    public function exists($key)
    {
        $cache = isset($this->_caches[$this->_driver]) ? $this->_caches[$this->_driver] : null;
        if (!$cache instanceof CacheImplement)
        {
            throw new \Exception('非法实例');
        }

        return $cache->exists($key);
    }

    public function get($key)
    {
        $cache = isset($this->_caches[$this->_driver]) ? $this->_caches[$this->_driver] : null;
        if (!$cache instanceof CacheImplement)
        {
            throw new \Exception('非法实例');
        }

        return $cache->get($key);
    }

    /**
     * @param $key
     * @param $value
     * @param int $expireTime
     * @return bool (同样的key，先设为字符串类型，再设为hash类型，就设置失败，返回false)
     * @throws \Exception
     */
    public function set($key, $value, $expireTime = 0)
    {
        $cache = isset($this->_caches[$this->_driver]) ? $this->_caches[$this->_driver] : null;
        if (!$cache instanceof CacheImplement)
        {
            throw new \Exception('非法实例');
        }

        return $cache->set($key, $value, $expireTime);
    }

    /**
     * 左进
     *
     * @param $key
     * @param array $values
     * @return int|bool 成功则返回列表长度，失败则返回false
     */
    public function leftPush($key, array $values)
    {
        $cache = isset($this->_caches[$this->_driver]) ? $this->_caches[$this->_driver] : null;
        if (!$cache instanceof RedisCache)
        {
            throw new \Exception('非法实例');
        }

        return $cache->leftPush($key, $values);
    }

    /**
     * 返回列表最右边的值
     *
     * @param $key
     * @return string|bool 成功则返回对应的值，失败则返回false
     */
    public function getLastValue($key)
    {
        $cache = isset($this->_caches[$this->_driver]) ? $this->_caches[$this->_driver] : null;
        if (!$cache instanceof RedisCache)
        {
            throw new \Exception('非法实例');
        }

        return $cache->getLastValue($key);
    }

    /**
     * 右出
     *
     * @param $key
     * @return string|bool 删除成功，返回删除的值；删除失败，返回false
     */
    public function rightPop($key)
    {
        $cache = isset($this->_caches[$this->_driver]) ? $this->_caches[$this->_driver] : null;
        if (!$cache instanceof RedisCache)
        {
            throw new \Exception('非法实例');
        }

        return $cache->rightPop($key);
    }

    /**
     * 返回删除个数
     *
     * @param $key
     * @return int
     * @throws \Exception
     */
    public function delete($key)
    {
        $cache = isset($this->_caches[$this->_driver]) ? $this->_caches[$this->_driver] : null;
        if (!$cache instanceof CacheImplement)
        {
            throw new \Exception('非法实例');
        }

        return $cache->delete($key);
    }

    public function getLength($key)
    {
        $cache = isset($this->_caches[$this->_driver]) ? $this->_caches[$this->_driver] : null;
        if (!$cache instanceof RedisCache)
        {
            throw new \Exception('非法实例');
        }

        return $cache->getLength($key);
    }

    public function getByIndex($key, $index)
    {
        $cache = isset($this->_caches[$this->_driver]) ? $this->_caches[$this->_driver] : null;
        if (!$cache instanceof RedisCache)
        {
            throw new \Exception('非法实例');
        }

        return $cache->getByIndex($key, $index);
    }

    public function searchKey($searchKey)
    {
        $cache = isset($this->_caches[$this->_driver]) ? $this->_caches[$this->_driver] : null;
        if (!$cache instanceof RedisCache)
        {
            throw new \Exception('非法实例');
        }

        return $cache->searchKey($searchKey);
    }

    public function testR()
    {
        $cache = isset($this->_caches[$this->_driver]) ? $this->_caches[$this->_driver] : null;
        if (!$cache instanceof RedisCache)
        {
            throw new \Exception('非法实例');
        }

        return $cache->testR();
    }
}