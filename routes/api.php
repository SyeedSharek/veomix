<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\DesignationController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\DistrictController;
use App\Http\Controllers\Admin\UpazilaController;
use App\Http\Controllers\Admin\UnionController;
use App\Http\Controllers\Admin\EducationController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\DivisionofficeController;
use App\Http\Controllers\Admin\BackendController;
use App\Http\Controllers\Admin\BranchManageController;
use App\Http\Controllers\Admin\MemberManageController;
use App\Http\Controllers\Admin\RegionalofficeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchGroupController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RegionController;



Route::get('/cc', function() {
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('config:clear');
    $exitCode = Artisan::call('route:clear');
    $exitCode = Artisan::call('view:clear');
    $exitCode = Artisan::call('config:cache');
    return '<h1>All Config cleared</h1>';
});

// Route::get('/run', function() {
//     Artisan::call('make:model', [
//         'name' => 'Project',
//         '-m' => true,
//         '-c' => true,
//         '--resource' => true,
//     ]);
//     // Artisan::call('migrate');
//     return '<h1>Successfully Created!!</h1>';
// });

// Route::get('/run', function () {
//     // Artisan::call('make:migration', [
//     //     'name' => 'create_countries_table'
//     // ]);
//     // Artisan::call('migrate');
//     // Artisan::call('make:model', [
//     //     'name' => 'CircularView',
//     // ]);
//     // Artisan::call('make:middleware', [
//     //     'name' => 'TrackCircularView',
//     // ]);
//     // Artisan::call('make:model Concern -mcr');

//     return 'Successfully done!!';
// });


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/home', [HomeController::class, 'home'])->name('home');

Route::prefix('v1')->group(function () {
    // projects
    // Route::resource('projects', ProjectController::class);
    // designation
    // Route::resource('designations', DesignationController::class);
    // education
    // Route::resource('education', EducationController::class);
    // employee
    // Route::resource('employees', EmployeeController::class);


    // division
    // Route::resource('/divisions', DivisionController::class);
    // district
    Route::resource('districts', DistrictController::class);
    // upazila
    Route::resource('upazilas', UpazilaController::class);
    // union
    Route::resource('unions', UnionController::class);

    // Division Office
    Route::resource('divisionoffices', DivisionofficeController::class);

    // get Divsion, District, Upazila, Union
    Route::get('get-divisions/{id}', [BackendController::class, 'getDivisions']);
    Route::get('get-districts/{id}', [BackendController::class, 'getDistricts']);
    Route::get('get-upazilas/{id}', [BackendController::class, 'getUpazilas']);
    Route::get('get-unions/{id}', [BackendController::class, 'getUnions']);

    // get Project wise Manager List(employee)
    Route::get('get-managers/{id}', [BackendController::class, 'getManagers']);

    //Country
    Route::resource('/countries', CountryController::class);
    // get Division Office wise Division
    Route::get('get-office-wise-division/{id}', [BackendController::class, 'getOfficeWiseDivision']);

    // Regional Offile
    Route::resource('regionaloffices', RegionalofficeController::class);

    // Branch Offile
    // Route::resource('branchoffices', BranchofficeController::class);

    // get-available-branch-managers
    Route::get('get-available-branch-managers', [BackendController::class, 'getAvailableBranchManager']);

    // get-available-managers (division)
    Route::get('get-available-managers', [BackendController::class, 'getAvailableManager']);


});



Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::post('/profile', [AuthController::class, 'profile'])->middleware('auth:api');


});


Route::group(['middleware' => 'api', 'prefix' => 'auth'], function() {
    //Role & Permissions

    Route::get('/all/roles',[RoleController::class,'allrole']);
    Route::get('/all/permisisions',[RoleController::class,'allpermisisions']);
    Route::post('/add/role',[RoleController::class,'addRole']);

    //  Country
    Route::get('/all/countries',[CountryController::class,'index']);
    Route::post('/store/country',[CountryController::class,'store']);
    Route::delete('/delete/country/{id}',[CountryController::class,'destroy']);
    Route::post('/update/country/{id}',[CountryController::class,'update']);

    //  Division

    Route::get('/all/divisions',[DivisionController::class,'index']);
    Route::post('/store/division',[DivisionController::class,'store']);
    Route::delete('/delete/division/{id}',[DivisionController::class,'destroy']);
    Route::post('/update/division/{id}',[DivisionController::class,'update']);

    // Regiion

    Route::get('/all/rigions',[RegionController::class,'index']);
    Route::post('/store/rigion',[RegionController::class,'store']);
    Route::delete('/delete/rigion/{id}',[RegionController::class,'destroy']);
    Route::post('/update/rigion/{id}',[RegionController::class,'update']);

    // Project

    Route::get('/all/project',[ProjectController::class,'index']);
    Route::post('/store/project',[ProjectController::class,'store']);
    Route::delete('/delete/project/{id}',[ProjectController::class,'destroy']);
    Route::post('/update/project/{id}',[ProjectController::class,'update']);

    // Designation

    Route::get('/all/designation',[DesignationController::class,'index']);
    Route::post('/store/designation',[DesignationController::class,'store']);
    Route::delete('/delete/designation/{id}',[DesignationController::class,'destroy']);
    Route::post('/update/designation/{id}',[DesignationController::class,'update']);

    // Education

    Route::get('/all/education',[EducationController::class,'index']);
    Route::post('/store/education',[EducationController::class,'store']);
    Route::delete('/delete/education/{id}',[EducationController::class,'destroy']);
    Route::post('/update/education/{id}',[EducationController::class,'update']);


    // Branch Manage

    Route::get('/all/branch',[BranchManageController::class,'index']);
    Route::post('/store/branch',[BranchManageController::class,'store']);
    Route::delete('/delete/branch/{id}',[BranchManageController::class,'destroy']);
    Route::post('/update/branch/{id}',[BranchManageController::class,'update']);
    Route::post('/branch/list',[BranchManageController::class,'branchList']);


    // Branch Group Create

    Route::get('/all/branchGroups',[BranchGroupController::class,'index']);
    Route::post('/store/branchGroup',[BranchGroupController::class,'store']);
    Route::delete('/delete/branchGroup/{id}',[BranchGroupController::class,'destroy']);
    Route::post('/update/branchGroup/{id}',[BranchGroupController::class,'update']);





    // Employee Create

    Route::get('/all/employees',[EmployeeController::class,'index']);
    Route::post('/store/employee',[EmployeeController::class,'store']);
    Route::delete('/delete/employee/{id}',[EmployeeController::class,'destroy']);
    Route::post('/update/employee/{id}',[EmployeeController::class,'update']);
    Route::post('/search/employee',[EmployeeController::class,'searchEmployee']);
    Route::post('/search/brach/employee',[EmployeeController::class,'getEmployeeByBranch']);




    // Manager Name By Id Search

    Route::get('/branch/employeeManagerId',[EmployeeController::class,'employeeManagerId']);

    // Member Manage

    Route::get('/all/members',[MemberManageController::class,'index']);
    Route::post('/store/member',[MemberManageController::class,'store']);
    Route::delete('/delete/member/{id}',[MemberManageController::class,'destroy']);
    Route::post('/update/member/{id}',[MemberManageController::class,'update']);
    Route::post('/search/member',[MemberManageController::class,'searchMember']);
    Route::post('/search/branch/member',[MemberManageController::class,'getMemberByBranch']);
    // Route::post('/all/eye/view/{id}',[MemberManageController::class,'eyeViewDetails']);







});
