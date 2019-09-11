<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::resource('postgraduates','PostgraduateController');
//Route::middleware('jwt.auth')->resource('subjects','SubjectController');
Route::resource('subjects','SubjectController');
//Route::middleware('jwt.auth')->resource('users','UserController');
Route::get('administrators/active','AdministratorController@active');
Route::resource('administrators','AdministratorController');
Route::get('teachers/active','TeacherController@active');
Route::resource('teachers','TeacherController');
Route::get('students/active','StudentController@active');
Route::resource('students','StudentController');
Route::get('schoolPeriods/current','SchoolPeriodController@current');
Route::resource('schoolPeriods','SchoolPeriodController');
Route::resource('subjectInscription','SubjectInscriptionController');
Route::resource('schoolPeriodInscription','SchoolPeriodInscriptionController');
Route::get('inscription/availableSubjects','InscriptionController@availableSubjects');
Route::resource('inscription','InscriptionController');
//Route::post('login', 'AuthController@login');
//Route::get('log', 'AuthController@getToken');
/*Route::group([
    'prefix' => 'auth',
], function () {
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('payload', 'AuthController@payload');
});*/
Route::post('login', 'AuthController@login');
Route::post('logout', 'AuthController@logout');
Route::post('refresh', 'AuthController@refresh');
Route::post('me', 'AuthController@me');
Route::post('payload', 'AuthController@payload');
Route::group(['middleware' => ['jwt.auth']], function() {
    /*AÑADE AQUI LAS RUTAS QUE QUIERAS PROTEGER CON JWT*/
});