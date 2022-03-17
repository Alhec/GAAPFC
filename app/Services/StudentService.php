<?php
/**
 * Created by PhpStorm.
 * User: halayon
 * Date: 29/08/19
 * Time: 02:41 PM
 */

namespace App\Services;

use App\Degree;
use App\Equivalence;
use App\FinalWork;
use App\Log;
use App\Roles;
use App\SchoolPeriodStudent;
use App\SchoolPeriodSubjectTeacher;
use App\SchoolProgram;
use App\Student;
use App\StudentSubject;
use App\Subject;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @package : Services
 * @author : Hector Alayon
 * @version : 1.0
 */
class StudentService
{
    const taskError = 'No se puede proceder con la tarea';
    const busyCredential = 'Identificacion o Correo ya registrados';
    const notFoundUser = 'Usuario no encontrado';
    const invalidTeacher = 'Profesor guia invalido';
    const invalidSchoolProgram = 'Programa escolar invalido';
    const taskPartialError = 'No se pudo proceder con la tarea en su totalidad';
    const notSendEmail = 'No se pudo enviar el correo electronico';
    const studentInProgram = 'El estudiante no ha culminado su programa escolar actual';
    const noAction = "No esta permitido realizar esa accion";
    const studentProgram = "El estudiante esta cursando un programa academico";
    const studentHasProgram = "El estudiante ya curso el programa escolar";
    const invalidEquivalences = "Equivalencias invalidas";
    const unauthorized = "Unauthorized";
    const notWarningStudent="Todos los estudiantes estan en un estatus regular";
    const ok = "OK";
    const logCreateStudent = 'Creo la entidad student para ';
    const logUpdateStudent = 'Actualizo la entidad student para ';
    const logDeleteStudent = 'Elimino la entidad student para ';

    /**
     * Valida que se cumpla las restricciones:
     * *guide_teacher_id: numerico
     * *student_type: requerido, máximo 3 y finaliza en REG, EXT, AMP, PER, PDO o ACT
     * *home_university: requerido y máximo 100
     * *current_postgraduate: máximo 100
     * *type_income: máximo 30
     * *is_ucv_teacher: booleano y requerido
     * *credits_granted: numérico y requerido
     * *with_work: booleano
     * *degrees.*.degree_obtained: requerido, máximo 3 y finaliza en TSU, TCM, Dr, Esp, Ing, MSc o Lic
     * *degrees.*.degree_name: requerido y máximo 50
     * *degrees.*.degree_description: máximo 200
     * *degrees.*.university: requerido y máximo 50
     * *equivalences.*.subject_id: requerido y numérico
     * *equivalences.*.qualification: requerido y numérico
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validate(Request $request)
    {
        $request->validate([
            'guide_teacher_id'=>'numeric',
            'student_type'=>'required|max:3|ends_with:REG,EXT,AMP,PER,PDO,ACT',
            'home_university'=>'required|max:100',
            'current_postgraduate'=>'max:100',
            'type_income'=>'max:30',
            'is_ucv_teacher'=>'boolean|required',
            'credits_granted'=>'numeric|required',
            'with_work'=>'boolean',
            'degrees.*.degree_obtained'=>'required|max:3|ends_with:TSU,TCM,Dr,Esp,Ing,MSc,Lic',
            'degrees.*.degree_name'=>'required|max:50',
            'degrees.*.degree_description'=>'max:200',
            'degrees.*.university'=>'required|max:100',
            'equivalences.*.subject_id'=>'required|numeric',
            'equivalences.*.qualification'=>'required|numeric',
        ]);
    }

    /**
     * Valida que se cumpla las restricciones:
     * *school_program_id:requerido y numérico
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validateSchoolProgramId(Request $request){
        $request->validate([
            'school_program_id'=>'required|numeric',
        ]);
    }

    /**
     * Agrega grados asociados a un estudiante con el método Degree::addDegree($degree) y agrega equivalencias de una
     * asignatura con el método
     * Equivalence::addEquivalence([
     * 'student_id'=>$studentId,
     * 'subject_id'=>$equivalence['subject_id'],
     * 'qualification'=>$equivalence['qualification']
     * ])
     * @param Request $request Objeto con los datos de la petición
     * @param string $studentId id del estudiante
     * @return integer en caso de existir un error devolvera 0.
     */
    public static function addEquivalencesAndDegrees(Request $request,$studentId)
    {
        if (isset($request['degrees'])){
            foreach ($request['degrees'] as $degree){
                $degree['student_id']=$studentId;
                $result = Degree::addDegree($degree);
                if (is_numeric($result)&& $result == 0){
                    return 0;
                }
            }
        }
        if (isset($request['equivalences'])){
            foreach($request['equivalences'] as $equivalence){
                $result = Equivalence::addEquivalence([
                    'student_id'=>$studentId,
                    'subject_id'=>$equivalence['subject_id'],
                    'qualification'=>$equivalence['qualification']
                ]);
                if (is_numeric($result) && $result == 0){
                    return 0;
                }
            }
        }
    }

    /**
     * Agrega la entidad estudiante a un usuario con el siguiente método
     * Student::addStudent([
     * 'school_program_id'=>$request['school_program_id'],
     * 'user_id'=>$userId,
     * 'guide_teacher_id'=>$request['guide_teacher_id'],
     * 'student_type'=>$request['student_type'],
     * 'home_university'=>$request['home_university'],
     * 'current_postgraduate'=>$request['current_postgraduate'],
     * 'type_income'=>$request['type_income'],
     * 'is_ucv_teacher'=>$request['is_ucv_teacher'],
     * 'is_available_final_work'=>false,
     * 'credits_granted'=>$request['credits_granted'],
     * 'with_work'=>$request['with_work'],
     * 'end_program'=>false,
     * 'test_period'=>false,
     * 'current_status'=>'REG'
     * ])
     * @param integer $userId id del usuario que se le agregara la entidad estudiante.
     * @param Request $request Contiene los datos del estudiante
     * @return integer en caso de existir un error devolvera 0.
     */
    public static function addStudent($userId,Request $request)
    {
        return Student::addStudent([
            'school_program_id'=>$request['school_program_id'],
            'user_id'=>$userId,
            'guide_teacher_id'=>$request['guide_teacher_id'],
            'student_type'=>$request['student_type'],
            'home_university'=>$request['home_university'],
            'current_postgraduate'=>$request['current_postgraduate'],
            'type_income'=>$request['type_income'],
            'is_ucv_teacher'=>$request['is_ucv_teacher'],
            'is_available_final_work'=>false,
            'credits_granted'=>$request['credits_granted'],
            'with_work'=>$request['with_work'],
            'end_program'=>false,
            'test_period'=>false,
            'current_status'=>'REG'
        ]);
    }

    /**
     * Verifica que las asignaturas a las cuales se realizarán equivalencias pertenezcan al programa escolar y tengan
     * una nota válida.
     * @param string $organizationId Id de la organiación
     * @param array $subjects lista de asignaturas a validar
     * @param integer $schoolProgramId id del programa escolar a donde pertenecen las asignaturas a validar
     * @return integer|boolean Devuelve un booleano si las equivalencias son validas en caso de existir un error
     * devolvera 0.
     */
    public static function validateEquivalences($organizationId,$subjects,$schoolProgramId)
    {
        $subjectsInBd=Subject::getSubjectsBySchoolProgram($schoolProgramId,$organizationId);
        if (is_numeric($subjects) && $subjects==0){
            return 0;
        }
        if (count($subjectsInBd)<1){
            return false;
        }
        $subjectsId=array_column($subjectsInBd->toArray(),'id');
        foreach ($subjects as $subject){
            if (!in_array($subject['subject_id'],$subjectsId)){
                return false;
            }
            if ($subject['qualification']>20 ||$subject['qualification']<0){
                return false;
            }
        }
        return true;
    }

    /**
     * Agrega un usuario estudiante y se envía un correo al usuario, con el método
     * self::addStudent($userId,$request)
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @param integer $userId id del usuario
     * @return Response|User de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera correcta
     * devolvera el objeto user.
     */
    public static function createStudent(Request $request, $organizationId, $userId)
    {
        $studentId=self::addStudent($userId,$request);
        if (is_numeric($studentId)&&$studentId==0){
            return response()->json(['message' => self::taskPartialError], 500);
        }
        $rol = Roles::addRol(['user_id'=>$userId,'user_type'=>'S']);
        if (is_numeric($rol)&&$rol==0){
            return response()->json(['message' => self::taskPartialError], 500);
        }
        $result = self::addEquivalencesAndDegrees($request,$studentId);
        if (is_numeric($result)&&$result==0){
            return response()->json(['message' => self::taskPartialError], 500);
        }
        $log = Log::addLog(auth('api')->user()['id'],self::logCreateStudent.$request['first_name'].
            ' '.$request['first_surname']);
        if (is_numeric($log)&&$log==0){
            return response()->json(['message' => self::taskPartialError], 500);
        }
        $result = EmailService::userCreate($userId,$organizationId,'S');
        if ($result==0){
            return response()->json(['message'=>self::notSendEmail],206);
        }
        return UserService::getUserById($userId,'S',$organizationId);
    }

    /**
     * Agrega una entidad usuario y estudiante nueva al sistema haciendo uso de los métodos
     * UserService::addUser($request,'S',$organizationId) y self::addStudent($userId,$request)
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @return Response|User de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera correcta
     * devolvera el objeto user.
     */
    public static function addNewStudent(Request $request,$organizationId)
    {
        self::validateSchoolProgramId($request);
        self::validate($request);
        if (isset($request['guide_teacher_id'])){
            $existTeacher=User::existUserById($request['guide_teacher_id'],'T',$organizationId);
        }else{
            $existTeacher=true;
        }
        $existSchoolProgram = SchoolProgram::existSchoolProgramById($request['school_program_id'],$organizationId);
        if ((is_numeric($existTeacher)&&$existTeacher==0)||(is_numeric($existSchoolProgram)&&$existSchoolProgram==0)){
            return response()->json(['message'=>self::taskError],500);
        }
        if (!$existTeacher){
            return response()->json(['message'=>self::invalidTeacher],206);
        }
        if (!$existSchoolProgram){
            return response()->json(['message'=>self::invalidSchoolProgram],206);
        }
        if (isset($request['equivalences'])){
            $validEquivalence =self::validateEquivalences($organizationId,$request['equivalences'],
                $request['school_program_id']);
            if (!$validEquivalence){
                return response()->json(['message'=>self::invalidEquivalences],206);
            }
        }
        $userId = UserService::addUser($request,'S',$organizationId);
        if (is_string($userId)&&$userId =='busy_credential'){
            $userByCredentials = User::getUserByIdentification($request['identification'],$organizationId);
            $userByEmail = User::getUserByEmail($request['email'],$organizationId);
            if ((is_numeric($userByCredentials)&& $userByCredentials==0)||(is_numeric($userByEmail)&&$userByEmail==0)){
                return response()->json(['message'=>self::taskError],500);
            }else{
                if (count($userByEmail)>0 && count($userByCredentials)>0){
                    if ($userByCredentials[0]['id']==$userByEmail[0]['id'] &&
                        $userByCredentials[0]['identification']==$request['identification'] &&
                        !isset($userByCredentials[0]['student'])){
                        $request['active'] = $userByCredentials[0]['active'];
                        $result = UserService::updateUser($request,$userByCredentials[0]['id'],'S',$organizationId);
                        if(is_numeric($result)&&$result==0){
                            return response()->json(['message'=>self::taskError],500);
                        }
                        return self::createStudent($request,$organizationId,$userByCredentials[0]['id']);
                    }
                }else if(count($userByCredentials)>0 && count($userByEmail)==0){
                    if ($userByCredentials[0]['identification']==$request['identification'] &&
                        !isset($userByCredentials[0]['student'])){
                        $request['active'] = $userByCredentials[0]['active'];
                        $result = UserService::updateUser($request,$userByCredentials[0]['id'],'S',$organizationId);
                        if(is_numeric($result)&&$result==0){
                            return response()->json(['message'=>self::taskError],500);
                        }
                        return self::createStudent($request,$organizationId,$userByCredentials[0]['id']);
                    }
                }else if(count($userByEmail)>0 && count($userByCredentials)==0){
                    if ($userByEmail[0]['identification']==$request['identification'] &&
                        !isset($userByCredentials[0]['student'])){
                        $request['active'] = $userByEmail[0]['active'];
                        $result = UserService::updateUser($request,$userByEmail[0]['id'],'S',$organizationId);
                        if(is_numeric($result)&&$result==0){
                            return response()->json(['message'=>self::taskError],500);
                        }
                        return self::createStudent($request,$organizationId,$userByEmail[0]['id']);
                    }
                }
                return response()->json(['message' => self::busyCredential], 206);
            }
        }else if (is_numeric($userId)&&$userId==0){
            return response()->json(['message'=>self::taskError],500);
        }else{
            return self::createStudent($request,$organizationId,$userId);
        }
    }

    /**
     * Agrega una entidad estudiante a un usuario ya existente en el sistema en caso de que el usuario continúe con sus
     * estudios en el postgrado después de graduarse y actualiza los datos del usuario con el método
     * UserService::updateUser($request,$userId,'S',$organizationId) y crea el estudiante con
     * self::addStudent($userId,$request).
     * @param Request $request Objeto con los datos de la petición
     * @param integer $userId id del usuario
     * @param string $organizationId Id de la organiación
     * @return Response|User de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera correcta
     * devolvera el objeto user.
     */
    public static function addStudentContinue(Request $request, $userId, $organizationId)
    {
        self::validateSchoolProgramId($request);
        self::validate($request);
        $result = Student::studentHasProgram($userId);
        if (is_numeric($result)&&$result == 0){
            return response()->json(['message'=>self::taskError],500);
        }
        if ($result){
            return response()->json(['message'=>self::studentInProgram],206);
        }
        if (isset($request['guide_teacher_id'])){
            $existTeacher=User::existUserById($request['guide_teacher_id'],'T',$organizationId);
        }else{
            $existTeacher=true;
        }
        $existSchoolProgram = SchoolProgram::existSchoolProgramById($request['school_program_id'],$organizationId);
        if ((is_numeric($existTeacher)&&$existTeacher==0)||(is_numeric($existSchoolProgram)&&$existSchoolProgram==0)){
            return response()->json(['message'=>self::taskError],500);
        }
        if (!$existTeacher){
            return response()->json(['message'=>self::invalidTeacher],206);
        }
        if (!$existSchoolProgram){
            return response()->json(['message'=>self::invalidSchoolProgram],206);
        }
        if (isset($request['equivalences'])){
            $validEquivalence =self::validateEquivalences($organizationId,$request['equivalences'],
                $request['school_program_id']);
            if (!$validEquivalence){
                return response()->json(['message'=>self::invalidEquivalences],206);
            }
        }
        $result=Student::existStudentInProgram($userId,$request['school_program_id']);
        if (is_numeric($result)&&$result == 0){
            return response()->json(['message'=>self::taskError],500);
        }
        if ($result){
            return response()->json(['message'=>self::studentHasProgram],206);
        }
        $result = UserService::updateUser($request,$userId,'S',$organizationId);
        if ($result==="not_found"){
            return response()->json(['message'=>self::notFoundUser],206);
        }else if (is_numeric($result)&&$result==0){
            return response()->json(['message'=>self::taskError],500);
        }else if ($result==="busy_credential"){
            return response()->json(['message'=>self::busyCredential],206);
        }else {
            $studentId=self::addStudent($userId,$request);
            if (is_numeric($studentId)&&$studentId==0){
                return response()->json(['message' => self::taskPartialError], 500);
            }
            $result = self::addEquivalencesAndDegrees($request,$studentId);
            if (is_numeric($result)&&$result==0){
                return response()->json(['message' => self::taskPartialError], 500);
            }
            $log = Log::addLog(auth('api')->user()['id'],self::logCreateStudent.$request['first_name']. ' '.
                $request['first_surname']);
            if (is_numeric($log)&&$log==0){
                return response()->json(['message' => self::taskPartialError], 500);
            }
            return UserService::getUserById($userId,'S',$organizationId);
        }
    }

    /**
     * Valida que se cumpla las restricciones:
     * *student_id: numérico y requerido
     * *is_available_final_work: booleano y requerido
     * *test_period:booleano y requerido
     * *current_status:requerido, máximo 5 y finaliza en RET-A, RET-B, DES-A, DES-B, (reingreso)RIN-A, RIN-B,
     * (reincorporado)REI-A, REI-B, REG o ENDED (graduado)
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validateUpdate(Request $request)
    {
        $request->validate([
            'student_id'=>'numeric|required',
            'is_available_final_work'=>'boolean|required',
            'test_period'=>'boolean|required',
            'current_status'=>'max:5|required|ends_with:RET-A,RET-B,DES-A,DES-B,RIN-A,RIN-B,REI-A,REI-B,REG,ENDED',//REI REINCORPORADO RIN REINGRESO
            'allow_post_inscription'=>'boolean|required',
        ]);
    }

    /**
     * Actualiza los datos de un usuario con el método UserService::updateUser($request,$userId,'S',$organizationId) y
     * los datos del estudiante dado su studentId para actualizar la entidad adecuada con el método
     *
     * Student::updateStudent($request['student_id'],[
     * 'school_program_id'=>$student[0]['school_program_id'],
     * 'user_id'=>$userId,
     * 'guide_teacher_id'=>$request['guide_teacher_id'],
     * 'student_type'=>$request['student_type'],
     * 'home_university'=>$request['home_university'],
     * 'current_postgraduate'=>$request['current_postgraduate'],
     * 'type_income'=>$request['type_income'],
     * 'is_ucv_teacher'=>$request['is_ucv_teacher'],
     * 'is_available_final_work'=>$request['is_available_final_work'],
     * 'credits_granted'=>$request['credits_granted'],
     * 'with_work'=>$request['with_work'],
     * 'end_program'=>$request['end_program'],
     * 'test_period'=>$request['test_period'],
     * 'current_status'=>$request['current_status'],])
     * @param Request $request Objeto con los datos de la petición
     * @param Request $userId id del usuario
     * @param string $organizationId Id de la organiación
     * @return Response|User de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera correcta
     * devolvera el objeto user.
     */
    public static function updateStudent(Request $request, $userId, $organizationId)
    {
        self::validate($request);
        self::validateUpdate($request);
        if (isset($request['guide_teacher_id'])){
            $existTeacher=User::existUserById($request['guide_teacher_id'],'T',$organizationId);
        }else{
            $existTeacher=true;
        }
        if ((is_numeric($existTeacher)&&$existTeacher==0)){
            return response()->json(['message'=>self::taskError],500);
        }
        if (!$existTeacher){
            return response()->json(['message'=>self::invalidTeacher],206);
        }
        $student = Student::getStudentById($request['student_id'],$organizationId);
        if (is_numeric($student)&&$student==0){
            return response()->json(['message'=>self::taskError],500);
        }
        if (count($student)<1){
            return response()->json(['message'=>self::notFoundUser],206);
        }
        if (isset($request['equivalences'])){
            $validEquivalence =self::validateEquivalences($organizationId,$request['equivalences'],
                $student[0]['school_program_id']);
            if (!$validEquivalence){
                return response()->json(['message'=>self::invalidEquivalences],206);
            }
        }
        $result = UserService::updateUser($request,$userId,'S',$organizationId);
        if ($result==="not_found"){
            return response()->json(['message'=>self::notFoundUser],206);
        }else if (is_numeric($result)&&$result==0){
            return response()->json(['message'=>self::taskError],500);
        }else if ($result==="busy_credential"){
            return response()->json(['message'=>self::busyCredential],206);
        }else {
            if ($request['current_status']=='RET-B'||$request['current_status']=='ENDED'){
                $request['end_program']=true;
            }else{
                $request['end_program']=false;
            }
            $result = Student::updateStudent($request['student_id'],[
                'school_program_id'=>$student[0]['school_program_id'],
                'user_id'=>$userId,
                'guide_teacher_id'=>$request['guide_teacher_id'],
                'student_type'=>$request['student_type'],
                'home_university'=>$request['home_university'],
                'current_postgraduate'=>$request['current_postgraduate'],
                'type_income'=>$request['type_income'],
                'is_ucv_teacher'=>$request['is_ucv_teacher'],
                'is_available_final_work'=>$request['is_available_final_work'],
                'credits_granted'=>$request['credits_granted'],
                'with_work'=>$request['with_work'],
                'end_program'=>$request['end_program'],
                'test_period'=>$request['test_period'],
                'current_status'=>$request['current_status'],
                'allow_post_inscription'=>$request['allow_post_inscription'],
            ]);
            if (is_numeric($result)&&$result==0){
                return response()->json(['message' => self::taskPartialError], 500);
            }
            $deleteDegrees = Degree::deleteDegree($request['student_id']);
            $deleteEquivalences = Equivalence::deleteEquivalence($request['student_id']);
            if ((is_numeric($deleteDegrees)&&$deleteDegrees==0)||(is_numeric($deleteEquivalences)&&
                    $deleteEquivalences==0)){
                return response()->json(['message' => self::taskPartialError], 500);
            }
            $result=self::addEquivalencesAndDegrees($request,$request['student_id']);
            if (is_numeric($result)&&$result==0){
                return response()->json(['message' => self::taskPartialError], 500);
            }
            $log = Log::addLog(auth('api')->user()['id'],self::logUpdateStudent.$request['first_name'].' '.
                $request['first_surname']);
            if (is_numeric($log)&&$log==0){
                return response()->json(['message' => self::taskPartialError], 500);
            }
            return UserService::getUserById($userId,'S',$organizationId);
        }
    }

    /**
     * Elimina una entidad student de un usuario si este tiene más de dos entidades student asociadas con el método
     * Student::deleteStudent($userId,$studentId).
     * @param string $userId Id del usuario
     * @param string $studentId id del estudiante
     * @param string $organizationId Id de la organiación
     * @return Response|User, de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera correcto
     * devolvera el objeto user.
     */
    public static function deleteStudent($userId,$studentId,$organizationId)
    {
        $user = User::getUserById($userId,'S',$organizationId);
        if (is_numeric($user)&& $user == 0 ){
            return response()->json(['message'=>self::taskError],500);
        }
        if (count($user)<1){
            return response()->json(['message'=>self::notFoundUser],206);
        }
        if (count($user[0]['student'])>=2){
            $result = self::removeAllInscriptionsByStudent($studentId,$organizationId);
            if (is_numeric($result)&&$result == 0 ){
                return response()->json(['message'=>self::taskError],500);
            }
            $result = Student::deleteStudent($userId,$studentId);
            if (is_numeric($result)&&$result == 0 ){
                return response()->json(['message'=>self::taskError],500);
            }
            $log = Log::addLog(auth('api')->user()['id'],self::logDeleteStudent.$user[0]['first_name'].' '.
                $user[0]['first_surname']);
            if (is_numeric($log)&&$log==0){
                return response()->json(['message' => self::taskError], 500);
            }
            return UserService::getUserById($userId,'S',$organizationId);
        }
        return response()->json(['message'=>self::noAction],206);
    }

    /**
     * Válida si los datos enviados por parámetros son del estudiante que realiza la petición o son de un usuario
     * administrador de lo contrario no estará autorizado.
     * @param string $organizationId Id de la organiación
     * @param string $studentId id del estudiante
     * @return string|Response Devuelve un string valid si si es el estudiante coincide con su id de sesion o es usuario
     * administrador de lo contrario o de ocurrir un error devolvera un mensaje asociado
     */
    public static function validateStudent($organizationId,$studentId)
    {
        $existStudentById = Student::existStudentById($studentId,$organizationId);
        if (is_numeric($existStudentById) && $existStudentById == 0) {
            return response()->json(['message'=>self::taskError],500);
        }
        if ($existStudentById) {
            $student = Student::getStudentById($studentId, $organizationId);
            if (is_numeric($student) && $student == 0) {
                return response()->json(['message'=>self::taskError],500);
            }
            if (count($student) > 0) {
                $roles =array_column(auth()->payload()['user']->roles,'user_type');
                if (in_array('A',$roles)){
                    return 'valid';
                }
                if (in_array('S',$roles)){
                    $studentsId = array_column(auth()->payload()['user']->student, 'id');
                    if (in_array($studentId, $studentsId)) {
                        return 'valid';
                    }
                }
                return response()->json(['message' => self::unauthorized], 401);
            }
        }
        return response()->json(['message' => self::notFoundUser], 206);
    }

    /**
     * Lista todos los estudiantes que tienen algún tipo de incidencia con el método
     * Student::warningStudent($organizationId)
     * @param string $organizationId Id de la organiación
     * @return Student|Response Devuelve una lista de estudiantes, con estatus diferentes a los regulares,de ocurrir un
     * error devolvera un mensaje asociado
     */
    public static function warningStudent($organizationId)
    {
        $warningStudent=Student::warningStudent($organizationId);
        if (is_numeric($warningStudent)&&$warningStudent==0){
            return response()->json(['message'=>self::taskError],500);
        }
        if (count($warningStudent)>0){
            return $warningStudent;
        }
        return response()->json(['message' => self::notWarningStudent], 200);
    }

    /**
     * Actualiza los estudiantes a periodo de prueba si estos tienen un promedio por debajo de 14 y si cumplen con los
     * requisitos para presentar el trabajo especial de grado los cambia a habilitados con el método
     * Student::updateStudent($student['id'],$student->toArray())
     * @param string $organizationId Id de la organiación
     * @return string|integer Actualiza los estudiantes que pasan a periodo de prueba y los que entran en requisitos
     * para presentar tesis
     */
    public static function warningOrAvailableWorkToStudent($organizationId)
    {
        $students=Student::getAllStudentToDegree($organizationId);
        if (is_numeric($students)&&$students==0){
            return 0;
        }
        if (count($students)>0){
            foreach ($students as $student){
                $update =false;
                $totalQualification = InscriptionService::getTotalQualification($student['id']);
                if (is_string($totalQualification)&&$totalQualification=='e'){
                    return 0;
                }
                $cantSubjectsEnrolled=StudentSubject::cantAllSubjectsEnrolledWithoutRETCUR($student['id']);
                if (is_string($cantSubjectsEnrolled)&&$cantSubjectsEnrolled=='e'){
                    return 0;
                }
                if($cantSubjectsEnrolled>0 && ($totalQualification/$cantSubjectsEnrolled)<14){
                    $student['testPeriod']=true;
                    $update = true;
                }
                $schoolProgram=SchoolProgram::getSchoolProgramById($student['school_program_id'],$organizationId);
                if (is_numeric($schoolProgram) && $schoolProgram===0){
                    return 0;
                }
                if (count($schoolProgram)>0){
                    if ($schoolProgram[0]['conducive_to_degree']){
                        $project = FinalWork::getFinalWorksByStudent($student['id'], true);
                        if (is_numeric($project)&&$project===0){
                            return 0;
                        }
                        $notApprovedProject = FinalWork::existNotApprovedFinalWork($student['id'], true);
                        if(is_numeric($notApprovedProject)&&$notApprovedProject===0){
                            return 0;
                        }
                        if (!$notApprovedProject && count($project)>0){
                            $student['is_available_final_work']=true;
                            $update = true;
                        }
                    }
                }
                if ($update){
                    $student = Student::updateStudent($student['id'],$student->toArray());
                    if (is_numeric($student)&& $student==0){
                        return 0;
                    }
                }
            }
        }
        return 'emptyStudent';
    }

    /**
     * Elimina todas las materias de un estudiante inscritas, y actualiza el contador de estudiantes inscritos en dichas
     * materias inscritas.
     * @param string $studentId id del estudiante
     * @param string $organizationId Id de la organiación
     * @return int Devuelve 0 en caso de error
     */
    public static function removeAllInscriptionsByStudent($studentId,$organizationId){
        $inscriptions = SchoolPeriodStudent::getEnrolledSchoolPeriodsByStudent($studentId,$organizationId);
        if (is_numeric($inscriptions)&&$inscriptions==0){
            return 0;
        }
        $inscriptions=$inscriptions->toArray();
        if (count($inscriptions)>0){
            foreach ($inscriptions as $inscription){
                foreach ($inscription['enrolled_subjects'] as $subject){
                    $result = StudentSubject::deleteStudentSubject($subject['id']);
                    if (is_numeric($result)&&$result==0){
                        return 0;
                    }
                    $result = SchoolPeriodSubjectTeacher::updateEnrolledStudent(
                        $subject['school_period_subject_teacher_id']);
                    if (is_numeric($result)&&$result==0){
                        return 0;
                    }
                }
            }
        }
    }
}
