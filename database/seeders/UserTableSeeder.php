<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            foreach( config("app.roles") as $user=>$role){
                User::create([
                    'name' =>  $user,
                    'role' =>  $role,
                    'email' => "{$user}@mail.com",
                    'email_verified_at' => now(),
                    'password' => bcrypt('password'), // password
                ]);
            }

    }
}
