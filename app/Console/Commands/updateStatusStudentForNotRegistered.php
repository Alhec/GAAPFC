<?php

namespace App\Console\Commands;

use App\Administrator;
use App\Log;
use App\Organization;
use App\SchoolPeriod;
use App\SchoolPeriodStudent;
use App\Student;
use Illuminate\Console\Command;

/**
 * @package : Commands
 * @author : Hector Alayon
 * @version : 1.0
 */
class updateStatusStudentForNotRegistered extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:status-student';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update student status to des-b for not enrolling in the school period on time';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    const logUpdateStudent = 'Actualizo la entidad student para el usuario con id ';
    const statusDESB = ' a un estatus de DES-B';

    /**
     * Este comando se ejecuta automáticamente en un periodo mensual, para actualizar los estatus de los estudiantes que
     * no se hayan inscrito a tiempo en el periodo escolar de sus respectivas organizaciones a un status de
     * desincorporación tipo b.
     */
    public function handle()
    {
        $organizations = Organization::getOrganizations();
        if (!is_numeric($organizations) && count($organizations)>0 ){
            foreach ($organizations->toArray() as $organization){
                $schoolPeriod = SchoolPeriod::getCurrentSchoolPeriod($organization['id']);
                if (!is_numeric($schoolPeriod) && count($schoolPeriod)>0){
                    if (($schoolPeriod[0]['inscription_start_date']<=now())&&
                        ($schoolPeriod[0]['inscription_visible']==false)){
                        $studentInscription=SchoolPeriodStudent::getSchoolPeriodStudentBySchoolPeriod(
                            $schoolPeriod[0]['id'],$organization['id']);
                        if (!is_numeric($studentInscription)&&count($studentInscription)>0){
                            $allStudent = Student::getAllStudentToDegree($organization['id']);
                            if (!is_numeric($allStudent)&&count($allStudent)>0){
                                $allStudentId=array_column( $allStudent->toArray(),'id');
                                $studentInscriptionId=array_column( $studentInscription->toArray(),'id');
                                $studentsNotInscription=array_diff($allStudentId,$studentInscriptionId);
                                foreach ($studentsNotInscription as $studentNotInscription){
                                    $student = Student::getStudentById($studentNotInscription,$organization['id']);
                                    if (!is_numeric($student)&&count($student)>0){
                                        $student[0]['current_status']='DES-B';
                                        $result =Student::updateStudent($studentNotInscription,$student->toArray()[0]);
                                        if (!is_numeric($result)){
                                            SchoolPeriodStudent::addSchoolPeriodStudentLikeArray(
                                                [
                                                    "student_id"=>$studentNotInscription,
                                                    "school_period_id"=>$schoolPeriod[0]['id'],
                                                    "status"=>'DES-B',
                                                    'pay_ref'=>null,
                                                    'financing'=>null,
                                                    'financing_description'=>null,
                                                    'amount_paid'=>0,
                                                    'test_period'=>false
                                                ]
                                            );
                                            $admin = Administrator::getPrincipalCoordinator($organization['id']);
                                            if (!is_numeric($admin) && count($admin)>0){
                                                Log::addLog($admin[0]['id'],self::logUpdateStudent.
                                                    $student[0]['user_id'].self::statusDESB);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
