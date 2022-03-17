<?php

use Illuminate\Database\Seeder;
use App\SchoolPeriod;
class SchoolPeriodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SchoolPeriod::query()->truncate();
        SchoolPeriod::create([
            'cod_school_period'=>'IV-2019',
            'start_date'=>'2020-10-01',
            'end_date'=>'2020-12-31',
            'withdrawal_deadline'=>'2020-10-31',
            'inscription_start_date'=>'2020-09-30',
            'inscription_visible'=>false,
            'organization_id'=>'ICT',
            'load_notes'=>false,
            'project_duty'=>3.4,
            'final_work_duty'=>3.5,
        ]);
        SchoolPeriod::create([
            'cod_school_period'=>'I-2020',
            'start_date'=>'2021-01-01',
            'end_date'=>'2021-03-31',
            'withdrawal_deadline'=>'2021-01-31',
            'inscription_start_date'=>'2021-12-31',
            'inscription_visible'=>false,
            'organization_id'=>'ICT',
            'load_notes'=>false,
            'project_duty'=>3.4,
            'final_work_duty'=>3.5,
        ]);
        SchoolPeriod::create([
            'cod_school_period'=>'II-2020',
            'start_date'=>'2021-04-01',
            'end_date'=>'2021-06-30',
            'withdrawal_deadline'=>'2021-04-30',
            'inscription_start_date'=>'2021-03-31',
            'inscription_visible'=>false,
            'organization_id'=>'ICT',
            'load_notes'=>false,
            'project_duty'=>3.4,
            'final_work_duty'=>3.5,
        ]);
        SchoolPeriod::create([
            'cod_school_period'=>'III-2020',
            'start_date'=>'2021-10-01',
            'end_date'=>'2021-12-30',
            'withdrawal_deadline'=>'2020-07-30',
            'inscription_start_date'=>'2020-06-30',
            'inscription_visible'=>true,
            'organization_id'=>'ICT',
            'load_notes'=>false,
            'project_duty'=>3.4,
            'final_work_duty'=>3.5,
        ]);
    }
}
