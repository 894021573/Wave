<?php
/**
 * 模型层父类
 *
 * @author: 洪涛
 * @date: 2018/05/24
 */

namespace core\framework;

use core\base\config\Config;
use core\Wave;

class Model extends \core\base\orm\Model
{
    protected $_tableName;

    public function __construct()
    {
        parent::__construct();

        /**
         * @var Config $config
         */
        $config = Wave::make('config');
        $this->_config = $config->get('database'); // 数据库配置
    }
}