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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::get('external-books', 'BooksController@external_books');

Route::prefix('/v1')->group(function (){

    Route::post('/books', 'BooksController@create_book');
    Route::get('/books', 'BooksController@all_books');
    Route::patch('/books/{id}', 'BooksController@update_book')->where('id', '[0-9]+');
    Route::delete('/books/{id}', 'BooksController@delete_book')->where('id', '[0-9]+');
    Route::get('/books/{id}', 'BooksController@show_book')->where('id', '[0-9]+');

});
