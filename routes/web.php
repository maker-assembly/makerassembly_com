<?php

use Illuminate\Support\Facades\Route;

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

Route::view('/', 'index');

Auth::routes(['verify' => true]);

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/profiles', 'ProfileController@index')->name('profiles.index');
Route::get('/profiles/@{user}', 'ProfileController@show')->name('profiles.show');
Route::patch('/profiles/{user}', 'ProfileController@update')->name('profiles.update');

// Route::get('/community', 'CommunityController')->name('community');
// Route::get('/community/{category}', 'CategoryController@show')->name('categories.show');

Route::get('/community/categories', 'CategoryController@index')->name('categories.index');
Route::get('/community/categories/create', 'CategoryController@create')->name('categories.create');
Route::post('/community/categories', 'CategoryController@store')->name('categories.store');
Route::get('/community/categories/{category}', 'CategoryController@show')->name('categories.show');
Route::get('/community/categories/{category}/edit', 'CategoryController@edit')->name('categories.edit');
Route::patch('/community/categories/{category}', 'CategoryController@update')->name('categories.update');
// Route::delete('/community/categories/{category}/archive', 'CategoryController@delete')->name('categories.delete');
Route::delete('/community/categories/{category}', 'CategoryController@destroy')->name('categories.destroy');
