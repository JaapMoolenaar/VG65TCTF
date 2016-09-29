<?php

use Illuminate\Database\Seeder;

class SubjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table( 'subjects' )->delete();

        DB::table( 'subjects' ) ->insert([
          'id'   => 1,
          'name' => 'Physical Education',
        ]);
        DB::table( 'subjects' ) ->insert([
          'id'   => 2,
          'name' => 'Health',
        ]);
        DB::table( 'subjects' ) ->insert([
          'id'   => 3,
          'name' => 'Music',
        ]);
        DB::table( 'subjects' ) ->insert([
          'id'   => 4,
          'name' => 'Art',
        ]);
        DB::table( 'subjects' ) ->insert([
          'id'   => 5,
          'name' => 'Foreign Language',
        ]);
        DB::table( 'subjects' ) ->insert([
          'id'   => 6,
          'name' => 'Social Studies',
        ]);
        DB::table( 'subjects' ) ->insert([
          'id'   => 7,
          'name' => 'English Language Arts',
        ]);
        DB::table( 'subjects' ) ->insert([
          'id'   => 8,
          'name' => 'Counseling',
        ]);
        DB::table( 'subjects' ) ->insert([
          'id'   => 9,
          'name' => 'STEM',
        ]);
        DB::table( 'subjects' ) ->insert([
          'id'   => 10,
          'name' => 'Science',
        ]);
    }
}