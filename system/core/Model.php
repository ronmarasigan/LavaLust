<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 * 
 * Copyright (c) 2020 Ronald M. Marasigan
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @copyright Copyright 2020 (https://techron.info)
 * @version Version 1.2
 * @link https://lavalust.com
 * @license https://opensource.org/licenses/MIT MIT License
 */

class Model extends PDO
{
    private $charset, $dbhost, $dbname, $dbuser, $dbpass, $dsn;
    protected $_fetchMode = PDO::FETCH_ASSOC;
    protected $_transactionCount = 0;

    /**
     * Class constructor
     *
     * @param  string  $dsn     Connection DSN
     * @param  string  $user    Connection user name
     * @param  string  $passwd  Connection password
     * @param  string  $options PDO driver options
     * @return PDO
     */
    public function  __construct()
    {
        $database_config =& database_config();
        $this->charset = $database_config['charset'];
		$this->dbhost = $database_config['hostname'];
		$this->dbname = $database_config['database'];
		$this->dbuser = $database_config['username'];
		$this->dbpass = $database_config['password'];
		$this->dsn = 'mysql:host=' . $this->dbhost . ';dbname=' . $this->dbname . ';charset=' . $this->charset;

        $driver_options = array(
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $this->charset
        );
        if(!empty($options)) {
            $driver_options = array_merge($driver_options, $options);
        }

        parent::__construct($this->dsn, $this->dbuser, $this->dbpass, $driver_options);
    }

    /**
     * Prepare and returns a PDOStatement
     *
     * @param  string  $sql  SQL statement
     * @param  array   $bind parameters. A single value or an array of values
     * @return PDOStatement
     */
    private function _prepare($sql, $bind = array())
    {
        $stmt = $this->prepare($sql);

        if (!$stmt) {
            $errorInfo = $this->errorInfo();
            throw new PDOException("Database error [{$errorInfo[0]}]: {$errorInfo[2]}, driver error code is $errorInfo[1]");
        }
        if(!is_array($bind)) {
            $bind = empty($bind) ? array() : array($bind);
        }
        if (!$stmt->execute($bind) || $stmt->errorCode() != '00000') {
            $errorInfo = $stmt->errorInfo();
            throw new PDOException("Database error [{$errorInfo[0]}]: {$errorInfo[2]}, driver error code is $errorInfo[1]");
        }

        return $stmt;
    }

    /**
     * Execute sql and returns number of effected rows
     *
     * Should be used for query which doesn't return resultset
     *
     * @param  string  $sql   SQL statement
     * @param  array   $bind  parameters. A single value or an array of values
     * @return integer Number of effected rows
     */
    public function run($sql, $bind = array())
    {
        $stmt = $this->_prepare($sql, $bind);
        return $stmt->rowCount();
    }

    /**
     * set fetch mode for PDO
     *
     * @param  string  $fetchMode    PDO fetch mode
     * @return PDO
     */
    public function setFetchMode($fetchMode)
    {
        $this->_fetchMode = $fetchMode;
        return $this;
    }

    /**
     * get where expression (if array, convert to sting)
     *
     * @param  string  $where  where string or array
     * @param  array   $andOr  AND or OR
     * @return string  where string
     */
    public function where($where, $andOr = 'AND')
    {
        if(is_array($where)) {
            $tmp = array();
            foreach($where as $k => $v) {
                $tmp[] = $k . '=' . $this->quote($v);
            }
            return '(' . implode(" $andOr ", $tmp) . ')';
        }
        return $where;
    }

    /**
     * select records from a table
     *
     * @param  string $table  table name
     * @param  string $fields  fields list
     * @param  string $where  where string
     * @param  array  $bind  parameters. A single value or an array of values
     * @param  string $order  order string
     * @param  string $limit  limit string (MySQL is "[offset,] row_count")
     * @return array
     */
    public function select($table, $fields = "*", $where = "", $bind = array(), $order = NULL, $limit = NULL)
    {
        $sql = "SELECT " . $fields . " FROM " . $table;
        if(!empty($where)) {
            $where = $this->where($where);
            $sql .= " WHERE " . $where;
        }
        if(!empty($order)) {
            $sql .= " ORDER BY " . $order;
        }
        if(!empty($limit)) {
            $sql .= " LIMIT " . $limit;
        }
        $stmt = $this->_prepare($sql, $bind);
        return $stmt->fetchAll($this->_fetchMode);
    }

    /**
     * insert a record to a table
     *
     * @param  string $table  table name
     * @param  array  $data  data array
     * @return integer Number of effected rows
     */
    public function insert($table, $data)
    {
        $fieldNames = array_keys($data);
        $sql = "INSERT INTO `$table` (" . implode($fieldNames, ", ") . ") VALUES (:" . implode($fieldNames, ", :") . ");";
        $bind = array();
        foreach($fieldNames as $field) {
            $bind[":$field"] = $data[$field];
        }
        return $this->run($sql, $bind);
    }

    /**
     * insert multiple records to a table
     *
     * @param  string $table  table name
     * @param  array  $fieldNames  fields array
     * @param  array  $data  data array
     * @param  bool  $replace  replace flag
     * @return integer Number of effected rows
     */
    public function bulkInsert($table, $fieldNames, $data, $replace = false)
    {
        if(empty($table) || empty($fieldNames) || empty($data)) {
            return 0;
        }
        $fieldCount = count($fieldNames);
        $valueList = '';
        foreach ($data as $values) {
            $dataCount = count($values);
            if($dataCount != $fieldCount) {
                if($dataCount > $fieldCount) {
                    $values = array_slice($values, 0, $fieldCount);
                } else {
                    throw new PDOException("Number of columns and values not match!");
                }
            }
            foreach ($values as &$val) {
                if (is_null($val)) {
                    $val = 'NULL';
                } elseif (is_string($val)) {
                    $val = $this->quote($val);
                } elseif (is_object($val) || is_array($val)) {
                    $val = $this->quote(json_encode($val));
                }
            }
            $valueList .= '(' . implode(',', $values) . '),';
        }
        $valueList = rtrim($valueList, ',');

        $insert = $replace ? 'REPLACE' : 'INSERT';
        $sql = "$insert INTO `$table` (" . implode(', ', $fieldNames) . ") VALUES " . $valueList . ";";
        return $this->run($sql);
    }

    /**
     * update records for one table
     *
     * @param  string $table  table name
     * @param  array  $data  data array
     * @param  string $where  where string
     * @param  array  $bind  parameters. A single value or an array of values
     * @return integer Number of effected rows
     */
    public function update($table, $data, $where="", $bind=array())
    {
        $sql = "UPDATE `$table` SET ";
        $comma = '';
        if(!is_array($bind)) {
            $bind = empty($bind) ? array() : array($bind);
        }
        foreach($data as $k => $v) {
            $sql .= $comma . $k . " = :upd_" . $k;
            $comma = ', ';
            $bind[":upd_" . $k] = $v;
        }
        if(!empty($where)) {
            $where = $this->where($where);
            $sql .= " WHERE " . $where;
        }
        return $this->run($sql, $bind);
    }

    /**
     * delete records from table
     *
     * @param  string $table  table name
     * @param  string $where  where string
     * @param  array  $bind  parameters. A single value or an array of values
     * @return integer Number of effected rows
     */
    public function delete($table, $where, $bind = array())
    {
        $sql = "DELETE FROM `$table`";
        if(!empty($where)) {
            $where = $this->where($where);
            $sql .= " WHERE " . $where;
        }
        return $this->run($sql, $bind);
    }

    /**
     * truncate table
     *
     * @param  string $table  table name
     * @return integer Number of effected rows
     */
    public function truncate($table)
    {
        $sql = "TRUNCATE TABLE `$table`";
        return $this->run($sql);
    }

    /**
     * save data to table (update is exists, else insert)
     *
     * @param  string $table  table name
     * @param  array $data  data array
     * @param  mixed $where  SQL WHERE string or key/value array
     * @param  array  $bind  parameters. A single value or an array of values
     * @return integer Number of effected rows
     */
    public function save($table, $data, $where = "", $bind = array())
    {
        $count = 0;
        if(!empty($where)) {
            $where = $this->where($where);
            $count = $this->fetchOne("SELECT COUNT(1) FROM $table WHERE $where", $bind);
        }
        if($count == 0) {
            return $this->insert($table, $data);
        } else {
            return $this->update($table, $data, $where, $bind);
        }
    }

    /**
     * Execute sql and returns a single value
     *
     * @param  string  $sql   SQL statement
     * @param  array   $bind  A single value or an array of values
     * @return mixed  Result value
     */
    public function fetchOne($sql, $bind = array())
    {
        $stmt = $this->_prepare($sql, $bind);
        return $stmt->fetchColumn(0);
    }

    /**
     * Execute sql and returns the first row
     *
     * @param  string  $sql    SQL statement
     * @param  array   $bind A single value or an array of values
     * @return array   A result row
     */
    public function fetchRow($sql, $bind = array())
    {
        $stmt = $this->_prepare($sql, $bind);
        return $stmt->fetch($this->_fetchMode);
    }

    /**
     * Execute sql and returns row(s) as 2D array
     *
     * @param  string  $sql    SQL statement
     * @param  array   $bind A single value or an array of values
     * @return array   Result rows
     */
    public function fetchAll($sql, $bind = array())
    {
        $stmt = $this->_prepare($sql, $bind);
        return $stmt->fetchAll($this->_fetchMode);
    }

    /**
     * Execute sql and returns row(s) as 2D array, array key is first column's values
     *
     * @param  string  $sql    SQL statement
     * @param  array   $bind A single value or an array of values
     * @return array   Result rows
     */
    public function fetchAssoc($sql, $bind = array())
    {
        $stmt = $this->_prepare($sql, $bind);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = array();
        if(!empty($records)) {
            $k0 = key($records[0]);
            foreach($records as $rec) {
                $result[$rec[$k0]] = $rec;
            }
        }
        return $result;
    }

    /**
     * Execute sql and returns row(s) as 3D array, array key is first column's values
     *
     * @param  string  $sql    SQL statement
     * @param  array   $bind A single value or an array of values
     * @return array   Result rows
     */
    public function fetchAssocArr($sql, $bind = array())
    {
        $stmt = $this->_prepare($sql, $bind);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = array();
        if(!empty($records)) {
            $k0 = key($records[0]);
            foreach($records as $rec) {
                $result[$rec[$k0]][] = $rec;
            }
        }
        return $result;
    }

    /**
     * Execute sql and returns a key/value pairs array
     *
     * @param  string  $sql    SQL statement
     * @param  array   $bind A single value or an array of values
     * @return array   Result rows
     */
    public function fetchPairs($sql, $bind = array())
    {
        $stmt = $this->_prepare($sql, $bind);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Execute sql and returns an values array of first column
     *
     * @param  string  $sql    SQL statement
     * @param  array   $bind A single value or an array of values
     * @return array   Result rows
     */
    public function fetchCol($sql, $bind = array())
    {
        $stmt = $this->_prepare($sql, $bind);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = array();
        if(!empty($records)) {
            $k0 = key($records[0]);
            foreach($records as $rec) {
                $result[] = $rec[$k0];
            }
        }
        return $result;
    }

    /**
     * create table
     *
     * @param  string $table  table name
     * @param  array  $fieldNames  field name array
     * @param  array  $fieldTypes  field type array
     * @param  array  $defaultValues  field default value array
     * @param  array  $fieldComments  field comment array
     * @param  string $primaryKey  primary key
     * @param  array  $indexes  index array
     * @param  string $engine  storage engine
     * @param  string $charset  default charset
     * @return integer Number of effected rows
     */
    public function createTable($table, $fieldNames, $fieldTypes, $defaultValues, $fieldComments, $primaryKey = '', $indexes = array(), $dbEngine = 'InnoDB', $charset='utf8')
    {
        $sql = "CREATE TABLE IF NOT EXISTS `$table` (";
        foreach($fieldNames as $i => $fieldName) {
            $sql .= "`$fieldName` " . $fieldTypes[$i];
            if(!empty($defaultValues[$i])) {
                $sql .= " DEFAULT " . $defaultValues[$i];
            }
            if(!empty($fieldComments[$i])) {
                $sql .= " COMMENT '" . $fieldComments[$i] . "'";
            }
            $sql .= ", ";
        }
        if(empty($primaryKey)) {
            $primaryKey = $fieldNames[0];
        }
        $sql .= " PRIMARY KEY $primaryKey";
        foreach($indexes as $i => $index) {
            $sql .= ",INDEX index_{$i} $index";
        }
        $sql .= ") ENGINE={$dbEngine} DEFAULT CHARSET={$charset};";
        return $this->run($sql);
    }

    /**
     * drop table
     *
     * @param  string $table  table name
     */
    public function dropTable($table)
    {
        $sql = "DROP TABLE IF EXISTS `$table`;";
        return $this->run($sql);
    }

    /**
     * begin transaction
     */
    public function beginTransaction()
    {
        if (!$this->_transactionCount++) {
            return parent::beginTransaction();
        }
        $this->exec('SAVEPOINT trans'.$this->_transactionCount);
        return $this->_transactionCount >= 0;
    }

    /**
     * commit transaction
     */
    public function commit()
    {
        if (!--$this->_transactionCount) {
            return parent::commit();
        }
        return $this->_transactionCount >= 0;
    }

    /**
     * rollback transaction
     */
    public function rollback()
    {
        if (--$this->_transactionCount) {
            $this->exec('ROLLBACK TO trans'.($this->_transactionCount + 1));
            return true;
        }
        return parent::rollback();
    }

    /**
     * has transaction ?
     */
    public function hasTransaction()
    {
        return $this->_transactionCount > 0;
    }

}