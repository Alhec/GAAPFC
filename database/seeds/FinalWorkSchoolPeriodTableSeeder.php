<?php

use Illuminate\Database\Seeder;
use App\FinalWorkSchoolPeriod;

class FinalWorkSchoolPeriodTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        FinalWorkSchoolPeriod::query()->truncate();
        FinalWorkSchoolPeriod::create([
            'status'=>'APPROVED',
            'final_work_id'=>1,
            'school_period_student_id'=>2,
        ]);
        FinalWorkSchoolPeriod::create([
            'status'=>'APPROVED',
            'final_work_id'=>2,
            'school_period_student_id'=>2,
        ]);
        FinalWorkSchoolPeriod::create([
            'status'=>'APPROVED',
            'final_work_id'=>3,
            'school_period_student_id'=>4,
        ]);
        FinalWorkSchoolPeriod::create([
            'status'=>'APPROVED',
            'final_work_id'=>4,
            'school_period_student_id'=>7,
        ]);
        FinalWorkSchoolPeriod::create([
            'status'=>'APPROVED',
            'final_work_id'=>5,
            'school_period_student_id'=>7,
        ]);
    }
}
