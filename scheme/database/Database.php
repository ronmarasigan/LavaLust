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
     * @var string
     */
    private $driver;

    /**
     * DB Prefix
     *
     * @var string
     */
    private $db_prefix = NULL;

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
    private $bind_values;

    /**
     * SQL Statement
     *
     * @var string
     */
    private $get_sql;

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
    private $row_count = 0;

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
    private $order_by;

    /**
     * Group By
     *
     * @var string
     */
    private $group_by = NULL;

    /**
     * Having
     *
     * @var string
     */
    private $having = NULL;

    /**
     * Last Inserted ID
     *
     * @var integer
     */
    private $last_id_inserted = 0;

    /**
     * Transaction Count
     *
     * @var integer
     */
    private $transaction_count = 0;

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

    /** @var array Executed queries log */
    private array $query_log = [];

    /** @var bool Whether query logging is enabled */
    private bool $query_logging = true;

    /**
     * Class Constructor
     *
     * @param string $dbname
     */
    public function __construct($dbname = NULL)
    {
        if(is_null($dbname)) {
            $database_config = database_config()['main'];
        } else {
            if(isset(database_config()[$dbname])) {
                $database_config = database_config()[$dbname];
            } else {
                throw new PDOException('No active configuration for this database.');
            }
        }
        $this->db_prefix = isset($database_config['dbprefix']) ? $database_config['dbprefix'] : '';

        $driver = isset($database_config['driver']) && !empty($database_config['driver'])
            ? strtolower($database_config['driver'])
            : 'mysql';

        $charset = isset($database_config['charset']) && !empty($database_config['charset'])
            ? $database_config['charset']
            : 'utf8mb4';

        $host = isset($database_config['hostname']) && !empty($database_config['hostname'])
            ? $database_config['hostname']
            : 'localhost';

        $port = isset($database_config['port']) && !empty($database_config['port'])
            ? $database_config['port']
            : null;

        $dbname_value = isset($database_config['database']) && !empty($database_config['database'])
            ? $database_config['database']
            : '';

        $username = isset($database_config['username']) && !empty($database_config['username'])
            ? $database_config['username']
            : 'root';

        $password = isset($database_config['password']) && !empty($database_config['password'])
            ? $database_config['password']
            : '';

        $path = isset($database_config['path']) && !empty($database_config['path'])
            ? $database_config['path']
            : null;

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
            $error = load_class('Errors', 'kernel');
            $error->show_database_error(
                $e->getMessage(),
                $this->get_sql ?? '',
                $this->bind_values ?? [],
                $e
            );
        }
    }

    /**
     * DB Instance
     *
     * @param string $dbname
     * @return void
     */
    public static function instance($dbname)
    {
        self::$instance = new Database($dbname);
        return self::$instance;
    }
    
    /**
     * Validate SQL identifier (table or column name)
     *
     * @param string $name
     * @return bool
     * @throws Exception if the identifier is invalid
     */
    private function validate_identifier($name)
    {
        static $blocked_keywords = [
            'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'CREATE', 'ALTER',
            'TRUNCATE', 'EXEC', 'EXECUTE', 'UNION', 'GRANT', 'REVOKE', 'LOAD',
            'OUTFILE', 'DUMPFILE', 'SLEEP', 'BENCHMARK', 'WAITFOR', 'XP_CMDSHELL',
        ];

        $name = trim($name);

        if ($name === '*') {
            return true;
        }

        if ($name === '' || strlen($name) > 256) {
            throw new Exception("Invalid SQL identifier: {$name}");
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $name)) {
            throw new Exception("Invalid SQL identifier: {$name}");
        }

        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*\s*\([^)]*\)(\s+AS\s+[a-zA-Z_][a-zA-Z0-9_]*)?$/i', $name)) {
            return true;
        }

        $base = preg_replace('/\s+(AS\s+)?[a-zA-Z_][a-zA-Z0-9_]*$/i', '', $name);
        $base = trim($base);

        $parts = explode('.', $base);

        if (count($parts) > 3) {
            throw new Exception("Invalid SQL identifier: {$name}");
        }

        foreach ($parts as $part) {
            $part = trim($part);

            // Strip backtick quoting
            if (strlen($part) >= 2 && $part[0] === '`' && $part[-1] === '`') {
                $part = substr($part, 1, -1);
            }

            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $part)) {
                throw new Exception("Invalid SQL identifier: {$name}");
            }

            if (in_array(strtoupper($part), $blocked_keywords, true)) {
                throw new Exception("Invalid SQL identifier: {$name}");
            }
        }

        return true;
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
        $this->reset_query();
        $query = trim($query);
        $this->get_sql = $query;
        $this->bind_values = $args;
        try
        {     
            $stmt = $this->db->prepare($query);
            $t_start = microtime(true);
            $stmt->execute($this->bind_values);
            $t_elapsed = microtime(true) - $t_start;
            
            $this->last_id_inserted = $this->db->lastInsertId();
            
            if ($this->query_logging) {
                $this->query_log[] = [
                    'query'    => $query,
                    'bindings' => $args,
                    'time'     => round($t_elapsed, 5),
                ];
            }
            return $stmt;
        } catch (Exception $e) {
            $error = load_class('Errors', 'kernel');
            $error->show_database_error(
                $e->getMessage(),
                $this->get_sql ?? '',
                $this->bind_values ?? [],
                $e
            );
        }
    }

    /**
     * Execute insert, update and delete
     *
     * @return integer
     */
    public function exec()
    {
        $this->sql .= $this->where;
        $this->get_sql = $this->sql;

        try {
            $stmt = $this->db->prepare($this->sql);

            $t_start = microtime(true);
            $stmt->execute($this->bind_values);
            $t_elapsed = microtime(true) - $t_start;

            if ($this->query_logging) {
                $this->query_log[] = [
                    'query'    => $this->sql,
                    'bindings' => $this->bind_values,
                    'time'     => round($t_elapsed, 5),
                ];
            }
            
            if (stripos($this->sql, 'INSERT') === 0) {
                $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);

                if ($driver === 'pgsql') {
                    if (strpos($this->sql, 'RETURNING') === false) {
                        $this->sql .= ' RETURNING id';
                        $stmt = $this->db->prepare($this->sql);
                        $stmt->execute($this->bind_values);
                    }

                    $this->last_id_inserted = (int) $stmt->fetchColumn();
                    return $this->last_id_inserted;
                }

                $this->last_id_inserted = (int) $this->db->lastInsertId();
                return $this->last_id_inserted;
            } else {
                return $stmt->rowCount();
            }
        } catch (Exception $e) {
            throw new PDOException($e->getMessage() . 'Query: ' . $this->get_sql . '');
        }
    }

    /**
     * Reset queries
     *
     * @return void
     */
    private function reset_query()
    {
        $this->table          = NULL;
        $this->columns        = NULL;
        $this->sql            = NULL;
        $this->bind_values    = array();
        $this->limit          = NULL;
        $this->offset         = NULL;
        $this->order_by       = NULL;
        $this->group_by       = NULL;
        $this->having         = NULL;
        $this->get_sql        = NULL;
        $this->where          = NULL;
        $this->join           = NULL;
        $this->row_count      = 0;
        $this->last_id_inserted = 0;
    }

    /**
     * Count rows in the table
     *
     * @return integer
     */
    public function count()
    {
        $sql = "SELECT COUNT(*) AS count FROM {$this->table}" . $this->where;
        $stmt = $this->raw($sql, $this->bind_values);
        $result = $stmt->fetch();

        $this->reset_query();

        return $result['count'] ?? 0;
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
        foreach ($columns as $column) {
            $this->validate_identifier($column);
        }
        $placeholders = rtrim(str_repeat('(' . rtrim(str_repeat('?, ', count($columns)), ', ') . '), ', count($records)), ', ');
        $this->bind_values = [];
        
        foreach ($records as $record) {
            $this->bind_values = array_merge($this->bind_values, array_values($record));
        }
        
        $this->sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES $placeholders";
        return $this->exec();
    }

    /**
     * Bulk update multiple records
     *
     * @param array $records (each record should include primary key value)
     * @param string $primary_key
     * @return integer
     */
    public function bulk_update($records, $primary_key = 'id') 
    {
        if (empty($records)) {
            return false;
        }
    
        $this->sql         = '';
        $this->bind_values = [];
        $ids               = [];
        $updates           = [];
    
        $columns = array_keys($records[0]);
        $columns = array_diff($columns, [$primary_key]);
    
        foreach ($columns as $column) {
            $this->validate_identifier($column);
            $cases  = [];
            $params = [];
            
            foreach ($records as $record) {
                $id    = $record[$primary_key];
                $value = $record[$column] ?? null;
                
                $cases[]  = "WHEN ? THEN ?";
                $params[] = $id;
                $params[] = $value;
                
                if (!in_array($id, $ids)) {
                    $ids[] = $id;
                }
            }
            
            $case_statement = "$column = CASE $primary_key " . implode(' ', $cases) . " ELSE $column END";
            $updates[]      = $case_statement;
            $this->bind_values = array_merge($this->bind_values, $params);
        }
    
        $this->sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . 
                    " WHERE $primary_key IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";
        $this->bind_values = array_merge($this->bind_values, $ids);
    
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
        $set         = '';
        $values      = [];
        $field_array = [];

        foreach ($fields as $column => $field) {
            $this->validate_identifier($column);
            $values[]      = $column . ' = ?';
            $field_array[] = $field;
        }
        $this->bind_values = array_merge($field_array, $this->bind_values);

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
        $keys   = implode(', ', array_keys($fields));
        $values = '';
        $x      = 1;
        foreach ($fields as $field => $value) {
            $this->validate_identifier($field);
            $values .= '?';
            $this->bind_values[] = $value;
            if ($x < count($fields)) {
                $values .= ', ';
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
        return $this->last_id_inserted;
    }

    /**
     * Get table names
     *
     * @param  string $table_name
     * @return object
     */
    public function table($table_name)
    {
        $this->validate_identifier($table_name);
        $this->reset_query();
        $this->table = $this->db_prefix . $table_name;
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
            $this->validate_identifier($column);
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
        $this->validate_identifier($column);

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
            ? ' ' . $type . 'JOIN' . ' ' . $this->db_prefix . $table_name . ' ON ' . $cond
            : $this->join . ' ' . $type . 'JOIN' . ' ' . $this->db_prefix . $table_name . ' ON ' . $cond;

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
     * @param  string $and_or
     * @return object
     */
    public function where($where, $op = null, $val = null, $type = '', $and_or = 'AND')
    {
        if (is_array($where) && ! empty($where)) {
            $_where = [];
            foreach ($where as $column => $data) {
                $this->validate_identifier($column);
                $_where[]            = $type . $column . ' = ?';
                $this->bind_values[] = $data;
            }
            $where = implode(' ' . $and_or . ' ', $_where);
        } else {
            $this->validate_identifier($where);
            if (is_null($where) || empty($where)) {
                return $this;
            }

            if (is_array($op)) {
                $params = explode('?', $where);
                $_where = '';
                foreach ($params as $key => $value) {
                    if (! empty($value)) {
                        $_where             .= $type . $value . (isset($op[$key]) ? ' ? ' : '');
                        $this->bind_values[] = $op[$key];
                    }
                }
                $where = $_where;
            } elseif (! in_array($op, $this->operators) || $op == false) {
                $where               = $type . $where . ' = ?';
                $this->bind_values[] = $op;
            } else {
                $where               = $type . $where . ' ' . $op . ' ?';
                $this->bind_values[] = $val;
            }
        }

        if ($this->grouped) {
            $where         = '(' . $where;
            $this->grouped = false;
        }

        $this->where = (is_null($this->where))
            ? ' WHERE ' . $where
            : $this->where . ' ' . $and_or . ' ' . $where;

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
     * @param  string $and_or
     * @return object
     */
    public function like($field, $data, $type = '', $and_or = 'AND')
    {
        $this->validate_identifier($field);
        $this->bind_values[] = $data;
        $where = $field . ' ' . $type . 'LIKE ?';

        if ($this->grouped) {
            $where         = '(' . $where;
            $this->grouped = false;
        }

        $this->where = (is_null($this->where))
            ? ' WHERE ' . $where
            : $this->where . ' ' . $and_or . ' ' . $where;

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
     * @param  string $and_or
     * @return object
     */
    public function between($field, $value1, $value2, $type = '', $and_or = 'AND')
    {
        $this->validate_identifier($field);
        $this->bind_values[] = $value1;
        $this->bind_values[] = $value2;
        $where = '(' . $field . ' ' . $type . 'BETWEEN ?  AND ?)';

        if ($this->grouped) {
            $where         = '(' . $where;
            $this->grouped = false;
        }

        $this->where = (is_null($this->where))
            ? ' WHERE ' . $where
            : $this->where . ' ' . $and_or . ' ' . $where;

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
     * @param  string $and_or
     * @return object
     */
    public function in($field, array $keys, $type = '', $and_or = 'AND')
    {
        $this->validate_identifier($field);
        if (!empty($keys)) {
            $placeholders = implode(', ', array_fill(0, count($keys), '?'));
            foreach ($keys as $v) {
                $this->bind_values[] = $v;
            }

            $where = "$field {$type}IN ($placeholders)";

            if ($this->grouped) {
                $where         = '(' . $where;
                $this->grouped = false;
            }

            $this->where = is_null($this->where)
                ? ' WHERE ' . $where
                : $this->where . ' ' . $and_or . ' ' . $where;
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
    public function limit($limit, $offset = null)
    {
        $this->limit  = (int) $limit;
        $this->offset = $offset !== null ? (int) $offset : null;

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
        $this->offset = (int) $offset;
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
        $page = max(1, (int) $page);
        $records_per_page = (int) $records_per_page;

        $this->limit  = $records_per_page;
        $this->offset = ($page - 1) * $records_per_page;

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
        $this->validate_identifier($field_name);

        $this->order_by = ' ORDER BY ';
        if (! is_null($order)) {
            $this->order_by .= $field_name . ' ' . strtoupper($order);
        } else {
            $this->order_by .= stristr($field_name, ' ') || strtolower($field_name) === 'rand()'
                ? $field_name
                : $field_name . ' ASC';
        }

        return $this;
    }

    /**
     * group_by
     *
     * @param  string $group_by
     * @return object
     */
    public function group_by($group_by)
    {
        $this->group_by = ' GROUP BY ';

        if (is_array($group_by)) {
            foreach ($group_by as $column) {
                $this->validate_identifier($column);
            }
            $this->group_by .= implode(', ', $group_by);
        } else {
            $this->validate_identifier($group_by);
            $this->group_by .= $group_by;
        }

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
        $this->validate_identifier($field);
        $this->having = ' HAVING ';
        if (is_array($op)) {
            $fields = explode('?', $field);
            $where  = '';
            foreach ($fields as $key => $value) {
                if (! empty($value)) {
                    $where              .= $value . (isset($op[$key]) ? ' ? ' : '');
                    $this->bind_values[] = $op[$key];
                }
            }
            $this->having .= $where;
        } elseif (! in_array($op, $this->operators)) {
            $this->having       .= $field . ' > ' . ' ? ';
            $this->bind_values[] = $op;
        } else {
            $this->having       .= $field . ' ' . $op . ' ' . ' ? ';
            $this->bind_values[] = $val;
        }

        return $this;
    }

    /**
     * build_query
     *
     * @return void
     */
    private function build_query()
    {
        $select = ($this->columns !== NULL) ? $this->columns : '*';

        $this->sql = "SELECT $select FROM {$this->table}";

        if ($this->join !== NULL) {
            $this->sql .= $this->join;
        }

        if ($this->where !== NULL) {
            $this->sql .= $this->where;
        }

        if ($this->group_by !== NULL) {
            $this->sql .= $this->group_by;
        }

        if ($this->having !== NULL) {
            $this->sql .= $this->having;
        }

        if ($this->order_by !== NULL) {
            $this->sql .= $this->order_by;
        }

        if ($this->limit !== NULL) {
            $driver = $this->driver;

            switch ($driver) {
                case 'mysql':
                    if ($this->offset !== null) {
                        $this->sql .= " LIMIT {$this->offset}, {$this->limit}";
                    } else {
                        $this->sql .= " LIMIT {$this->limit}";
                    }
                    break;

                case 'pgsql':
                case 'sqlite':
                    $this->sql .= " LIMIT {$this->limit}";
                    if ($this->offset !== null) {
                        $this->sql .= " OFFSET {$this->offset}";
                    }
                    break;

                case 'sqlsrv':
                    $offset = $this->offset ?? 0;
                    $this->sql .= " OFFSET {$offset} ROWS FETCH NEXT {$this->limit} ROWS ONLY";
                    break;
            }
        }
    }

    /**
     * get - Fetch single row
     *
     * @param  int $mode PDO fetch mode
     * @param  mixed ...$args Additional arguments for fetch()
     * @return mixed
     */
    public function get($mode = PDO::FETCH_ASSOC, ...$args)
    {
        $this->build_query();
        $this->get_sql = $this->sql;
        try {
            $stmt    = $this->db->prepare($this->sql);
            $t_start = microtime(true);
            $stmt->execute($this->bind_values);
            $t_elapsed = microtime(true) - $t_start;

            if ($this->query_logging) {
                $this->query_log[] = [
                    'query'    => $this->sql,
                    'bindings' => $this->bind_values,
                    'time'     => round($t_elapsed, 5),
                ];
            }
            $this->row_count = $stmt->rowCount();
            return $stmt->fetch($mode, ...$args);
        } catch (Exception $e) {
            $error = load_class('Errors', 'kernel');
            $error->show_database_error(
                $e->getMessage(),
                $this->get_sql ?? '',
                $this->bind_values ?? [],
                $e
            );
        }
    }

    /**
     * get_all - Fetch all rows
     *
     * @param  int $mode PDO fetch mode
     * @param  mixed ...$args Additional arguments for fetchAll()
     * @return array
     */
    public function get_all($mode = PDO::FETCH_ASSOC, ...$args)
    {
        $this->build_query();
        $this->get_sql = $this->sql;
        try {
            $stmt    = $this->db->prepare($this->sql);
            $t_start = microtime(true);
            $stmt->execute($this->bind_values);
            $t_elapsed = microtime(true) - $t_start;

            if ($this->query_logging) {
                $this->query_log[] = [
                    'query'    => $this->sql,
                    'bindings' => $this->bind_values,
                    'time'     => round($t_elapsed, 5),
                ];
            }
            $this->row_count = $stmt->rowCount();
            return $stmt->fetchAll($mode, ...$args);
        } catch (Exception $e) {
            $error = load_class('Errors', 'kernel');
            $error->show_database_error(
                $e->getMessage(),
                $this->get_sql ?? '',
                $this->bind_values ?? [],
                $e
            );
        }
    }

    /**
     * get_sql
     *
     * @return string
     */
    public function get_sql()
    {
        return $this->get_sql;
    }

    /**
     * row_count
     *
     * @return integer
     */
    public function row_count()
    {
        return $this->row_count;
    }

    /**
     * Increment column value
     *
     * @param string $column
     * @param integer $amount
     * @return void
     */
    public function increment($column, $amount = 1)
    {
        $this->validate_identifier($column);
        $this->sql         = "UPDATE {$this->table} SET {$column} = {$column} + ?";
        $this->bind_values = array_merge([$amount], $this->bind_values);

        return $this->exec();
    }

    /**
     * Decrement column value
     *
     * @param string $column
     * @param integer $amount
     * @return void
     */
    public function decrement($column, $amount = 1)
    {
        $this->validate_identifier($column);
        $this->sql         = "UPDATE {$this->table} SET {$column} = {$column} - ?";
        $this->bind_values = array_merge([$amount], $this->bind_values);

        return $this->exec();
    }

    /**
     * Returns all rows as array of objects (stdClass)
     *
     * @return array
     */
    public function result()
    {
        return $this->get_all(PDO::FETCH_OBJ);
    }

    /**
     * Returns all rows as associative array
     *
     * @return array
     */
    public function result_array()
    {
        return $this->get_all(PDO::FETCH_ASSOC);
    }

    /**
     * Returns single row as object (stdClass)
     *
     * @param int $row_index Optional row index (for fetching specific row)
     * @return object|null
     */
    public function row($row_index = null)
    {
        if ($row_index !== null) {
            $results = $this->get_all(PDO::FETCH_OBJ);
            return $results[$row_index] ?? null;
        }
        return $this->get(PDO::FETCH_OBJ);
    }

    /**
     * Returns single row as associative array
     *
     * @param int $row_index Optional row index (for fetching specific row)
     * @return array|null
     */
    public function row_array($row_index = null)
    {
        if ($row_index !== null) {
            $results = $this->get_all(PDO::FETCH_ASSOC);
            return $results[$row_index] ?? null;
        }
        return $this->get(PDO::FETCH_ASSOC);
    }

    /**
     * Maps all rows to custom class instances
     *
     * @param string $classname Class name
     * @param array $ctor_args Constructor arguments
     * @return array Array of class instances
     */
    public function custom_result_object($classname, $ctor_args = [])
    {
        if (empty($ctor_args)) {
            return $this->get_all(PDO::FETCH_CLASS, $classname);
        }
        return $this->get_all(PDO::FETCH_CLASS, $classname, ...$ctor_args);
    }

    /**
     * Maps single row to custom class instance
     *
     * @param int $row_index Row index
     * @param string $classname Class name
     * @param array $ctor_args Constructor arguments
     * @return object|null
     */
    public function custom_row_object($row_index, $classname, $ctor_args = [])
    {
        $results = empty($ctor_args) 
            ? $this->get_all(PDO::FETCH_CLASS, $classname)
            : $this->get_all(PDO::FETCH_CLASS, $classname, ...$ctor_args);
        
        return $results[$row_index] ?? null;
    }

    /**
     * Returns first row as object
     *
     * @return object|null
     */
    public function first_row()
    {
        return $this->row(0);
    }

    /**
     * Returns last row as object
     *
     * @return object|null
     */
    public function last_row()
    {
        $results = $this->get_all(PDO::FETCH_OBJ);
        return !empty($results) ? $results[count($results) - 1] : null;
    }

    /**
     * Returns number of rows in result set
     *
     * @return int
     */
    public function num_rows()
    {
        return $this->row_count;
    }

    /**
     * Get single column from all rows (like array_column)
     *
     * @param int $column_index Column index (0-based)
     * @return array
     */
    public function get_column($column_index = 0)
    {
        return $this->get_all(PDO::FETCH_COLUMN, $column_index);
    }

    /**
     * Get key-value pairs (first column as key, second as value)
     * Great for dropdowns and select options
     *
     * @return array
     */
    public function get_key_pair()
    {
        return $this->get_all(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Get grouped results by first column value
     *
     * @return array
     */
    public function get_grouped()
    {
        return $this->get_all(PDO::FETCH_GROUP);
    }

    /**
     * Get results as numeric arrays
     *
     * @return array
     */
    public function result_num()
    {
        return $this->get_all(PDO::FETCH_NUM);
    }

    /**
     * Get single row as numeric array
     *
     * @return array|null
     */
    public function row_num()
    {
        return $this->get(PDO::FETCH_NUM);
    }
    
    /**
     * Enable or disable query logging
     *
     * @param bool $state
     * @return void
     */
    public function enable_query_log($state = true)
    {
        $this->query_logging = $state;
    }

    /**
     * Get the query log
     *
     * @return array
     */
    public function get_query_log()
    {
        return $this->query_log;
    }

    /**
     * transaction
     *
     * @return boolean
     */
    public function transaction()
    {
        if (! $this->transaction_count++) {
            return $this->db->beginTransaction();
        }

        $this->db->exec('SAVEPOINT trans' . $this->transaction_count);
        return $this->transaction_count >= 0;
    }

    /**
     * commit
     *
     * @return boolean
     */
    public function commit()
    {
        if (! --$this->transaction_count) {
            return $this->db->commit();
        }

        return $this->transaction_count >= 0;
    }

    /**
     * roll_back
     *
     * @return mixed
     */
    public function roll_back()
    {
        if (--$this->transaction_count) {
            $this->db->exec('ROLLBACK TO trans' . ($this->transaction_count + 1));
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