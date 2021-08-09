<?php

use Illuminate\Database\Seeder;

class RemindersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('reminders')->updateOrInsert(
            [
                'id' => '1',
                'name' => '1 hour before',
                'unix_value' => 3600
            ]
        );

        DB::table('reminders')->updateOrInsert(
            [
                'id' => '2',
                'name' => '1 day before',
                'unix_value' => (3600 * 24)
            ]
        );

        DB::table('reminders')->updateOrInsert(
            [
                'id' => '3',
                'name' => '1 week before',
                'unix_value' => (3600 * 24 * 7)
            ]
        );

        DB::table('reminders')->updateOrInsert(
            [
                'id' => '4',
                'name' => '2 weeks before',
                'unix_value' => (3600 * 24 * 7 * 2)
            ]
        );
    }
}
