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
 * @since Version 4
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/**
 * Class Migration
 */
class Migration {

    /**
     * LavaLust Super Object
     *
     * @var object
     */
    private $_lava;

    /**
     * Migration folder
     *
     * @var string
     */
    protected $migrations_folder = '';

    /**
     * Migration Table
     *
     * @var string
     */
    protected $migration_table = '';

    /**
     * Constructor
     */
    public function __construct()
    {   
        $this->_lava = lava_instance();

        $this->_lava->config->load('migration');

        if(! config_item('migration_enabled')) {
            show_error('Migrations is disabled or set up incorrectly.');
        }

        $this->migrations_folder = config_item('migration_path');

        $this->migration_table = config_item('migration_table');

        if (! file_exists($this->migrations_folder)) {
            mkdir($this->migrations_folder, 0755, true);
        }

        $this->_lava->call->database();
        
    }

    /**
     * Create Migration
     *
     * @param string $migration_name
     * @return void
     */
    public function create_migration($migration_name)
    {
        $latest_version = $this->get_latest_migration_version();

        $new_version = str_pad($latest_version + 1, 3, '0', STR_PAD_LEFT);

        $filename = "{$new_version}_{$migration_name}.php";
        $filepath = $this->migrations_folder . $filename;

        $migration_class = ucfirst($migration_name);
        $template = <<<EOT
    <?php

    class {$migration_class} {
        
        private \$_lava;
        protected \$dbforge;

        public function __construct()
        {
            \$this->_lava = lava_instance();
            \$this->_lava->call->dbforge();
        }

        public function up()
        {
            // Define the table structure here
            \$this->_lava->dbforge->add_field(array(
                'id' => array(
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'auto_increment' => TRUE,
                )
            ));
            \$this->_lava->dbforge->add_key('id', TRUE);
            \$this->_lava->dbforge->create_table('your_table_name');
        }

        public function down()
        {
            // Drop the table
            \$this->_lava->dbforge->drop_table('your_table_name');
        }
    }

    EOT;

        if (file_put_contents($filepath, $template)) {
            echo "Migration {$filename} created successfully.<br>";
        } else {
            echo "Error: Could not create migration file.<br>";
        }
    }

    /**
     * Get the Latest Migration
     *
     * @return mixed
     */
    protected function get_latest_migration_version()
    {
        $files = glob($this->migrations_folder . '*.php');
        if (!$files) {
            return 0;
        }

        $versions = array_map(function($file) {
            return (int) substr(basename($file), 0, 3);
        }, $files);

        return max($versions);
    }

    /**
     * Migrate ALl Waiting Migration
     *
     * @return void
     */
    public function migrate()
    {
        $applied_migrations = $this->get_applied_migrations();
        $migrations = glob($this->migrations_folder . '*.php');

        foreach ($migrations as $migration_file) {
            $version = (int) substr(basename($migration_file), 0, 3);
            if (!in_array($version, $applied_migrations)) {
                require_once $migration_file;
                $class_name = $this->get_class_name_from_file($migration_file);
                $migration = new $class_name($this->_lava->db);
                $migration->up();
                $this->record_migration($version);
            }
        }
    }

    /**
     * Roolback Migration
     *
     * @return void
     */
    public function rollback()
    {
        $applied_migrations = $this->get_applied_migrations();
        if (empty($applied_migrations)) {
            echo "No migrations to roll back.\n";
            return;
        }

        $latest_version = max($applied_migrations);

        $migration_file = $this->migrations_folder . str_pad($latest_version, 3, '0', STR_PAD_LEFT) . '_*.php';
        $files = glob($migration_file);

        if (count($files) === 1) {
            require_once $files[0];
            $class_name = $this->get_class_name_from_file($files[0]);
            $migration = new $class_name($this->_lava->db);
            $migration->down();
            $this->remove_migration($latest_version);
        }
    }

    /**
     * Check Database for Applied Migrations
     *
     * @return mixed
     */
    protected function get_applied_migrations()
    {
        $stmt = $this->_lava->db->raw("SELECT migration FROM {$this->migration_table}");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Save Migration to Database
     *
     * @param mixed $version
     * @return void
     */
    protected function record_migration($version)
    {
        $this->_lava->db->raw("INSERT INTO {$this->migration_table} (migration) VALUES (:migration)", ['migration' => $version]);
    }

    /**
     * Remove Migration from the Database
     *
     * @param mixed $version
     * @return void
     */
    protected function remove_migration($version)
    {
        $this->_lava->db->raw("DELETE FROM {$this->migration_table} WHERE migration = :migration", ['migration' => $version]);
    }

    /**
     * Get Class Name from File
     *
     * @param string $file
     * @return void
     */
    protected function get_class_name_from_file($file)
    {
    $base_name = basename($file, '.php');
    
    $parts = explode('_', $base_name, 2);
    
    $class_name = $parts[1];
    
    return $class_name;
    }
}
