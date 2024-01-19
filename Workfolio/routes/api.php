<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\IndexController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::post('/userLogin', [IndexController::class, 'userLogin']);
// Route::post('/userRegister', [IndexController::class, 'userRegister']);
// Route::post('/adminRegister', [IndexController::class, 'adminRegister']);

Route::middleware(['auth:api', 'isAdmin'])->group(function () {
    // Route::get('/userDetailCheck', [UserController::class, 'userDetailCheck']);
    Route::get('/userLeave', [UserController::class, 'userLeave']);
    Route::put('/leaveApproved/{id}', [UserController::class, 'leaveApproved']);
    Route::put('/leaveReject/{id}', [UserController::class, 'leaveReject']);
    Route::get('/allUsers', [UserController::class, 'allUsers']);
    Route::get('/leaveAndAttendence/{id}', [UserController::class, 'leaveAndAttendence']);
    Route::get('/todayLeaveOrCheckIn', [UserController::class, 'todayLeaveOrCheckIn']);
        // Route::post('/weekDuration/{id}', 'App\Http\Controllers\Api\UserController@weekDuration');
    Route::get('/users', 'App\Http\Controllers\Api\userController@getUserByToken');
});

Route::middleware(['auth:api', 'isUser'])->group(function () {
    Route::post('/userCheckIn', [UserController::class, 'userCheckIn']);
    Route::get('/userCheckOut', [UserController::class, 'userCheckOut']);
    // Route::post('/leaves', [IndexController::class, 'leaves']);
    Route::post('users/applyleaves', 'App\Http\Controllers\Api\IndexController@leaves');
    Route::get('/users', 'App\Http\Controllers\Api\IndexController@getUserByToken');
    // Route::post('users/checkin', 'App\Http\Controllers\Api\IndexController@userCheckIn');
});

Route::post('/users/admin', 'App\Http\Controllers\Api\Indexcontroller@adminsignup');
Route::post('/users/user', 'App\Http\Controllers\Api\Indexcontroller@usersignup');
Route::post('users/loginadmin', 'App\Http\Controllers\Api\IndexController@loginadmin');
Route::post('users/loginuser', 'App\Http\Controllers\Api\IndexController@loginuser');

Route::put('users/change_password/{id}', 'App\Http\Controllers\Api\IndexController@change_password');
Route::post('users/newPassword', 'App\Http\Controllers\Api\IndexController@newpassword');
Route::post('users/forget', 'App\Http\Controllers\Api\IndexController@forgetPassword');
Route::get('users/details/{id}', 'App\Http\Controllers\Api\IndexController@userdetails');
