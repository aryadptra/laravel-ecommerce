<?php

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

// Admin routes
Route::prefix('admin')->group(function () {
    // Route login
    Route::post('/login', App\Http\Controllers\Api\Admin\LoginController::class, ['as' => 'admin']);
    // Group route with middleware "auth:api"
    Route::group(['middleware' => 'auth:api'], function () {
        // Route user logged in
        Route::get('/user', function (Request $request) {
            return $request->user();
        })->name('user');
    });
});

// Group route with middleware "auth:api"
Route::group(['middleware' => 'auth:api'], function () {
    //route user logged in
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('user');
    //route logout
    Route::post('/logout', App\Http\Controllers\Api\LogoutController::class);
});
Route::post('/register', App\Http\Controllers\Api\RegisterController::class);
Route::post('/login', App\Http\Controllers\Api\LoginController::class);

Route::apiResource('/categories', App\Http\Controllers\Api\Admin\CategoryController::class, [
    'except' => ['create', 'edit']
]);
