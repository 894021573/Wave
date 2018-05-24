<?php
/**
 * 数据库配置
 *
 * @author: 洪涛
 * @date: 2017/12/15
 */

return [
    'default' => [
        'is_log' => true, // 是否记录SQL
        'master' => [
            'name' => 'master',
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'user' => 'root',
            'password' => 'root',
            'database_name' => 'test',
        ],
        'slave' => [
            [
                'name' => 'slave_1',
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => '3306',
                'user' => 'root',
                'password' => 'root',
                'database_name' => 'test',
            ],
            [
                'name' => 'slave_2',
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => '3306',
                'user' => 'root',
                'password' => 'root',
                'database_name' => 'test',
            ],
            [
                'name' => 'slave_3',
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => '3306',
                'user' => 'root',
                'password' => 'root',
                'database_name' => 'test',
            ],
        ],
    ],

    'aa' => [
        'is_log' => true, // 是否记录SQL
        'master' => [
            'name' => 'master',
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'user' => 'root',
            'password' => 'root',
            'database_name' => 'test',
        ],
        'slave' => [
            [
                'name' => 'slave_1',
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => '3306',
                'user' => 'root',
                'password' => 'root',
                'database_name' => 'test',
            ],
            [
                'name' => 'slave_2',
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => '3306',
                'user' => 'root',
                'password' => 'root',
                'database_name' => 'test',
            ],
            [
                'name' => 'slave_3',
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => '3306',
                'user' => 'root',
                'password' => 'root',
                'database_name' => 'test',
            ],
        ],
    ],
];