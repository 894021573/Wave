<?php
/**
 *
 * @author: 洪涛
 * @date: 2018/5/24
 */
return [
    'cache' =>
        [
            'class_name' => '\core\base\cache\Cache',
            'params' => [
                'driver' => 'redis', // 缓存驱动
                'driverConfig' =>
                    [ // redis配置
                        'host' => '127.0.0.1',
                        'port' => 6379,
                        'expire_time' => 10 // 缓存时间
                    ],
            ],
        ],

    // 日志配置
    'log' =>
        [
            'class_name' => '\core\base\log\Log',
            'params' => [
                'config' => [
                    'dir' => RUNTIME_DIR . 'log/'
                ],
            ],
        ],

    'debug' => [
        'class_name' => '\core\base\debug\Debug',
    ],

    'view' => [
        'class_name' => '\core\framework\View',
        'params' => [
            'config' => [
                'dir' => VIEW_DIR,
                'is_open_cache' => true, // 如果为false，不生成静态缓存文件,开发期间应设置为false
                'cache_dir' => RUNTIME_DIR . 'view_cache/', // 静态文件缓存路径
                'expire_time' => 10, // 缓存时间
            ],
        ],
    ],

    'route' => [
        'class_name' => '\core\framework\Route',
    ],

    'request' => [
        'class_name' => '\core\framework\Request',
    ],

    'response' => [
        'class_name' => '\core\framework\Response',
    ],

];