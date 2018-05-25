<?php
/**
 * 容器类
 *
 * @author: 洪涛
 * @date: 2018/05/24
 */

namespace core;

use core\base\command\Command;
use core\base\config\Config;
use core\base\debug\Debug;
use core\base\log\Log;
use core\base\orm\PDO;
use core\framework\Response;
use core\framework\Route;

class Wave
{
    private static $_relyInstances = []; // 依赖实例

    /**
     * [单例]获取对象示例
     *
     * @param $classMapName
     * @return mixed
     * @throws \Exception
     */
    public static function make($classMapName)
    {
        if (isset(self::$_relyInstances[$classMapName])) {
            return self::$_relyInstances[$classMapName];
        } else {
            if ($classMapName != 'config') {
                /**
                 * @var Config $config
                 */
                $config = self::make('config');
                $classMaps = $config->get('class_map');
                if (!isset($classMaps[$classMapName])) {
                    throw new \Exception($classMapName . '不存在');
                }

                $reflection = new \ReflectionClass($classMaps[$classMapName]['class_name']);
                if (!isset($classMaps[$classMapName]['params'])) {
                    $classMaps[$classMapName]['params'] = [];
                }
                self::$_relyInstances[$classMapName] = $reflection->newInstanceArgs($classMaps[$classMapName]['params']);
                return self::$_relyInstances[$classMapName];
            } else {
                self::$_relyInstances[$classMapName] = new Config();
                return self::$_relyInstances[$classMapName];
            }
        }
    }

    /**
     * @return mixed
     */
    public static function getRelyInstances()
    {
        return self::$_relyInstances;
    }

    /*
     * 自动加载类
     */
    public static function autoload()
    {
        $autoloadFile = __DIR__ . '/../vendor/autoload.php';
        if (file_exists($autoloadFile)) {
            require $autoloadFile;
        }

        spl_autoload_register(function ($class) {
            $class = str_replace('\\', '/', $class);

            $projectFile = PROJECT_DIR . $class . '.php';
            $rootFile = ROOT_DIR . $class . '.php';

            if (file_exists($projectFile)) {
                require_once $projectFile;
            } elseif (file_exists($rootFile)) {
                require_once $rootFile;
            } else {
                throw new \Exception("文件（{$projectFile}）不存在");
            }
        });
    }

    /**
     * 自定义错误
     * 自定义错误后，自带的错误提示将不显示，所以必须自己设置错误提示
     */
    public static function processError()
    {
        set_error_handler(function ($errNo, $errStr, $errFile, $errLine) {
            // 加入调试面板
            $messageHtml = "错误：{$errStr}(码号：{$errNo})<br>文件：{$errFile}(行号：{$errLine})<br>";
            /**
             * @var Debug $debug
             */
            $debug = self::make('debug');
            $debug->addDebug('Error', $messageHtml);

            self::writeLog($messageHtml);
        });
    }

    /**
     * 自定义异常
     * 自定义异常后，自带的异常还是会显示，除非设置display_errors为0
     */
    public static function processException()
    {
        set_exception_handler(function (\Exception $exception) {
            $errNo = $exception->getCode();
            $errStr = $exception->getMessage();
            $errFile = $exception->getFile();
            $errLine = $exception->getLine();

            // 加入调试面板
            $messageHtml = "异常：{$errStr}(码号：{$errNo})<br>文件：{$errFile}(行号：{$errLine})<br>";
            /**
             * @var Debug $debug
             */
            $debug = self::make('debug');
            $debug->addDebug('Exception', str_replace("\r\n", '<br>', $messageHtml));

            self::writeLog($messageHtml);
        });
    }

    private static function writeLog($messageHtml)
    {
        $isOpenLog = self::make('config')->get('main.is_open_log');
        if (!$isOpenLog) {
            return false;
        }

        $message = '';
        $message .= "\r\n" . str_replace('<br>', "\r\n", $messageHtml) . '时间：' . date('H:i:s') . "\r\n======";

        /**
         * @var Log $log
         */
        $log = self::make('log');

        if (IS_CLI) {
            return $log->write($message, Log::TYPE_CLIENT);
        } else {
            return $log->write($message);
        }
    }

    /**
     * 运行web
     */
    public static function runWeb()
    {
        self::commonPrepare();

        // 加载路由类

        /**
         * @var Route $route
         */
        $route = self::make('route');

        /**
         * @var Config $config
         */
        $config = self::make('config');

        $autoRoute = $config->get('main.auto_route');
        if ($autoRoute) {
            $route->autoRun();
        } else {
            $route->run(ROUTE_DIR . 'route.php');
        }
    }

    /**
     * 运行CLI
     */
    public static function runCommand($argv = [])
    {
        self::commonPrepare();

        $name = $argv[1];
        $params = array_slice($argv, 2);

        $file = APP_DIR . 'commands\config.php';
        new Command($file, $name, $params);
    }

    /**
     * 运行前的共同准备
     */
    private static function commonPrepare()
    {
        // 自动加载类
        self::autoload();

        // 自定义错误和异常
        self::processError();
        self::processException();
    }

    public function __destruct()
    {
        /**
         * @var Debug $debug
         */
        $debug = self::make('debug');
        /**
         * @var Config $config
         */
        $config = self::make('config');

        /**
         * @var Response $response
         */
        $response = self::make('response');

        if ($config->get('main.debug') && !IS_CLI && $response->getContentType() == 'html') {
            // 加入调试面板
            $SQLs = PDO::getSQL();
            if (!empty($SQLs)) {
                $str = '';
                foreach ($SQLs as $k => $sql) {
                    $k += 1;
                    $str .= "<b style='color:#ff8e27'>{$k}：</b>" . $sql[0] . '<i style="color:grey;display:inline-block;margin-left:50">' . $sql[2] . '</i><br>';
                }
                $debug->addDebug('SQL', $str);
            }

            // 输出调试面板
            $debug->showDebug();
        }
    }
}