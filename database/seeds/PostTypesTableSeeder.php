<?php

use Illuminate\Database\Seeder;

class PostTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table( 'post_types' )->delete();

      DB::table( 'post_types' ) ->insert([
        'id'   => 1,
        'name' => 'image',
      ]);
      DB::table( 'post_types' )->insert([
        'id'   => 2,
        'name' => 'link',
      ]);
      DB::table( 'post_types' )->insert([
        'id'   => 3,
        'name' => 'text',
      ]);
    }
}