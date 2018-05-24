<?php
/**
 *
 * @author: 洪涛
 * @date: 2018/05/24
 */

namespace core\base\orm;

require 'SQLBuilder.php';
require 'PDO.php';

class Model
{
    protected $_tableName;
    protected $_primaryKey = 'id'; // 主键
    protected $_alias;

    protected $_config;
    private $_connectionName = 'default'; // 数据库连接名称

    private static $_PDO;
    private static $_SQLBuilder;

    private $_attributes = [];

    public function __construct()
    {
        //$this->_config = require_once 'config.php'; // 数据库配置 于子类实现之
        self::$_SQLBuilder = SQLBuilder::getInstance();
    }
    
    private function getConnectionConfig()
    {
        return $this->_config[$this->_connectionName];
    }

    // 切换数据库
    public function setDatabase($database)
    {
        $this->_config[$this->_connectionName]['master']['database_name'] = $database;
        if (!empty($this->_config[$this->_connectionName]['slave'])) {
            foreach ($this->_config[$this->_connectionName]['slave'] as $k => $v) {
                $this->_config[$this->_connectionName]['slave'][$k]['database_name'] = $database;
            }
        }
    }

    // 切换数据库连接
    public function setConnectionName($connectionName)
    {
        $this->_connectionName = $connectionName;
    }

    // 是否忽略错误
    public function setIsIgnoreError($isIgnoreError)
    {
        $this->makePDO()->setIsIgnoreError($isIgnoreError);
    }

    // 强制查主库
    public function setIsForceMaster($isForceMaster)
    {
        $this->makePDO()->setIsForceMaster($isForceMaster);
    }

    public function setAlias($alias)
    {
        $this->_alias = $alias;
        return $this;
    }

    public function insert(array $data)
    {
        list($SQL, $bindParams) = self::$_SQLBuilder->insert($this->_tableName, $data);
        $rowCount = $this->run($SQL, $bindParams, 'rowCount');
        if($rowCount > 0)
        {
            $this->setAttribute($data);
        }

        return $rowCount;
    }

    public function batchInsert(array $data, array $fields = [])
    {
        list($SQL, $bindParams) = self::$_SQLBuilder->batchInsert($this->_tableName, $data, $fields);

        return $this->run($SQL, $bindParams, 'rowCount');
    }

    public function delete()
    {
        list($SQL, $bindParams) = self::$_SQLBuilder->delete($this->_tableName);

        return $this->run($SQL, $bindParams, 'rowCount');
    }

    public function deleteByPk($id)
    {
        list($SQL, $bindParams) = self::$_SQLBuilder->where([$this->_primaryKey => $id])->delete($this->_tableName);

        return $this->run($SQL, $bindParams, 'rowCount');
    }

    public function update(array $data)
    {
        list($SQL, $bindParams) = self::$_SQLBuilder->update($this->_tableName, $data);
        $rowCount = $this->run($SQL, $bindParams, 'rowCount');
        if($rowCount > 0)
        {
            $this->setAttribute($data);
        }

        return $rowCount;
    }

    public function updateByPk($id, array $data)
    {
        list($SQL, $bindParams) = self::$_SQLBuilder->where([$this->_primaryKey => $id])->update($this->_tableName, $data);
        $rowCount = $this->run($SQL, $bindParams, 'rowCount');
        if($rowCount > 0)
        {
            $this->setAttribute($data);
        }

        return $rowCount;
    }

    public function one()
    {
        list($SQL, $bindParams) = self::$_SQLBuilder->limit([1])->select($this->_tableName, $this->_alias);
        $result = $this->run($SQL, $bindParams);
        $result = !empty($result) ? current($result) : [];

        $this->_attributes = $result;

        return $result;
    }

    public function getByPk($id)
    {
        list($SQL, $bindParams) = self::$_SQLBuilder->where([$this->_primaryKey => $id])->limit([1])->select($this->_tableName, $this->_alias);
        $result = $this->run($SQL, $bindParams);
        $result = !empty($result) ? current($result) : [];

        $this->_attributes = $result;

        return $result;
    }

    public function all()
    {
        list($SQL, $bindParams) = self::$_SQLBuilder->select($this->_tableName, $this->_alias);
        $result = $this->run($SQL, $bindParams);

        return $result;
    }

    public function field($fields)
    {
        self::$_SQLBuilder->field($fields);
        return $this;
    }

    public function where(array $condition, $connector = '', $isFilter = false)
    {
        self::$_SQLBuilder->where($condition, $connector, $isFilter);
        return $this;
    }

    public function andWhere(array $condition)
    {
        self::$_SQLBuilder->andWhere($condition);
        return $this;
    }

    public function orWhere(array $condition)
    {
        self::$_SQLBuilder->orWhere($condition);
        return $this;
    }

    public function filterWhere(array $condition)
    {
        self::$_SQLBuilder->filterWhere($condition);
        return $this;
    }

    public function andFilterWhere(array $condition)
    {
        self::$_SQLBuilder->andFilterWhere($condition);
        return $this;
    }

    public function orFilterWhere(array $condition)
    {
        self::$_SQLBuilder->orFilterWhere($condition);
        return $this;
    }

    public function limit(array $limit)
    {
        self::$_SQLBuilder->limit($limit);
        return $this;
    }

    public function order(array $order)
    {
        self::$_SQLBuilder->order($order);
        return $this;
    }

    public function join($table, $on, $type = 'INNER')
    {
        self::$_SQLBuilder->join($table, $on, $type);
        return $this;
    }

    public function innerJoin($table, $on)
    {
        $this->join($table, $on);
        return $this;
    }

    public function leftJoin($table, $on)
    {
        $this->join($table, $on, 'LEFT');
        return $this;
    }

    public function rightJoin($table, $on)
    {
        $this->join($table, $on, 'LEFT');
        return $this;
    }

    /**
     * 事务操作
     *
     * @param callable $handler
     */
    public function transaction(callable $handler)
    {
        self::$_PDO = $this->makePDO();
        self::$_PDO->beginTransaction();

        if ($handler()) {
            self::$_PDO->commit();
        } else {
            self::$_PDO->rollBack();
        }
    }

    //=== 原生SQL ===//
    public function query($SQL)
    {
        return $this->run($SQL);
    }

    public function rowCount($SQL)
    {
        return $this->run($SQL, [], 'rowCount');
    }

    //=== 原生SQL ===//

    private function run($SQL, array $bindParams = [], $type = 'query')
    {
        self::$_SQLBuilder->clear();
        self::$_PDO = $this->makePDO();

        if ($type == 'query') {
            return self::$_PDO->query($SQL, $bindParams);
        } else {
            return self::$_PDO->rowCount($SQL, $bindParams);
        }
    }

    public function getSQL()
    {
        $sql = PDO::getSQL();
        return $sql;
    }

    public function __get($name)
    {
        if (isset($this->_attributes[$name])) {
            if (method_exists($this, 'get' . ucfirst($name))) {
                return call_user_func([$this, 'get' . ucfirst($name)], $this->_attributes[$name]);
            }

            return $this->_attributes[$name];
        } else {
            return null;
        }
    }

    public function setAttribute($result)
    {
        $this->_attributes = $result;
    }

    /**
     * 返回模型数组
     *
     * @param $result
     * @param Model $obj
     * @return array
     */
    public static function returnObjects($result, Model $obj)
    {
        $objects = [];
        foreach ($result as $k => $item) {
            $cloneObject = clone $obj;
            $cloneObject->setAttribute($item);
            $objects[] = $cloneObject;
        }
        return $objects;
    }

    private function makePDO()
    {
        return PDO::getInstance($this->getConnectionConfig(),$this->_connectionName);
    }
}