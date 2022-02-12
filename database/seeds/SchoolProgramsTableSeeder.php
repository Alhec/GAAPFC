<?php

use Illuminate\Database\Seeder;
use App\SchoolProgram;

class SchoolProgramsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SchoolProgram::query()->truncate();
        SchoolProgram::create([
            'school_program_name' => 'Especializacion',
            'num_cu' => 3,
            'organization_id'=>'ICT',
            'duration'=>3,
            'conducive_to_degree'=>true,
            'min_duration'=>1,
            'min_num_cu_final_work'=>3
        ]);
        SchoolProgram::create([
            'school_program_name' => 'Maestria',
            'num_cu' => 6,
            'organization_id'=>'ICT',
            'duration'=>3,
            'conducive_to_degree'=>true,
            'min_duration'=>1,
            'min_num_cu_final_work'=>3
        ]);
        SchoolProgram::create([
            'school_program_name' => 'Doctorado',
            'num_cu' => 9,
            'organization_id'=>'ICT',
            'duration'=>3,
            'conducive_to_degree'=>true,
            'min_duration'=>2,
            'min_num_cu_final_work'=>3,
            'doctoral_exam'=>true,
            'min_cu_to_doctoral_exam'=>3
        ]);
        SchoolProgram::create([
            'school_program_name' => 'Ampliacion',
            'grant_certificate' => true,
            'organization_id'=>'ICT',
            'conducive_to_degree'=>false,
        ]);
        /*SchoolProgram::create([
            'school_program_name' => 'Especializacion',
            'num_cu' => 3,
            'organization_id'=>'C',
            'duration'=>3,
            'conducive_to_degree'=>true,
            'min_duration'=>1,
            'min_num_cu_final_work'=>3
        ]);
        SchoolProgram::create([
            'school_program_name' => 'Maestria',
            'num_cu' => 6,
            'organization_id'=>'C',
            'duration'=>3,
            'conducive_to_degree'=>true,
            'min_duration'=>1,
            'min_num_cu_final_work'=>3
        ]);
        SchoolProgram::create([
            'school_program_name' => 'Doctorado',
            'num_cu' => 9,
            'organization_id'=>'C',
            'duration'=>3,
            'conducive_to_degree'=>true,
            'min_duration'=>2,
            'min_num_cu_final_work'=>3,
            'doctoral_exam'=>true,
            'min_cu_to_doctoral_exam'=>3
        ]);
        SchoolProgram::create([
            'school_program_name' => 'Perfeccionamiento',
            'grant_certificate' => true,
            'organization_id'=>'C',
            'conducive_to_degree'=>false,
        ]);*/
    }
}
