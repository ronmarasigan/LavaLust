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
| -------------------------------------------------------------------
| Model Class
| -------------------------------------------------------------------
 * 
 * @method static array         all(bool $with_deleted = false)
 * @method static mixed        query()
 * @method static mixed        find(int $id, bool $with_deleted = false)
 * @method static mixed        find_by(string $column, mixed $value, bool $with_deleted = false)
 * @method static array        find_all_by(string $column, mixed $value, bool $with_deleted = false)
 * @method static mixed        value(string $column, array $conditions = [])
 * @method static array        pluck(string $column, array $conditions = [], bool $with_deleted = false)
 * @method static array        first_or_create(array $conditions, array $extra_data = [])
 * @method static int          update_or_create(array $conditions, array $data)
 * @method static int          update_where(array $conditions, array $data)
 * @method static int          delete_where(array $conditions)
 * @method static float        sum(string $column, array $conditions = [], bool $with_deleted = false)
 * @method static float|null   max(string $column, array $conditions = [], bool $with_deleted = false)
 * @method static float|null   min(string $column, array $conditions = [], bool $with_deleted = false)
 * @method static float|null   avg(string $column, array $conditions = [], bool $with_deleted = false)
 * @method static int          count(bool $with_deleted = false)
 * @method static mixed        first(bool $with_deleted = false)
 * @method static mixed        last(bool $with_deleted = false)
 * @method static mixed        with(mixed $relations)
 * @method static mixed        restore(int $id)
 * @method static bool         exists(mixed $conditions = [], bool $with_deleted = false)
 * @method static array        paginate(int $per_page = 10, int $page = 1, array $conditions = [], bool $with_deleted = false)
 * @method static mixed        truncate()
 * @method static array        order_by(string $column, string $order = 'ASC', bool $with_deleted = false)
 * @method static array        limit(int $number, bool $with_deleted = false)
 * @method static array        get_columns()
 * @method static mixed        bulk_insert(array $data)
 * @method static int          bulk_update(array $data, string $key = '', array $merge_fillable = [])
 * @method static int|string|false insert(array $data)
 * @method static mixed        update(int $id, array $data)
 * @method static mixed        soft_delete(int $id)
 * @method static mixed        delete(int $id)
 * @method static mixed        filter(array $conditions = [], bool $with_deleted = false)
 * @method static mixed        where(string $column, string $operator, mixed $value)
 * @method static mixed        group_by(string $column)
 * @method static mixed        having(string $column, string $operator, mixed $value)
 * @method static mixed        raw(string $sql, array $params = [])
 * @method static mixed        scope(callable $callback)
 * @method static void         transaction()
 * @method static void         commit()
 * @method static void         rollback()
*/
class Model {  
    /**
     * Table Name of the Database
     *
     * @var string
     */
    protected $table = '';

    /**
     * Primary Key of the Database Column
     *
     * @var string
     */
    protected $primary_key = 'id';

    /**
     * Fillable attributes for Mass Assignment
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * Guarded attributes that cannot be mass assigned
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Automatically manage created_at and updated_at timestamps
     *
     * @var boolean
     */
    protected $timestamps = false;

    /**
     * Column name for created_at timestamp
     *
     * @var string
     */
    protected $created_at_column = 'created_at';

    /**
     * Column name for updated_at timestamp
     *
     * @var string
     */
    protected $updated_at_column = 'updated_at';

    /**
     * Column name to use for Soft Delete
     *
     * @var string
     */
    protected $soft_delete_column = 'deleted_at';

    /**
     * Allow Soft Delete of Rows (It will be added in config/config.php later on)
     *
     * @var boolean
     */
    protected $has_soft_delete;

    /**
     * Fillable attributes for Mass Assignment
     *
     * @var array
     */
    protected $with = [];

      /**
     * Class Constructor
     * @return void
     */
    public function __construct()
    {
        $this->timestamps = $this->timestamps ?? config_item('timestamps');
        $this->has_soft_delete = $this->has_soft_delete ?? config_item('soft_delete');
        $this->soft_delete_column = $this->soft_delete_column ?? config_item('soft_delete_column');
        $this->created_at_column = $this->created_at_column ?? config_item('created_at_column');
        $this->updated_at_column = $this->updated_at_column ?? config_item('updated_at_column');
    }

    /**
     * Filter input data to only include fillable attributes
     * 
     * @param array $data Input data array
     * @param array $merge_fillable Additional fillable fields for this operation
     * @return array Filtered data array
     */
    protected function fillable_attributes(array $data, array $merge_fillable = [])
    {
        $fillable = !empty($merge_fillable) 
            ? array_merge($this->fillable, $merge_fillable) 
            : $this->fillable;

        // Case 1: fillable is defined → use whitelist
        if (!empty($fillable)) {
            return array_intersect_key($data, array_flip($fillable));
        }

        // Case 2: guarded is defined → use blacklist (remove guarded fields)
        if (!empty($this->guarded)) {
            return array_diff_key($data, array_flip($this->guarded));
        }

        // Case 3: both empty → allow everything (legacy mode)
        return $data;
    }

    /**
     * Add timestamps to data array if enabled
     *
     * @param array $data Input data array
     * @param bool $is_update Whether this is an update operation (true) or create (false)
     * @return array Data array with timestamps added if enabled
     */
    protected function add_timestamps(array $data, bool $is_update = false)
    {
        if (!$this->timestamps) {
            return $data;
        }

        $now = date('Y-m-d H:i:s');
        if (!$is_update) {
            $data[$this->created_at_column] = $now;
        }
        $data[$this->updated_at_column] = $now;

        return $data;
    }

    /**
     * Query Builder
     *
     * @return Database
     */
    public function _query()
    {
        return $this->db->table($this->table);
    }

    /**
     * Find Single Record
     *
     * @param integer $id
     * @param boolean $with_deleted
     * @return void
     */
    public function _find($id, $with_deleted = false) {
        $this->db->table($this->table);
        $this->apply_soft_delete($with_deleted);
        return $this->db->where($this->primary_key, $id)->get();
    }

    /**
     * Find Single Record by Column
     *
     * @param string $column    
     * @param mixed $value
     * @param boolean $with_deleted
     * @return void
     */
    public function _find_by($column, $value, $with_deleted = false)
    {
        $this->db->table($this->table);
        $this->apply_soft_delete($with_deleted);
        return $this->db->where($column, $value)->get();
    }

    /**
     * Find All Records by Column
     *
     * @param string $column    
     * @param mixed $value
     * @param boolean $with_deleted
     * @return void
     */
    public function _find_all_by($column, $value, $with_deleted = false)
    {
        $this->db->table($this->table);
        $this->apply_soft_delete($with_deleted);
        return $this->db->where($column, $value)->get_all() ?: [];
    }

    /**
     * Get Column Value
     *
     * @param string $column    
     * @param array $conditions
     * @return void
     */
    public function _value($column, $conditions = [])
    {
        $this->db->table($this->table)->select($column);
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        $row = $this->db->get();
        return $row[$column] ?? null;
    }

    /**
     * Get Column Values
     *
     * @param string $column    
     * @param array $conditions
     * @return void
     */
    public function _pluck($column, $conditions = [], $with_deleted = false)
    {
        $this->db->table($this->table)->select($column);
        $this->apply_soft_delete($with_deleted);
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        $rows = $this->db->get_all() ?: [];
        return array_column($rows, $column);
    }

    /**
     * Find First Record Matching Conditions or Create New One
     *
     * @param array $conditions
     * @param array $extra_data
     * @return void
     */
    public function _first_or_create($conditions, $extra_data = [])
    {
        $this->db->table($this->table);
        $this->apply_soft_delete(false);
        $record = $this->db->where($conditions)->get();

        if ($record) {
            return ['record' => $record, 'created' => false];
        }

        $id = $this->_insert(array_merge($conditions, $extra_data));
        return ['record' => $this->_find($id), 'created' => true];
    }

    /**
     * Update existing record matching conditions or create new one
     *
     * @param array $conditions
     * @param array $data
     * @return int ID of the updated or created record
     */
    public function _update_or_create($conditions, $data)
    {
        $this->db->table($this->table);
        $this->apply_soft_delete(false);
        $record = $this->db->where($conditions)->get();

        if ($record) {
            $this->_update($record[$this->primary_key], $data);
            return (int) $record[$this->primary_key];
        }

        return (int) $this->_insert(array_merge($conditions, $data));
    }

    /**
     * Update Records Matching Conditions
     *
     * @param array $conditions
     * @param array $data
     * @return int Number of affected rows
     */
    public function _update_where($conditions, $data)
    {
        $data = $this->fillable_attributes($data);
        if (empty($data)) return 0;

        $data = $this->add_timestamps($data, true);
        return (int) $this->db->table($this->table)->where($conditions)->update($data);
    }
    
    /**
     * Delete Records Matching Conditions
     *
     * @param array $conditions
     * @return int Number of affected rows
     */
    public function _delete_where($conditions)
    {
        return (int) $this->db->table($this->table)->where($conditions)->delete();
    }

    /**
     * Get the sum of a column.
     *
     * @param string $column
     * @param array $conditions
     * @param boolean $with_deleted
     * @return float
     */
    public function _sum($column, $conditions = [], $with_deleted = false)
    {
        $this->db->table($this->table)->select_sum($column, 'aggregate');
        $this->apply_soft_delete($with_deleted);
        if (!empty($conditions)) $this->db->where($conditions);
        $row = $this->db->get();
        return $row['aggregate'] ?? 0;
    }

    /**
     * Get the max value of a column.
     *
     * @param string $column
     * @param array $conditions
     * @param boolean $with_deleted
     * @return float
     */
    public function _max($column, $conditions = [], $with_deleted = false)
    {
        $this->db->table($this->table)->select_max($column, 'aggregate');
        $this->apply_soft_delete($with_deleted);
        if (!empty($conditions)) $this->db->where($conditions);
        $row = $this->db->get();
        return $row['aggregate'] ?? null;
    }

    /**
     * Get the min value of a column.
     *
     * @param string $column
     * @param array $conditions
     * @param boolean $with_deleted
     * @return float
     */
    public function _min($column, $conditions = [], $with_deleted = false)
    {
        $this->db->table($this->table)->select_min($column, 'aggregate');
        $this->apply_soft_delete($with_deleted);
        if (!empty($conditions)) $this->db->where($conditions);
        $row = $this->db->get();
        return $row['aggregate'] ?? null;
    }

    /**
     * Get the average value of a column.
     *
     * @param string $column
     * @param array $conditions
     * @param boolean $with_deleted
     * @return float
     */
    public function _avg($column, $conditions = [], $with_deleted = false)
    {
        $this->db->table($this->table)->select_avg($column, 'aggregate');
        $this->apply_soft_delete($with_deleted);
        if (!empty($conditions)) $this->db->where($conditions);
        $row = $this->db->get();
        return $row['aggregate'] ?? null;
    }

    /**
     * Row Count
     *
     * @param boolean $with_deleted
     * @return void
     */
    public function _count($with_deleted = false) {
        $this->db->table($this->table);
        $this->apply_soft_delete($with_deleted);
        return $this->db->count();
    }

    /**
     * Find First Record
     *
     * @param boolean $with_deleted
     * @return void
     */
    public function _first($with_deleted = false) {
        $this->db->table($this->table);
        $this->apply_soft_delete($with_deleted);
        return $this->db->order_by($this->primary_key, 'ASC')->limit(1)->get();
    }

    /**
     * Find Last Record
     *
     * @param boolean $with_deleted
     * @return void
     */
    public function _last($with_deleted = false) {
        $this->db->table($this->table);
        $this->apply_soft_delete($with_deleted);
        return $this->db->order_by($this->primary_key, 'DESC')->limit(1)->get();
    }

    /**
     * Get All Records
     *
     * @param boolean $with_deleted
     * @return array
     */
    public function _all($with_deleted = false)
    {
        $this->db->table($this->table);
        $this->apply_soft_delete($with_deleted);
        $records = $this->db->get_all();

        if (!empty($this->with) && !empty($records)) {
            $this->load_relations($records);
        }

        $this->with = []; // reset after use
        return $records;
    }

    /**
     * Load related records for eager loading
     *
     * @param array $records
     * @return void
     */
    protected function load_relations(&$records)
    {
        foreach ($records as &$record) {
            foreach ($this->with as $relationName) {
                if (method_exists($this, $relationName)) {
                    $rel = $this->{$relationName}();

                    $this->call->model($rel['related']);

                    switch ($rel['type'] ?? '') {
                        case 'has_one':
                            $record[$relationName] = $this->{$rel['related']}
                                ->_filter([$rel['foreign_key'] => $record[$this->primary_key]])
                                ->get();
                            break;

                        case 'has_many':
                            $record[$relationName] = $this->{$rel['related']}
                                ->_filter([$rel['foreign_key'] => $record[$this->primary_key]])
                                ->get_all();
                            break;

                        case 'belongs_to':
                            $record[$relationName] = $this->{$rel['related']}
                                ->_find($record[$rel['foreign_key']] ?? null);
                            break;

                        case 'many_to_many':
                            $query = "SELECT r.* FROM {$this->{$rel['related']}->table} r
                                      JOIN {$rel['pivot_table']} p 
                                      ON r.{$this->{$rel['related']}->primary_key} = p.{$rel['related_key']}
                                      WHERE p.{$rel['current_key']} = ?";
                            $record[$relationName] = $this->db->raw($query, [$record[$this->primary_key]])
                                                              ->fetchAll(PDO::FETCH_ASSOC);
                            break;
                    }
                }
            }
        }
    }

    /**
     * Eager Load Relationships
     *
     * @param mixed $relations
     * @return $this
     */
    public function _with($relations)
    {
        $this->with = is_array($relations) ? $relations : [$relations];
        return $this;
    }


    /**
     * Restore Soft Deleted Row
     *
     * @param int $id
     * @return void
     */
    public function _restore($id)
    {
        if (!$this->has_soft_delete) return false;

        return $this->db->table($this->table)
                        ->where($this->primary_key, $id)
                        ->update([$this->soft_delete_column => null]);
    }

    /**
     * Check if record exists
     *
     * @param mixed $conditions
     * @param bool $with_deleted
     * @return bool
     */
    public function _exists($conditions = [], $with_deleted = false)
    {
        $this->db->table($this->table);
        $this->apply_soft_delete($with_deleted);
        return !empty($this->db->where($conditions)->get_all());
    }

    /**
     * Paginate results
     *
     * @param int $per_page
     * @param int $page
     * @param array $conditions
     * @param bool $with_deleted
     * @return array
     */
    public function _paginate($per_page = 10, $page = 1, $conditions = [], $with_deleted = false)
    {
        $offset = ($page - 1) * $per_page;
        $this->db->table($this->table);
        $this->apply_soft_delete($with_deleted);
        
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        
        $total = $this->db->count();
        $results = $this->db->table($this->table)->limit($per_page, $offset)->get_all();
        
        return [
            'data' => $results,
            'total' => $total,
            'per_page' => $per_page,
            'current_page' => $page,
            'last_page' => ceil($total / $per_page)
        ];
    }

    /**
     * Empty Table
     *
     * @return void
     */
    public function _truncate() {
        return $this->db->table($this->table)->delete();
    }

    /**
     * Order By
     *
     * @param string $column
     * @param string $order
     * @return void
     */
    public function _order_by($column, $order = 'ASC', $with_deleted = false) {
        $this->db->table($this->table);
        $this->apply_soft_delete($with_deleted);
        return $this->db->order_by($column, $order)->get_all();
    }

    /**
     * Limit
     *
     * @param int $number
     * @return void
     */
    public function _limit($number, $with_deleted = false) {
        $this->db->table($this->table);
        $this->apply_soft_delete($with_deleted);
        return $this->db->limit($number)->get_all();
    }
    
    /**
     * Get Table Columns
     *
     * @return void
     */
    public function _get_columns() {
        $stmt = $this->db->raw("SHOW COLUMNS FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Bulk Insert
     *
     * @param array $data
     * @return void
     */
    public function _bulk_insert($data)
    {
        if (empty($data)) {
            return false;
        }

        foreach ($data as &$row) {
            $row = $this->fillable_attributes($row);
            if (!empty($row) && $this->timestamps) {
                $row = $this->add_timestamps($row);
            }
        }

        return $this->db->table($this->table)->bulk_insert(array_filter($data));
    }

    /**
     * Bulk Update
     *
     * @param array $data
     * @param int $key
     * @return void
     */
    public function _bulk_update($data, $key = '', $merge_fillable = [])
    {
        if (empty($data)) {
            return false;
        }

        $key = $key ?: $this->primary_key;
        $updatedCount = 0;

        foreach ($data as $row) {
            if (empty($row[$key])) {
                continue;
            }

            $id = $row[$key];
            unset($row[$key]);

            $updateData = $this->fillable_attributes($row, $merge_fillable);
            if (empty($updateData)) {
                continue;
            }

            $updateData = $this->add_timestamps($updateData, true);

            $result = $this->db->table($this->table)
                               ->where($key, $id)
                               ->update($updateData);

            if ($result) {
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    /**
     * Insert Record to the Database
     *
     * @param array $data
     * @return int|string|false The inserted ID or false on failure
     */
    public function _insert($data)
    {
        $data = $this->fillable_attributes($data);
        if (empty($data)) {
            return false;
        }

        $data = $this->add_timestamps($data);
        $this->db->table($this->table)->insert($data);
        return $this->db->last_id();
    }

    /**
     * Update Record from the Database
     *
     * @param integer $id
     * @param array $data
     * @return void
     */
    public function _update($id, $data)
    {
        $data = $this->fillable_attributes($data);
        if (empty($data)) {
            return false;
        }

        $data = $this->add_timestamps($data, true);
        return $this->db->table($this->table)
                        ->where($this->primary_key, $id)
                        ->update($data);
    }

    /**
     * Soft Delete. Check the column name to be added in the table. Check protected $soft_delete_column = 'deleted_at';
     *
     * @param integer $id
     * @return void
     */
    public function _soft_delete($id)
    {
        if (!$this->has_soft_delete) {
            return $this->_delete($id);
        }

        return $this->db->table($this->table)
                        ->where($this->primary_key, $id)
                        ->update([$this->soft_delete_column => date('Y-m-d H:i:s')]);
    }

    /**
     * Delete Records from the Database
     *
     * @param integer $id
     * @return void
     */
    public function _delete($id)
    {
        return $this->db->table($this->table)
                        ->where($this->primary_key, $id)
                        ->delete();
    }

    /**
     * Filter using where Clause
     *
     * @param array $conditions
     * @param boolean $with_deleted
     * @return void
     */
    public function _filter($conditions = [], $with_deleted = false) {
        $this->db->table($this->table);
        $this->apply_soft_delete($with_deleted);
        $this->db->where($conditions);
        return $this;
    }

    /**
     * Alias for _filter
     *
     * @param array $conditions
     * @param boolean $with_deleted
     * @return void
     */
    public function _where($conditions = [], $with_deleted = false) {
        return $this->_filter($conditions, $with_deleted);
    }

    /**
     * Group by clause
     *
     * @param string $column
     * @return $this
     */
    public function _group_by($column)
    {
        $this->db->table($this->table);
        $this->db->group_by($column);
        return $this;
    }

    /**
     * Having clause
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return $this
     */
    public function _having($column, $operator, $value)
    {
        $this->db->table($this->table);
        $this->db->having($column, $operator, $value);
        return $this;
    }

    /**
     * Raw SQL Query
     *
     * @param string $sql
     * @param array $params
     * @return void
     */
    public function _raw($sql, $params = []) {
        return $this->db->raw($sql, $params);
    }

    /**
     * Apply Soft Delete when Displaying Records
     *
     * @param boolean $with_deleted
     * @return void
     */
    protected function apply_soft_delete($with_deleted)
    {
        if (!$with_deleted && $this->has_soft_delete) {
            $this->db->where_null($this->soft_delete_column);
        }
    }

    /**
     * ORM Has Many Relationship
     *
     * @param string $related
     * @param string $foreign_key
     * @return boolean
     */
    protected function has_many($related, $foreign_key)
    {
        return ['type' => 'has_many', 'related' => $related, 'foreign_key' => $foreign_key];
    }

    /**
     * ORM Has One Relationship
     *
     * @param string $related
     * @param string $foreign_key
     * @return boolean
     */
    protected function has_one($related, $foreign_key)
    {
        return ['type' => 'has_one', 'related' => $related, 'foreign_key' => $foreign_key];
    }

    /**
     * Many To Many Relationship
     *
     * @param string $related
     * @param string $pivot_table
     * @param string $current_key
     * @param string $related_key
     * @param mixed $current_key
     * @return void
     */
    protected function many_to_many($related, $pivot_table, $current_key, $related_key)
    {
        return [
            'type'         => 'many_to_many',
            'related'      => $related,
            'pivot_table'  => $pivot_table,
            'current_key'  => $current_key,
            'related_key'  => $related_key
        ];
    }

    /**
     * Belong To Relationship
     *
     * @param string $related
     * @param string $foreign_key
     * @return void
     */
    protected function belongs_to($related, $foreign_key)
    {
        return ['type' => 'belongs_to', 'related' => $related, 'foreign_key' => $foreign_key];
    }
    
    /**
     * Scope query
     *
     * @param callable $callback
     * @return $this
     */
    public function _scope($callback)
    {
        call_user_func($callback, $this->db);
        return $this;
    }

    /**
     * Begin Transaction
     *
     * @return void
     */
    public function _transaction()
    {
        $this->db->transaction();
    }

    /**
     * Commit
     *
     * @return void
     */
    public function _commit()
    {
        $this->db->commit();
    }

    /**
     * Rollback
     *
     * @return void
     */
    public function _rollback()
    {
        $this->db->rollback();
    }

     /**
     * magic __get
     *
     * @param string $key
     * @return void
     */
    public function __get($key)
    {
        return lava_instance()->$key;
    }
                            
    /**
     * __call handles dynamic method calls for instance methods
     *
     * @param string $method
     * @param array $args
     * @return void
     */
    public function __call($method, $args)
    {
        $internal = '_' . $method;
        if (method_exists($this, $internal)) {
            return $this->$internal(...$args);
        }
        throw new \BadMethodCallException("Method {$method}() does not exist.");
    }

    /**
     * __callStatic handles dynamic method calls for static methods
     *
     * @param string $method
     * @param array $args
     * @return void
     */
    public static function __callStatic($method, $args)
    {
        $model_name = (new \ReflectionClass(static::class))->getShortName();
        $instance = lava_instance()->$model_name;

        if ($instance === null) {
            $instance = new static();
            lava_instance()->$model_name = $instance;
        }

        $internal = '_' . $method;
        if (!method_exists($instance, $internal)) {
            throw new \BadMethodCallException(
                "{$model_name}::{$method}() does not exist."
            );
        }

        return $instance->$internal(...$args);
    }
}