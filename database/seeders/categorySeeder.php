<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class categorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('categories')->insert([
            'name' => 'All',
        ]);
        DB::table('categories')->insert([
            'name' => 'Hair',
        ]);
        DB::table('categories')->insert([
            'name' => 'Makeup',
        ]);
        DB::table('categories')->insert([
            'name' => 'Skin Care',
        ]);
        DB::table('categories')->insert([
            'name' => 'Body Care',
        ]);
        DB::table('categories')->insert([
            'name' => 'Nails',
        ]);
        DB::table('categories')->insert([
            'name' => 'Tattoo&Henna',
        ]);
    }
}
