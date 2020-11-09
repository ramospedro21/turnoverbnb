<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $now = new \DateTime();

        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Administrator',
            'email' => 'administrator@turnover.com',
            'password' =>  bcrypt('123456'),
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }
}
