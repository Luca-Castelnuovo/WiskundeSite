<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::create([
            'name' => 'studentAccount',
            'email' => 'studentAccount@gmail.com',
            'password' => app('hash')->make('fooBar1234'),
            'role' => 'student',
        ]);

        User::create([
            'name' => 'teacherAccount',
            'email' => 'teacherAccount@gmail.com',
            'password' => app('hash')->make('fooBar1234'),
            'role' => 'teacher',
        ]);

        User::create([
            'name' => 'adminAccount',
            'email' => 'adminAccount@gmail.com',
            'password' => app('hash')->make('fooBar1234'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'unlimitedAccount',
            'email' => 'unlimitedAccount@gmail.com',
            'password' => app('hash')->make('fooBar1234'),
            'role' => '*',
        ]);
    }
}
