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

Route::prefix('/community')->group(function () {
    Route::get('/categories', 'CategoryController@index')->name('categories.index');
    Route::get('/categories/create', 'CategoryController@create')->name('categories.create');
    Route::post('/categories', 'CategoryController@store')->name('categories.store');
    Route::get('/categories/{category}', 'CategoryController@show')->name('categories.show');
    Route::get('/categories/{category}/edit', 'CategoryController@edit')->name('categories.edit');
    Route::patch('/categories/{category}', 'CategoryController@update')->name('categories.update');
    // Route::delete('/categories/{category}/archive', 'CategoryController@delete')->name('categories.delete');
    Route::delete('/categories/{category}', 'CategoryController@destroy')->name('categories.destroy');

    Route::get('/threads/create', 'ThreadController@create')->name('threads.create');
    Route::post('/threads', 'ThreadController@store')->name('threads.store');
    Route::get('/{category?}', 'ThreadController@index')->name('threads.index');
    Route::get('/{category}/{thread}', 'ThreadController@show')->name('threads.show');
    Route::get('/{category}/{thread}/edit', 'ThreadController@edit')->name('threads.edit');
    Route::patch('/{category}/{thread}', 'ThreadController@update')->name('threads.update');
    // Route::delete('/{category}/{thread}/archive', 'ThreadController@delete')->name('threads.delete');
    Route::delete('/{category}/{thread}', 'ThreadController@destroy')->name('threads.destroy');

    Route::get('/{category}/{thread}/replies/create', 'ReplyController@create')->name('replies.create');
    Route::post('/{category}/{thread}/replies', 'ReplyController@store')->name('replies.store');
    Route::get('/{category}/{thread}/{reply}', 'ReplyController@show')->name('replies.show');
    Route::get('/{category}/{thread}/{reply}/edit', 'ReplyController@edit')->name('replies.edit');
    Route::patch('/{category}/{thread}/{reply}', 'ReplyController@update')->name('replies.update');
    // Route::delete('/{category}/{thread}/{reply}/archive', 'ReplyController@delete')->name('replies.delete');
    Route::delete('/{category}/{thread}/{reply}', 'ReplyController@destroy')->name('replies.destroy');
});
