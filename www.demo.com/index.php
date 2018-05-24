<?php
/**
 * wave 框架
 * 框架结构
 *
 * --- project 项目目录
 *          --- app 应用目录
 *              --- controllers 控制器层
 *              --- models 模型层
 *              --- proxies 代理层 代理模式的代码
 *              --- services 业务层
 *              --- validators 数据验证层
 *              --- views 视图层
 *          --- config 配置目录
 *              --- main.php 主配置问文件
 *          --- route 路由目录
 *              --- route.php 路由文件
 *          --- runtime 程序运行目录，包括日志，缓存等
 *          --- tao 核心目录和第三方类库
 *          --- tests 单元测试目录
 * --- tao 框架核心文件
 * ---vendor composer文件
 *
 * @author: 洪涛
 * @date: 2017/8/30
 */
//phpinfo();exit();
//ini_set('display_errors', 0); // 错误信息均在自定义错误和异常中处理
//header("Content-type:text/html;charset=utf-8");
//
define('PROJECT_DIR', __DIR__ . '/'); // 当前项目根目录

require '../core/constant.php'; // 常量文件
require '../core/Wave.php'; // 核心文件

if (!IS_CLI)
{
    \core\Wave::runWeb();
} else
{
    \core\Wave::runCommand($argv);
}