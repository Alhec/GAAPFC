<?php

use Illuminate\Database\Seeder;
use App\Student;

class StudentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Student::query()->truncate();
        Student::create([
            'school_program_id'=>1,
            'user_id'=>7,
            'student_type'=>"REG", // REG EXT AMP
            'home_university'=>'Universidad Central de Venezuela',
            'is_ucv_teacher'=>true,
            'is_available_final_work' => true,
            'guide_teacher_id'=>3,
            'end_program'=>true,
            'current_status'=>'ENDED'
        ]);
        Student::create([
            'school_program_id'=>2,
            'user_id'=>8,
            'student_type'=>"REG", // REG EXT AMP
            'home_university'=>'Universidad Central de Venezuela',
            'is_ucv_teacher'=>false,
            'is_available_final_work' => false,
            'guide_teacher_id'=>4
        ]);
        Student::create([
            'school_program_id'=>3,
            'user_id'=>9,
            'student_type'=>"REG", // REG EXT AMP
            'home_university'=>'Universidad Central de Venezuela',
            'is_ucv_teacher'=>true,
            'is_available_final_work' => false,
            'guide_teacher_id'=>5,
            'credits_granted'=>3
        ]);
        Student::create([
            'school_program_id'=>4,
            'user_id'=>10,
            'student_type'=>"REG", // REG EXT AMP
            'home_university'=>'Universidad Central de Venezuela',
            'is_ucv_teacher'=>false,
            'is_available_final_work' => false,
            'guide_teacher_id'=>6
        ]);
        Student::create([
            'school_program_id'=>1,
            'user_id'=>11,
            'student_type'=>"REG", // REG EXT AMP
            'home_university'=>'Universidad Central de Venezuela',
            'is_ucv_teacher'=>false,
            'is_available_final_work' => false,
            'guide_teacher_id'=>1
        ]);
        Student::create([
            'school_program_id'=>2,
            'user_id'=>12,
            'student_type'=>"REG", // REG EXT AMP
            'home_university'=>'Universidad Central de Venezuela',
            'is_ucv_teacher'=>true,
            'is_available_final_work' => false,
            'guide_teacher_id'=>3
        ]);
        Student::create([
            'school_program_id'=>3,
            'user_id'=>13,
            'student_type'=>"REG", // REG EXT AMP
            'home_university'=>'Universidad Central de Venezuela',
            'is_ucv_teacher'=>false,
            'is_available_final_work' => false,
            'guide_teacher_id'=>4
        ]);
        Student::create([
            'school_program_id'=>4,
            'user_id'=>14,
            'student_type'=>"REG", // REG EXT AMP
            'home_university'=>'Universidad Central de Venezuela',
            'is_ucv_teacher'=>true,
            'is_available_final_work' => false,
            'guide_teacher_id'=>5
        ]);
        Student::create([
            'school_program_id'=>1,
            'user_id'=>15,
            'student_type'=>"REG", // REG EXT AMP
            'home_university'=>'Universidad Central de Venezuela',
            'is_ucv_teacher'=>false,
            'is_available_final_work' => true,
            'guide_teacher_id'=>6
        ]);
        Student::create([
            'school_program_id'=>2,
            'user_id'=>16,
            'student_type'=>"REG", // REG EXT AMP
            'home_university'=>'Universidad Central de Venezuela',
            'is_ucv_teacher'=>false,
            'is_available_final_work' => false,
            'guide_teacher_id'=>1
        ]);
        Student::create([
            'school_program_id'=>2,
            'user_id'=>7,
            'student_type'=>"REG", // REG EXT AMP
            'home_university'=>'Universidad Central de Venezuela',
            'is_ucv_teacher'=>true,
            'is_available_final_work' => false,
            'guide_teacher_id'=>3
        ]);
    }
}
