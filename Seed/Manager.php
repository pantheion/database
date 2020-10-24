<?php

namespace Pantheion\Database\Seed;

use Pantheion\Filesystem\Exception\FileDoesNotExistException;
use Pantheion\Filesystem\File;

/**
 * Manages the table seeding
 */
class Manager
{
    const SEEDS_PATH = "database/seeds";

    /**
     * Calls the seed function for
     * the Seed class
     *
     * @param string $filename 
     * @return void
     */
    public function seed(string $filename)
    {
        $this->instanciate($filename)->seed();
    }

    /**
     * Tries to instanciate the file
     * passed as argument and returns
     * an instance of it
     *
     * @param string $filename
     * @throws FileDoesNotExistException
     * @return Seed
     */
    protected function instanciate(string $filename)
    {
        try {
            $file = File::get(Manager::SEEDS_PATH . DIRECTORY_SEPARATOR . $filename . ".php");
            
            require_once $file->fullpath;
            return new $filename;
        } catch (FileDoesNotExistException $exception) {
            echo "Seeder file named {$filename} does not exist";
            die();
        }
    }
}