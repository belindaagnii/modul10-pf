<?php

namespace Database\Seeders;

use App\models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Employee::factory()->count(200)->create();

    }
}
 // DB::table('employees')->insert([
        //     [
        //         'firstname'=>'Purnama',
        //         'lastname'=>'Anaking',
        //         'email'=>'purnama.anaking@gmail.com',
        //         'age'=> 20,
        //         'position_id'=> 1
        //     ],
        //     [
        //         'firstname'=>'Adzanil',
        //         'lastname'=>'Rachmadi',
        //         'email'=>'adzanil.rachmadi@gmail.com',
        //         'age'=> 25,
        //         'position_id'=> 2
        //     ],
        //     [
        //         'firstname'=>'Berlian',
        //         'lastname'=>'Rahmy',
        //         'email'=>'berlian.rahmy@gmail.com',
        //         'age'=> 23,
        //         'position_id'=> 3
        //     ],
        // ]);
