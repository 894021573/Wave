<?php
/*
 * Redis缓存类
 *
 * @author: 洪涛
 * @date: 2018/05/24
 */

namespace core\base\cache;

class RedisCache implements CacheImplement
{
    private $_redis;
    private $_expireTime;

    public function __construct($configs)
    {
        if (!extension_loaded('redis'))
        {
            throw new \Exception('Redis扩展未开启');
        }

        $this->_redis = new \Redis();

        if (!$this->_redis->connect($configs['host'], $configs['port']))
        {
            throw new \Exception('Redis连接失败');
        }

        $this->_expireTime = $configs['expire_time'];

        return $this->_redis;
    }

    public function exists($key)
    {
        return $this->_redis->exists($key);
    }

    public function get($key)
    {
        $type = $this->_redis->type($key);

        if ($type == \Redis::REDIS_HASH)
        {
            return $this->_redis->hGetAll($key);
        } elseif ($type == \Redis::REDIS_STRING)
        {
            return $this->_redis->get($key);
        } else
        {
            return null;
        }
    }

    public function set($key, $value, $expireTime = 0)
    {
        if (!empty($this->_expireTime) && $expireTime == 0)
        {
            $expireTime = $this->_expireTime;
        }

        if (is_array($value))
        {
            $result = true;
            foreach ($value as $k => $v)
            {
                if ($this->_redis->hSet($key, $k, $v) === false)
                {
                    $result = false;
                }
            }
            if ($expireTime != 0)
            {
                $this->_redis->setTimeout($key, $expireTime); // 当$expireTime为0，表示立刻过期
            }

            return $result;
        } else
        {
            return $this->_redis->setex($key, $expireTime, $value); // 当$expireTime为0，则表示不过期
        }
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
        $length = 0;
        foreach ($values as $value)
        {
            $length = $this->_redis->lPush($key, $value);
            if ($length === false)
            {
                break;
            }
        }

        return $length;
    }

    /**
     * 返回列表最右边的值
     *
     * @param $key
     * @return string|bool 成功则返回对应的值，失败则返回false
     */
    public function getLastValue($key)
    {
        $length = $this->_redis->lLen($key);
        return $this->_redis->lIndex($key, $length - 1);
    }

    /**
     * 右出
     *
     * @param $key
     * @return string|bool 删除成功，返回删除的值；删除失败，返回false
     */
    public function rightPop($key)
    {
        return $this->_redis->rPop($key);
    }

    public function delete($key)
    {
        return $this->_redis->del($key);
    }

    public function getLength($key)
    {
        return $this->_redis->lLen($key);
    }

    public function getByIndex($key,$index)
    {
        return $this->_redis->lIndex($key, $index);
    }

    public function searchKey($searchKey)
    {
        return $this->_redis->keys($searchKey);
    }

    public function testR()
    {
        var_dump( $this->_redis->keys('number*'));
    }
}