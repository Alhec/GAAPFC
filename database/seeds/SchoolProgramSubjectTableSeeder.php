<?php

use Illuminate\Database\Seeder;
use App\SchoolProgramSubject;

class SchoolProgramSubjectTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SchoolProgramSubject::query()->truncate();
        SchoolProgramSubject::create([
            'school_program_id'=>1,
            'subject_id'=>1,
            'type'=>'OP',
        ]);
        SchoolProgramSubject::create([
            'school_program_id'=>1,
            'subject_id'=>2,
            'type'=>'OB',
        ]);
        SchoolProgramSubject::create([
            'school_program_id'=>1,
            'subject_id'=>3,
            'type'=>'EL',
        ]);
        SchoolProgramSubject::create([
            'school_program_id'=>2,
            'subject_id'=>5,
            'type'=>'OP',
        ]);
        SchoolProgramSubject::create([
            'school_program_id'=>2,
            'subject_id'=>6,
            'type'=>'OB',
        ]);
        SchoolProgramSubject::create([
            'school_program_id'=>2,
            'subject_id'=>7,
            'type'=>'EL',
        ]);
        SchoolProgramSubject::create([
            'school_program_id'=>2,
            'subject_id'=>8,
            'type'=>'EL',
        ]);
        SchoolProgramSubject::create([
            'school_program_id'=>3,
            'subject_id'=>1,
            'type'=>'OP',
        ]);
        SchoolProgramSubject::create([
            'school_program_id'=>3,
            'subject_id'=>2,
            'type'=>'OB',
        ]);
        SchoolProgramSubject::create([
            'school_program_id'=>3,
            'subject_id'=>3,
            'type'=>'EL',
        ]);
        SchoolProgramSubject::create([
            'school_program_id'=>3,
            'subject_id'=>4,
            'type'=>'EL',
        ]);
    }
}