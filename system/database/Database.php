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
 * @copyright Copyright 2020 (https://ronmarasigan.github.io)
 * @version Version 1.3.4
 * @link https://lavalust.pinoywap.org
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
* ------------------------------------------------------
*  Class Database / Model
* ------------------------------------------------------
*/
class Database {
    private static $instance = NULL;
    private $db = NULL;
    private $table;
    private $columns;
    private $sql;
    private $bindValues;
    private $getSQL;
    private $join = NULL;
    private $where;
    private $grouped = false;
    private $whereCount = 0;
    private $rowCount = 0;
    private $limit;
    private $orderBy;
    private $groupBy = NULL;
    private $having = NULL;
    private $lastIDInserted = 0;
    private $transactionCount = 0;
    private $operators = array('=', '!=', '<', '>', '<=', '>=', '<>');


    public function __construct()
    {
        $database_config = database_config();
        $this->charset = $database_config['charset'];
        $this->dbost = $database_config['hostname'];
        $this->dbname = $database_config['database'];
        $this->dbuser = $database_config['username'];
        $this->dbpass = $database_config['password'];
        $this->dsn = 'mysql:host=' . $this->dbost . ';dbname=' . $this->dbname . ';charset=' . $this->charset;

        $options = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        );

        try {
            $this->db = new PDO($this->dsn, $this->dbuser, $this->dbpass, $options);
            $database_config = NULL;
        } catch (Exception $e) {
            show_error('Database Error Occured', $e->getMessage(), 'error_db', 500);
        }
    }

    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function raw($query, $args = [])
    {
        $this->resetQuery();
        $query = trim($query);
        $this->getSQL = $query;
        $this->bindValues = $args;

        if (strpos( strtoupper($query), "SELECT" ) === 0 ) {
            $stmt = $this->db->prepare($query);
            $stmt->execute($this->bindValues);
            $this->rowCount = $stmt->rowCount();
            return $stmt->fetchAll();
        }else{
            $this->getSQL = $query;
            $stmt = $this->db->prepare($query);
            $stmt->execute($this->bindValues);
            return $stmt->rowCount();
        }
    }
    public function exec()
    {
            $this->sql .= $this->where;
            $this->getSQL = $this->sql;
            $stmt = $this->db->prepare($this->sql);
            $stmt->execute($this->bindValues);
            if (strpos( strtoupper($this->sql), "INSERT" ) === 0 ) {
                $this->lastIDInserted = $this->db->lastInsertId();
                return $this->lastIDInserted;
            }
            else
                return $stmt->rowCount();
    }

    private function resetQuery()
    {
        $this->table = NULL;
        $this->columns = NULL;
        $this->sql = NULL;
        $this->bindValues = NULL;
        $this->limit = NULL;
        $this->orderBy = NULL;
        $this->groupBy = NULL;
        $this->having = NULL;
        $this->getSQL = NULL;
        $this->where = NULL;
        $this->rowCount = 0;
        $this->lastIDInserted = 0;
    }

    public function delete()
    {
        $this->sql = "DELETE FROM {$this->table}";
        return $this;
    }

    public function update($fields = [])
    {
        $set = '';
        $values = [];

        foreach ($fields as $column => $field) {
            $values[] = $column . ' = ?';
            $this->bindValues[] = $field;
        }
        $set .= implode(', ', $values);

        $this->sql = "UPDATE {$this->table} SET {$set}";

        return $this;
    }

    public function insert($fields = [])
    {
        $keys = implode(', ', array_keys($fields));
        $values = '';
        $x = 1;
        foreach ($fields as $field => $value) {
            $values .='?';
            $this->bindValues[] =  $value;
            if ($x < count($fields)) {
                $values .=', ';
            }
            $x++;
        }
 
        $this->sql = "INSERT INTO {$this->table} ({$keys}) VALUES ({$values})";
        
        return $this;
    }

    public function lastId()
    {
        return $this->lastIDInserted;
    }

    public function table($table_name)
    {
        $this->resetQuery();
        $this->table = $table_name;
        return $this;
    }

    public function select($columns)
    {
        $columns = explode(',', $columns);
        foreach ($columns as $key => $column) {
            $columns[$key] = trim($column);
        }
        
        $columns = implode(', ', $columns);

        $this->columns = "{$columns}";
        return $this;
    }

    public function max($column, $name = null)
    {
        $func = 'MAX(' . $column . ')' . (! is_null($name) ? ' AS ' . $name : '');
        $this->columns = ($this->columns == NULL ? $func : $this->columns . ', ' . $func);

        return $this;
    }

    public function min($field, $name = null)
    {
        $func = 'MIN(' . $field . ')' . (! is_null($name) ? ' AS ' . $name : '');
        $this->columns = ($this->columns == NULL ? $func : $this->columns . ', ' . $func);

        return $this;
    }

    public function sum($field, $name = null)
    {
        $func = 'SUM(' . $field . ')' . (! is_null($name) ? ' AS ' . $name : '');
        $this->columns = ($this->columns == NULL ? $func : $this->columns . ', ' . $func);

        return $this;
    }

    public function count($field, $name = null)
    {
        $func = 'COUNT(' . $field . ')' . (! is_null($name) ? ' AS ' . $name : '');
        $this->columns = ($this->columns == NULL ? $func : $this->columns . ', ' . $func);

        return $this;
    }

    public function avg($field, $name = null)
    {
        $func = 'AVG(' . $field . ')' . (! is_null($name) ? ' AS ' . $name : '');
        $this->columns = ($this->columns == NULL ? $func : $this->columns . ', ' . $func);

        return $this;
    }

    public function join($table_name, $field1 = NULL, $op = NULL, $field2 = NULL, $type = '')
    {
        $on = $field1;

        if (! is_NULL($op)) {
            $on = (! in_array($op, $this->operators) ? $field1 . ' = ' . $op : $field1 . ' ' . $op . ' ' . $field2);
        }

        $this->join = (is_NULL($this->join))
            ? ' ' . $type . 'JOIN' . ' ' . $table_name . ' ON ' . $on
            : $this->join . ' ' . $type . 'JOIN' . ' ' . $table_name . ' ON ' . $on;

        return $this;
    }

    public function innerJoin($table_name, $field1, $op = '', $field2 = '')
    {
        $this->join($table_name, $field1, $op, $field2, 'INNER ');

        return $this;
    }

    public function leftJoin($table_name, $field1, $op = '', $field2 = '')
    {
        $this->join($table_name, $field1, $op, $field2, 'LEFT ');

        return $this;
    }

    public function rightJoin($table_name, $field1, $op = '', $field2 = '')
    {
        $this->join($table_name, $field1, $op, $field2, 'RIGHT ');

        return $this;
    }

    public function fullOuterJoin($table_name, $field1, $op = '', $field2 = '')
    {
        $this->join($table_name, $field1, $op, $field2, 'FULL OUTER ');

        return $this;
    }

    public function leftOuterJoin($table_name, $field1, $op = '', $field2 = '')
    {
        $this->join($table_name, $field1, $op, $field2, 'LEFT OUTER ');

        return $this;
    }

    public function rightOuterJoin($table_name, $field1, $op = '', $field2 = '')
    {
        $this->join($table_name, $field1, $op, $field2, 'RIGHT OUTER ');

        return $this;
    }

    public function grouped(Closure $obj)
    {
        $this->grouped = true;
        call_user_func_array($obj, [$this]);
        $this->where .= ')';

        return $this;
    }

    public function where($where, $op = null, $val = null, $type = '', $andOr = 'AND')
    {
        if (is_array($where) && ! empty($where)) {
            $_where = [];
            foreach ($where as $column => $data) {
                $_where[] = $type . $column . ' = ?';
                $this->bindValues[] = $data;
            }
            $where = implode(' ' . $andOr . ' ', $_where);
        } else {
            if (is_null($where) || empty($where)) {
                return $this;
            }

            if (is_array($op)) {
                $params = explode('?', $where);
                $_where = '';
                foreach ($params as $key => $value) {
                    if (! empty($value)) {
                        $_where .= $type . $value . (isset($op[$key]) ? ' ? ' : '');
                        $this->bindValues[] = $op[$key];
                    }
                }
                $where = $_where;
            } elseif (! in_array($op, $this->operators) || $op == false) {
                $where = $type . $where . ' = ?';
                $this->bindValues[] = $op;
            } else {
                $where = $type . $where . ' ' . $op . ' ?';
                $this->bindValues[] = $val;
            }
        }

        if ($this->grouped) {
            $where = '(' . $where;
            $this->grouped = false;
        }

        $this->where = (is_null($this->where))
            ? ' WHERE ' . $where
            : $this->where . ' ' . $andOr . ' ' . $where;

        return $this;
    }

    public function orWhere($where, $op = null, $val = null)
    {
        $this->where($where, $op, $val, '', 'OR');

        return $this;
    }

    public function notWhere($where, $op = null, $val = null)
    {
        $this->where($where, $op, $val, 'NOT ', 'AND');

        return $this;
    }

    public function orNotWhere($where, $op = null, $val = null)
    {
        $this->where($where, $op, $val, 'NOT ', 'OR');

        return $this;
    }

    public function whereNull($where)
    {
        $where = $where . ' IS NULL';
        $this->where = (is_null($this->where))
            ? ' WHERE ' . $where
            : $this->where . ' ' . 'AND ' . $where;

        return $this;
    }

    public function whereNotNull($where)
    {
        $where = $where . ' IS NOT NULL';
        $this->where = (is_null($this->where))
            ? ' WHERE ' . $where
            : $this->where . ' ' . 'AND ' . $where;

        return $this;
    }

    public function limit($limit, $offset=NULL)
    {
        if ($offset ==NULL ) {
            $this->limit = " LIMIT {$limit}";
        }else{
            $this->limit = " LIMIT {$limit} OFFSET {$offset}";
        }

        return $this;
    }

    public function orderBy($field_name, $order = 'ASC')
    {
        $field_name = trim($field_name);

        $order =  trim(strtoupper($order));

        if ($field_name !== NULL && ($order == 'ASC' || $order == 'DESC')) {
            if ($this->orderBy ==NULL ) {
                $this->orderBy = " ORDER BY {$field_name} {$order}";
            }else{
                $this->orderBy .= ", {$field_name} {$order}";
            }
            
        }

        return $this;
    }

     public function groupBy($groupBy)
    {
        $this->groupBy = ' GROUP BY ';
        $this->groupBy .= (is_array($groupBy))
            ? implode(', ', $groupBy)
            : $groupBy;

        return $this;
    }

    public function having($field, $op = null, $val = null)
    {
        $this->having = ' HAVING ';
        if (is_array($op)) {
            $fields = explode('?', $field);
            $where = '';
            foreach ($fields as $key => $value) {
                if (! empty($value)) {
                    $where .= $value . (isset($op[$key]) ? ' ? ' : '');
                    $this->bindValues[] = $op[$key];
                }
            }
            $this->having .= $where;
        } elseif (! in_array($op, $this->operators)) {
            $this->having .= $field . ' > ' . ' ? ';
            $this->bindValues[] = $op;
        } else {
            $this->having .= $field . ' ' . $op . ' ' . ' ? ';
            $this->bindValues[] = $val;
        }

        return $this;
    }

    public function in($field, array $keys, $type = '', $andOr = 'AND')
    {
        if (is_array($keys)) {
            $_keys = [];
            foreach ($keys as $k => $v) {
                $_keys[] = (is_numeric($v) ? $v : '?');
            }
            $where = $field . ' ' . $type . 'IN (' . implode(', ', $_keys) . ')';

            if ($this->grouped) {
                $where = '(' . $where;
                $this->grouped = false;
            }

            $this->where = (is_null($this->where))
                ? ' WHERE ' . $where
                : $this->where . ' ' . $andOr . ' ' . $where;
        }

        return $this;
    }

    public function notIn($field, array $keys)
    {
        $this->in($field, $keys, 'NOT ', 'AND');

        return $this;
    }

    public function orIn($field, array $keys)
    {
        $this->in($field, $keys, '', 'OR');

        return $this;
    }

    public function orNotIn($field, array $keys)
    {
        $this->in($field, $keys, 'NOT ', 'OR');

        return $this;
    }

    public function get($mode = PDO::FETCH_ASSOC)
    {
        $this->buildQuery();
        $this->getSQL = $this->sql;
        try {
            $stmt = $this->db->prepare($this->sql);
            $stmt->execute($this->bindValues);
            $this->rowCount = $stmt->rowCount();
            return $stmt->fetch($mode);
        } catch(PDOException $e) {
            show_error('Database Error Occured', $e->getMessage().'<br>SQL Query: '.html_escape($this->getSQL), 'error_db', 500);
        }
    }

    public function getAll()
    {
        $this->buildQuery();
        $this->getSQL = $this->sql;
        try {
            $stmt = $this->db->prepare($this->sql);
            $stmt->execute($this->bindValues);
            $this->rowCount = $stmt->rowCount();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            show_error('Database Error Occured', $e->getMessage().'<br>SQL Query: '.html_escape($this->getSQL), 'error_db', 500);
        }
    }

    private function buildQuery()
    {
        if ( $this->columns !== NULL ) {
            $select = $this->columns;
        }else{
            $select = "*";
        }

        $this->sql = "SELECT $select FROM $this->table";
        if ($this->join !== NULL) {
            $this->sql .= $this->join;
        }

        if ($this->where !== NULL) {
            $this->sql .= $this->where;
        }

        if ($this->groupBy !== NULL) {
            $this->sql .= $this->groupBy;
        }

        if ($this->having !== NULL) {
            $this->sql .= $this->having;
        }

        if ($this->orderBy !== NULL) {
            $this->sql .= $this->orderBy;
        }

        if ($this->limit !== NULL) {
            $this->sql .= $this->limit;
        }
    }

    public function getSQL()
    {
        return $this->getSQL;
    }

    public function rowCount()
    {
        return $this->rowCount;
    }

    public function __destruct()
    {
        $this->db = null;
    }

    public function transaction()
    {
        if (! $this->transactionCount++) {
            return $this->db->beginTransaction();
        }

        $this->pdo->exec('SAVEPOINT trans' . $this->transactionCount);
        return $this->transactionCount >= 0;
    }

    public function commit()
    {
        if (! --$this->transactionCount) {
            return $this->db->commit();
        }

        return $this->transactionCount >= 0;
    }

    public function rollBack()
    {
        if (--$this->transactionCount) {
            $this->db->exec('ROLLBACK TO trans' . ($this->transactionCount + 1));
            return true;
        }

        return $this->db->rollBack();
    }
}