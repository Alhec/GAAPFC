<?php

namespace App\Services;

use App\Log;
use Illuminate\Http\Request;
use App\SchoolProgram;
use Illuminate\Http\Response;

/**
 * @package : Services
 * @author : Hector Alayon
 * @version : 1.0
 */
class SchoolProgramService
{
    const taskError = 'No se puede proceder con la tarea';
    const emptyProgram = 'No existen programas asociados';
    const notFoundProgram = 'Programa no encontrado';
    const busyName = 'Nombre del programa en uso';
    const ok = 'OK';
    const logCreateSchoolProgram = 'Creo el programa escolar ';
    const logUpdateSchoolProgram = 'Actualizo el programa escolar ';
    const logDeleteSchoolProgram = 'Elimino el programa escolar ';

    /**
     * Obtiene todos los programas dado una organización usando el método
     * SchoolProgram::getSchoolProgram($organizationId)
     * @param string $organizationId Id de la organiación
     * @param integer $perPage Parámetro opcional, cantidad de elementos por página, default:0
     * @return SchoolProgram|Response Obtiene todos los programas escolares en la organizacion.
     */
    public static function getSchoolProgram($organizationId,$perPage=0)
    {
        $perPage == 0 ? $schoolPrograms = SchoolProgram::getSchoolProgram($organizationId):
            $schoolPrograms = SchoolProgram::getSchoolProgram($organizationId,$perPage);
        if (is_numeric($schoolPrograms)&&$schoolPrograms == 0){
            return response()->json(['message'=>self::taskError],500);
        }
        if ($perPage == 0) {
            if (count($schoolPrograms)>0){
                return $schoolPrograms;
            }
            return response()->json(['message'=>self::emptyProgram],206);
        }else{
            return $schoolPrograms;
        }
    }

    /**
     * Obtiene un programa dado un id y una organización usando el método
     * SchoolProgram::getSchoolProgramById($id,$organizationId)
     * @param string $id Id del programa escolar
     * @param string $organizationId Id de la organiación
     * @return SchoolProgram|Response Obtiene un programa escolar dado su id en la organizacion.
     */
    public static function getSchoolProgramById($id,$organizationId)
    {
        $schoolProgram = SchoolProgram::getSchoolProgramById($id,$organizationId);
        if (is_numeric($schoolProgram) && $schoolProgram == 0){
            return response()->json(['message'=>self::taskError],500);
        }
        if (count($schoolProgram)>0) {
            return $schoolProgram[0];
        }
        return response()->json(['message'=>self::notFoundProgram],206);
    }

    /**
     *Valida que se cumpla las restricciones:
     * *school_program_name: requerido y máximo 100
     * *doctoral_exam: booleano
     * *min_cu_to_doctoral_exam: numérico
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validate(Request $request)
    {
        $request->validate([
            'school_program_name'=>'required|max:100',
            'doctoral_exam'=>'boolean',
            'min_cu_to_doctoral_exam'=>'numeric'
        ]);
    }

    /**
     *Valida que se cumpla las restricciones:
     * *num_cu: requerido y numérico
     * *min_num_cu_final_work: requerido y numérico
     * *duration: requerido y numérico
     * *min_duration: requerido y numérico
     * *conducive_to_degree: requerido y booleano
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validateWithDegree(Request $request)
    {
        $request->validate([
            'num_cu'=>'required|numeric',
            'duration'=>'required|numeric',
            'min_duration'=>'numeric|required',
            'min_num_cu_final_work'=>'numeric|required',
            'conducive_to_degree'=>'required|boolean',
        ]);
    }

    /**
     *Valida que se cumpla las restricciones:
     * *duration: numérico
     * *grant_certificate: requerido y booleano
     * @param Request $request Objeto con los datos de la petición
     */
    public static function validateWithoutDegree(Request $request)
    {
        $request->validate([
            'duration'=>'numeric',
            'grant_certificate'=>'required|boolean',
        ]);
    }

    /**
     * Agrega un programa escolar dado una organización usando el método SchoolProgram::addSchoolProgram($request).
     * @param Request $request Objeto con los datos de la petición
     * @param string $organizationId Id de la organiación
     * @return Response|SchoolProgram de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera
     * correcta devolvera el objeto SchoolProgram.
     */
    public static function addSchoolProgram(Request $request,$organizationId)
    {
        self::validate($request);
        if ($request['conducive_to_degree']){
            self::validateWithDegree($request);
        }else{
            self::validateWithoutDegree($request);
        }
        $existSchoolProgramByName=SchoolProgram::existSchoolProgramByName($request['school_program_name'],
            $organizationId);
        if (is_numeric($existSchoolProgramByName)&& $existSchoolProgramByName==0){
            return response()->json(['message'=>self::taskError],500);
        }
        if (!$existSchoolProgramByName){
            $request['organization_id']=$organizationId;
            if ($request['conducive_to_degree']){
                $request['grant_certificate']=false;
            }
            if ($request['grant_certificate']){
                $request['conducive_to_degree']=false;
                $request['min_duration']=0;
                $request['num_cu']=0;
                $request['min_num_cu_final_work']=0;
                $request['doctoral_exam']=false;
            }
            if (!$request['doctoral_exam']){
                $request['min_cu_to_doctoral_exam']=0;
            }
            $id = SchoolProgram::addSchoolProgram($request);
            if (is_numeric($id) && $id == 0){
                return response()->json(['message'=>self::taskError],500);
            }
            $log = Log::addLog(auth('api')->user()['id'],self::logCreateSchoolProgram.$request['school_program_name']);
            if (is_numeric($log)&&$log==0){
                return response()->json(['message' => self::taskError], 500);
            }
            return self::getSchoolProgramById($id,$organizationId);
        }
        return response()->json(['message'=>self::busyName],206);
    }

    /**
     * Elimina un programa escolar con el metodo  SchoolProgram::deleteSchoolProgram($id).
     * @param string $id Id del programa escolar
     * @param string $organizationId Id de la organización a consultar
     * @return Response, de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera correcto
     * devolvera un objeto con mensaje OK.
     */
    public static function deleteSchoolProgram($id,$organizationId)
    {
        $schoolProgram = SchoolProgram::getSchoolProgramById($id,$organizationId);
        if (is_numeric($schoolProgram) && $schoolProgram == 0){
            return response()->json(['message'=>self::taskError],500);
        }
        if (count($schoolProgram)>0){
            $result = SchoolProgram::deleteSchoolProgram($id);
            if (is_numeric($result) && $result == 0){
                return response()->json(['message'=>self::taskError],500);
            }
            $log = Log::addLog(auth('api')->user()['id'],self::logDeleteSchoolProgram.
                $schoolProgram[0]['school_program_name']);
            if (is_numeric($log)&&$log==0){
                return response()->json(['message' => self::taskError], 500);
            }
            return response()->json(['message'=>self::ok]);
        }
        return response()->json(['message'=>self::notFoundProgram],206);
    }

    /**
     * Edita un programa escolar dado un id y una organización con el método
     * SchoolProgram::updateSchoolProgram($id,$request).
     * @param Request $request Objeto con los datos de la petición
     * @param string $id Id del programa escolar
     * @param string $organizationId Id de la organiación
     * @return Response|SchoolProgram de ocurrir un error devolvera un mensaje asociado, y si se realiza de manera
     * correcta devolvera el objeto SchoolProgram.
     */
    public static function updateSchoolProgram(Request $request, $id,$organizationId)
    {
        self::validate($request);
        if ($request['conducive_to_degree']){
            self::validateWithDegree($request);
        }else{
            self::validateWithoutDegree($request);
        }
        $existSchoolProgram = SchoolProgram::existSchoolProgramById($id,$organizationId);
        if (is_numeric($existSchoolProgram) && $existSchoolProgram == 0){
            return response()->json(['message'=>self::taskError],500);
        }
        if ($existSchoolProgram){
            $request['organization_id']=$organizationId;
            $schoolProgramName=SchoolProgram::getSchoolProgramByName($request['school_program_name'],$organizationId);
            if (is_numeric($schoolProgramName) && $schoolProgramName == 0){
                return response()->json(['message'=>self::taskError],500);
            }
            if (count($schoolProgramName)>0){
                if ($schoolProgramName[0]['id']!=$id){
                    return response()->json(['message'=>self::busyName],206);
                }
            }
            if ($request['conducive_to_degree']){
                $request['grant_certificate']=false;
            }
            if ($request['grant_certificate']){
                $request['conducive_to_degree']=false;
                $request['min_duration']=0;
                $request['num_cu']=0;
                $request['min_num_cu_final_work']=0;
                $request['doctoral_exam']=false;
            }
            if (!$request['doctoral_exam']){
                $request['min_cu_to_doctoral_exam']=0;
            }
            $result = SchoolProgram::updateSchoolProgram($id,$request);
            if (is_numeric($result) && $result == 0){
                return response()->json(['message'=>self::taskError],500);
            }
            $log = Log::addLog(auth('api')->user()['id'],self::logUpdateSchoolProgram.
                $request['school_program_name']);
            if (is_numeric($log)&&$log==0){
                return response()->json(['message' => self::taskError], 500);
            }
            return self::getSchoolProgramById($id,$organizationId);
        }
        return response()->json(['message'=>self::notFoundProgram],206);
    }
}
