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
 * Migration Class
 */
class Migration {

    /**
     * Environment variable set by the CLI command to request a dry run.
     * Lets the CLI talk to the framework without needing any route changes.
     */
    const ENV_DRY_RUN = 'LAVA_MIGRATION_DRY_RUN';

    /**
     * Environment variable set by the CLI command (--force) to skip
     * the confirmation prompt of fresh().
     */
    const ENV_FORCE = 'LAVA_MIGRATION_FORCE';

    /**
     * Allowed migration template types
     */
    const TYPES = ['create', 'modify', 'drop'];

    /**
     * LavaLust Super Object
     *
     * @var object
     */
    private $_lava;

    /**
     * Absolute path to the migrations folder (always ends with a separator)
     *
     * @var string
     */
    protected $migrations_folder = '';

    /**
     * Database table used to track applied migrations
     *
     * @var string
     */
    protected $migration_table = '';

    /**
     * Batch number for grouping migrations run together
     *
     * @var int
     */
    protected $current_batch = 0;

    /**
     * Cached flag: the tracking table is known to exist
     *
     * @var bool
     */
    protected $table_ready = false;

    public function __construct()
    {
        $this->_lava = lava_instance();

        $this->_lava->config->load('migration');

        if (!config_item('migration_enabled')) {
            $this->error('Migrations are disabled in the configuration.');
            exit;
        }

        $this->migration_table = (string) config_item('migration_table');

        // The table name is interpolated into SQL, so it must be a plain identifier
        if (!preg_match('/^[A-Za-z0-9_]+$/', $this->migration_table)) {
            $this->error('Invalid migration table name in the configuration.');
            exit;
        }

        $this->migrations_folder = rtrim((string) config_item('migration_path'), '/\\') . DIRECTORY_SEPARATOR;

        if (!is_dir($this->migrations_folder)) {
            mkdir($this->migrations_folder, 0755, true);
        }

        $this->_lava->call->database();

        // NOTE: the tracking table is created lazily (see ensure_migration_table())
        // so read-only operations such as dry runs, status and preview never
        // modify the database.
    }

    /**
     * Create the migration tracking table if it doesn't already exist.
     *
     * @return void
     */
    protected function ensure_migration_table()
    {
        $table = $this->migration_table;

        $this->_lava->db->raw("
            CREATE TABLE IF NOT EXISTS `{$table}` (
                `id`         INT      NOT NULL AUTO_INCREMENT,
                `migration`  INT      NOT NULL,
                `batch`      INT      NOT NULL DEFAULT 1,
                `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `migration_unique` (`migration`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->table_ready = true;
    }

    /**
     * Check (without creating anything) whether the tracking table exists.
     *
     * @return bool
     */
    protected function table_exists()
    {
        if ($this->table_ready) {
            return true;
        }

        $stmt = $this->_lava->db->raw(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = :t",
            ['t' => $this->migration_table]
        );

        $this->table_ready = ((int) $stmt->fetchColumn()) > 0;
        return $this->table_ready;
    }

    /**
     * Whether the CLI asked for a dry run.
     *
     * @return bool
     */
    protected function is_dry_run_requested()
    {
        return getenv(self::ENV_DRY_RUN) === '1';
    }

    /**
     * Create a new migration file from the stub template.
     *
     * The file is prefixed with the next sequential version number
     * (zero-padded to 3 digits) and the class name is derived from
     * the migration name.
     *
     * @param  string $migration_name  Snake_case name (e.g. create_users_table)
     * @param  string $type            'create', 'modify' or 'drop' (default: 'create')
     * @return void
     */
    public function create_migration($migration_name, $type = 'create')
    {
        $migration_name = strtolower(trim(str_replace(['-', ' '], '_', (string) $migration_name)));

        if (!preg_match('/^[a-z][a-z0-9_]*$/', $migration_name)) {
            $this->error("Invalid migration name. Use letters, numbers and underscores (e.g. create_users_table).");
            return;
        }

        $type = $type === null || $type === '' ? 'create' : strtolower((string) $type);
        if (!in_array($type, self::TYPES, true)) {
            $this->error("Invalid migration type \"{$type}\". Allowed: " . implode(', ', self::TYPES) . ".");
            return;
        }

        $class_name = $this->build_class_name($migration_name);

        // Two files with the same class name would cause a fatal "cannot redeclare class"
        foreach ($this->get_migration_files() as $existing) {
            if (strcasecmp($this->get_class_name_from_file($existing), $class_name) === 0) {
                $this->error("A migration named \"{$migration_name}\" already exists (" . basename($existing) . ").");
                return;
            }
        }

        $new_version = str_pad($this->get_latest_migration_version() + 1, 3, '0', STR_PAD_LEFT);
        $filename    = "{$new_version}_{$migration_name}.php";
        $filepath    = $this->migrations_folder . $filename;

        $template = $this->get_migration_template($class_name, $migration_name, $type);

        if (file_put_contents($filepath, $template) !== false) {
            $this->success("Migration created successfully!");
            $this->info("File: {$filename}");
        } else {
            $this->error("Failed to create migration file.");
        }
    }

    /**
     * Generate the migration template based on type.
     *
     * @param  string $class_name     The migration class name
     * @param  string $migration_name The migration name for table reference
     * @param  string $type           Migration type: create, modify, drop
     * @return string
     */
    protected function get_migration_template($class_name, $migration_name, $type)
    {
        // Extract the table name from the migration name:
        //   add_status_to_posts_table -> posts
        //   create_users_table        -> users
        $table_name = 'your_table_name';
        if (preg_match('/^(?:add|remove|update|modify)_.+?_(?:to|from|in)_(.+?)_table$/', $migration_name, $matches)) {
            $table_name = $matches[1];
        } elseif (preg_match('/^(?:create|modify|drop|update|add|remove)_(.+?)_table$/', $migration_name, $matches)) {
            $table_name = $matches[1];
        }

        $up_body = '';
        $down_body = '';

        switch ($type) {
            case 'modify':
                $up_body = <<<EOT
        // Add or modify columns
        \$this->_lava->dbforge->add_column('{$table_name}', [
            'new_column' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => TRUE,
            ]
        ]);
EOT;
                $down_body = <<<EOT
        // Revert changes
        \$this->_lava->dbforge->drop_column('{$table_name}', 'new_column');
EOT;
                break;

            case 'drop':
                $up_body = <<<EOT
        // Drop the table
        \$this->_lava->dbforge->drop_table('{$table_name}');
EOT;
                $down_body = <<<EOT
        // Recreate the table (you'll need to define its structure)
        // \$this->_lava->dbforge->add_field([...]);
        // \$this->_lava->dbforge->create_table('{$table_name}');
EOT;
                break;

            case 'create':
            default:
                $up_body = <<<EOT
        \$this->_lava->dbforge->add_field([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => TRUE,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => TRUE,
            ],
        ]);

        \$this->_lava->dbforge->add_key('id', TRUE);
        \$this->_lava->dbforge->create_table('{$table_name}');
EOT;
                $down_body = <<<EOT
        \$this->_lava->dbforge->drop_table('{$table_name}');
EOT;
                break;
        }

        return <<<EOT
<?php

class {$class_name} {

    private \$_lava;

    public function __construct()
    {
        \$this->_lava = lava_instance();
        \$this->_lava->call->dbforge();
    }

    public function up()
    {
{$up_body}
    }

    public function down()
    {
{$down_body}
    }
}
EOT;
    }

    /**
     * Run all pending migrations in ascending version order.
     *
     * Skips any migration whose version number already exists in the
     * migration table. Records each successfully applied migration.
     * A dry run (argument, or requested by the CLI) changes nothing,
     * including the tracking table.
     *
     * @param  bool $dry_run  If true, only show what would be migrated
     * @return void
     */
    public function migrate($dry_run = false)
    {
        $this->run_pending(null, (bool) $dry_run || $this->is_dry_run_requested());
    }

    /**
     * Run migrations up to (and including) a specific version.
     *
     * @param  int  $target_version  The target version to migrate to
     * @param  bool $dry_run         If true, only show what would be migrated
     * @return void
     */
    public function migrate_to($target_version, $dry_run = false)
    {
        $this->run_pending((int) $target_version, (bool) $dry_run || $this->is_dry_run_requested());
    }

    /**
     * Shared implementation of migrate() and migrate_to().
     *
     * @param  int|null $target_version  Highest version to run, or null for all
     * @param  bool     $dry_run
     * @return void
     */
    protected function run_pending($target_version, $dry_run)
    {
        if ($dry_run) {
            $this->line("Dry run - showing pending migrations (nothing will be changed):", 'yellow');
        } elseif ($target_version !== null) {
            $this->line("Migrating to version {$target_version}...", 'blue');
        } else {
            $this->line("Running pending migrations...", 'blue');
        }

        $applied = $this->get_applied_migrations();
        $files   = $this->get_migration_files();   // already sorted by version
        $count   = 0;

        if (!$dry_run) {
            $this->current_batch = $this->get_next_batch_number();
        }

        foreach ($files as $version => $file) {
            if ($target_version !== null && $version > $target_version) {
                break;
            }

            if (in_array($version, $applied, true)) {
                continue;
            }

            if ($dry_run) {
                $this->info("Would migrate: " . basename($file));
                $count++;
                continue;
            }

            // Stop at the first failure: later migrations may depend on this one
            if (!$this->run_up($file, $version)) {
                return;
            }
            $count++;
        }

        if ($count === 0) {
            $this->info($target_version !== null
                ? "No migrations to run up to version {$target_version}."
                : "All migrations are already up to date.");
        } elseif ($dry_run) {
            $this->info("{$count} migration(s) would be run.");
        } else {
            $this->success("{$count} migration(s) completed successfully.");
        }
    }

    /**
     * Execute the up() method of one migration file and record it.
     *
     * @param  string $file
     * @param  int    $version
     * @return bool   TRUE on success
     */
    protected function run_up($file, $version)
    {
        require_once $file;
        $class_name = $this->get_class_name_from_file($file);

        if (!class_exists($class_name, false)) {
            $this->error("Class {$class_name} not found in " . basename($file));
            return false;
        }

        $migration = new $class_name();

        if (!method_exists($migration, 'up')) {
            $this->error("Migration {$class_name} missing up() method.");
            return false;
        }

        try {
            $migration->up();
        } catch (Throwable $e) {
            $this->error("Failed: " . basename($file) . " - " . $e->getMessage());
            return false;
        }

        $this->record_migration($version, $this->current_batch);
        $this->success("Migrated: " . basename($file));

        return true;
    }

    /**
     * Rollback the most recently applied migration batch.
     *
     * @param  int  $steps  Number of batches to rollback (default: 1)
     * @return void
     */
    public function rollback($steps = 1)
    {
        $steps = max(1, (int) $steps);

        $applied = $this->get_applied_migrations_with_batch();
        if (empty($applied)) {
            $this->info("No migrations to rollback.");
            return;
        }

        // Get unique batches in descending order
        $batches = array_unique(array_column($applied, 'batch'));
        rsort($batches);
        $batches_to_rollback = array_slice($batches, 0, $steps);

        $this->line("Rolling back batch(es): " . implode(', ', $batches_to_rollback), 'yellow');

        foreach ($batches_to_rollback as $batch) {
            $migrations_in_batch = array_filter($applied, fn($m) => $m['batch'] == $batch);

            // Sort by version descending (newest first)
            usort($migrations_in_batch, fn($a, $b) => $b['migration'] <=> $a['migration']);

            foreach ($migrations_in_batch as $record) {
                $this->rollback_single($record['migration']);
            }
        }
    }

    /**
     * Rollback every migration newer than the given version.
     *
     * @param  int $version  The version to rollback down to
     * @return void
     */
    public function rollback_to($version)
    {
        $version = (int) $version;
        $applied = $this->get_applied_migrations();

        if (empty($applied)) {
            $this->info("No migrations to rollback.");
            return;
        }

        $to_rollback = array_values(array_filter($applied, fn($v) => $v > $version));

        if (empty($to_rollback)) {
            $this->info("No migrations to rollback to version {$version}.");
            return;
        }

        rsort($to_rollback);

        $this->line("Rolling back to version {$version}...", 'yellow');

        foreach ($to_rollback as $v) {
            $this->rollback_single($v);
        }
    }

    /**
     * Rollback a single migration by version.
     *
     * @param  int $version  The version to rollback
     * @return bool
     */
    protected function rollback_single($version)
    {
        $version = (int) $version;
        $files   = $this->get_migration_files();

        if (!isset($files[$version])) {
            $this->warning("Migration file not found for version {$version}.");
            return false;
        }

        $file = $files[$version];
        require_once $file;
        $class_name = $this->get_class_name_from_file($file);

        if (!class_exists($class_name, false)) {
            $this->error("Class {$class_name} not found.");
            return false;
        }

        $migration = new $class_name();

        if (!method_exists($migration, 'down')) {
            $this->error("Migration {$class_name} missing down() method.");
            return false;
        }

        try {
            $migration->down();
        } catch (Throwable $e) {
            $this->error("Failed to roll back " . basename($file) . " - " . $e->getMessage());
            return false;
        }

        $this->remove_migration($version);
        $this->success("Rolled back: " . basename($file));

        return true;
    }

    /**
     * Refresh the database by rolling back all migrations then re-running them.
     *
     * @return void
     */
    public function refresh()
    {
        $this->line("Refreshing all migrations...", 'yellow');
        $this->rollback_all();
        $this->run_pending(null, false);
    }

    /**
     * Reset the database by rolling back all migrations without re-running.
     *
     * @return void
     */
    public function reset()
    {
        $this->line("Resetting all migrations...", 'yellow');
        $this->rollback_all();
        $this->success("Database reset complete.");
    }

    /**
     * Drop all tables and re-run all migrations.
     *
     * WARNING: This will delete all data! CLI only, and it asks for
     * confirmation unless the CLI passed --force.
     *
     * @return void
     */
    public function fresh()
    {
        // Never allow a destructive, unconfirmable operation from a web request
        if (!(defined('IS_CLI') && IS_CLI)) {
            $this->error("fresh can only be run from the command line.");
            return;
        }

        $this->warning("This will drop ALL tables and re-run migrations!");

        if (getenv(self::ENV_FORCE) !== '1') {
            echo "Are you sure? Type 'yes' to continue: ";
            $handle = fopen("php://stdin", "r");
            $line   = $handle ? fgets($handle) : false;

            if ($line === false || strtolower(trim($line)) !== 'yes') {
                $this->info("Operation cancelled.");
                return;
            }
        }

        $this->line("Dropping all tables...", 'yellow');

        // Base tables only (views can't be removed with DROP TABLE)
        $stmt   = $this->_lava->db->raw("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        // Foreign keys would otherwise block dropping in arbitrary order
        $this->_lava->db->raw("SET FOREIGN_KEY_CHECKS = 0");
        try {
            foreach ($tables as $table) {
                $this->_lava->db->raw("DROP TABLE IF EXISTS `{$table}`");
                $this->info("Dropped: {$table}");
            }
        } finally {
            $this->_lava->db->raw("SET FOREIGN_KEY_CHECKS = 1");
        }

        // The tracking table was dropped too: recreate it
        $this->table_ready = false;
        $this->ensure_migration_table();

        $this->success("All tables dropped.");
        $this->run_pending(null, false);
    }

    /**
     * Rollback every applied migration in reverse version order.
     *
     * @return void
     */
    public function rollback_all()
    {
        $applied = $this->get_applied_migrations();
        if (empty($applied)) {
            $this->info("No migrations to rollback.");
            return;
        }

        $this->line("Rolling back all migrations...", 'yellow');

        rsort($applied);

        foreach ($applied as $version) {
            $this->rollback_single($version);
        }
    }

    /**
     * Display a status table of all discovered migration files.
     *
     * @param  bool $show_batch  Whether to show batch numbers
     * @return void
     */
    public function status($show_batch = true)
    {
        $files = $this->get_migration_files();

        if (empty($files)) {
            $this->info("No migration files found.");
            return;
        }

        $applied_lookup = [];
        foreach ($this->get_applied_migrations_with_batch() as $record) {
            $applied_lookup[(int) $record['migration']] = $record['batch'];
        }

        $this->line("Migration Status", 'blue');
        echo str_repeat("-", 85) . "\n";
        echo str_pad("Version", 10) . str_pad("Migration Name", 45);
        if ($show_batch) {
            echo str_pad("Batch", 8);
        }
        echo "Status\n";
        echo str_repeat("-", 85) . "\n";

        $pending   = 0;
        $completed = 0;

        foreach ($files as $version => $file) {
            $is_applied = isset($applied_lookup[$version]);
            $status     = $is_applied ? "APPLIED" : "PENDING";
            $color      = $is_applied ? 'green' : 'yellow';

            $is_applied ? $completed++ : $pending++;

            echo str_pad((string) $version, 10) . str_pad(basename($file), 45);
            if ($show_batch) {
                echo str_pad($is_applied ? (string) $applied_lookup[$version] : '-', 8);
            }
            echo $this->color($status, $color) . "\n";
        }

        echo str_repeat("-", 85) . "\n";
        echo "Total: " . count($files) . " | ";
        echo $this->color("Applied: {$completed}", 'green') . " | ";
        echo $this->color("Pending: {$pending}", 'yellow') . "\n";
    }

    /**
     * Show the up() code of pending migrations.
     * This is a best-effort preview and does not execute anything.
     *
     * @return void
     */
    public function preview()
    {
        $applied = $this->get_applied_migrations();
        $files   = $this->get_migration_files();

        $this->line("Migration Preview (pending migrations)", 'blue');
        echo str_repeat("=", 70) . "\n";

        $count = 0;
        foreach ($files as $version => $file) {
            if (in_array($version, $applied, true)) {
                continue;
            }

            $count++;
            echo "\n";
            $this->line("File: " . basename($file), 'cyan');

            $content = file_get_contents($file);
            if (preg_match('/public function up\(\).*?\{(.*?)\n    \}/s', $content, $matches)) {
                echo "UP method:\n";
                echo trim($matches[1]) . "\n";
            }
            echo str_repeat("-", 70) . "\n";
        }

        if ($count === 0) {
            $this->info("No pending migrations to preview.");
        }
    }

    // ====================== HELPER METHODS ======================

    /**
     * Discover migration files as [version => absolute path], sorted by version.
     * Files that don't start with "<digits>_" are ignored.
     *
     * @return array
     */
    protected function get_migration_files()
    {
        $found = glob($this->migrations_folder . '*.php');
        $map   = [];

        foreach ($found ?: [] as $file) {
            $version = $this->get_version_from_file($file);

            if ($version === null) {
                continue;
            }

            if (isset($map[$version])) {
                $this->warning("Duplicate version {$version}: " . basename($file) . " ignored.");
                continue;
            }

            $map[$version] = $file;
        }

        ksort($map, SORT_NUMERIC);
        return $map;
    }

    /**
     * Extract the numeric version from a migration filename.
     *
     * @param  string $file
     * @return int|null
     */
    protected function get_version_from_file($file)
    {
        if (preg_match('/^(\d+)_.+\.php$/', basename($file), $m)) {
            return (int) $m[1];
        }
        return null;
    }

    /**
     * Return the highest version number found in the migrations folder.
     *
     * @return int
     */
    protected function get_latest_migration_version()
    {
        $files = $this->get_migration_files();
        return empty($files) ? 0 : max(array_keys($files));
    }

    /**
     * Fetch all version numbers recorded in the migration table.
     * Returns an empty list (without creating the table) if it doesn't exist.
     *
     * @return array  Flat array of integer version numbers
     */
    protected function get_applied_migrations()
    {
        if (!$this->table_exists()) {
            return [];
        }

        $stmt = $this->_lava->db->raw("SELECT migration FROM `{$this->migration_table}` ORDER BY migration ASC");
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Fetch all applied migrations with their batch numbers.
     *
     * @return array  Array of ['migration' => int, 'batch' => int]
     */
    protected function get_applied_migrations_with_batch()
    {
        if (!$this->table_exists()) {
            return [];
        }

        $stmt = $this->_lava->db->raw("SELECT migration, batch FROM `{$this->migration_table}` ORDER BY migration ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['migration'] = (int) $row['migration'];
            $row['batch']     = (int) $row['batch'];
        }
        unset($row);

        return $rows;
    }

    /**
     * Get the next batch number.
     *
     * @return int
     */
    protected function get_next_batch_number()
    {
        if (!$this->table_exists()) {
            return 1;
        }

        $stmt = $this->_lava->db->raw("SELECT MAX(batch) FROM `{$this->migration_table}`");
        $max  = $stmt->fetchColumn();
        return ($max === null || $max === false) ? 1 : (int) $max + 1;
    }

    /**
     * Insert a version number into the migration table to mark it as applied.
     * This is the only place the tracking table gets created on a normal run.
     *
     * @param  int $version  The version number to record
     * @param  int $batch    The batch number
     * @return void
     */
    protected function record_migration($version, $batch = 1)
    {
        if (!$this->table_ready) {
            $this->ensure_migration_table();
        }

        $this->_lava->db->raw(
            "INSERT INTO `{$this->migration_table}` (migration, batch) VALUES (:migration, :batch)",
            ['migration' => $version, 'batch' => $batch]
        );
    }

    /**
     * Delete a version number from the migration table to mark it as rolled back.
     *
     * @param  int $version  The version number to remove
     * @return void
     */
    protected function remove_migration($version)
    {
        $this->_lava->db->raw(
            "DELETE FROM `{$this->migration_table}` WHERE migration = :migration",
            ['migration' => $version]
        );
    }

    /**
     * Build a class name from a migration name (create_users_table -> Create_users_table).
     *
     * @param  string $migration_name
     * @return string
     */
    protected function build_class_name($migration_name)
    {
        return ucfirst(str_replace(['-', ' '], '_', $migration_name));
    }

    /**
     * Derive the migration class name from its filename, using the same
     * rule as create_migration() so hyphenated names still resolve.
     *
     * @param  string $file  Path to the migration file
     * @return string
     */
    protected function get_class_name_from_file($file)
    {
        if (preg_match('/^\d+_(.+)\.php$/', basename($file), $m)) {
            return $this->build_class_name($m[1]);
        }
        return basename($file, '.php');
    }

    // ====================== CLI OUTPUT HELPERS ======================

    /**
     * Print a line of text in the given color.
     *
     * @param  string $text
     * @param  string $color
     * @return void
     */
    protected function line($text, $color = 'white')
    {
        echo $this->color($text, $color) . PHP_EOL;
    }

    /**
     * Print a success message.
     *
     * @param  string $text
     * @return void
     */
    protected function success($text)
    {
        echo $this->color("✔ " . $text, 'green') . PHP_EOL;
    }

    /**
     * Print an informational message.
     *
     * @param  string $text
     * @return void
     */
    protected function info($text)
    {
        echo $this->color("→ " . $text, 'cyan') . PHP_EOL;
    }

    /**
     * Print a warning message.
     *
     * @param  string $text
     * @return void
     */
    protected function warning($text)
    {
        echo $this->color("⚠ " . $text, 'yellow') . PHP_EOL;
    }

    /**
     * Print an error message (exits with status 1 when running in the CLI).
     *
     * @param  string $text
     * @return void
     */
    protected function error($text)
    {
        echo $this->color("✘ " . $text, 'red') . PHP_EOL;
        if (defined('IS_CLI') && IS_CLI) exit(1);
    }

    /**
     * Wrap text in ANSI color escape codes for CLI output.
     *
     * @param  string $text
     * @param  string $color
     * @return string
     */
    protected function color($text, $color = 'white')
    {
        if (!(defined('IS_CLI') && IS_CLI)) {
            return $text;
        }

        $colors = [
            'red'    => "\033[31m",
            'green'  => "\033[32m",
            'yellow' => "\033[33m",
            'blue'   => "\033[34m",
            'cyan'   => "\033[36m",
            'white'  => "\033[37m",
        ];

        return ($colors[$color] ?? '') . $text . "\033[0m";
    }
}