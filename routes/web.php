<?php

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/deployment/list', [\App\Http\Controllers\BaseController::class, 'fetchDeployments']);
Route::get('/deployment/create', [\App\Http\Controllers\BaseController::class, 'createDeployment']);
Route::get('/deployment/scale/{replicas}', [\App\Http\Controllers\BaseController::class, 'scaleDeployment']);
Route::get('/service/create', [\App\Http\Controllers\BaseController::class, 'createService']);
