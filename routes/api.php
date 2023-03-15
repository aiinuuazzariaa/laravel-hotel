<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderDetailController;
use App\Http\Controllers\UserController;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

//user login
Route::post('/user/register', [UserController::class, 'register']);
Route::post('/user/login', [UserController::class, 'login']);
Route::get('/login_check', [UserController::class, 'getAuthenticatedUser']);

//route room_type
Route::get('/room_type', [RoomTypeController::class, 'show']);
Route::get('/room_type/{id}', [RoomTypeController::class, 'detail']);
Route::post('/room_type/filtering', [RoomTypeController::class, 'filter']);

//route room
Route::get('/room', [RoomController::class, 'show']);

//route order
Route::post('/order', [OrderController::class, 'add']);

Route::group(['middleware' => ['jwt.verify']], function () {
    // Route::get('/login/check', [UserController::class, 'getAuthenticatedUser']);

    Route::group(['middleware' => ['jwt.verify:admin']], function () {

        //route user
        Route::get('/user', [UserController::class, 'show']);
        Route::get('/user/{id}', [UserController::class, 'detail']);
        Route::put('/user/{id}', [UserController::class, 'update']);
        Route::delete('/user/{id}', [UserController::class, 'destroy']);
        Route::post('/user/image/{id}', [UserController::class, 'uploadImage']);

        //route room_type
        Route::post('/room_type', [RoomTypeController::class, 'add']);
        Route::post('/room_type/upload_photo/{id}', [RoomTypeController::class, 'upload_photo_hotel']);
        Route::put('/room_type/{id}', [RoomTypeController::class, 'update']);
        Route::delete('/room_type/{id}', [RoomTypeController::class, 'delete']);

        //route room
        Route::get('/room/{id}', [RoomController::class, 'detail']);
        Route::post('/room', [RoomController::class, 'add']);
        Route::put('/room/{id}', [RoomController::class, 'update']);
        Route::delete('/room/{id}', [RoomController::class, 'delete']);
    });

    //route order_detail
    Route::get('/order_detail', [OrderDetailController::class, 'show']);
    Route::get('/order_detail/{id}', [OrderDetailController::class, 'detail']);
    Route::post('/order_detail', [OrderDetailController::class, 'add']);
    Route::put('/order_detail/{id}', [OrderDetailController::class, 'update']);
    Route::delete('/order_detail/{id}', [OrderDetailController::class, 'delete']);

    Route::group(['middleware' => ['jwt.verify:receptionist']], function () {

        //route order
        Route::get('/order', [OrderController::class, 'show']);
        Route::get('/detail_order/{id}', [OrderController::class, 'detail']);
        Route::post('/order/filtering', [OrderController::class, 'findByNumber']);
        Route::put('/order/{id}', [OrderController::class, 'status']);
        Route::delete('/order/{id}', [OrderController::class, 'delete']);
    });
});
