<?php

use Illuminate\Database\Seeder;
use App\Teacher;


class TeachersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        Teacher::query()->truncate();
        Teacher::create([
            'id'=>1,
            'teacher_type'=>'CON', //CON JUB REG
            'category'=>'INS', //INS ASI AGR ASO TIT INV
            'dedication'=>'EXC'
        ]);
        Teacher::create([
            'id'=>3,
            'teacher_type'=>'CON', //CON JUB REG
            'category'=>'TIT', //INS ASI AGR ASO TIT INV
            'dedication'=>'TC'
        ]);
        Teacher::create([
            'id'=>4,
            'teacher_type'=>'CON', //CON JUB REG
            'category'=>'INV', //INS ASI AGR ASO TIT INV
            'dedication'=>'CON'
        ]);
        Teacher::create([
            'id'=>5,
            'teacher_type'=>'CON', //CON JUB REG
            'category'=>'ASI', //INS ASI AGR ASO TIT INV
            'dedication'=>'MT'
        ]);
        Teacher::create([
            'id'=>6,
            'teacher_type'=>'CON', //CON JUB REG
            'category'=>'AGR', //INS ASI AGR ASO TIT INV
            'dedication'=>'EXC'
        ]);
        /*Teacher::create([
            'id'=>19,
            'teacher_type'=>'CON', //CON JUB REG
            'category'=>'INS', //INS ASI AGR ASO TIT INV
            'dedication'=>'EXC'
        ]);
        Teacher::create([
            'id'=>20,
            'teacher_type'=>'CON', //CON JUB REG
            'category'=>'TIT', //INS ASI AGR ASO TIT INV
            'dedication'=>'TC'
        ]);
        Teacher::create([
            'id'=>21,
            'teacher_type'=>'CON', //CON JUB REG
            'category'=>'INV', //INS ASI AGR ASO TIT INV
            'dedication'=>'CON'
        ]);
        Teacher::create([
            'id'=>22,
            'teacher_type'=>'CON', //CON JUB REG
            'category'=>'ASI', //INS ASI AGR ASO TIT INV
            'dedication'=>'MT'
        ]);
        Teacher::create([
            'id'=>23,
            'teacher_type'=>'CON', //CON JUB REG
            'category'=>'AGR', //INS ASI AGR ASO TIT INV
            'dedication'=>'EXC'
        ]);*/
    }
}
