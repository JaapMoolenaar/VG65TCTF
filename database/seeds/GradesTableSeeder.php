<?php

use Illuminate\Database\Seeder;

class GradesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table( 'grades' )->delete();

        DB::table( 'grades' ) ->insert([
          'id'   => 1,
          'name' => 'Pre-K',
        ]);
        DB::table( 'grades' ) ->insert([
          'id'   => 2,
          'name' => 'K',
        ]);
        DB::table( 'grades' ) ->insert([
          'id'   => 3,
          'name' => '1',
        ]);
        DB::table( 'grades' ) ->insert([
          'id'   => 4,
          'name' => '2',
        ]);
        DB::table( 'grades' ) ->insert([
          'id'   => 5,
          'name' => '3',
        ]);
        DB::table( 'grades' ) ->insert([
          'id'   => 6,
          'name' => '4',
        ]);
        DB::table( 'grades' ) ->insert([
          'id'   => 7,
          'name' => '5',
        ]);
        DB::table( 'grades' ) ->insert([
          'id'   => 8,
          'name' => '6',
        ]);
        DB::table( 'grades' ) ->insert([
          'id'   => 9,
          'name' => '7',
        ]);
        DB::table( 'grades' ) ->insert([
          'id'   => 10,
          'name' => '8',
        ]);
        DB::table( 'grades' ) ->insert([
          'id'   => 11,
          'name' => '9',
        ]);
        DB::table( 'grades' ) ->insert([
          'id'   => 12,
          'name' => '10',
        ]);
        DB::table( 'grades' ) ->insert([
          'id'   => 13,
          'name' => '11',
        ]);
        DB::table( 'grades' ) ->insert([
          'id'   => 14,
          'name' => '12',
        ]);
    }
}
