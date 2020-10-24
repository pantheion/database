<?php

namespace Pantheion\Database\Migration;

/**
 * Basis for a migration file
 */
interface Migration
{
    /**
     * Performs the migration up code
     *
     * @return void
     */
    public function up();

    /**
     * Performs the migration down code
     *
     * @return void
     */
    public function down();
}