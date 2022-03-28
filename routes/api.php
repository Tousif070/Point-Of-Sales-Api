<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['namespace' => 'App\Http\Controllers'], function() {

    Route::post('register', 'LoginController@register');
    
    

});

Route::group(['prefix' => 'product', 'namespace' => 'App\Http\Controllers', 'middleware' => ['auth:sanctum']], function() {

    Route::get('index', 'ProductController@index');
    Route::post('store', 'ProductController@store');
    Route::post('update/{product_id}', 'ProductController@update');
    Route::delete('delete/{product_id}', 'ProductController@delete');

});



Route::group(['namespace' => 'App\Http\Controllers'], function() {

    Route::post('login', 'LoginController@login');

    Route::group(['middleware' => ['auth:sanctum']], function() {

        Route::post('logout', 'LoginController@logout');

        

    });

});