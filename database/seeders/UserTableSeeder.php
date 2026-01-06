<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = array(
            [
                'name' => 'Administrator',
                'email' => 'admin@mail.com',
                'password' => bcrypt('123456'),
                'foto' => '/img/user.jpg',
                'level' => 1,
                'branch_id' => 1,
            ],
            [
                'name' => 'Testing User',
                'email' => 'astro@mail.com',
                'password' => bcrypt('123456'),
                'foto' => '/img/user.jpg',
                'level' => 2,
                'branch_id' => 1,
            ]
        );

        array_map(function (array $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }, $users);
    }
}
