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

Route::group(['prefix' => 'product', 'namespace' => 'App\Http\Controllers'], function() {

    Route::get('index', 'ProductController@index');
    Route::post('store', 'ProductController@store');
    Route::put('update/{product_id}', 'ProductController@update');
    Route::delete('delete/{product_id}', 'ProductController@delete');

});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
