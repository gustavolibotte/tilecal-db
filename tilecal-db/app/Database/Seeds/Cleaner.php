<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Cleaner extends Seeder
{
    public function run()
    {
        helper(['filesystem']);
        $paths = config('Paths');
        delete_files($paths->uploadedFilesDirectory);
    }
}
