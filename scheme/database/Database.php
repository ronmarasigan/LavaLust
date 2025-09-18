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
 * @since Version 1
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/**
* ------------------------------------------------------
*  Class Database
* ------------------------------------------------------
 */
class Database {
    /**
     * Database instance
     *
     * @var object
     */
    private static $instance = NULL;
    
    /**
     * Database Instance
     *
     * @var object
     */
    private $db = NULL;

    /**
     * Database Driver
     */
    private $driver;

    /**
     * DB Prefix
     *
     * @var string
     */
    private $dbprefix = NULL;

    /**
     * Table name
     *
     * @var string
     */
    private $table;

    /**
     * Columns
     *
     * @var string
     */
    private $columns;

    /**
     * SQL Statement
     *
     * @var string
     */
    private $sql;

    /**
     * Values
     *
     * @var array
     */
    private $bindValues;

    /**
     * SQL Statement
     *
     * @var string
     */
    private $getSQL;

    /**
     * Join
     *
     * @var string
     */
    private $join = NULL;

    /**
     * WHERE
     *
     * @var string
     */
    private $where;

    /**
     * Group
     *
     * @var boolean
     */
    private $grouped = false;

    /**
     * Row Count
     *
     * @var integer
     */
    private $rowCount = 0;

    /**
     * Limit
     *
     * @var string
     */
    private $limit;

    /**
     * Order By
     *
     * @var string
     */
    private $orderBy;

    /**
     * Group By
     *
     * @var string
     */
    private $groupBy = NULL;

    /**
     * Having
     *
     * @var string
     */
    private $having = NULL;

    /**
     * Last Inseted ID
     *
     * @var integer
     */
    private $lastIDInserted = 0;

    /**
     * Transaction Count
     *
     * @var integer
     */
    private $transactionCount = 0;

    /**
     * Offset
     *
     * @var string
     */
    private $offset = null;

    /**
     * Operators
     *
     * @var array
     */
    private $operators = array('=', '!=', '<', '>', '<=', '>=', '<>');

    /**
     * Class Constructor
     *
     * @param PDO $pdo
     * @param string $dbprefix
     */
    public function __construct($dbname = NULL)
    {
        if(is_null($dbname)) {
        $database_config =& database_config()['main'];
        } else {
            if(isset(database_config()[$dbname])) {
                $database_config =& database_config()[$dbname];
            } else {
                throw new PDOException('No active configuration for this database.');
            }
        }
        $this->dbprefix = $database_config['dbprefix'];
        $driver = strtolower($database_config['driver']);
        $charset = $database_config['charset'];
        $host = $database_config['hostname'];
        $port = $database_config['port'];
        $dbname_value = $database_config['database'];
        $username = $database_config['username'];
        $password = $database_config['password'];
        $path = isset($database_config['path']) ? $database_config['path'] : null;

        switch ($driver) {
            case 'mysql':
                $dsn = "mysql:host=$host;dbname=$dbname_value;charset=$charset;port=$port";
                break;
            case 'pgsql':
                $dsn = "pgsql:host=$host;port=$port;dbname=$dbname_value;user=$username;password=$password";
                break;
            case 'sqlite':
                if (empty($path)) {
                    throw new PDOException('SQLite requires a valid file path.');
                }
                $dsn = "sqlite:$path";
                break;
            case 'sqlsrv':
                $dsn = "sqlsrv:Server=$host,$port;Database=$dbname_value";
                break;
            default:
                throw new PDOException("Unsupported database driver: $driver");
        }

        $options = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        );

        try {
            $this->db = new PDO($dsn, $username, $password, $options);
             $this->driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
        } catch (Exception $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * Get Database Instance
     *
     * @return instance
     */
    public static function instance($dbname)
    {
        self::$instance = new Database($dbname);
        return self::$instance;
    }

    /**
     * Raw Query
     *
     * @param  string $query
     * @param  array  $args  arguments
     * @return mixed
     */
    public function raw($query, $args = array())
    {
        $this->resetQuery();
        $query = trim($query);
        $this->getSQL = $query;
        $this->bindValues = $args;

        $stmt = $this->db->prepare($query);
        $stmt->execute($this->bindValues);
        return $stmt;
    }

    /**
     * Execute insert, update and delete
     *
     * @return integer
     */
    public function exec()
    {
        $this->sql .= $this->where;
        $this->getSQL = $this->sql;

        try {
            $stmt = $this->db->prepare($this->sql);
            $stmt->execute($this->bindValues);

            if (stripos($this->sql, 'INSERT') === 0) {
                $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);

                if ($driver === 'pgsql') {
                    // Try to detect primary key and use RETURNING
                    if (strpos($this->sql, 'RETURNING') === false) {
                        // Automatically add RETURNING if not already there
                        $this->sql .= ' RETURNING id';
                        $stmt = $this->db->prepare($this->sql);
                        $stmt->execute($this->bindValues);
                    }

                    $this->lastIDInserted = (int) $stmt->fetchColumn();
                    return $this->lastIDInserted;
                }

                // MySQL, SQLite, SQL Server
                $this->lastIDInserted = (int) $this->db->lastInsertId();
                return $this->lastIDInserted;
            } else {
                return $stmt->rowCount();
            }
        } catch (Exception $e) {
            throw new PDOException($e->getMessage() . 'Query: ' . $this->getSQL . '');
        }
    }

    /**
     * Reset queries
     *
     * @return void
     */
    private function resetQuery()
    {
        $this->table = NULL;
        $this->columns = NULL;
        $this->sql = NULL;
        $this->bindValues = array();
        $this->limit = NULL;
        $this->offset = NULL;
        $this->orderBy = NULL;
        $this->groupBy = NULL;
        $this->having = NULL;
        $this->getSQL = NULL;
        $this->where = NULL;
        $this->join = NULL;
        $this->rowCount = 0;
        $this->lastIDInserted = 0;
    }

    /**
     * Count rows in the table
     *
     * @return integer
     */
    public function count()
    {
        return $this->raw("SELECT COUNT(*) AS count FROM {$this->table}" . $this->where)->fetch()['count'];
    }

    /**
     * Bulk insert multiple records into the database
     *
     * @param array $records
     * @return integer
     */
    public function bulk_insert($records)
    {
        if (empty($records)) {
            return false;
        }
        
        $columns = array_keys($records[0]);
        $placeholders = rtrim(str_repeat('(' . rtrim(str_repeat('?, ', count($columns)), ', ') . '), ', count($records)), ', ');
        $this->bindValues = [];
        
        foreach ($records as $record) {
            $this->bindValues = array_merge($this->bindValues, array_values($record));
        }
        
        $this->sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES $placeholders";
        return $this->exec();
    }

    /**
     * Bulk update multiple records
     *
     * @param array $records (each record should include primary key value)
     * @param string $primaryKey
     * @return integer
     */
    public function bulk_update($records, $primaryKey = 'id') 
    {
        if (empty($records)) {
            return false;
        }
    
        // Reset query components
        $this->sql = '';
        $this->bindValues = [];
        $ids = [];
        $updates = [];
    
        // Get all columns that should be updated (excluding primary key)
        $columns = array_keys($records[0]);
        $columns = array_diff($columns, [$primaryKey]);
    
        // Build the update statements for each column
        foreach ($columns as $column) {
            $cases = [];
            $params = [];
            
            foreach ($records as $record) {
                $id = $record[$primaryKey];
                $value = $record[$column] ?? null;
                
                $cases[] = "WHEN ? THEN ?";
                $params[] = $id;
                $params[] = $value;
                
                if (!in_array($id, $ids)) {
                    $ids[] = $id;
                }
            }
            
            $caseStatement = "$column = CASE $primaryKey " . implode(' ', $cases) . " ELSE $column END";
            $updates[] = $caseStatement;
            $this->bindValues = array_merge($this->bindValues, $params);
        }
    
        // Build the complete SQL query
        $this->sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . 
                    " WHERE $primaryKey IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";
        $this->bindValues = array_merge($this->bindValues, $ids);
    
        // Execute and return the result
        return $this->exec();
    }

    /**
     * Delete Records
     *
     * @return integer
     */
    public function delete()
    {
        $this->sql = "DELETE FROM {$this->table}";

        return $this->exec();
    }

    /**
     * Update Record
     *
     * @param array $fields
     * @return integer
     */
    public function update($fields = [])
    {
        $set = '';
        $values = [];
        $field_array = [];

        foreach ($fields as $column => $field) {
            $values[] = $column . ' = ?';
            $field_array[] = $field;
        }
        $this->bindValues = array_merge($field_array, $this->bindValues);

        $set .= implode(', ', $values);

        $this->sql = "UPDATE {$this->table} SET {$set}";

        return $this->exec();
    }


    /**
     * Insert record
     *
     * @param  array  $fields
     * @return integer
     */
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

        return $this->exec();
    }

    /**
     * Last inserted ID
     *
     * @return integer
     */
    public function last_id()
    {
        return $this->lastIDInserted;
    }

    /**
     * Get table names
     *
     * @param  string $table_name
     * @return object
     */
    public function table($table_name)
    {
        $this->resetQuery();
        $this->table = $this->dbprefix.$table_name;
        return $this;
    }

    /**
     * Select
     *
     * @param  string $columns
     * @return object
     */
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

    /**
     * max_min_sum_count_avg
     *
     * @param  string $column
     * @param  string $alias
     * @param  string $type
     * @return object
     */
    public function _sql_function($column, $alias = null, $type = 'MAX')
    {
        if( ! in_array($type, array('MAX', 'MIN', 'SUM', 'COUNT', 'AVG', 'DISTINCT'))) {
            throw new RuntimeException('Invalid function type: ' . $type);
        }

        $function = $type . '(' . $column . ')' . (! is_null($alias) ? ' AS ' . $alias : '');
        $this->columns = ( is_null($this->columns) ? $function : $this->columns . ', ' . $function);

        return $this;
    }

    /**
     * select_max
     *
     * @param  string $column
     * @param  string $alias
     * @return object
     */
    public function select_max($column, $alias = null)
    {
        return $this->_sql_function($column, $alias, $type = 'MAX');
    }

    /**
     * select_min
     *
     * @param  string $column
     * @param  string $alias
     * @return object
     */
    public function select_min($column, $alias = null)
    {
        return $this->_sql_function($column, $alias, $type = 'MIN');
    }

    /**
     * select_sum
     *
     * @param  string $column
     * @param  string $alias
     * @return object
     */
    public function select_sum($column, $alias = null)
    {
        return $this->_sql_function($column, $alias, $type = 'SUM');
    }

    /**
     * select_count
     *
     * @param  string $column
     * @param  string $alias
     * @return object
     */
    public function select_count($column, $alias = null)
    {
        return $this->_sql_function($column, $alias, $type = 'COUNT');
    }

    /**
     * select_avg
     *
     * @param  string $column
     * @param  string $alias
     * @return object
     */
    public function select_avg($column, $alias = null)
    {
        return $this->_sql_function($column, $alias, $type = 'AVG');
    }

    /**
     * select distinct
     *
     * @param string $column
     * @param string $alias
     * @return object
     */
    public function select_distinct($column, $alias = null)
    {
        return $this->_sql_function($column, $alias, $type = 'DISTINCT');
    }

    /**
     * join
     *
     * @param  string $table_name
     * @param  string $cond
     * @param  string $type
     * @return object
     */
    public function join($table_name, $cond, $type = '')
    {
        $this->join = (is_null($this->join))
            ? ' ' . $type . 'JOIN' . ' ' . $this->dbprefix.$table_name . ' ON ' . $cond
            : $this->join . ' ' . $type . 'JOIN' . ' ' . $this->dbprefix.$table_name . ' ON ' . $cond;

        return $this;
    }

    /**
     * inner_join
     *
     * @param  string $table_name
     * @param  string $cond
     * @return object
     */
    public function inner_join($table_name, $cond)
    {
        return $this->join($table_name, $cond, 'INNER ');
    }

    /**
     * left_join
     *
     * @param  string $table_name
     * @param  string $cond
     * @return object
     */
    public function left_join($table_name, $cond)
    {
        $this->join($table_name, $cond, 'LEFT ');

        return $this;
    }

    /**
     * right_join
     *
     * @param  string $table_name
     * @param  string $cond
     * @return object
     */
    public function right_join($table_name, $cond)
    {
        $this->join($table_name, $cond, 'RIGHT ');

        return $this;
    }

    /**
     * full_outer_join
     *
     * @param  string $table_name
     * @param  string $cond
     * @return object
     */
    public function full_outer_join($table_name, $cond)
    {
        $this->join($table_name, $cond, 'FULL OUTER ');

        return $this;
    }

    /**
     * left_outer_join
     *
     * @param  string $table_name
     * @param  string $cond
     * @return object
     */
    public function left_outer_join($table_name, $cond)
    {
        $this->join($table_name, $cond, 'LEFT OUTER ');

        return $this;
    }

    /**
     * right_outer_join
     *
     * @param  string $table_name
     * @param  string $cond
     * @return object
     */
    public function right_outer_join($table_name, $cond)
    {
        $this->join($table_name, $cond, 'RIGHT OUTER ');

        return $this;
    }

    /**
     * grouped
     *
     * @param  Closure $obj
     * @return object
     */
    public function grouped(Closure $obj)
    {
        $this->grouped = true;
        call_user_func_array($obj, [$this]);
        $this->where .= ')';

        return $this;
    }

    /**
     * where
     *
     * @param  string $where
     * @param  string $op
     * @param  mixed $val
     * @param  string $type
     * @param  string $andOr
     * @return object
     */
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

    /**
     * or_where
     *
     * @param  string $where
     * @param  string $op
     * @param  mixed $val
     * @return object
     */
    public function or_where($where, $op = null, $val = null)
    {
        $this->where($where, $op, $val, '', 'OR');

        return $this;
    }

    /**
     * not_where
     *
     * @param  string $where
     * @param  string $op
     * @param  mixed $val
     * @return object
     */
    public function not_where($where, $op = null, $val = null)
    {
        $this->where($where, $op, $val, 'NOT ', 'AND');

        return $this;
    }

    /**
     * or_not_where
     *
     * @param  string $where
     * @param  string $op
     * @param  mixed $val
     * @return object
     */
    public function or_not_where($where, $op = null, $val = null)
    {
        $this->where($where, $op, $val, 'NOT ', 'OR');

        return $this;
    }

    /**
     * where_null
     *
     * @param  string $where
     * @return object
     */
    public function where_null($where)
    {
        $where = $where . ' IS NULL';
        $this->where = (is_null($this->where))
            ? ' WHERE ' . $where
            : $this->where . ' ' . 'AND ' . $where;

        return $this;
    }

    /**
     * where_not_null
     *
     * @param  string $where
     * @return object
     */
    public function where_not_null($where)
    {
        $where = $where . ' IS NOT NULL';
        $this->where = (is_null($this->where))
            ? ' WHERE ' . $where
            : $this->where . ' ' . 'AND ' . $where;

        return $this;
    }

    /**
     * like
     *
     * @param  string $field
     * @param  mixed $data
     * @param  string $type
     * @param  string $andOr
     * @return object
     */
    public function like($field, $data, $type = '', $andOr = 'AND')
    {
        $this->bindValues[] = $data;
        $where = $field . ' ' . $type . 'LIKE ?';

        if ($this->grouped) {
            $where = '(' . $where;
            $this->grouped = false;
        }

        $this->where = (is_null($this->where))
            ? ' WHERE ' . $where
            : $this->where . ' ' . $andOr . ' ' . $where;

        return $this;
    }

    /**
     * or_like
     * @param  string $field
     * @param  mixed $data
     * @return object
     */
    public function or_like($field, $data)
    {
        return $this->like($field, $data, '', 'OR');
    }

    /**
     * not_like
     * @param  string $field
     * @param  mixed $data
     * @return object
     */
    public function not_like($field, $data)
    {
        return $this->like($field, $data, 'NOT ', 'AND');
    }

    /**
     * or_not_like
     *
     * @param  string $field
     * @param  mixed $data
     * @return object
     */
    public function or_not_like($field, $data)
    {
        return $this->like($field, $data, 'NOT ', 'OR');
    }

    /**
     * between
     *
     * @param  string $field
     * @param  mixed $value1
     * @param  mixed $value2
     * @param  string $type
     * @param  string $andOr
     * @return object
     */
    public function between($field, $value1, $value2, $type = '', $andOr = 'AND')
    {
        $this->bindValues[] = $value1;
        $this->bindValues[] = $value2;
        $where = '(' . $field . ' ' . $type . 'BETWEEN ?  AND ?)';

        if ($this->grouped) {
            $where = '(' . $where;
            $this->grouped = false;
        }

        $this->where = (is_null($this->where))
            ? ' WHERE ' . $where
            : $this->where . ' ' . $andOr . ' ' . $where;

        return $this;
    }

    /**
     * not_between
     *
     * @param  string $field
     * @param  mixed $value1
     * @param  mixed $value2
     * @return object
     */
    public function not_between($field, $value1, $value2)
    {
        return $this->between($field, $value1, $value2, 'NOT ', 'AND');
    }

    /**
     * or_between
     *
     * @param  string $field
     * @param  mixed $value1
     * @param  mixed $value2
     * @return object
     */
    public function or_between($field, $value1, $value2)
    {
        return $this->between($field, $value1, $value2, '', 'OR');
    }

    /**
     * or_not_between
     *
     * @param  string $field
     * @param  mixed $value1
     * @param  mixed $value2
     * @return object
     */
    public function or_not_between($field, $value1, $value2)
    {
        return $this->between($field, $value1, $value2, 'NOT ', 'OR');
    }

    /**
     * in
     *
     * @param  string $field
     * @param  array  $keys
     * @param  string $type
     * @param  string $andOr
     * @return object
     */
    public function in($field, array $keys, $type = '', $andOr = 'AND')
    {
        if (!empty($keys)) {
            $placeholders = implode(', ', array_fill(0, count($keys), '?'));
            foreach ($keys as $v) {
                $this->bindValues[] = $v;
            }

            $where = "$field {$type}IN ($placeholders)";

            if ($this->grouped) {
                $where = '(' . $where;
                $this->grouped = false;
            }

            $this->where = is_null($this->where)
                ? ' WHERE ' . $where
                : $this->where . ' ' . $andOr . ' ' . $where;
        }

        return $this;
    }

    /**
     * not_in
     *
     * @param  string $field
     * @param  array  $keys
     * @return object
     */
    public function not_in($field, array $keys)
    {
        $this->in($field, $keys, 'NOT ', 'AND');

        return $this;
    }

    /**
     * or_in
     *
     * @param  string $field
     * @param  array  $keys
     * @return object
     */
    public function or_in($field, array $keys)
    {
        $this->in($field, $keys, '', 'OR');

        return $this;
    }

    /**
     * or_not_in
     *
     * @param  string $field
     * @param  array  $keys
     * @return object
     */
    public function or_not_in($field, array $keys)
    {
        $this->in($field, $keys, 'NOT ', 'OR');

        return $this;
    }

    /**
     * limit
     *
     * @param  integer $limit
     * @param  integer $offset
     * @return object
     */
    public function limit($limit, $end = null)
    {
        $driver = $this->driver; // Assume this is already set in your PDO wrapper

        if ($end === null) {
            // Only a limit value
            switch ($driver) {
                case 'mysql':
                case 'pgsql':
                case 'sqlite':
                    $this->limit = " LIMIT $limit";
                    break;
                case 'sqlsrv':
                    // Note: LIMIT not supported directly in SQL Server
                    $this->limit = " OFFSET 0 ROWS FETCH NEXT $limit ROWS ONLY";
                    break;
            }
        } else {
            // Limit and offset
            switch ($driver) {
                case 'mysql':
                    $this->limit = " LIMIT $limit, $end";
                    break;
                case 'pgsql':
                case 'sqlite':
                    $this->limit = " LIMIT $end OFFSET $limit";
                    break;
                case 'sqlsrv':
                    $this->limit = " OFFSET $limit ROWS FETCH NEXT $end ROWS ONLY";
                    break;
            }
        }

        return $this;
    }

    /**
     * Offset
     *
     * @param int $offset
     * @return object
     */
    public function offset($offset)
    {
        $this->offset = ' OFFSET ';
        $this->offset .= $offset;

        return $this;
    }

    /**
     * Pagination
     *
     * @param int $records_per_page
     * @param int $page
     * @return void
     */
    public function pagination($records_per_page, $page)
    {
        $offset = ($page - 1) * $records_per_page;

        $this->limit = ' LIMIT '.$offset.', '.$records_per_page;

        return $this;
    }

    /**
     * order_by
     *
     * @param  string $field_name
     * @param  string $order
     * @return object
     */
    public function order_by($field_name, $order = null)
    {
        $field_name = trim($field_name);

        $this->orderBy = ' ORDER BY ';
        if (! is_null($order)) {
            $this->orderBy .= $field_name . ' ' . strtoupper($order);
        } else {
            $this->orderBy .= stristr($field_name, ' ') || strtolower($field_name) === 'rand()'
                ? $field_name
                : $field_name . ' ASC';
        }

        return $this;
    }

    /**
     * group_by
     *
     * @param  string $groupBy
     * @return object
     */
     public function group_by($groupBy)
    {
        $this->groupBy = ' GROUP BY ';
        $this->groupBy .= (is_array($groupBy))
            ? implode(', ', $groupBy)
            : $groupBy;

        return $this;
    }

    /**
     * having
     *
     * @param  string $field
     * @param  string $op
     * @param  mixed $val
     * @return object
     */
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

    /**
     * buildQuery
     *
     * @return void
     */
    private function buildQuery()
    {
        if ( $this->columns !== NULL ) {
            $select = $this->columns;
        }else{
            $select = "*";
        }

        $this->sql = "SELECT $select FROM {$this->table}";
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

        if ($this->offset !== NULL) {
            $this->sql .= $this->offset;
        }
    }

    /**
     * get
     *
     * @param  string $mode
     * @return mixed
     */
    public function get($mode = PDO::FETCH_ASSOC)
    {
        $this->buildQuery();
        $this->getSQL = $this->sql;
        try {
            $stmt = $this->db->prepare($this->sql);
            $stmt->execute($this->bindValues);
            $this->rowCount = $stmt->rowCount();
            return $stmt->fetch($mode);
        } catch(Exception $e) {
            throw new PDOException($e->getMessage().'<div style="background-color:#000;color:#fff;padding:15px">Query: '.$this->getSQL.'</div>');
        }
    }

    /**
     * get_all
     *
     * @return mixed
     */
    public function get_all($mode = PDO::FETCH_ASSOC)
    {
        $this->buildQuery();
        $this->getSQL = $this->sql;
        try {
            $stmt = $this->db->prepare($this->sql);
            $stmt->execute($this->bindValues);
            $this->rowCount = $stmt->rowCount();
            return $stmt->fetchAll($mode);
        } catch(Exception $e) {
            throw new PDOException($e->getMessage().'<div style="background-color:#000;color:#fff;padding:15px">Query: '.$this->getSQL.'</div>');
        }
    }

    /**
     * get_sql
     *
     * @return string
     */
    public function get_sql()
    {
        return $this->getSQL;
    }

    /**
     * row_count
     *
     * @return integer
     */
    public function row_count()
    {
        return $this->rowCount;
    }

    /**
     * transaction
     *
     * @return boolean
     */
    public function transaction()
    {
        if (! $this->transactionCount++) {
            return $this->db->beginTransaction();
        }

        $this->db->exec('SAVEPOINT trans' . $this->transactionCount);
        return $this->transactionCount >= 0;
    }

    /**
     * commit
     *
     * @return boolean
     */
    public function commit()
    {
        if (! --$this->transactionCount) {
            return $this->db->commit();
        }

        return $this->transactionCount >= 0;
    }

    /**
     * roll_back
     *
     * @return mixed
     */
    public function roll_back()
    {
        if (--$this->transactionCount) {
            $this->db->exec('ROLLBACK TO trans' . ($this->transactionCount + 1));
            return true;
        }

        return $this->db->rollBack();
    }

    /**
     * __destruct
     */
    public function __destruct()
    {
        $this->db = null;
    }
}