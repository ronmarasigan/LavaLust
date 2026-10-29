<?php

class Initial_setup {

    private $_lava;

    public function __construct()
    {
        $this->_lava = lava_instance();
        $this->_lava->call->dbforge();
    }

    public function up()
    {
        if ($this->_lava->dbforge->table_exists('migrations')) {
            return;
        }

        $this->_lava->dbforge
            ->add_field([
                'id' => [
                    'type'           => 'INT',
                    'unsigned'       => TRUE,
                    'auto_increment' => TRUE,
                    'null'           => FALSE,
                ],
                'migration' => [
                    'type'     => 'INT',
                    'null'     => FALSE,
                ],
                'batch' => [
                    'type'     => 'INT',
                    'null'     => FALSE,
                ],
                'applied_at' => [
                    'type'    => 'DATETIME',
                    'null'    => FALSE,
                    'default' => 'CURRENT_TIMESTAMP',
                ],
            ])
            ->add_key('id', primary: TRUE)
            ->add_key('migration', unique: TRUE, name: 'migration_unique')
            ->create_table('migrations');
    }

    public function down()
    {
        // Intentionally left as a no-op.
        // Dropping the migrations table would destroy all migration history
        // and break every subsequent rollback. Only drop manually if you
        // are wiping the entire database.
    }
}