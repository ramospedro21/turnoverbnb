<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// HOME ROUTE
Route::get('/home', 'HomeController@index')->name('home');

Route::prefix('/painel')->group(function(){

    // PRODUCTS ROUTE
    Route::get('/products', 'Painel\ProductsController@view');
    Route::get('/product', 'Painel\ProductsController@index');
    Route::post('/product', 'Painel\ProductsController@store');
    Route::post('/product/update', 'Painel\ProductsController@update');
    Route::delete('/product/{id}', 'Painel\ProductsController@destroy');

});