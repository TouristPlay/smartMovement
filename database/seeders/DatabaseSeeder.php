<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;



class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cities')->insert([
            'name' => 'Саранск',
            'slug' => 'saransk',
            'city_id' => 42,
            'latitude' => '54.206539',
            'longitude' => '45.175620'
        ]);
    }
}
