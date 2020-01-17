<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SchoolPeriodStudent extends Model
{
    protected $fillable = ['student_id','school_period_id','status','pay_ref','financing','financing_description','amount_paid','inscription_date'];

    protected $table = 'school_period_student';

    public $timestamps = false;

    public function schoolPeriod()
    {
        return $this->belongsTo('App\SchoolPeriod');
    }

    public function student()
    {
        return $this->belongsTo('App\Student')
            ->with('user');
    }

    public function enrolledSubjects()
    {
        return $this->hasMany('App\StudentSubject')
            ->with('dataSubject');
    }

    public static function getSchoolPeriodStudent($organizationId)
    {
        try{
            return self::with('schoolPeriod')
                ->with('student')
                ->with('enrolledSubjects')
                ->whereHas('schoolPeriod',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    public static function getSchoolPeriodStudentById($id,$organizationId)
    {
        try{
            return self::where('id',$id)
                ->with('student')
                ->with('enrolledSubjects')
                ->with('schoolPeriod')
                ->whereHas('schoolPeriod',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    public static function getSchoolPeriodStudentBySchoolPeriod($schoolPeriodId,$organizationId)
    {
        try{
            return self::where('school_period_id',$schoolPeriodId)
                ->with('student')
                ->with('enrolledSubjects')
                ->with('schoolPeriod')
                ->whereHas('schoolPeriod',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    public static function existSchoolPeriodStudent($studentId,$schoolPeriodId)
    {
        try{
            return self::where('student_id',$studentId)
                ->where('school_period_id',$schoolPeriodId)
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    public static function addSchoolPeriodStudent($schoolPeriodStudent){
        try{
            return self::insertGetId($schoolPeriodStudent->only('student_id','school_period_id','status','pay_ref',
                'financing','financing_description','amount_paid','test_period'));
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    public static function addSchoolPeriodStudentLikeArray($schoolPeriodStudent){
        try{
            return self::create($schoolPeriodStudent);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    public static function existSchoolPeriodStudentById($id,$organizationId){
        try{
            return self::where('id',$id)
                ->whereHas('schoolPeriod',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    public static function deleteSchoolPeriodStudent($id)
    {
        try{
            self::find($id)
                ->delete();
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    public static function findSchoolPeriodStudent($studentId,$schoolPeriodId)
    {
        try{
            return self::where('student_id',$studentId)
                ->where('school_period_id',$schoolPeriodId)
                ->with('enrolledSubjects')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    public static function updateSchoolPeriodStudent($id,$schoolPeriodSubject)
    {
        try{
            self::find($id)
                ->update($schoolPeriodSubject->all());
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    public static function updateSchoolPeriodStudentLikeArray($id,$schoolPeriodSubject)
    {
        try{
            self::find($id)
                ->update($schoolPeriodSubject);
        }catch (\Exception $e){
            DB::rollback();
            return 0;
        }
    }

    public static function thereIsUnpaidSchoolPeriod($studentId)
    {
        try{
            return self::where('student_id',$studentId)
                ->where('amount_paid',null)
                ->exists();
        }catch (\Exception $e){
            return 0;
        }
    }

    public static function getEnrolledSubjectsByStudent($studentId,$organizationId)
    {
        try{
            return self::where('student_id',$studentId)
                ->with('student')
                ->with('enrolledSubjects')
                ->with('schoolPeriod')
                ->whereHas('schoolPeriod',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->orderBy('inscription_date','ASC')
                ->get();
        }catch (\Exception $e){
            return 0;
        }
    }

    public static function getCantEnrolledSchoolPeriodByStudent($studentId,$organizationId)
    {
        try{
            return self::where('student_id',$studentId)
                ->with('student')
                ->whereHas('schoolPeriod',function (Builder $query) use ($organizationId){
                    $query
                        ->where('organization_id','=',$organizationId);
                })
                ->get('id');
        }catch (\Exception $e){
            return 0;
        }
    }


}
