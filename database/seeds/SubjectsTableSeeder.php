<?php

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Subject::create(['name' => 'Wiskunde B']);
        Subject::create(['name' => 'Engels']);
    }
}
