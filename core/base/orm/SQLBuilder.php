<?php
/**
 * Created by PhpStorm.
 * User: ht
 * @date: 2018/05/24
 */

namespace core\base\orm;

/**
 * SQL语句构造器
 *
 * Class SQLBuilder
 */
class SQLBuilder
{
    private static $_instance;

    private $_SQL;
    private $_bindParams = [];

    private $_fields;
    private $_where;
    private $_order;
    private $_limit;

    private $_join;

    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function insert($tableName, array $data)
    {
        $fields = array_keys($data);
        $fieldString = '';
        $valueString = '';
        foreach ($fields as $v) {
            $fieldString .= $v . ',';
            $valueString .= ':' . $v . ',';
        }

        $fieldString = '(' . trim($fieldString, ',') . ')';
        $valueString = '(' . trim($valueString, ',') . ')';

        $this->_SQL = "INSERT INTO {$tableName} {$fieldString} VALUES {$valueString}";

        $this->_bindParams = [];
        foreach ($data as $k => $v) {
            $this->_bindParams[":{$k}"] = $v;
        }

        return [$this->_SQL, $this->_bindParams];
    }

    public function batchInsert($tableName, array $data, array $fields = [])
    {
        if (!empty($fields)) {
            $fieldString = '(' . implode(',', $fields) . ')';
        } else {
            $fieldString = '(' . implode(',', array_keys(current($data))) . ')';
        }

        $valueString = '';
        $this->_bindParams = [];
        foreach ($data as $k => $v) {
            $singleValueString = '';
            foreach ($v as $fieldName => $fieldValue) {
                $singleValueString .= ':' . $fieldName . $k . ',';
                $this->_bindParams[":{$fieldName}{$k}"] = $fieldValue;
            }

            $valueString .= '(' . trim($singleValueString, ',') . '),';
        }

        $valueString = trim($valueString, ',');

        $this->_SQL = "INSERT INTO {$tableName} {$fieldString} VALUES {$valueString}";

        return [$this->_SQL, $this->_bindParams];
    }

    public function delete($tableName)
    {
        if (empty($this->_where)) {
            throw new \Exception('Delete condition cannot be empty');
        }

        $this->_where = "WHERE {$this->_where}";

        $this->_SQL = "DELETE FROM {$tableName} {$this->_where}";

        return [$this->_SQL, $this->_bindParams];
    }

    public function update($tableName, array $data)
    {
        if (empty($this->_where)) {
            throw new \Exception('Update condition cannot be empty');
        }

        if (empty($data)) {
            throw new \Exception('Delete data cannot be empty');
        }

        $this->_where = "WHERE {$this->_where}";

        $str = '';
        $bindParams = [];
        foreach ($data as $k => $v) {
            $str .= "{$k} = :{$k} ,";
            $bindParams[":{$k}"] = $v;
        }

        $str = trim($str, ',');

        $this->_SQL = "UPDATE {$tableName} SET {$str} {$this->_where}";
        $this->_bindParams = array_merge($bindParams, $this->_bindParams);

        return [$this->_SQL, $this->_bindParams];
    }

    public function select($tableName, $alias)
    {
        if (!empty($this->_where)) {
            $this->_where = "WHERE {$this->_where}";
        }

        if (!empty($this->_order)) {
            $this->_order = "ORDER BY {$this->_order}";
        }

        if (empty($this->_select)) {
            $this->_select = 'SELECT *';
        }

        if (!empty($alias)) {
            $alias = "AS {$alias}";
        }

        $this->_SQL = "{$this->_select} FROM {$tableName} {$alias} {$this->_join} {$this->_where} {$this->_order} {$this->_limit}";

        return [$this->_SQL, $this->_bindParams];
    }

    /**
     * @param array|string $fields
     * @return $this
     */
    public function field($fields)
    {
        if (is_array($fields)) {
            $newFields = implode(',', $fields);
        } else {
            $newFields = $fields;
        }

        $this->_fields = "SELECT {$newFields}";
        return $this;
    }

    public function where(array $condition, $connector = '', $isFilter = false)
    {
        $where = '';
        foreach ($condition as $field => $item) {
            if (!is_array($item)) {
                $item = (array)$item;
            }

            $operate = '=';
            $value = '';

            $count = count($item);
            if ($count == 0) {
                unset($condition[$field]);
                continue;
            } elseif ($count == 1) {
                $value = current($item);
            } elseif ($count >= 2) {
                list($operate, $value) = $item;
                $operate = strtoupper($operate);

                if (is_null($value) && $isFilter) {
                    unset($condition[$field]);
                    continue;
                }
            }

            // 指定表名的字段，生成对应的占位符，把点号转成下划线。比如user.id  对应的占位符是 :user_id
            $tempField = str_replace('.', '_', $field);
            $fieldWithColon = ":{$tempField}";

            // 处理in操作符
            if ($operate == 'IN') {
                $fieldWithColon = $this->processIn($value, $fieldWithColon);
            } elseif ($operate == 'BETWEEN') {
                $fieldWithColon = $this->processBetween($value, $fieldWithColon);
            } elseif ($operate == 'LIKE') {
                $fieldWithColon = $this->processLike($value, $fieldWithColon);
            } else {
                // 防止重复
                if (isset($this->_bindParams["{$fieldWithColon}"])) {
                    $fieldWithColon = $fieldWithColon . count($this->_bindParams);
                }
                $this->_bindParams["{$fieldWithColon}"] = $value;
            }

            $where .= "{$field} {$operate} {$fieldWithColon} AND ";
        }

        if (!empty($where)) {
            // 过滤
            $where = trim(trim($where), 'AND');

            // 是否加括号
            if (count($condition) >= 2) {
                $where = "({$where})";
            }

            // 连接符
            if (!empty($connector)) {
                $where = " {$connector} {$where}";
            }
        }

        $this->_where .= $where;
        return $this;
    }

    /**
     * in:['id' => ['in', [522, 523]]]
     * like:['name' => ['like', '%ht1']]
     * between:['id' => ['between', [523,525]]]
     * 大于:['id' => ['>', '5']]
     *
     * @param $value
     * @param $fieldWithColon
     * @return string
     */
    private function processIn($value, $fieldWithColon)
    {
        if (!is_array($value)) {
            $value = (array)$value;
        }

        $temp = '';
        $tempCount = count($value);
        for ($i = 0; $i < $tempCount; $i++) {
            $key = $fieldWithColon . ($i + 1);
            $temp .= $key . ',';
            $this->_bindParams[$key] = $value[$i];
        }
        $temp = '(' . trim($temp, ',') . ')';

        return $temp;
    }

    private function processBetween($value, $fieldWithColon)
    {
        if (!is_array($value) || count($value) != 2) {
            throw new \Exception('BETWEEN   后面的值必须是包含2个元素的数组');
        }

        list($start, $end) = $value;
        $startWithColon = $fieldWithColon . 0;
        $endWithColon = $fieldWithColon . 1;

        $this->_bindParams[$startWithColon] = $start;
        $this->_bindParams[$endWithColon] = $end;

        $temp = "{$startWithColon} AND {$endWithColon}";
        return $temp;
    }

    private function processLike($value, $fieldWithColon)
    {
        $this->_bindParams[$fieldWithColon] = $value;
        $temp = "{$fieldWithColon}";
        return $temp;
    }

    /**
     * @param $condition
     */
    public function andWhere($condition)
    {
        $this->where($condition, 'AND');
    }

    /**
     * @param $condition
     */
    public function orWhere($condition)
    {
        $this->where($condition, 'OR');
    }

    /**
     * @param $condition
     */
    public function filterWhere($condition)
    {
        $this->where($condition, '', true);
    }

    /**
     * @param $condition
     */
    public function andFilterWhere($condition)
    {
        $this->where($condition, 'AND', true);
    }

    /**
     * @param $condition
     */
    public function orFilterWhere($condition)
    {
        $this->where($condition, 'OR', true);
    }

    /**
     * @param array $limit
     */
    public function limit(array $limit)
    {
        $count = count($limit);
        if ($count == 0) {
            $this->_limit = '';
        } elseif ($count == 1) {
            $this->_limit = 'LIMIT ' . current($limit);
        } else {
            list($offset, $num) = $limit;
            $this->_limit = "LIMIT {$num} OFFSET {$offset}";
        }

        return $this;
    }

    /**
     * @param array $order ['id' => 'DESC','age' => 'ASC']
     */
    public function order(array $orders)
    {
        foreach ($orders as $field => $value) {
            $value = strtoupper($value);
            $this->_order .= "{$field} {$value},";
        }

        $this->_order = trim($this->_order, ',');
    }

    public function join($table, $on, $type = 'INNER')
    {
        $this->_join = "{$type} JOIN {$table} ON {$on}";
    }

    public function innerJoin($table, $on)
    {
        $this->join($table, $on);
    }

    public function leftJoin($table, $on)
    {
        $this->join($table, $on, 'LEFT');
    }

    public function rightJoin($table, $on)
    {
        $this->join($table, $on, 'LEFT');
    }

    /**
     * 重置属性
     */
    public function clear()
    {
        $this->_where = $this->_order = $this->_limit = $this->_join = '';
        $this->_bindParams = [];//$this->_isAsArray = false;
        ;
    }
}