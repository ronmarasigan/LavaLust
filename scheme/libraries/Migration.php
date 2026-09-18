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
     * LavaLust Super Object
     *
     * @var object
     */
    private $_lava;

    /**
     * Absolute path to the migrations folder
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

    public function __construct()
    {   
        $this->_lava = lava_instance();

        $this->_lava->config->load('migration');

        if (!config_item('migration_enabled')) {
            $this->error('Migrations are disabled in the configuration.');
            exit;
        }

        $this->migrations_folder = config_item('migration_path');
        $this->migration_table   = config_item('migration_table');

        if (!is_dir($this->migrations_folder)) {
            mkdir($this->migrations_folder, 0755, true);
        }

        $this->_lava->call->database();

        // Guarantee the migrations table exists before any query touches it
        $this->ensure_migration_table();
    }

    /**
     * Create the migration tracking table if it doesn't already exist.
     *
     * The table will have columns for an auto-incrementing ID, the
     * migration version number, and a timestamp of when it was applied.
     *
     * @return void
     */
    protected function ensure_migration_table(): void
    {
        $table = $this->migration_table;

        $this->_lava->db->raw("
            CREATE TABLE IF NOT EXISTS `{$table}` (
                `id`         INT      NOT NULL AUTO_INCREMENT,
                `migration`  INT      NOT NULL,
                `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `migration_unique` (`migration`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
    }

    /**
     * Create a new timestamped migration file from the stub template.
     *
     * The file will be prefixed with the next sequential version number
     * (zero-padded to 3 digits) and the class name will be derived from
     * the provided migration name.
     *
     * @param  string $migration_name  Snake_case name for the migration (e.g. create_users_table)
     * @return void
     */
    public function create_migration($migration_name)
    {
        $latest_version = $this->get_latest_migration_version();
        $new_version    = str_pad($latest_version + 1, 3, '0', STR_PAD_LEFT);

        $filename = "{$new_version}_{$migration_name}.php";
        $filepath = $this->migrations_folder . $filename;

        $class_name = ucfirst(str_replace(['-', ' '], '_', $migration_name));

        $template = <<<EOT
<?php

class {$class_name} {

    private \$_lava;
    protected \$dbforge;

    public function __construct()
    {
        \$this->_lava = lava_instance();
        \$this->_lava->call->dbforge();
    }

    public function up()
    {
        // Write your "UP" migration here
        \$this->_lava->dbforge->add_field([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE
            ]
        ]);

        \$this->_lava->dbforge->add_key('id', TRUE);
        \$this->_lava->dbforge->create_table('your_table_name');
    }

    public function down()
    {
        // Write your "DOWN" migration here
        \$this->_lava->dbforge->drop_table('your_table_name');
    }
}
EOT;

        if (file_put_contents($filepath, $template)) {
            $this->success("Migration created successfully!");
            $this->info("File: {$filename}");
        } else {
            $this->error("Failed to create migration file.");
        }
    }

    /**
     * Run all pending migrations in ascending version order.
     *
     * Skips any migration whose version number already exists in the
     * migration table. Records each successfully applied migration.
     *
     * @return void
     */
    public function migrate()
    {
        $this->line("Running pending migrations...", 'blue');

        $applied    = $this->get_applied_migrations();
        $files      = glob($this->migrations_folder . '*.php');
        $migrated   = 0;

        foreach ($files as $file) {
            $version = (int) substr(basename($file), 0, 3);

            if (!in_array($version, $applied)) {
                require_once $file;
                $class_name = $this->get_class_name_from_file($file);

                $migration = new $class_name();
                $migration->up();

                $this->record_migration($version);
                $this->success("✓ Migrated: " . basename($file));
                $migrated++;
            }
        }

        if ($migrated === 0) {
            $this->info("All migrations are already up to date.");
        } else {
            $this->success("{$migrated} migration(s) completed successfully.");
        }
    }

    /**
     * Rollback only the most recently applied migration.
     *
     * Calls the migration's down() method and removes its record from
     * the migration table.
     *
     * @return void
     */
    public function rollback()
    {
        $applied = $this->get_applied_migrations();
        if (empty($applied)) {
            $this->info("No migrations to rollback.");
            return;
        }

        $latest_version = max($applied);
        $pattern = $this->migrations_folder . str_pad($latest_version, 3, '0', STR_PAD_LEFT) . '_*.php';
        $files = glob($pattern);

        if (empty($files)) {
            $this->error("Migration file not found.");
            return;
        }

        $file = $files[0];
        require_once $file;
        $class_name = $this->get_class_name_from_file($file);

        $migration = new $class_name();
        $migration->down();

        $this->remove_migration($latest_version);
        $this->success("Rolled back: " . basename($file));
    }

    /**
     * Refresh the database by rolling back all migrations then re-running them.
     *
     * Equivalent to calling rollback_all() followed by migrate(). Useful
     * during development to rebuild the schema from scratch.
     *
     * @return void
     */
    public function refresh()
    {
        $this->line("Refreshing all migrations...", 'yellow');
        $this->rollback_all();
        $this->migrate();
    }

    /**
     * Rollback every applied migration in reverse version order.
     *
     * Calls each migration's down() method from newest to oldest and
     * clears all records from the migration table.
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

        foreach (array_reverse($applied) as $version) {
            $pattern = $this->migrations_folder . str_pad($version, 3, '0', STR_PAD_LEFT) . '_*.php';
            $files = glob($pattern);

            if (!empty($files)) {
                $file = $files[0];
                require_once $file;
                $class_name = $this->get_class_name_from_file($file);

                $migration = new $class_name();
                $migration->down();

                $this->remove_migration($version);
                $this->success("Rolled back: " . basename($file));
            }
        }
    }

    /**
     * Display a status table of all discovered migration files.
     *
     * Each row shows the version number, filename, and whether the
     * migration has been applied (APPLIED) or not yet run (PENDING).
     *
     * @return void
     */
    public function status()
    {
        $applied = $this->get_applied_migrations();
        $files   = glob($this->migrations_folder . '*.php');

        $this->line("Migration Status", 'blue');
        echo str_pad("Version", 10) . str_pad("Migration Name", 45) . "Status\n";
        echo str_repeat("-", 70) . "\n";

        foreach ($files as $file) {
            $version = (int) substr(basename($file), 0, 3);
            $name    = basename($file);
            $status  = in_array($version, $applied) ? "APPLIED" : "PENDING";
            $color   = in_array($version, $applied) ? 'green' : 'yellow';

            echo str_pad($version, 10) . str_pad($name, 45) . $this->color($status, $color) . "\n";
        }
    }

    // ====================== HELPER METHODS ======================

    /**
     * Return the highest version number found in the migrations folder.
     *
     * Reads the 3-digit numeric prefix from each filename. Returns 0 when
     * no migration files exist yet.
     *
     * @return int
     */
    protected function get_latest_migration_version()
    {
        $files = glob($this->migrations_folder . '*.php');
        if (empty($files)) return 0;

        $versions = array_map(fn($f) => (int) substr(basename($f), 0, 3), $files);
        return max($versions);
    }

    /**
     * Fetch all version numbers that have been recorded in the migration table.
     *
     * @return array  Flat array of integer version numbers
     */
    protected function get_applied_migrations()
    {
        $stmt = $this->_lava->db->raw("SELECT migration FROM {$this->migration_table}");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Insert a version number into the migration table to mark it as applied.
     *
     * @param  int $version  The 3-digit version number to record
     * @return void
     */
    protected function record_migration($version)
    {
        $this->_lava->db->raw("INSERT INTO {$this->migration_table} (migration) VALUES (:migration)", 
            ['migration' => $version]);
    }

    /**
     * Delete a version number from the migration table to mark it as rolled back.
     *
     * @param  int $version  The 3-digit version number to remove
     * @return void
     */
    protected function remove_migration($version)
    {
        $this->_lava->db->raw("DELETE FROM {$this->migration_table} WHERE migration = :migration", 
            ['migration' => $version]);
    }

    /**
     * Derive the migration class name from its filename.
     *
     * Strips the 3-digit version prefix and returns the remainder as the
     * class name (e.g. "001_create_users_table.php" → "create_users_table").
     *
     * @param  string $file  Absolute path to the migration file
     * @return string
     */
    protected function get_class_name_from_file($file)
    {
        $base_name = basename($file, '.php');
        $parts = explode('_', $base_name, 2);
        return $parts[1] ?? $base_name;
    }

    // ====================== CLI OUTPUT HELPERS ======================

    /**
     * Print a line of text in the given color.
     *
     * @param  string $text
     * @param  string $color  One of: red, green, yellow, blue, cyan, white
     * @return void
     */
    protected function line($text, $color = 'white')
    {
        echo $this->color($text, $color) . PHP_EOL;
    }

    /**
     * Print a success message prefixed with a checkmark in green.
     *
     * @param  string $text
     * @return void
     */
    protected function success($text)
    {
        echo $this->color("✔ " . $text, 'green') . PHP_EOL;
    }

    /**
     * Print an informational message prefixed with an arrow in cyan.
     *
     * @param  string $text
     * @return void
     */
    protected function info($text)
    {
        echo $this->color("→ " . $text, 'cyan') . PHP_EOL;
    }

    /**
     * Print a warning message prefixed with a warning sign in yellow.
     *
     * @param  string $text
     * @return void
     */
    protected function warning($text)
    {
        echo $this->color("⚠ " . $text, 'yellow') . PHP_EOL;
    }

    /**
     * Print an error message prefixed with a cross in red.
     *
     * Exits the process with code 1 when running in CLI context.
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
     * Returns the text unmodified when not running in a CLI context,
     * since ANSI codes render as garbage in a browser.
     *
     * @param  string $text
     * @param  string $color  One of: red, green, yellow, blue, cyan, white
     * @return string
     */
    protected function color($text, $color = 'white')
    {
        if (!(defined('IS_CLI') && IS_CLI)) {
            return $text; // No colors in browser
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