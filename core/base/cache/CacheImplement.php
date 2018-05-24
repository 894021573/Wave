<?php
/**
 *
 * @author: 洪涛
 * @date: 2017/8/30
 */
namespace core\base\cache;

interface CacheImplement
{
    public function exists($key);
    public function get($key);
    public function set($key,$value,$expireTime = 0);
    public function delete($key);
}