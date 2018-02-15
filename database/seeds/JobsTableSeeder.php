<?php

use Illuminate\Database\Seeder;

class JobsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('jobs')->insert([
            'user_id' => 1,
            'name' => '前端开发工程师',
            'description' => '前端前端'
        ]);
        DB::table('jobs')->insert([
            'user_id' => 2,
            'name' => 'PHP开发工程师',
            'description' => 'PHP'
        ]);
    }
}
