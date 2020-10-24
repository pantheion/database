<?php

namespace Pantheion\Database\Seed;

/**
 * Represents a table seed
 */
interface Seed
{
    /**
     * Performs the seeding in the table
     *
     * @return void
     */
    public function seed();
}