<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Reset the admins table
         */
        if (\App::environment('local')) {
            \DB::table('admins')->truncate();
        }

        \DB::table('admins')->insert([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'phone' => '',
            'image' => '',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin@dmin123'),
            'is_active' => 1,
            'is_system_admin' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
