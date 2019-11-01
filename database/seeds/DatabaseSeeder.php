<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         Schema::disableForeignKeyConstraints();
         $this->call(UniversitiesTableSeeder::class);
         $this->call(FacultiesTableSeeder::class);
         $this->call(OrganizationsTableSeeder::class);
         $this->call(UsersTableSeeder::class);
         $this->call(AdministratorsTableSeeder::class);
         $this->call(TeachersTableSeeder::class);
         $this->call(SchoolProgramsTableSeeder::class);
         $this->call(StudentsTableSeeder::class);
         $this->call(SchoolPeriodsTableSeeder::class);
         $this->call(SubjectsTableSeeder::class);
         $this->call(SchoolPeriodSubjectTeacherTableSeeder::class);
         $this->call(SchoolProgramSubjectTableSeeder::class);
         $this->call(SchoolPeriodStudentTableSeeder::class);
         $this->call(StudentSubjectTableSeeder::class);
         $this->call(SchedulesTableSeeder::class);
         $this->call(DegreesTableSeeder::class);
         Schema::enableForeignKeyConstraints();
    }
}
