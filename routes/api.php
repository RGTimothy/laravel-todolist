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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => '/to-do', 'middleware' => 'ensure.token'], function () {
	Route::post('/', 'TodoItemController@create');
	Route::get('/{id?}', 'TodoItemController@list');
	Route::post('/{id}', 'TodoItemController@update');
	Route::delete('/{id}', 'TodoItemController@delete');
	Route::post('/mark/{id}', 'TodoItemController@mark');
});
