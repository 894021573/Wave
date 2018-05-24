<?php
/**
 *
 * @author: 洪涛
 * @date: 2018/05/24
 */
if (!defined('PROJECT_DIR'))
{
    throw new Exception('PROJECT_DIR need to be defined in index.php of current project');
}

define('ROOT_DIR', dirname(__DIR__) . '/'); // 框架代码根目录

// 项目常量
define('APP_DIR', PROJECT_DIR . 'app/'); // app目录
define('ROUTE_DIR', PROJECT_DIR . 'route/'); // route目录
define('RUNTIME_DIR', PROJECT_DIR . 'runtime/'); // runtime目录

define('CONFIG_DIR', PROJECT_DIR . 'config/'); // 配置文件目录

define('CONTROLLER_DIR', APP_DIR . 'controllers/'); // 控制器目录
define('VIEW_DIR', APP_DIR . 'views/'); // 视图目录
define('STATICS_PATH', APP_DIR . 'views/statics/'); // 静态文件目录

define('CONTROLLER_NAMESPACE', 'app\\controllers'); // 控制器命名空间

define('IS_CLI', php_sapi_name() == 'cli' ? true : false);
define('COMMAND_DIR', APP_DIR . 'commands/');

define('CSRF_TOKEN','csrf_token');