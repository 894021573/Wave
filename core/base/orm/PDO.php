<?php
/**
 *
 * @author: 洪涛
 * @date: 2018/05/24
 */

namespace core\base\orm;

class PDO
{
    private static $_PDOs = [];
    private $_PDOStatement;

    private static $_instances;
    private static $_connectionConfig;
    private static $_connectionName;
    private static $_slaveIndex = 0;

    private $_isForceMaster = false;
    private $_currentConfig;
    private static $_SQLs = [];

    private $_isLog = true;
    private $_isIgnoreError = false;

    public function setIsIgnoreError($isIgnoreError)
    {
        $this->_isIgnoreError = $isIgnoreError;
    }

    public function setIsForceMaster($isForceMaster)
    {
        $this->_isForceMaster = $isForceMaster;
    }

    /**
     * @return PDO
     */
    public static function getInstance($config,$connectionName)
    {
        if (!isset(self::$_instances[$connectionName]) || !self::$_instances[$connectionName] instanceof self) {
            self::$_connectionConfig = $config;
            self::$_connectionName = $connectionName;
            self::$_instances[$connectionName] = new self();
        }

        self::$_slaveIndex++;

        return self::$_instances[$connectionName];
    }

    public function rowCount($prepareSQL, $bindParams = [])
    {
        $this->execute($prepareSQL, $bindParams, false);

        if ($this->_PDOStatement instanceof \PDOStatement) {
            return $this->_PDOStatement->rowCount();
        } else {
            throw new \Exception('不是合法的PDOStatement实例');
        }
    }

    public function query($prepareSQL, array $bindParams = [])
    {
        $this->execute($prepareSQL, $bindParams, true);
        if ($this->_PDOStatement instanceof \PDOStatement) {
            return $this->_PDOStatement->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            throw new \Exception('不是合法的PDOStatement实例');
        }
    }

    /**
     * 执行SQL
     */
    private function execute($prepareSQL, $bindParams, $isSlave)
    {
        if ($this->_isForceMaster) {
            $isSlave = false;
        }

        $this->_PDOStatement = $this->makePDO(self::$_connectionConfig, $isSlave)->prepare($prepareSQL);

        foreach ($bindParams as $placeHolder => $value) {
            switch (gettype($value)) {
                case 'integer':
                    $dataType = \PDO::PARAM_INT;
                    break;
                case 'double':
                    $dataType = \PDO::PARAM_INT;
                    break;
                case 'string':
                    $dataType = \PDO::PARAM_STR;
                    break;
                case 'null':
                    $dataType = \PDO::PARAM_NULL;
                    break;
                case 'boolean':
                    $dataType = \PDO::PARAM_BOOL;
                    break;
                default:
                    $dataType = \PDO::PARAM_STR;
            }

            // bindParam第二个参数是引用参数，所以不能用$value
            $this->_PDOStatement->bindParam($placeHolder, $bindParams[$placeHolder], $dataType);
        }

        $this->_PDOStatement->execute();

        // 记录SQL
        if ($this->_isLog) {
            $this->addSQL($prepareSQL, $bindParams, $this->_currentConfig);
        }

        // 报错
        if ($this->_isIgnoreError == false && $this->_PDOStatement->errorCode() != 00000) {
            $errorInfo = $this->_PDOStatement->errorInfo();
            $errorInfo = end($errorInfo);
            throw new \Exception($errorInfo);
        }
    }

    public function beginTransaction()
    {
        $pdo = $this->makePDO(self::$_connectionConfig, false);
        $pdo->beginTransaction();
    }

    public function commit()
    {
        $pdo = $this->makePDO(self::$_connectionConfig, false);
        $pdo->commit();
    }

    public function rollBack()
    {
        $pdo = $this->makePDO(self::$_connectionConfig, false);
        $pdo->rollBack();
    }

    /**
     * @param $config
     * @return \PDO
     */
    private function makePDO($connectionConfig, $isSlave = true)
    {
        $this->_isLog = $connectionConfig['is_log'];
        $this->_currentConfig = !$isSlave ? $connectionConfig['master'] : $connectionConfig['slave'][self::$_slaveIndex - 1];

        if (self::$_slaveIndex >= count($connectionConfig['slave'])) {
            self::$_slaveIndex = 0;
        }

        $config = $this->_currentConfig;
        $key = !$isSlave ? 'master' : 'slave_' . self::$_slaveIndex;

        if (!isset(self::$_PDOs[$key]) || !self::$_PDOs[$key] instanceof \PDO) {
            $dsn = "{$config['driver']}:dbname={$config['database_name']};host={$config['host']}";
            $user = $config['user'];
            $password = $config['password'];

            try {
                self::$_PDOs[$key] = new \PDO($dsn, $user, $password);
            } catch (\PDOException $e) {
                echo 'Connection failed';exit();
            }
        }

        return self::$_PDOs[$key];
    }

    private function addSQL($SQL, $bindParams, $databaseInfo = [])
    {
        $search = [];
        $replace = [];

        foreach ($bindParams as $k => $v) {
            $search[] = $k;
            $replace[] = is_string($v) ? "'{$v}'" : $v;
        }

        $newSQL = self::strReplaceLimit($search, $replace, $SQL, 1);

        if (empty($databaseInfo)) {
            self::$_SQLs[] = [$newSQL, [$SQL, $bindParams]];
        } else {
            $connectionName = self::$_connectionName;
            $info = "[{$connectionName}][{$databaseInfo['name']}][{$databaseInfo['driver']}:dbname={$databaseInfo['database_name']};host={$databaseInfo['host']};user={$databaseInfo['user']};password={$databaseInfo['password']}]";

            self::$_SQLs[] = [$newSQL, [$SQL, $bindParams], $info];
        }
    }

    /**
     * 依次替换
     *
     * @param $search
     * @param $replace
     * @param $subject
     * @param int $limit
     * @return mixed
     */
    private static function strReplaceLimit($search, $replace, $subject, $limit = -1)
    {
        if (is_array($search)) {
            foreach ($search as $k => $v) {
                $search[$k] = '`' . preg_quote($search[$k], '`') . '`';
            }
        } else {
            $search = '`' . preg_quote($search, '`') . '`';
        }
        return preg_replace($search, $replace, $subject, $limit);
    }

    public static function getSQL()
    {
        return self::$_SQLs;
    }
}